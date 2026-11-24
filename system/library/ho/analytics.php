<?php
namespace Ho;
/**
 * HappyOnline Analytics — first-party, server-side measurement for Reports › Analytics.
 * Switched on by $_['ho_analytics_status'] in system/config/happyonline.php.
 *
 * One row per hit in oc_ho_analytics_hit: page views (from common/footer and the kit checkout), add_to_cart
 * (checkout/cart/add) and purchase (checkout/success). No JavaScript, so ad blockers do not hide visits.
 *
 * Privacy: no cookie without consent. The visitor is then a hash of a salt that changes every day, the IP and
 * the browser — the IP itself is never stored, and the same person is a new visitor the next day. With the
 * «statistics» consent (cookie ho_consent { an: true } of HO Tracking, or CookieYes «analytics»), a first-party
 * cookie ho_aid (random, 13 months) recognises returning visitors.
 */
class Analytics {
	const SESSION_MINUTES = 30;
	const BOTS = '/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|embedly|preview|headless|lighthouse|pingdom|uptime|monitor|curl|wget|python|httpclient|java\/|php\/|go-http|okhttp|axios|scrapy|semrush|ahrefs|mj12|dotbot|petal|bytespider|gptbot|claude|perplexity/i';

	private $registry;
	private $db;
	private $config;
	private $request;

	public function __construct($registry) {
		$this->registry = $registry;
		$this->db = $registry->get('db');
		$this->config = $registry->get('config');
		$this->request = $registry->get('request');
	}

	// ------------------------------------------------------------------------------------------- schema

	public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ho_analytics_hit` (
			`hit_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`date_added` datetime NOT NULL,
			`visitor` char(16) NOT NULL,
			`session` char(16) NOT NULL,
			`type` varchar(16) NOT NULL DEFAULT 'page_view',
			`page_type` varchar(16) NOT NULL DEFAULT '',
			`route` varchar(64) NOT NULL DEFAULT '',
			`path` varchar(255) NOT NULL DEFAULT '',
			`item_id` int(11) NOT NULL DEFAULT '0',
			`search` varchar(64) NOT NULL DEFAULT '',
			`landing` tinyint(1) NOT NULL DEFAULT '0',
			`new_visitor` tinyint(1) NOT NULL DEFAULT '1',
			`consent` tinyint(1) NOT NULL DEFAULT '0',
			`customer` tinyint(1) NOT NULL DEFAULT '0',
			`channel` varchar(16) NOT NULL DEFAULT '',
			`source` varchar(64) NOT NULL DEFAULT '',
			`medium` varchar(32) NOT NULL DEFAULT '',
			`campaign` varchar(64) NOT NULL DEFAULT '',
			`referrer` varchar(128) NOT NULL DEFAULT '',
			`device` varchar(8) NOT NULL DEFAULT '',
			`browser` varchar(16) NOT NULL DEFAULT '',
			`os` varchar(16) NOT NULL DEFAULT '',
			`language` varchar(8) NOT NULL DEFAULT '',
			`country` char(2) NOT NULL DEFAULT '',
			`value` decimal(15,4) NOT NULL DEFAULT '0.0000',
			`quantity` int(11) NOT NULL DEFAULT '0',
			`order_id` int(11) NOT NULL DEFAULT '0',
			`store_id` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`hit_id`),
			KEY `date_added` (`date_added`),
			KEY `visitor` (`visitor`, `date_added`),
			KEY `session` (`session`),
			KEY `type` (`type`, `date_added`),
			KEY `store_id` (`store_id`, `date_added`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}

	public function installed() {
		return (bool)$this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . 'ho_analytics_hit') . "'")->num_rows;
	}

	// dekor: two stores (DEKOR, APOKRIFA) -> every hit carries its store; adds the column to a table made without it
	public function upgrade() {
		if (!$this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "ho_analytics_hit` LIKE 'store_id'")->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "ho_analytics_hit` ADD `store_id` int(11) NOT NULL DEFAULT '0', ADD KEY `store_id` (`store_id`, `date_added`)");
		}
	}

	// ------------------------------------------------------------------------------------------- collection

	/**
	 * Records one hit for the current request. $type: page_view | add_to_cart | purchase.
	 * $extra: route, item_id, value, quantity, order_id (anything else is worked out from the request).
	 */
	public function track($type = 'page_view', $extra = array()) {
		$server = $this->request->server;
		$agent = isset($server['HTTP_USER_AGENT']) ? (string)$server['HTTP_USER_AGENT'] : '';

		// people only: no bots / tools, no prefetches, no background requests for a page view
		if ($agent === '' || preg_match(self::BOTS, $agent)) {
			return false;
		}

		$purpose = strtolower((isset($server['HTTP_SEC_PURPOSE']) ? $server['HTTP_SEC_PURPOSE'] : '') . (isset($server['HTTP_PURPOSE']) ? $server['HTTP_PURPOSE'] : '') . (isset($server['HTTP_X_MOZ']) ? $server['HTTP_X_MOZ'] : ''));

		if (strpos($purpose, 'prefetch') !== false || strpos($purpose, 'prerender') !== false) {
			return false;
		}

		if ($type == 'page_view' && (!empty($server['HTTP_X_REQUESTED_WITH']) || (isset($server['REQUEST_METHOD']) && $server['REQUEST_METHOD'] == 'HEAD'))) {
			return false;
		}

		try {
			return $this->insert($type, $extra, $agent);
		} catch (\Exception $e) {
			// the table not there yet (Reports › Analytics not opened since the switch went on), a full disk ...:
			// the page itself must never fail because of a measurement
			return false;
		}
	}

	private function insert($type, $extra, $agent) {
		// one purchase per order: a reload of the thank-you page is not a second sale
		if ($type == 'purchase' && !empty($extra['order_id']) && $this->db->query("SELECT 1 FROM `" . DB_PREFIX . "ho_analytics_hit` WHERE `type` = 'purchase' AND `order_id` = '" . (int)$extra['order_id'] . "' LIMIT 1")->num_rows) {
			return false;
		}

		$get = $this->request->get;
		$route = isset($extra['route']) ? $extra['route'] : (isset($get['route']) ? (string)$get['route'] : 'common/home');

		list($visitor, $consent, $returning) = $this->visitor($agent);

		// the session: the visitor's last hit less than 30 minutes ago, else a new one (its first page = landing)
		$last = $this->db->query("SELECT `session`, `channel`, `source`, `medium`, `campaign`, `referrer` FROM `" . DB_PREFIX . "ho_analytics_hit` WHERE `visitor` = '" . $this->db->escape($visitor) . "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' AND `date_added` > DATE_SUB(NOW(), INTERVAL " . self::SESSION_MINUTES . " MINUTE) ORDER BY `hit_id` DESC LIMIT 1")->row;

		$source = $this->source();

		// a campaign link / an outside referrer opens a new session, like GA4
		if ($last && $source['channel'] != 'direct' && $source['channel'] != 'internal' && ($source['source'] != $last['source'] || $source['campaign'] != $last['campaign'])) {
			$last = array();
		}

		if ($last) {
			$session = $last['session'];
			$landing = 0;
			$source = array('channel' => $last['channel'], 'source' => $last['source'], 'medium' => $last['medium'], 'campaign' => $last['campaign'], 'referrer' => $last['referrer']);
		} else {
			$session = substr(hash('sha256', $visitor . microtime(true) . mt_rand()), 0, 16);
			$landing = $type == 'page_view' ? 1 : 0;

			if ($source['channel'] == 'internal') {
				$source = array('channel' => 'direct', 'source' => '(direct)', 'medium' => '(none)', 'campaign' => '', 'referrer' => '');
			}
		}

		$device = $this->device($agent);
		$search = '';

		foreach (array('search', 'tag', 'filter_name') as $key) {
			if (!empty($get[$key])) {
				$search = mb_strtolower(trim(html_entity_decode((string)$get[$key], ENT_QUOTES, 'UTF-8')), 'UTF-8');

				break;
			}
		}

		// (OpenCart's request object holds every value HTML-escaped: &amp;)
		$path = isset($this->request->server['REQUEST_URI']) ? html_entity_decode((string)$this->request->server['REQUEST_URI'], ENT_QUOTES, 'UTF-8') : '';
		// no tracking / session parameters in the stored address
		$path = preg_replace('/([?&])(gclid|fbclid|msclkid|ttclid|_ga|_gl|utm_[a-z]+|ho_pk_preview|token|key)=[^&]*/i', '$1', $path);
		$path = rtrim(preg_replace('/[?&]+$/', '', preg_replace('/&{2,}/', '&', $path)), '?&');

		$language = isset($this->request->server['HTTP_ACCEPT_LANGUAGE']) ? strtolower(substr((string)$this->request->server['HTTP_ACCEPT_LANGUAGE'], 0, 2)) : '';
		$country = isset($this->request->server['HTTP_CF_IPCOUNTRY']) && preg_match('/^[A-Z]{2}$/', $this->request->server['HTTP_CF_IPCOUNTRY']) ? $this->request->server['HTTP_CF_IPCOUNTRY'] : '';

		$customer = $this->registry->get('customer');

		$this->db->query("INSERT INTO `" . DB_PREFIX . "ho_analytics_hit` SET
			`date_added` = NOW(),
			`visitor` = '" . $this->db->escape($visitor) . "',
			`session` = '" . $this->db->escape($session) . "',
			`type` = '" . $this->db->escape($type) . "',
			`page_type` = '" . $this->db->escape($this->pageType($route)) . "',
			`route` = '" . $this->db->escape(substr($route, 0, 64)) . "',
			`path` = '" . $this->db->escape(mb_substr($path, 0, 255, 'UTF-8')) . "',
			`item_id` = '" . (int)(isset($extra['item_id']) ? $extra['item_id'] : $this->itemId($route)) . "',
			`search` = '" . $this->db->escape(mb_substr($search, 0, 64, 'UTF-8')) . "',
			`landing` = '" . (int)$landing . "',
			`new_visitor` = '" . ($returning ? 0 : 1) . "',
			`consent` = '" . (int)$consent . "',
			`customer` = '" . ($customer && $customer->isLogged() ? 1 : 0) . "',
			`channel` = '" . $this->db->escape($source['channel']) . "',
			`source` = '" . $this->db->escape(mb_substr($source['source'], 0, 64, 'UTF-8')) . "',
			`medium` = '" . $this->db->escape(mb_substr($source['medium'], 0, 32, 'UTF-8')) . "',
			`campaign` = '" . $this->db->escape(mb_substr($source['campaign'], 0, 64, 'UTF-8')) . "',
			`referrer` = '" . $this->db->escape(mb_substr($source['referrer'], 0, 128, 'UTF-8')) . "',
			`device` = '" . $this->db->escape($device['device']) . "',
			`browser` = '" . $this->db->escape($device['browser']) . "',
			`os` = '" . $this->db->escape($device['os']) . "',
			`language` = '" . $this->db->escape($language) . "',
			`country` = '" . $this->db->escape($country) . "',
			`value` = '" . (float)(isset($extra['value']) ? $extra['value'] : 0) . "',
			`quantity` = '" . (int)(isset($extra['quantity']) ? $extra['quantity'] : 0) . "',
			`order_id` = '" . (int)(isset($extra['order_id']) ? $extra['order_id'] : 0) . "',
			`store_id` = '" . (int)$this->config->get('config_store_id') . "'");

		// keep the table to the retention the store set (25 months by default): now and then, never on every hit
		if (mt_rand(1, 500) == 1) {
			$months = (int)$this->config->get('ho_analytics_retention_months') ?: 25;

			$this->db->query("DELETE FROM `" . DB_PREFIX . "ho_analytics_hit` WHERE `date_added` < DATE_SUB(NOW(), INTERVAL " . $months . " MONTH)");
		}

		return true;
	}

	/**
	 * array(visitor id, consent given, known from before): the ho_aid cookie with consent, else a daily hash.
	 */
	private function visitor($agent) {
		$cookie = $this->request->cookie;

		if ($this->consented()) {
			if (!empty($cookie['ho_aid']) && preg_match('/^[a-f0-9]{16}$/', $cookie['ho_aid'])) {
				$id = $cookie['ho_aid'];
				$known = (bool)$this->db->query("SELECT 1 FROM `" . DB_PREFIX . "ho_analytics_hit` WHERE `visitor` = '" . $this->db->escape($id) . "' AND `date_added` < CURDATE() LIMIT 1")->num_rows;
			} else {
				$id = bin2hex(random_bytes(8));
				$known = false;
			}

			if (!headers_sent()) {
				setcookie('ho_aid', $id, array('expires' => time() + 400 * 86400, 'path' => '/', 'secure' => !empty($this->request->server['HTTPS']) && $this->request->server['HTTPS'] != 'off', 'httponly' => true, 'samesite' => 'Lax'));
			}

			return array($id, 1, $known);
		}

		$ip = isset($this->request->server['REMOTE_ADDR']) ? (string)$this->request->server['REMOTE_ADDR'] : '';
		$salt = hash('sha256', date('Y-m-d') . '|' . (defined('DB_PASSWORD') ? DB_PASSWORD : '') . '|' . DB_DATABASE . '|' . DB_PREFIX);

		return array(substr(hash('sha256', $salt . '|' . $ip . '|' . $agent), 0, 16), 0, false);
	}

	/** «statistics» consent: HO Tracking's banner (ho_consent { an }) or CookieYes (analytics:yes) */
	public function consented() {
		$cookie = $this->request->cookie;

		if (!empty($cookie['ho_consent'])) {
			$c = json_decode(urldecode((string)$cookie['ho_consent']), true);

			if (is_array($c)) {
				return !empty($c['an']);
			}
		}

		if (!empty($cookie['cookieyes-consent'])) {
			return strpos(urldecode((string)$cookie['cookieyes-consent']), 'analytics:yes') !== false;
		}

		return false;
	}

	/**
	 * Where the visit came from: campaign tags first, then the click ids of the ad networks, then the referrer.
	 * channel: organic | paid | social | marketplace | email | referral | direct | internal
	 */
	public function source() {
		$get = $this->request->get;
		$utm = function ($key) use ($get) {
			return isset($get['utm_' . $key]) ? mb_strtolower(trim(html_entity_decode((string)$get['utm_' . $key], ENT_QUOTES, 'UTF-8')), 'UTF-8') : '';
		};

		$referrer = isset($this->request->server['HTTP_REFERER']) ? (string)$this->request->server['HTTP_REFERER'] : '';
		$host = strtolower((string)parse_url($referrer, PHP_URL_HOST));
		$host = preg_replace('/^(www|m|l|lm|mobile)\./', '', $host);
		$own = strtolower((string)preg_replace('/^www\./', '', (string)(isset($this->request->server['HTTP_HOST']) ? $this->request->server['HTTP_HOST'] : '')));

		if ($utm('source') !== '') {
			$medium = $utm('medium');

			return array('channel' => $this->channel($utm('source'), $medium), 'source' => $utm('source'), 'medium' => $medium ?: '(not set)', 'campaign' => $utm('campaign'), 'referrer' => $host);
		}

		if (!empty($get['gclid']) || !empty($get['gbraid']) || !empty($get['wbraid'])) {
			return array('channel' => 'paid', 'source' => 'google', 'medium' => 'cpc', 'campaign' => '', 'referrer' => $host);
		}

		if (!empty($get['msclkid'])) {
			return array('channel' => 'paid', 'source' => 'bing', 'medium' => 'cpc', 'campaign' => '', 'referrer' => $host);
		}

		if ($host === '') {
			return array('channel' => 'direct', 'source' => '(direct)', 'medium' => '(none)', 'campaign' => '', 'referrer' => '');
		}

		if ($host == $own || ($own && substr($host, -strlen('.' . $own)) == '.' . $own)) {
			return array('channel' => 'internal', 'source' => $host, 'medium' => 'internal', 'campaign' => '', 'referrer' => $host);
		}

		$name = $this->siteName($host);

		return array('channel' => $this->channel($name, ''), 'source' => $name, 'medium' => $this->channel($name, '') == 'organic' ? 'organic' : 'referral', 'campaign' => '', 'referrer' => $host);
	}

	// a referrer host as people know it: google.gr -> google, l.facebook.com -> facebook
	private function siteName($host) {
		$known = array('google' => 'google', 'bing' => 'bing', 'yahoo' => 'yahoo', 'duckduckgo' => 'duckduckgo', 'yandex' => 'yandex', 'ecosia' => 'ecosia', 'facebook' => 'facebook', 'fb.' => 'facebook', 'instagram' => 'instagram', 't.co' => 'twitter', 'twitter' => 'twitter', 'x.com' => 'twitter', 'linkedin' => 'linkedin', 'lnkd.in' => 'linkedin', 'pinterest' => 'pinterest', 'tiktok' => 'tiktok', 'youtube' => 'youtube', 'reddit' => 'reddit', 'skroutz' => 'skroutz', 'bestprice' => 'bestprice', 'shopflix' => 'shopflix', 'chatgpt' => 'chatgpt', 'openai' => 'chatgpt', 'perplexity' => 'perplexity', 'gemini' => 'gemini', 'copilot' => 'copilot', 'mail.' => 'webmail', 'outlook' => 'webmail');

		foreach ($known as $needle => $name) {
			if (strpos($host, $needle) !== false) {
				return $name;
			}
		}

		return $host;
	}

	private function channel($source, $medium) {
		if (preg_match('/^(cpc|ppc|paid|paidsearch|paid_social|paidsocial|display|cpm|banner|shopping)$/', $medium)) {
			return 'paid';
		}

		if (preg_match('/e-?mail|newsletter/', $medium . ' ' . $source)) {
			return 'email';
		}

		if (preg_match('/^(skroutz|bestprice|shopflix|public|plaisio)/', $source)) {
			return 'marketplace';
		}

		if (preg_match('/^(facebook|instagram|twitter|linkedin|pinterest|tiktok|youtube|reddit|threads|messenger|whatsapp|viber)/', $source) || $medium == 'social') {
			return 'social';
		}

		if (preg_match('/^(google|bing|yahoo|duckduckgo|yandex|ecosia|baidu|qwant|startpage)$/', $source) || $medium == 'organic') {
			return 'organic';
		}

		if (preg_match('/^(chatgpt|perplexity|gemini|copilot|claude)/', $source)) {
			return 'ai';
		}

		return 'referral';
	}

	public function device($agent) {
		$a = strtolower($agent);

		if (preg_match('/ipad|tablet|kindle|silk|playbook|(android(?!.*mobile))/', $a)) {
			$device = 'tablet';
		} elseif (preg_match('/mobi|iphone|ipod|android|blackberry|opera mini|iemobile|windows phone/', $a)) {
			$device = 'mobile';
		} else {
			$device = 'desktop';
		}

		$browsers = array('edg/' => 'Edge', 'opr/' => 'Opera', 'samsungbrowser' => 'Samsung', 'fban' => 'Facebook app', 'instagram' => 'Instagram app', 'firefox' => 'Firefox', 'fxios' => 'Firefox', 'crios' => 'Chrome', 'chrome' => 'Chrome', 'safari' => 'Safari');
		$browser = 'Other';

		foreach ($browsers as $needle => $name) {
			if (strpos($a, $needle) !== false) {
				$browser = $name;

				break;
			}
		}

		$systems = array('iphone' => 'iOS', 'ipad' => 'iPadOS', 'android' => 'Android', 'windows' => 'Windows', 'mac os' => 'macOS', 'cros' => 'ChromeOS', 'linux' => 'Linux');
		$os = 'Other';

		foreach ($systems as $needle => $name) {
			if (strpos($a, $needle) !== false) {
				$os = $name;

				break;
			}
		}

		return array('device' => $device, 'browser' => $browser, 'os' => $os);
	}

	public function pageType($route) {
		$types = array(
			'common/home' => 'home', 'product/category' => 'category', 'product/product' => 'product', 'product/search' => 'search',
			'product/special' => 'category', 'product/manufacturer/info' => 'category', 'product/manufacturer' => 'brands',
			'checkout/cart' => 'cart', 'checkout/checkout' => 'checkout', 'extension/quickcheckout/checkout' => 'checkout',
			'checkout/success' => 'success', 'information/information' => 'info', 'information/contact' => 'contact',
			'extension/blog/home' => 'blog', 'extension/blog/post' => 'blog', 'extension/blog/category' => 'blog',
			'error/not_found' => 'not_found'
		);

		if (isset($types[$route])) {
			return $types[$route];
		}

		return strpos($route, 'account/') === 0 ? 'account' : 'other';
	}

	private function itemId($route) {
		$get = $this->request->get;

		switch ($route) {
			case 'product/product':
				return isset($get['product_id']) ? (int)$get['product_id'] : 0;
			case 'product/category':
				$path = isset($get['path']) ? explode('_', (string)$get['path']) : array();

				return (int)end($path);
			case 'product/manufacturer/info':
				return isset($get['manufacturer_id']) ? (int)$get['manufacturer_id'] : 0;
			case 'information/information':
				return isset($get['information_id']) ? (int)$get['information_id'] : 0;
		}

		return 0;
	}
}
