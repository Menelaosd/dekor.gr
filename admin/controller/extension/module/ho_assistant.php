<?php
/**
 * HO Shopping Assistant — the AI chat of the storefront (system/library/ho/assistant.php, catalog extension/ho_assistant).
 * Settings: OpenAI key (stored encrypted), model, name / greeting / suggestions per language, owner's notes, daily
 * budget, messages per visitor per hour, payments the chat may complete, conversation retention; usage and the log.
 * On / off only with $_['ho_assistant_status'] in system/config/happyonline.php (like the other ho_* features).
 */
class ControllerExtensionModuleHoAssistant extends Controller {
	private $error = array();

	public function index() {
		// dekor: no happyonline.php switch here; installed by upgrade-3.0.5.1/ho_assistant_install.php, live once a key is set

		$this->load->language('extension/module/ho_assistant');
		$this->load->model('setting/setting');
		$this->load->model('localisation/language');

		$this->document->setTitle($this->language->get('heading_title'));

		\Ho\Assistant::install($this->db);
		\Ho\Assistant::upgrade($this->db);

		// dekor: one switch / test customer / texts / profile / notes / chat payments per store; key, model, limits common (store 0)
		$stores = $this->stores();
		$store_id = isset($this->request->get['store_id']) && isset($stores[(int)$this->request->get['store_id']]) ? (int)$this->request->get['store_id'] : 0;
		$store_keys = array('status', 'only_customer', 'name', 'greeting', 'suggestions', 'instructions', 'profile', 'payments');

		$current = $this->model_setting_setting->getSetting('module_ho_assistant');

		if ($store_id) {
			foreach ($store_keys as $k) {
				unset($current['module_ho_assistant_' . $k]);
			}

			$current = array_merge($current, $this->model_setting_setting->getSetting('module_ho_assistant', $store_id));
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
			$post = $this->request->post;
			$save = array(
				'module_ho_assistant_status'       => !empty($post['status']) ? 1 : 0,
				// tests on a live store: only this logged-in customer sees the chat (empty = everyone)
				'module_ho_assistant_only_customer' => isset($post['only_customer']) ? mb_substr(trim((string)$post['only_customer']), 0, 96) : '',
				'module_ho_assistant_model'        => in_array(isset($post['model']) ? $post['model'] : '', array_merge(\Ho\Assistant::models(), array('mock'))) ? $post['model'] : (isset($current['module_ho_assistant_model']) ? $current['module_ho_assistant_model'] : 'gpt-4.1-mini'),
				'module_ho_assistant_name'         => isset($post['name']) ? (array)$post['name'] : array(),
				'module_ho_assistant_greeting'     => isset($post['greeting']) ? (array)$post['greeting'] : array(),
				'module_ho_assistant_suggestions'  => isset($post['suggestions']) ? (array)$post['suggestions'] : array(),
				'module_ho_assistant_instructions' => isset($post['instructions']) ? mb_substr((string)$post['instructions'], 0, 3000) : '',
				'module_ho_assistant_profile'      => isset($post['profile']) ? mb_substr(html_entity_decode((string)$post['profile'], ENT_QUOTES, 'UTF-8'), 0, 8000) : '',
				'module_ho_assistant_budget'       => max(0, (float)(isset($post['budget']) ? $post['budget'] : 5)),
				'module_ho_assistant_rate'         => max(1, (int)(isset($post['rate']) ? $post['rate'] : 30)),
				'module_ho_assistant_payments'     => isset($post['payments']) ? array_values((array)$post['payments']) : array(),
				'module_ho_assistant_retention'    => max(1, (int)(isset($post['retention']) ? $post['retention'] : 90)),
				// a new key only when one is typed; the stored one is never sent back to the page
				'module_ho_assistant_key'          => isset($post['key']) && trim($post['key']) !== '' ? \Ho\Assistant::encrypt(trim(html_entity_decode($post['key'], ENT_QUOTES, 'UTF-8'))) : (isset($current['module_ho_assistant_key']) ? $current['module_ho_assistant_key'] : ''),
				'module_ho_assistant_anthropic_key' => isset($post['anthropic_key']) && trim($post['anthropic_key']) !== '' ? \Ho\Assistant::encrypt(trim(html_entity_decode($post['anthropic_key'], ENT_QUOTES, 'UTF-8'))) : (isset($current['module_ho_assistant_anthropic_key']) ? $current['module_ho_assistant_anthropic_key'] : '')
			);

			if (!empty($post['key_remove'])) {
				$save['module_ho_assistant_key'] = '';
			}

			if (!empty($post['anthropic_key_remove'])) {
				$save['module_ho_assistant_anthropic_key'] = '';
			}

			if ($store_id) {
				// another store: only its own part (the common part stays as the first store has it)
				$own = array();

				foreach ($store_keys as $k) {
					$own['module_ho_assistant_' . $k] = $save['module_ho_assistant_' . $k];
				}

				$this->model_setting_setting->editSetting('module_ho_assistant', $own, $store_id);
			} else {
				$this->model_setting_setting->editSetting('module_ho_assistant', $save);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/ho_assistant', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id, true));
		}

		$data = array();

		foreach ($this->language->all() as $key => $value) {
			$data[$key] = $value;
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['success'] = isset($this->session->data['success']) ? $this->session->data['success'] : '';
		unset($this->session->data['success']);

		$data['breadcrumbs'] = array(
			array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)),
			array('text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)),
			array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/module/ho_assistant', 'user_token=' . $this->session->data['user_token'], true))
		);

		$data['action'] = $this->url->link('extension/module/ho_assistant', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id, true);

		$data['store_id'] = $store_id;
		$data['stores'] = array();

		foreach ($stores as $s) {
			$data['stores'][] = array('store_id' => $s['store_id'], 'name' => $s['name'], 'href' => $this->url->link('extension/module/ho_assistant', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $s['store_id'], true));
		}

		$data['status'] = isset($current['module_ho_assistant_status']) ? (int)$current['module_ho_assistant_status'] : ($store_id ? 0 : 1);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		$data['chat_url'] = html_entity_decode($this->url->link('extension/module/ho_assistant/chat', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8');

		$data['switch_on'] = (bool)$data['status'];
		$data['only_customer'] = isset($current['module_ho_assistant_only_customer']) ? $current['module_ho_assistant_only_customer'] : '';

		// the test customer must exist, or nobody sees the chat
		$data['only_customer_missing'] = $data['only_customer'] !== '' && !$this->db->query("SELECT `customer_id` FROM `" . DB_PREFIX . "customer` WHERE LCASE(`email`) = '" . $this->db->escape(utf8_strtolower(html_entity_decode($data['only_customer'], ENT_QUOTES, 'UTF-8'))) . "'")->num_rows;

		$get = function ($key, $default) use ($current) {
			return isset($current['module_ho_assistant_' . $key]) ? $current['module_ho_assistant_' . $key] : $default;
		};

		$data['model'] = $get('model', 'gpt-4.1-mini');
		$data['models'] = \Ho\Assistant::models();
		$data['names'] = $get('name', array());
		$data['greetings'] = $get('greeting', array());
		$data['suggestions'] = $get('suggestions', array());
		$data['instructions'] = $get('instructions', '');
		$data['profile'] = $get('profile', '');
		$data['profile_url'] = html_entity_decode($this->url->link('extension/module/ho_assistant/profile', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id, true), ENT_QUOTES, 'UTF-8');
		$data['budget'] = $get('budget', 5);
		$data['rate'] = $get('rate', 30);
		$data['payments'] = $get('payments', array('bank_transfer'));
		$data['retention'] = $get('retention', 90);

		$key = \Ho\Assistant::decrypt($get('key', ''));
		$data['key_saved'] = $key !== '' ? '••••••••' . substr($key, -4) : '';

		// a key in system/config/happyonline.php wins: the admin field is then only informative
		$file_key = trim((string)$this->config->get('ho_assistant_openai_key'));
		$data['key_file'] = $file_key !== '' ? '••••••••' . substr($file_key, -4) : '';

		// Claude models: the Anthropic key (a config file wins here too)
		$anthropic_key = \Ho\Assistant::decrypt($get('anthropic_key', ''));
		$data['anthropic_key_saved'] = $anthropic_key !== '' ? '••••••••' . substr($anthropic_key, -4) : '';
		$anthropic_file = trim((string)$this->config->get('ho_assistant_anthropic_key'));
		$data['anthropic_key_file'] = $anthropic_file !== '' ? '••••••••' . substr($anthropic_file, -4) : '';

		// what the shop needs besides the switch: the key of the chosen model's provider, or the «mock» model of the demo / test installs
		$provider = \Ho\Assistant::provider($data['model']);
		$data['ready'] = $provider == 'mock' || ($provider == 'anthropic' ? $anthropic_file !== '' || $anthropic_key !== '' : $file_key !== '' || $key !== '');
		$data['provider'] = $provider;

		$data['languages'] = $this->model_localisation_language->getLanguages();

		// payments the chat may complete: the enabled ones without a card / bank page in between
		$this->load->model('setting/extension');

		$data['payment_methods'] = array();

		foreach ($this->model_setting_extension->getInstalled('payment') as $code) {
			if ($this->config->get('payment_' . $code . '_status')) {
				$this->load->language('extension/payment/' . $code, 'pm');

				$data['payment_methods'][] = array('code' => $code, 'name' => strip_tags($this->language->get('pm')->get('heading_title')), 'offline' => in_array($code, array('cod', 'bank_transfer', 'cheque', 'free_checkout')));
			}
		}

		// usage: today, this month; the latest conversations
		$data['today'] = $this->db->query("SELECT COALESCE(SUM(`requests`), 0) AS requests, COALESCE(SUM(`cost`), 0) AS cost FROM `" . DB_PREFIX . "ho_assistant_usage` WHERE `day` = CURDATE()")->row;
		$data['month'] = $this->db->query("SELECT COALESCE(SUM(`requests`), 0) AS requests, COALESCE(SUM(`cost`), 0) AS cost FROM `" . DB_PREFIX . "ho_assistant_usage` WHERE `day` >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->row;
		$data['orders'] = (int)$this->db->query("SELECT COUNT(*) AS n FROM `" . DB_PREFIX . "ho_assistant_chat` WHERE `store_id` = '" . $store_id . "' AND `order_id` > 0 AND `date_added` >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->row['n'];
		$data['chats'] = array();

		foreach ($this->db->query("SELECT `chat_id`, `customer_id`, `turns`, `cost`, `order_id`, `date_added`, `date_modified`, `transcript` FROM `" . DB_PREFIX . "ho_assistant_chat` WHERE `store_id` = '" . $store_id . "' ORDER BY `date_modified` DESC LIMIT 30")->rows as $row) {
			$log = json_decode($row['transcript'], true) ?: array();
			$first = '';

			foreach ($log as $m) {
				if ($m['role'] == 'user') {
					$first = $m['text'];

					break;
				}
			}

			$data['chats'][] = array(
				'chat_id'  => (int)$row['chat_id'],
				'date'     => date('d/m/Y H:i', strtotime($row['date_modified'])),
				'turns'    => (int)$row['turns'],
				'cost'     => '$' . number_format((float)$row['cost'], 4),
				'order_id' => (int)$row['order_id'],
				'order'    => $row['order_id'] ? $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$row['order_id'], true) : '',
				'first'    => htmlspecialchars(mb_substr($first, 0, 90), ENT_QUOTES, 'UTF-8') // what a customer typed: never raw HTML in the admin
			);
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ho_assistant', $data));
	}

	/**
	 * «Build the store profile»: reads what the store really is (catalogue, brands, prices, best sellers, options,
	 * pages, shipping / payment) and writes the «This store» part of the assistant's instructions — with the AI when
	 * there is a key, else straight from the data. Saved at once; the owner can edit it afterwards.
	 */
	public function profile() {
		$this->load->language('extension/module/ho_assistant');

		$json = array();

		if (!$this->user->hasPermission('modify', 'extension/module/ho_assistant')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			\Ho\Assistant::install($this->db);

			$this->load->model('setting/setting');

			$current = $this->model_setting_setting->getSetting('module_ho_assistant');
			$stores = $this->stores();
			$store_id = isset($this->request->get['store_id']) && isset($stores[(int)$this->request->get['store_id']]) ? (int)$this->request->get['store_id'] : 0;
			$model = isset($current['module_ho_assistant_model']) ? $current['module_ho_assistant_model'] : 'gpt-4.1-mini';
			$anthropic = \Ho\Assistant::provider($model) == 'anthropic';
			$file_key = trim((string)$this->config->get($anthropic ? 'ho_assistant_anthropic_key' : 'ho_assistant_openai_key'));
			$stored = $anthropic ? 'module_ho_assistant_anthropic_key' : 'module_ho_assistant_key';
			$key = $file_key !== '' ? $file_key : \Ho\Assistant::decrypt(isset($current[$stored]) ? $current[$stored] : '');

			$facts = $this->facts($store_id);
			$text = '';
			$json['source'] = 'data';

			if ($model != 'mock' && $key !== '') {
				$engine = new \Ho\Assistant($key, $model, 90);
				$result = $engine->write($this->profileInstructions(), $facts, 2200);

				if ($result['error'] === '' && $result['text'] !== '') {
					$text = $result['text'];
					$json['source'] = 'ai';

					$cost = \Ho\Assistant::cost($model, $result['tokens_in'], $result['tokens_out']);

					$this->db->query("INSERT INTO `" . DB_PREFIX . "ho_assistant_usage` SET `day` = CURDATE(), `requests` = 1, `tokens_in` = '" . (int)$result['tokens_in'] . "', `tokens_out` = '" . (int)$result['tokens_out'] . "', `cost` = '" . (float)$cost . "' ON DUPLICATE KEY UPDATE `requests` = `requests` + 1, `tokens_in` = `tokens_in` + VALUES(`tokens_in`), `tokens_out` = `tokens_out` + VALUES(`tokens_out`), `cost` = `cost` + VALUES(`cost`)");
				} else {
					$json['warning'] = sprintf($this->language->get('text_profile_ai_failed'), $result['error']);
				}
			}

			// no key / test model / the AI failed: the facts themselves, under clear headings
			if ($text === '') {
				$text = $this->language->get('text_profile_data_intro') . "\n\n" . $facts;
			}

			$text = mb_substr($text, 0, 8000, 'UTF-8');

			$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . $store_id . "' AND `code` = 'module_ho_assistant' AND `key` = 'module_ho_assistant_profile'");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '" . $store_id . "', `code` = 'module_ho_assistant', `key` = 'module_ho_assistant_profile', `value` = '" . $this->db->escape($text) . "', `serialized` = '0'");

			$json['profile'] = $text;
			$json['success'] = $this->language->get($json['source'] == 'ai' ? 'text_profile_done_ai' : 'text_profile_done_data');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
	}

	// what the AI is asked to write: the store-specific part only (the core rules are fixed in the shop's code)
	private function profileInstructions() {
		$this->load->model('localisation/language');

		$language = $this->model_localisation_language->getLanguage((int)$this->config->get('config_language_id'));
		$language_name = $language ? $language['name'] : 'English';

		return "You design conversational shopping assistants for online stores. From the store data you are given, write the «This store» section of the assistant's instructions: the knowledge a top salesperson of THIS store would carry in their head. The assistant already has fixed core rules (tools for live prices and stock, never invent facts, order form, privacy), so do not repeat them.\n\n"
			. "Write in " . $language_name . ", in plain Markdown with these headings (### level), about 350-550 words in total:\n"
			. "### Identity — 2-3 sentences: what kind of store this is, what it sells, for whom, its price positioning (budget / mid / premium, from the price spread), its strongest brands.\n"
			. "### Tone — how to speak to this store's customers (e.g. expert and precise for electronics, warm and stylish for fashion, caring for cosmetics or kids), in 2-3 bullets.\n"
			. "### Where to look — one bullet per main category, with its category_id: what is inside, notable brands, typical price band, and the everyday words, uses, problems and gift intents customers would say that should lead there (synonyms, slang, misspellings, both languages if the store has two). This is the most important section: it is how vague requests become searches.\n"
			. "### How to recommend here — 4-6 bullets specific to this kind of products: the questions that matter (size and fit, use and specs, skin type, age, compatibility …), what to compare, natural companion products between the store's categories (cross-sell pairs), and how to handle a budget.\n"
			. "### Customer questions to expect — 4-6 bullets: the likely questions for this store and where the answer comes from (which tool or which information page by its title). Never state a policy, cost or delivery time yourself.\n"
			. "### Careful — 2-4 bullets on what must not be claimed or promised for this store (e.g. compatibility without checking specs, medical claims, stock without checking).\n\n"
			. "Use ONLY the data given; where it is silent, stay general. No prices of individual products (they change), no invented policies, discounts, delivery times or contact details. Output only the section, starting with the first heading.";
	}

	// the store as data: the input of the profile (and the profile itself when there is no AI)
	// dekor: the stores (0 = the main one)
	private function stores() {
		$stores = array(0 => array('store_id' => 0, 'name' => html_entity_decode((string)$this->config->get('config_name'), ENT_QUOTES, 'UTF-8')));

		foreach ($this->db->query("SELECT `store_id`, `name` FROM `" . DB_PREFIX . "store` ORDER BY `store_id`")->rows as $row) {
			$stores[(int)$row['store_id']] = array('store_id' => (int)$row['store_id'], 'name' => html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8'));
		}

		return $stores;
	}

	private function facts($store_id = 0) {
		$store_id = (int)$store_id;
		$db = $this->db;
		$p = DB_PREFIX;
		$lang = (int)$this->config->get('config_language_id');
		$currency = $this->config->get('config_currency');
		$lines = array();
		$one = function ($sql) use ($db) {
			$row = $db->query($sql)->row;

			return $row ? reset($row) : null;
		};
		$setting = function ($key) use ($one, $p, $store_id, $lang) {
			$value = $one("SELECT `value` FROM `" . $p . "setting` WHERE `store_id` IN (0, " . $store_id . ") AND `key` = '" . $key . "' ORDER BY `store_id` DESC LIMIT 1");

			// dekor keeps some texts per language ({"2": "…"}): the admin language's one
			$list = is_string($value) && substr(ltrim($value), 0, 1) == '{' ? json_decode($value, true) : null;

			if (is_array($list)) {
				$value = isset($list[$lang]) ? $list[$lang] : (string)reset($list);
			}

			return trim(html_entity_decode(strip_tags((string)$value), ENT_QUOTES, 'UTF-8'));
		};
		$money = function ($value) use ($currency) {
			return number_format((float)$value, 2, ',', '.') . ' ' . $currency;
		};
		$names = function ($sql) use ($db) {
			$out = array();

			foreach ($db->query($sql)->rows as $row) {
				$out[] = html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8');
			}

			return $out;
		};

		// the store
		$lines[] = '## Store';
		$lines[] = '- Name: ' . $setting('config_name') . ($setting('config_meta_title') !== '' && $setting('config_meta_title') != $setting('config_name') ? ' — ' . $setting('config_meta_title') : '');

		foreach (array('config_meta_description' => 'Description', 'config_comment' => 'Note', 'config_address' => 'Location', 'config_open' => 'Opening hours') as $key => $label) {
			if ($setting($key) !== '') {
				$lines[] = '- ' . $label . ': ' . preg_replace('/\s+/u', ' ', mb_substr($setting($key), 0, 300, 'UTF-8'));
			}
		}

		$lines[] = '- Languages: ' . implode(', ', $names("SELECT `name` FROM `" . $p . "language` WHERE `status` = '1' ORDER BY `sort_order`")) . ' · currency ' . $currency;

		// the products: how many, what prices
		$products = " FROM `" . $p . "product` p JOIN `" . $p . "product_to_store` p2s ON (p2s.product_id = p.product_id AND p2s.store_id = '" . $store_id . "') JOIN `" . $p . "product_description` pd ON (pd.product_id = p.product_id AND pd.language_id = '" . $lang . "')";
		$active = " WHERE p.status = '1' AND p.date_available <= NOW()";
		$prices = array();

		foreach ($db->query("SELECT p.price" . $products . $active . " AND p.price > 0 ORDER BY p.price LIMIT 50000")->rows as $row) {
			$prices[] = (float)$row['price'];
		}

		$count = count($prices);
		$at = function ($q) use ($prices, $count) {
			return $count ? $prices[min($count - 1, (int)floor($q * ($count - 1)))] : 0;
		};

		$lines[] = '';
		$lines[] = '## Catalogue';
		$lines[] = '- Active products: ' . (int)$one("SELECT COUNT(*)" . $products . $active);

		if ($count) {
			$lines[] = '- Prices (before tax): from ' . $money($at(0)) . ' · a quarter under ' . $money($at(.25)) . ' · median ' . $money($at(.5)) . ' · a quarter over ' . $money($at(.75)) . ' · up to ' . $money($at(1));
		}

		$specials = (int)$one("SELECT COUNT(DISTINCT ps.product_id) FROM `" . $p . "product_special` ps JOIN `" . $p . "product_to_store` ps2s ON (ps2s.product_id = ps.product_id AND ps2s.store_id = '" . $store_id . "') WHERE (ps.date_start = '0000-00-00' OR ps.date_start <= NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())");

		if ($specials) {
			$lines[] = '- Products on offer now: ' . $specials;
		}

		// categories with how many active products each
		$cats = $db->query("SELECT c.category_id, c.parent_id, cd.name, (SELECT COUNT(*) FROM `" . $p . "product_to_category` p2c JOIN `" . $p . "product` pp ON (pp.product_id = p2c.product_id AND pp.status = '1') WHERE p2c.category_id = c.category_id) AS n FROM `" . $p . "category` c JOIN `" . $p . "category_to_store` c2s ON (c2s.category_id = c.category_id AND c2s.store_id = '" . $store_id . "') JOIN `" . $p . "category_description` cd ON (cd.category_id = c.category_id AND cd.language_id = '" . $lang . "') WHERE c.status = '1' ORDER BY c.sort_order, cd.name")->rows;
		$children = array();

		foreach ($cats as $c) {
			$children[(int)$c['parent_id']][] = $c;
		}

		$lines[] = '';
		$lines[] = '## Categories (category_id: name, active products)';

		foreach (isset($children[0]) ? $children[0] : array() as $c) {
			$sub = array();

			foreach (isset($children[(int)$c['category_id']]) ? $children[(int)$c['category_id']] : array() as $child) {
				$sub[] = $child['category_id'] . ': ' . html_entity_decode($child['name'], ENT_QUOTES, 'UTF-8') . ' (' . (int)$child['n'] . ')';
			}

			$lines[] = '- ' . $c['category_id'] . ': ' . html_entity_decode($c['name'], ENT_QUOTES, 'UTF-8') . ' (' . (int)$c['n'] . ')' . ($sub ? ' › ' . implode(', ', $sub) : '');
		}

		// brands
		$brands = array();

		foreach ($db->query("SELECT m.name, COUNT(*) AS n" . $products . " JOIN `" . $p . "manufacturer` m ON (m.manufacturer_id = p.manufacturer_id)" . $active . " GROUP BY m.manufacturer_id ORDER BY n DESC LIMIT 30")->rows as $row) {
			$brands[] = html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8') . ' (' . (int)$row['n'] . ')';
		}

		if ($brands) {
			$lines[] = '';
			$lines[] = '## Brands (products)';
			$lines[] = '- ' . implode(', ', $brands);
		}

		// what sells, what is looked at, the cheapest and the dearest
		$best = $names("SELECT op.name, SUM(op.quantity) AS q FROM `" . $p . "order_product` op JOIN `" . $p . "order` o ON (o.order_id = op.order_id AND o.order_status_id > '0' AND o.store_id = '" . $store_id . "') GROUP BY op.product_id ORDER BY q DESC LIMIT 15");

		$lines[] = '';
		$lines[] = '## Products';

		if ($best) {
			$lines[] = '- Best sellers: ' . implode('; ', $best);
		}

		$lines[] = '- Most viewed: ' . implode('; ', $names("SELECT pd.name" . $products . $active . " ORDER BY p.viewed DESC LIMIT 15"));
		$lines[] = '- Most expensive: ' . implode('; ', $names("SELECT pd.name" . $products . $active . " ORDER BY p.price DESC LIMIT 6"));
		$lines[] = '- Cheapest: ' . implode('; ', $names("SELECT pd.name" . $products . $active . " AND p.price > 0 ORDER BY p.price ASC LIMIT 6"));

		// what customers choose (size, colour …) and what the products are described by
		$options = array();

		foreach ($db->query("SELECT od.name, COUNT(DISTINCT po.product_id) AS n FROM `" . $p . "product_option` po JOIN `" . $p . "option_description` od ON (od.option_id = po.option_id AND od.language_id = '" . $lang . "') GROUP BY po.option_id ORDER BY n DESC LIMIT 10")->rows as $row) {
			$options[] = html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8') . ' (' . (int)$row['n'] . ' products)';
		}

		if ($options) {
			$lines[] = '- Options the customer chooses: ' . implode(', ', $options);
		}

		$attributes = $names("SELECT ad.name, COUNT(*) AS n FROM `" . $p . "product_attribute` pa JOIN `" . $p . "attribute_description` ad ON (ad.attribute_id = pa.attribute_id AND ad.language_id = '" . $lang . "') WHERE pa.language_id = '" . $lang . "' GROUP BY pa.attribute_id ORDER BY n DESC LIMIT 15");

		if ($attributes) {
			$lines[] = '- Specifications used: ' . implode(', ', $attributes);
		}

		$reviews = $db->query("SELECT COUNT(*) AS n, AVG(rating) AS r FROM `" . $p . "review` WHERE status = '1'")->row;

		if ((int)$reviews['n']) {
			$lines[] = '- Reviews: ' . (int)$reviews['n'] . ', average ' . round((float)$reviews['r'], 1) . '/5';
		}

		// the store's pages, shipping and payment (names only: costs and terms are read live by the assistant)
		$lines[] = '';
		$lines[] = '## Service';

		$pages = $names("SELECT id.title AS name FROM `" . $p . "information` i JOIN `" . $p . "information_to_store` i2s ON (i2s.information_id = i.information_id AND i2s.store_id = '" . $store_id . "') JOIN `" . $p . "information_description` id ON (id.information_id = i.information_id AND id.language_id = '" . $lang . "') WHERE i.status = '1' ORDER BY i.sort_order");

		if ($pages) {
			$lines[] = '- Information pages: ' . implode('; ', $pages);
		}

		$this->load->model('setting/extension');

		foreach (array('shipping' => 'Shipping methods', 'payment' => 'Payment methods') as $type => $label) {
			$methods = array();

			foreach ($this->model_setting_extension->getInstalled($type) as $code) {
				if ($this->config->get($type . '_' . $code . '_status') && $code != 'free_checkout') {
					$this->load->language('extension/' . $type . '/' . $code, 'fx_' . $code);

					$methods[] = strip_tags($this->language->get('fx_' . $code)->get('heading_title'));
				}
			}

			if ($methods) {
				$lines[] = '- ' . $label . ': ' . implode(', ', $methods);
			}
		}

		if ($this->config->get('shipping_free_status') && (float)$this->config->get('shipping_free_total') > 0) {
			$lines[] = '- Free shipping from ' . $money($this->config->get('shipping_free_total'));
		}

		return implode("\n", $lines);
	}

	// one conversation as the customer saw it
	public function chat() {
		$json = array();

		if ($this->user->hasPermission('access', 'extension/module/ho_assistant')) {
			$row = $this->db->query("SELECT `transcript` FROM `" . DB_PREFIX . "ho_assistant_chat` WHERE `chat_id` = '" . (int)(isset($this->request->get['chat_id']) ? $this->request->get['chat_id'] : 0) . "'")->row;

			$json['log'] = $row ? (json_decode($row['transcript'], true) ?: array()) : array();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ho_assistant')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install() {
		\Ho\Assistant::install($this->db);

		$this->load->model('setting/setting');

		$this->model_setting_setting->editSetting('module_ho_assistant', array('module_ho_assistant_status' => 1, 'module_ho_assistant_model' => 'gpt-4.1-mini', 'module_ho_assistant_budget' => 5, 'module_ho_assistant_rate' => 30, 'module_ho_assistant_payments' => array('bank_transfer'), 'module_ho_assistant_retention' => 90));
	}

	public function uninstall() {
		$this->load->model('setting/setting');

		$this->model_setting_setting->deleteSetting('module_ho_assistant');
	}
}
