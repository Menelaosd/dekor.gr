<?php
namespace Ho;
/**
 * HappyOnline shopping assistant — the conversation engine (catalog/controller/extension/ho_assistant.php is the shop
 * side: the tools, the order). Switched on by $_['ho_assistant_status'] in system/config/happyonline.php.
 *
 * The model never knows the shop by itself: every answer comes from tools that read the catalogue (prices, stock,
 * options, shipping, policies) at that moment. It cannot place an order on its own either: start_checkout only opens
 * the order form in the chat, and the customer's own «Confirm» places it. The customer's personal details are typed in
 * that form and go to the shop only — never to the model.
 *
 * Providers: OpenAI Chat Completions with tools; Anthropic Messages API (Claude models, «claude-…»); «mock» (no network,
 * no cost) answers by simple rules for tests. The conversation is kept in the OpenAI shape whatever the model, so the
 * model can change in the middle of a conversation; requestAnthropic() translates it for Claude.
 */
class Assistant {
	const ENDPOINT = 'https://api.openai.com/v1/chat/completions';
	const ANTHROPIC_ENDPOINT = 'https://api.anthropic.com/v1/messages';
	const ANTHROPIC_VERSION = '2026-10-21';

	// approximate USD per 1M tokens (input, output) — for the daily budget only
	private static $prices = array(
		'gpt-4.1-mini' => array(0.40, 1.60),
		'gpt-4o-mini'  => array(0.15, 0.60),
		'gpt-4.1'      => array(2.00, 8.00),
		'gpt-4o'       => array(2.50, 10.00),
		// Claude (Anthropic): Haiku is the cheap, fast one; Sonnet the stronger one. Haiku's rate holds for prompts up to
		// 100K tokens (the assistant's are a few thousand)
		'claude-haiku-5-5'  => array(0.10, 0.50),
		'claude-sonnet-5-5' => array(2.00, 10.00),
		'mock'         => array(0, 0)
	);

	private $key;
	private $model;
	private $timeout;

	public function __construct($key, $model = 'gpt-4.1-mini', $timeout = 40) {
		$this->key = (string)$key;
		$this->model = $model ?: 'gpt-4.1-mini';
		$this->timeout = $timeout;
	}

	// openai, anthropic or mock: which API (and which key) a model needs
	public static function provider($model) {
		if ($model == 'mock') {
			return 'mock';
		}

		return strpos((string)$model, 'claude-') === 0 ? 'anthropic' : 'openai';
	}

	public static function models() {
		return array_keys(array_diff_key(self::$prices, array('mock' => 1)));
	}

	public static function cost($model, $in, $out) {
		$p = isset(self::$prices[$model]) ? self::$prices[$model] : self::$prices['gpt-4.1-mini'];

		return ($in * $p[0] + $out * $p[1]) / 1000000;
	}

	// ------------------------------------------------------------------------------------------- tables

	// conversations (what the customer typed and saw, for the admin) and the daily usage (requests, tokens, cost)
	public static function install($db) {
		$db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ho_assistant_chat` (
			`chat_id` int(11) NOT NULL AUTO_INCREMENT,
			`customer_id` int(11) NOT NULL DEFAULT '0',
			`language` varchar(8) NOT NULL DEFAULT '',
			`turns` int(11) NOT NULL DEFAULT '0',
			`tokens_in` int(11) NOT NULL DEFAULT '0',
			`tokens_out` int(11) NOT NULL DEFAULT '0',
			`cost` decimal(10,5) NOT NULL DEFAULT '0.00000',
			`order_id` int(11) NOT NULL DEFAULT '0',
			`transcript` mediumtext NOT NULL,
			`date_added` datetime NOT NULL,
			`date_modified` datetime NOT NULL,
			PRIMARY KEY (`chat_id`),
			KEY `date_modified` (`date_modified`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

		$db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ho_assistant_usage` (
			`day` date NOT NULL,
			`requests` int(11) NOT NULL DEFAULT '0',
			`tokens_in` int(11) NOT NULL DEFAULT '0',
			`tokens_out` int(11) NOT NULL DEFAULT '0',
			`cost` decimal(10,5) NOT NULL DEFAULT '0.00000',
			PRIMARY KEY (`day`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}

	// ------------------------------------------------------------------------------------------- the API key at rest

	// the key is stored encrypted with a secret only this server has (config.php), never shown again in full
	public static function encrypt($plain) {
		$iv = random_bytes(16);

		return base64_encode($iv . openssl_encrypt((string)$plain, 'aes-256-cbc', self::secret(), OPENSSL_RAW_DATA, $iv));
	}

	public static function decrypt($stored) {
		$raw = base64_decode((string)$stored, true);

		if ($raw === false || strlen($raw) < 17) {
			return '';
		}

		$plain = openssl_decrypt(substr($raw, 16), 'aes-256-cbc', self::secret(), OPENSSL_RAW_DATA, substr($raw, 0, 16));

		return $plain === false ? '' : $plain;
	}

	private static function secret() {
		return hash('sha256', 'ho_assistant|' . (defined('DB_PASSWORD') ? DB_PASSWORD : '') . '|' . DB_DATABASE . '|' . DB_PREFIX, true);
	}

	// ------------------------------------------------------------------------------------------- the conversation

	/**
	 * One customer turn: the model may call tools (up to $rounds times) before it answers.
	 * $execute($name, $args) returns the tool's result (array, sent to the model as JSON).
	 *
	 * @return array text, messages (everything added this turn), tokens_in, tokens_out, error
	 */
	public function run(array $messages, array $tools, callable $execute, $rounds = 5) {
		$added = array();
		$in = 0;
		$out = 0;

		for ($i = 0; $i < $rounds; $i++) {
			$response = $this->request(array_merge($messages, $added), $tools);

			if (!empty($response['error'])) {
				return array('text' => '', 'messages' => self::plain($added), 'tokens_in' => $in, 'tokens_out' => $out, 'error' => $response['error']);
			}

			$in += $response['tokens_in'];
			$out += $response['tokens_out'];

			$message = $response['message'];

			if (empty($message['tool_calls'])) {
				$added[] = array('role' => 'assistant', 'content' => (string)$message['content']);

				return array('text' => (string)$message['content'], 'messages' => self::plain($added), 'tokens_in' => $in, 'tokens_out' => $out, 'error' => '');
			}

			// Claude: its own content blocks (with its thinking) are sent back unchanged while this turn's tools run
			$added[] = array('role' => 'assistant', 'content' => isset($message['content']) ? $message['content'] : null, 'tool_calls' => $message['tool_calls']) + (isset($message['_anthropic']) ? array('_anthropic' => $message['_anthropic']) : array());

			foreach ($message['tool_calls'] as $call) {
				$args = json_decode((string)$call['function']['arguments'], true);
				$result = $execute((string)$call['function']['name'], is_array($args) ? $args : array());

				$added[] = array('role' => 'tool', 'tool_call_id' => $call['id'], 'content' => json_encode($result, JSON_UNESCAPED_UNICODE));
			}
		}

		// still calling tools after every round: answer with what we have
		return array('text' => '', 'messages' => self::plain($added), 'tokens_in' => $in, 'tokens_out' => $out, 'error' => 'rounds');
	}

	// what the shop keeps in the session: the OpenAI shape only (no Claude blocks: smaller, and any model can read it)
	private static function plain(array $messages) {
		foreach ($messages as $i => $m) {
			unset($messages[$i]['_anthropic']);
		}

		return $messages;
	}

	/**
	 * One plain answer, no tools (the admin's «build the store profile»). The mock model has nothing to write:
	 * it returns error «mock» and the caller builds the text itself.
	 *
	 * @return array text, tokens_in, tokens_out, error
	 */
	public function write($system, $user, $max_tokens = 1800) {
		if ($this->model == 'mock') {
			return array('text' => '', 'tokens_in' => 0, 'tokens_out' => 0, 'error' => 'mock');
		}

		$response = $this->request(array(array('role' => 'system', 'content' => $system), array('role' => 'user', 'content' => $user)), array(), $max_tokens);

		if (!empty($response['error'])) {
			return array('text' => '', 'tokens_in' => 0, 'tokens_out' => 0, 'error' => $response['error']);
		}

		return array('text' => trim((string)$response['message']['content']), 'tokens_in' => $response['tokens_in'], 'tokens_out' => $response['tokens_out'], 'error' => '');
	}

	private function request(array $messages, array $tools, $max_tokens = 700) {
		if ($this->model == 'mock') {
			return $this->mock($messages);
		}

		if ($this->key === '' || !function_exists('curl_init')) {
			return array('error' => 'no_key');
		}

		if (self::provider($this->model) == 'anthropic') {
			return $this->requestAnthropic($messages, $tools, $max_tokens);
		}

		$body = array('model' => $this->model, 'messages' => self::plain($messages), 'temperature' => 0.3, 'max_tokens' => (int)$max_tokens);

		if ($tools) {
			$body['tools'] = $tools;
			$body['tool_choice'] = 'auto';
			$body['parallel_tool_calls'] = true;
		}

		$ch = curl_init(self::ENDPOINT);

		curl_setopt_array($ch, array(
			CURLOPT_POST           => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => $this->timeout,
			CURLOPT_CONNECTTIMEOUT => 8,
			CURLOPT_HTTPHEADER     => array('Content-Type: application/json', 'Authorization: Bearer ' . $this->key),
			CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE)
		));

		$raw = curl_exec($ch);
		$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($ch);

		curl_close($ch);

		$json = json_decode((string)$raw, true);

		if ($raw === false || $status >= 400 || !isset($json['choices'][0]['message'])) {
			// the reason for the admin log; the customer only sees «not available right now»
			$reason = $curl_error ?: (isset($json['error']['message']) ? $json['error']['message'] : 'HTTP ' . $status);

			return array('error' => mb_substr($reason, 0, 200));
		}

		return array(
			'message'    => $json['choices'][0]['message'],
			'tokens_in'  => isset($json['usage']['prompt_tokens']) ? (int)$json['usage']['prompt_tokens'] : 0,
			'tokens_out' => isset($json['usage']['completion_tokens']) ? (int)$json['usage']['completion_tokens'] : 0
		);
	}

	/**
	 * Claude, through the Anthropic Messages API (raw HTTP like the OpenAI side: the package has no Composer runtime for an
	 * SDK, and some stores run PHP 8.0). The OpenAI-shaped conversation is translated: system messages → `system`,
	 * tool calls → `tool_use` blocks, tool replies → `tool_result` blocks in one user message. The answer comes back in
	 * the OpenAI shape, with Claude's own blocks in `_anthropic` so the rest of the turn sends them back unchanged.
	 *
	 * Thinking stays on (adaptive) at low effort: quick answers for a chat, and the model still plans its tool calls.
	 * The long, stable part (tools + system prompt) is cached: `cache_control` on the system block and at the end of the
	 * conversation, so every tool round of a turn re-reads the conversation at the cached rate.
	 */
	private function requestAnthropic(array $messages, array $tools, $max_tokens) {
		$system = array();
		$conversation = array();

		foreach ($messages as $m) {
			if ($m['role'] == 'system') {
				$system[] = (string)$m['content'];

				continue;
			}

			if ($m['role'] == 'assistant') {
				$role = 'assistant';

				if (!empty($m['_anthropic'])) {
					$blocks = $m['_anthropic'];
				} else {
					$blocks = array();

					if (isset($m['content']) && trim((string)$m['content']) !== '') {
						$blocks[] = array('type' => 'text', 'text' => (string)$m['content']);
					}

					foreach (isset($m['tool_calls']) ? (array)$m['tool_calls'] : array() as $call) {
						$input = json_decode((string)$call['function']['arguments'], true);

						$blocks[] = array('type' => 'tool_use', 'id' => (string)$call['id'], 'name' => (string)$call['function']['name'], 'input' => is_array($input) && $input ? $input : new \stdClass());
					}
				}
			} elseif ($m['role'] == 'tool') {
				$role = 'user';
				$blocks = array(array('type' => 'tool_result', 'tool_use_id' => (string)$m['tool_call_id'], 'content' => (string)$m['content']));
			} else {
				$role = 'user';
				$text = trim((string)$m['content']);
				$blocks = $text !== '' ? array(array('type' => 'text', 'text' => $text)) : array();
			}

			if (!$blocks) {
				continue;
			}

			// one message per role in a row: a turn's tool results travel together, before any text
			$last = count($conversation) - 1;

			if ($last >= 0 && $conversation[$last]['role'] == $role) {
				$conversation[$last]['content'] = array_merge($conversation[$last]['content'], $blocks);
			} else {
				$conversation[] = array('role' => $role, 'content' => $blocks);
			}
		}

		// the conversation starts with the customer (an older turn trimmed away can leave an assistant reply first)
		while ($conversation && $conversation[0]['role'] != 'user') {
			array_shift($conversation);
		}

		$body = array(
			'model'         => $this->model,
			'max_tokens'    => max(8000, (int)$max_tokens * 4), // thinking counts here too
			'messages'      => $conversation,
			'thinking'      => array('type' => 'adaptive'),
			'output_config' => array('effort' => 'low'),
			'cache_control' => array('type' => 'ephemeral')
		);

		if ($system) {
			$body['system'] = array(array('type' => 'text', 'text' => implode("\n\n", $system), 'cache_control' => array('type' => 'ephemeral')));
		}

		if ($tools) {
			$body['tools'] = array();

			foreach ($tools as $tool) {
				$body['tools'][] = array('name' => $tool['function']['name'], 'description' => $tool['function']['description'], 'input_schema' => $tool['function']['parameters']);
			}
		}

		$headers = array('Content-Type: application/json', 'x-api-key: ' . $this->key, 'anthropic-version: ' . self::ANTHROPIC_VERSION);

		// Sonnet: when its safety classifier declines, the API retries on the model it picks (no list to maintain)
		if ($this->model == 'claude-sonnet-5-5') {
			$body['fallbacks'] = 'default';
			$headers[] = 'anthropic-beta: server-side-fallback-2026-07-01';
		}

		$ch = curl_init(self::ANTHROPIC_ENDPOINT);

		curl_setopt_array($ch, array(
			CURLOPT_POST           => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => $this->timeout,
			CURLOPT_CONNECTTIMEOUT => 8,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE)
		));

		$raw = curl_exec($ch);
		$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($ch);

		curl_close($ch);

		$json = json_decode((string)$raw, true);

		if ($raw === false || $status >= 400 || !isset($json['content']) || !is_array($json['content'])) {
			$reason = $curl_error ?: (isset($json['error']['message']) ? $json['error']['message'] : 'HTTP ' . $status);

			return array('error' => mb_substr($reason, 0, 200));
		}

		// a decline by the model's safety classifier: no answer to show
		if (isset($json['stop_reason']) && $json['stop_reason'] == 'refusal') {
			return array('error' => 'refusal' . (!empty($json['stop_details']['category']) ? ' (' . $json['stop_details']['category'] . ')' : ''));
		}

		$text = '';
		$calls = array();
		$blocks = $json['content'];

		foreach ($blocks as $i => $block) {
			if ($block['type'] == 'text') {
				$text .= $block['text'];
			} elseif ($block['type'] == 'tool_use') {
				// decoded as arrays, an empty input {} would go back as [] (not an object): keep it an object
				if (empty($block['input'])) {
					$blocks[$i]['input'] = new \stdClass();
				}

				$calls[] = array('id' => $block['id'], 'type' => 'function', 'function' => array('name' => $block['name'], 'arguments' => json_encode($block['input'] ?: new \stdClass(), JSON_UNESCAPED_UNICODE)));
			}
		}

		$message = array('role' => 'assistant', 'content' => $text, '_anthropic' => $blocks);

		if ($calls) {
			$message['tool_calls'] = $calls;
		}

		// for the daily budget, cached input counts at its price: writes 1.25×, reads 0.1× the input rate
		$usage = isset($json['usage']) ? $json['usage'] : array();
		$in = (isset($usage['input_tokens']) ? (int)$usage['input_tokens'] : 0)
			+ (int)round((isset($usage['cache_creation_input_tokens']) ? (int)$usage['cache_creation_input_tokens'] : 0) * 1.25)
			+ (int)round((isset($usage['cache_read_input_tokens']) ? (int)$usage['cache_read_input_tokens'] : 0) * 0.1);

		return array('message' => $message, 'tokens_in' => $in, 'tokens_out' => isset($usage['output_tokens']) ? (int)$usage['output_tokens'] : 0);
	}

	/**
	 * Tests without network and cost: product questions search, «add» puts the first product found in the cart,
	 * «order» opens the order form, shipping / payment / returns questions read the shop info.
	 */
	private function mock(array $messages) {
		$last = end($messages);
		$id = function () {
			return 'call_' . bin2hex(random_bytes(4));
		};
		$call = function ($name, $args) use ($id) {
			return array('message' => array('role' => 'assistant', 'content' => null, 'tool_calls' => array(array('id' => $id(), 'type' => 'function', 'function' => array('name' => $name, 'arguments' => json_encode($args, JSON_UNESCAPED_UNICODE))))), 'tokens_in' => 0, 'tokens_out' => 0);
		};
		$say = function ($text) {
			return array('message' => array('role' => 'assistant', 'content' => $text), 'tokens_in' => 0, 'tokens_out' => 0);
		};

		if ($last['role'] == 'tool') {
			$result = json_decode($last['content'], true);

			if (isset($result['products'])) {
				// what was asked: the arguments of the search that gave this result
				$asked = array();

				for ($i = count($messages) - 1; $i >= 0; $i--) {
					if ($messages[$i]['role'] == 'assistant' && !empty($messages[$i]['tool_calls'])) {
						$asked = json_decode($messages[$i]['tool_calls'][0]['function']['arguments'], true) ?: array();

						break;
					}
				}

				$range = !empty($asked['max_price']) ? ' έως ' . (float)$asked['max_price'] . '€' : (!empty($asked['min_price']) ? ' από ' . (float)$asked['min_price'] . '€ και πάνω' : '');
				$what = !empty($asked['on_sale']) ? 'σε προσφορά' : (!empty($asked['query']) ? 'για «' . $asked['query'] . '»' : '');

				if (!$result['products']) {
					return $say('Δεν βρήκα κάτι ' . trim($what . $range) . '. Πείτε μου λίγο περισσότερα (τι είδος, για ποιον, μέχρι πόσα θέλετε να δώσετε) και ψάχνω ξανά.');
				}

				$n = !empty($result['cards_shown']) ? (int)$result['cards_shown'] : count($result['products']);
				$first = $result['products'][0];

				return $say(($n == 1 ? 'Βρήκα ένα προϊόν ' : 'Βρήκα ' . $n . ' προϊόντα ') . trim($what . $range) . '. '
					. ($n > 1 ? 'Ξεκινήστε από το **' . $first['name'] . '** (' . $first['price'] . ') και δείτε και τα υπόλοιπα δίπλα του. ' : '')
					. 'Πατήστε «Στο καλάθι» σε όποιο σας αρέσει ή ρωτήστε με για κάποιο.');
			}

			// get_product: a few lines about it
			if (isset($result['description'], $result['name'], $result['price'])) {
				$lines = array('**' . $result['name'] . '** · ' . $result['price'] . '.');

				foreach (array('availability', 'delivery_time', 'stock_text') as $k) {
					if (!empty($result[$k])) {
						$lines[] = $result[$k];
					}
				}

				if ($result['description'] !== '') {
					$lines[] = mb_substr($result['description'], 0, 260, 'UTF-8') . (mb_strlen($result['description'], 'UTF-8') > 260 ? '…' : '');
				}

				return $say(implode("\n", $lines));
			}

			if (isset($result['error'])) {
				return $say('Δεν έγινε: ' . $result['error']);
			}

			if (isset($result['checkout'])) {
				return $say('Συμπληρώστε τα στοιχεία σας στη φόρμα για να ολοκληρωθεί η παραγγελία.');
			}

			if (isset($result['cart'])) {
				return $say('Το έβαλα στο καλάθι. Σύνολο: ' . $result['cart']['total'] . '.');
			}

			// store information: say what the store answered, not just «here it is»
			$lines = array();

			if (!empty($result['methods'])) {
				foreach ($result['methods'] as $method) {
					$lines[] = is_array($method) ? '- **' . $method['name'] . '**' . (!empty($method['cost']) ? ': ' . $method['cost'] : '') : '- ' . $method;
				}
			}

			if (isset($result['general']['how'])) {
				// shipping_cost: the cart, the product asked about, how it is charged in general
				foreach (array('in_cart' => 'Για τα προϊόντα στο καλάθι σας', 'with_product' => 'Μαζί με το προϊόν που ρωτήσατε') as $part => $label) {
					if (!empty($result[$part]['options'])) {
						$names = array();

						foreach ($result[$part]['products'] as $product) {
							$names[] = $product['name'] . ' × ' . $product['quantity'];
						}

						$lines[] = $label . ' (' . implode(', ', $names) . '), Τ.Κ. ' . $result['postcode'] . ':';

						foreach ($result[$part]['options'] as $option) {
							$lines[] = '- **' . $option['name'] . '**: ' . $option['cost'];
						}
					}
				}

				$lines[] = 'Γενικά: τα μεταφορικά υπολογίζονται για κάθε παραγγελία από τον Τ.Κ. και τα προϊόντα της.';
			} elseif (!empty($result['cost']) && is_string($result['cost'])) {
				$lines[] = 'Το κόστος εξαρτάται από τον Τ.Κ. και τα προϊόντα: πείτε μου τον Τ.Κ. σας.';
			}

			if (!empty($result['free_shipping_from'])) {
				$lines[] = 'Δωρεάν αποστολή για παραγγελίες από ' . $result['free_shipping_from'] . '.';
			}

			if (!empty($result['telephone']) || !empty($result['email'])) {
				$lines[] = trim((!empty($result['telephone']) ? 'Τηλέφωνο: ' . $result['telephone'] . '  ' : '') . (!empty($result['email']) ? 'E-mail: ' . $result['email'] : ''));
			}

			$page = isset($result['page']) && is_array($result['page']) ? $result['page'] : (isset($result['text']) && is_array($result['text']) ? $result['text'] : array());

			if (!empty($page['text']) && mb_strlen($page['text'], 'UTF-8') > 60) {
				$lines[] = mb_substr($page['text'], 0, 280, 'UTF-8') . (mb_strlen($page['text'], 'UTF-8') > 280 ? '… [Όλη η σελίδα](' . $page['url'] . ')' : '');
			}

			return $say($lines ? implode("
", $lines) : 'Δεν έχω αυτή την πληροφορία εδώ. Για λεπτομέρειες επικοινωνήστε με το κατάστημα.');
		}

		$text = mb_strtolower((string)$last['content'], 'UTF-8');

		// a quick answer to the filter question (a value of the latest search's refine): the same search with that filter
		for ($i = count($messages) - 1; $i >= 0; $i--) {
			if ($messages[$i]['role'] == 'tool') {
				$r = json_decode($messages[$i]['content'], true);

				if (!empty($r['refine'])) {
					foreach ($r['refine'] as $values) {
						foreach (array_keys($values) as $value) {
							if (mb_strtolower(trim($value), 'UTF-8') === trim($text)) {
								for ($j = $i - 1; $j >= 0; $j--) {
									if ($messages[$j]['role'] == 'assistant' && !empty($messages[$j]['tool_calls'])) {
										$prev = json_decode($messages[$j]['tool_calls'][0]['function']['arguments'], true) ?: array();
										$prev['filters'] = array_merge(isset($prev['filters']) ? (array)$prev['filters'] : array(), array($value));

										return $call('search_products', $prev);
									}
								}
							}
						}
					}
				}

				break;
			}
		}

		// «Πληροφορίες» on a card
		if (preg_match('/product_id (\d+)/u', $text, $pid)) {
			return $call('get_product', array('product_id' => (int)$pid[1]));
		}

		if (preg_match('/παραγγελ|ολοκλήρωσ|order|checkout/u', $text)) {
			return $call('start_checkout', array());
		}

		if (preg_match('/καλάθι|βάλε|πρόσθεσε|add to cart/u', $text)) {
			// the first product of the latest search
			for ($i = count($messages) - 1; $i >= 0; $i--) {
				if ($messages[$i]['role'] == 'tool') {
					$r = json_decode($messages[$i]['content'], true);

					if (!empty($r['products'])) {
						return $call('add_to_cart', array('product_id' => $r['products'][0]['product_id'], 'quantity' => 1));
					}
				}
			}

			return $say('Ποιο προϊόν θέλετε;');
		}

		if (preg_match('/αποστολ|μεταφορικ|shipping/u', $text)) {
			// with a postcode, the exact costs for it (for the first product of the latest search too, if there was one)
			if (preg_match('/\b(\d{3}\s?\d{2})\b/u', $text, $pc)) {
				$args = array('postcode' => $pc[1]);

				for ($i = count($messages) - 1; $i >= 0; $i--) {
					if ($messages[$i]['role'] == 'tool') {
						$r = json_decode($messages[$i]['content'], true);

						if (!empty($r['products'])) {
							$args['product_id'] = $r['products'][0]['product_id'];

							break;
						}
					}
				}

				return $call('shipping_cost', $args);
			}

			return $call('store_info', array('topic' => 'shipping'));
		}

		if (preg_match('/πληρωμ|πληρώ|πληρω|κάρτα|καρτα|payment|pay\b/u', $text)) {
			return $call('store_info', array('topic' => 'payment'));
		}

		if (preg_match('/επιστροφ|return/u', $text)) {
			return $call('store_info', array('topic' => 'returns'));
		}

		// a search: budget, offers and the words that are not just «I want / a gift / up to»
		$args = array();

		if (preg_match('/(?:έως|εως|μέχρι|μεχρι|κάτω από|κατω απο|ως|under|up to|max)\s*(\d+(?:[.,]\d+)?)/u', $text, $m)) {
			$args['max_price'] = (float)str_replace(',', '.', $m[1]);
		} elseif (preg_match('/(?:πάνω από|πανω απο|over|from)\s*(\d+(?:[.,]\d+)?)/u', $text, $m)) {
			$args['min_price'] = (float)str_replace(',', '.', $m[1]);
		}

		if (preg_match('/προσφορ|έκπτωσ|εκπτωσ|sale|offer|deal/u', $text)) {
			$args['on_sale'] = true;
		}

		$words = preg_replace('/(?:έως|εως|μέχρι|μεχρι|κάτω από|κατω απο|πάνω από|πανω απο|under|up to|over|from)\s*\d+(?:[.,]\d+)?/u', ' ', $text);
		$words = preg_replace('/[^\p{L}\p{N} ]+|\d+/u', ' ', $words);
		$stop = array('ψάχνω', 'ψαχνω', 'θέλω', 'θελω', 'έχετε', 'εχετε', 'τι', 'κάτι', 'κατι', 'ένα', 'ενα', 'μια', 'μία', 'για', 'σε', 'το', 'τα', 'την', 'τον', 'η', 'ο', 'οι', 'και', 'με', 'δώρο', 'δωρο', 'δώρα', 'ευρώ', 'ευρω', 'προσφορά', 'προσφορα', 'προσφορές', 'προσφορες', 'μου', 'μας', 'είναι', 'ειναι', 'πρότεινε', 'προτεινε', 'προτείνετε', 'δείξε', 'δειξε', 'i', 'a', 'an', 'the', 'for', 'gift', 'something', 'want', 'need', 'looking', 'show', 'me', 'what', 'do', 'you', 'have', 'on', 'eur', 'euro');
		$args['query'] = trim(implode(' ', array_diff(preg_split('/\s+/u', trim($words), -1, PREG_SPLIT_NO_EMPTY), $stop)));

		// product codes / barcodes stay whole («HM3290.01», «5208067199230»), not cut as prices
		if (preg_match_all('~(?<![\p{L}\d])[a-z0-9][a-z0-9.\-/]*\d[a-z0-9.\-/]*~u', $text, $codes)) {
			foreach ($codes[0] as $code) {
				if (strlen(preg_replace('/[^a-z0-9]/', '', $code)) >= 4 && empty($args['max_price']) && empty($args['min_price'])) {
					$args['query'] = trim($args['query'] . ' ' . rtrim($code, '.-/'));
				}
			}
		}

		if (!empty($args['max_price']) && $args['query'] === '') {
			$args['sort'] = 'popular';
		}

		return $call('search_products', $args);
	}
}
