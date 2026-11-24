<?php
/**
 * Reports › Analytics: the numbers behind the page. Visits come from oc_ho_analytics_hit (system/library/ho/analytics.php),
 * sales from the store's own orders — valid orders = Settings › Option «processing» + «complete» statuses, so the
 * sales part works for the store's whole history, also before the measurement started.
 */
class ModelReportHoAnalytics extends Model {
	// static: OpenCart's loader runs every model method on its own instance (a proxy per method),
	// so the range set by setRange() must not live on the object
	private static $from;
	private static $to;
	private static $filter = array();

	/**
	 * $from / $to: Y-m-d (inclusive). $filter: device, channel (visits only), store_id (visits + orders; dekor: two stores).
	 */
	public function setRange($from, $to, $filter = array()) {
		self::$from = $from . ' 00:00:00';
		self::$to = $to . ' 23:59:59';
		self::$filter = $filter;
	}

	public function installed() {
		return (bool)$this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . 'ho_analytics_hit') . "'")->num_rows;
	}

	public function install() {
		(new \Ho\Analytics($this->registry))->install();
	}

	public function upgrade() {
		(new \Ho\Analytics($this->registry))->upgrade();
	}

	// dekor: the stores for the store filter (Default + Settings > Stores)
	public function getStores() {
		$stores = array(array('store_id' => 0, 'name' => $this->config->get('config_name')));

		foreach ($this->db->query("SELECT `store_id`, `name` FROM `" . DB_PREFIX . "store` ORDER BY `store_id`")->rows as $row) {
			$stores[] = array('store_id' => (int)$row['store_id'], 'name' => $row['name']);
		}

		return $stores;
	}

	// visits of the range (+ the filters), as SQL
	private function hits($alias = 'h', $type = 'page_view') {
		$sql = $alias . ".`date_added` BETWEEN '" . $this->db->escape(self::$from) . "' AND '" . $this->db->escape(self::$to) . "'";

		if ($type) {
			$sql .= " AND " . $alias . ".`type` = '" . $this->db->escape($type) . "'";
		}

		foreach (array('device', 'channel') as $key) {
			if (!empty(self::$filter[$key])) {
				$sql .= " AND " . $alias . ".`" . $key . "` = '" . $this->db->escape(self::$filter[$key]) . "'";
			}
		}

		if (isset(self::$filter['store_id'])) {
			$sql .= " AND " . $alias . ".`store_id` = '" . (int)self::$filter['store_id'] . "'";
		}

		return $sql;
	}

	private function table() {
		return "`" . DB_PREFIX . "ho_analytics_hit`";
	}

	// valid orders of the range, as SQL
	private function orders($alias = 'o') {
		$statuses = array_merge((array)$this->config->get('config_processing_status'), (array)$this->config->get('config_complete_status'));
		$statuses = array_unique(array_map('intval', $statuses)) ?: array(0);

		return $alias . ".`order_status_id` IN (" . implode(',', $statuses) . ")" . (isset(self::$filter['store_id']) ? " AND " . $alias . ".`store_id` = '" . (int)self::$filter['store_id'] . "'" : "") . " AND " . $alias . ".`date_added` BETWEEN '" . $this->db->escape(self::$from) . "' AND '" . $this->db->escape(self::$to) . "'";
	}

	// ------------------------------------------------------------------------------------------- headline numbers

	public function totals() {
		$t = $this->db->query("SELECT COUNT(*) AS pageviews, COUNT(DISTINCT `visitor`) AS users, COUNT(DISTINCT IF(`new_visitor` = 1, `visitor`, NULL)) AS new_users, COUNT(DISTINCT `session`) AS sessions FROM " . $this->table() . " h WHERE " . $this->hits())->row;

		$s = $this->db->query("SELECT COUNT(*) AS sessions, SUM(pv = 1) AS bounces, AVG(TIMESTAMPDIFF(SECOND, first_hit, last_hit)) AS duration FROM (SELECT `session`, COUNT(*) AS pv, MIN(`date_added`) AS first_hit, MAX(`date_added`) AS last_hit FROM " . $this->table() . " h WHERE " . $this->hits() . " GROUP BY `session`) x")->row;

		$events = $this->db->query("SELECT `type`, COUNT(DISTINCT `session`) AS sessions, COUNT(*) AS hits, SUM(`value`) AS value FROM " . $this->table() . " h WHERE " . $this->hits('h', '') . " AND `type` <> 'page_view' GROUP BY `type`")->rows;
		$ev = array();

		foreach ($events as $row) {
			$ev[$row['type']] = $row;
		}

		$sales = $this->db->query("SELECT COUNT(*) AS orders, COALESCE(SUM(o.`total`), 0) AS revenue, COUNT(DISTINCT IF(o.`customer_id` > 0, o.`customer_id`, o.`email`)) AS buyers FROM `" . DB_PREFIX . "order` o WHERE " . $this->orders())->row;
		$items = $this->db->query("SELECT COALESCE(SUM(op.`quantity`), 0) AS items FROM `" . DB_PREFIX . "order_product` op JOIN `" . DB_PREFIX . "order` o ON (o.`order_id` = op.`order_id`) WHERE " . $this->orders())->row;

		$sessions = (int)$t['sessions'];
		$purchases = isset($ev['purchase']) ? (int)$ev['purchase']['sessions'] : 0;

		return array(
			'users'           => (int)$t['users'],
			'new_users'       => (int)$t['new_users'],
			'sessions'        => $sessions,
			'pageviews'       => (int)$t['pageviews'],
			'pages_per_session' => $sessions ? round($t['pageviews'] / $sessions, 2) : 0,
			'duration'        => (int)round((float)$s['duration']),
			'bounce_rate'     => $s['sessions'] ? round($s['bounces'] / $s['sessions'] * 100, 1) : 0,
			'add_to_cart'     => isset($ev['add_to_cart']) ? (int)$ev['add_to_cart']['hits'] : 0,
			'purchases'       => $purchases,
			'conversion_rate' => $sessions ? round($purchases / $sessions * 100, 2) : 0,
			'tracked_revenue' => isset($ev['purchase']) ? (float)$ev['purchase']['value'] : 0,
			'orders'          => (int)$sales['orders'],
			'revenue'         => (float)$sales['revenue'],
			'aov'             => $sales['orders'] ? (float)$sales['revenue'] / $sales['orders'] : 0,
			'buyers'          => (int)$sales['buyers'],
			'items'           => (int)$items['items']
		);
	}

	// ------------------------------------------------------------------------------------------- over time

	/**
	 * Per hour (one day), per day (up to ~4 months), per week, per month: array(bucket => metrics)
	 */
	public function series($unit) {
		$formats = array('hour' => '%Y-%m-%d %H:00', 'day' => '%Y-%m-%d', 'week' => '%x-W%v', 'month' => '%Y-%m');
		$f = $formats[$unit];
		$out = array();

		foreach ($this->db->query("SELECT DATE_FORMAT(`date_added`, '" . $f . "') AS b, COUNT(*) AS pageviews, COUNT(DISTINCT `visitor`) AS users, COUNT(DISTINCT IF(`new_visitor` = 1, `visitor`, NULL)) AS new_users, COUNT(DISTINCT `session`) AS sessions FROM " . $this->table() . " h WHERE " . $this->hits() . " GROUP BY b")->rows as $row) {
			$out[$row['b']] = array('pageviews' => (int)$row['pageviews'], 'users' => (int)$row['users'], 'new_users' => (int)$row['new_users'], 'sessions' => (int)$row['sessions']);
		}

		// per session (in the bucket it started): bounce rate, duration, conversion rate — the same definitions as totals()
		foreach ($this->db->query("SELECT b, COUNT(*) AS sessions, SUM(pv = 1) AS bounces, AVG(duration) AS duration, SUM(buys > 0) AS converted FROM (SELECT `session`, DATE_FORMAT(MIN(`date_added`), '" . $f . "') AS b, SUM(`type` = 'page_view') AS pv, TIMESTAMPDIFF(SECOND, MIN(`date_added`), MAX(`date_added`)) AS duration, SUM(`type` = 'purchase') AS buys FROM " . $this->table() . " h WHERE " . $this->hits('h', '') . " GROUP BY `session`) s WHERE pv > 0 GROUP BY b")->rows as $row) {
			$out[$row['b']]['bounce_rate'] = $row['sessions'] ? round($row['bounces'] / $row['sessions'] * 100, 1) : 0;
			$out[$row['b']]['duration'] = (int)round((float)$row['duration']);
			$out[$row['b']]['conversion_rate'] = $row['sessions'] ? round($row['converted'] / $row['sessions'] * 100, 2) : 0;
		}

		foreach ($this->db->query("SELECT DATE_FORMAT(`date_added`, '" . $f . "') AS b, COUNT(DISTINCT IF(`type` = 'add_to_cart', `session`, NULL)) AS carts, COUNT(DISTINCT IF(`type` = 'purchase', `session`, NULL)) AS purchases FROM " . $this->table() . " h WHERE " . $this->hits('h', '') . " GROUP BY b")->rows as $row) {
			$out[$row['b']]['carts'] = (int)$row['carts'];
			$out[$row['b']]['purchases'] = (int)$row['purchases'];
		}

		foreach ($this->db->query("SELECT DATE_FORMAT(o.`date_added`, '" . $f . "') AS b, COUNT(*) AS orders, SUM(o.`total`) AS revenue FROM `" . DB_PREFIX . "order` o WHERE " . $this->orders() . " GROUP BY b")->rows as $row) {
			$out[$row['b']]['orders'] = (int)$row['orders'];
			$out[$row['b']]['revenue'] = round((float)$row['revenue'], 2);
			$out[$row['b']]['aov'] = $row['orders'] ? round($row['revenue'] / $row['orders'], 2) : 0;
		}

		return $out;
	}

	// ------------------------------------------------------------------------------------------- acquisition

	/**
	 * Sessions grouped by channel / source / medium / campaign / referrer, with what they brought.
	 */
	public function acquisition($by, $limit = 50) {
		$columns = array('channel' => '`channel`', 'source' => "CONCAT(`source`, ' / ', `medium`)", 'campaign' => '`campaign`', 'referrer' => '`referrer`');
		$col = $columns[$by];
		$where = $this->hits('h', '');

		if ($by == 'campaign') {
			$where .= " AND `campaign` <> ''";
		} elseif ($by == 'referrer') {
			$where .= " AND `referrer` <> '' AND `channel` NOT IN ('direct', 'internal')";
		}

		$sql = "SELECT k AS name, COUNT(*) AS sessions, COUNT(DISTINCT visitor) AS users, SUM(pv = 1) AS bounces, SUM(pv) AS pageviews, SUM(carts > 0) AS carts, SUM(buys > 0) AS purchases, SUM(revenue) AS revenue FROM (SELECT `session`, MIN(" . $col . ") AS k, MIN(`visitor`) AS visitor, SUM(`type` = 'page_view') AS pv, SUM(`type` = 'add_to_cart') AS carts, SUM(`type` = 'purchase') AS buys, SUM(IF(`type` = 'purchase', `value`, 0)) AS revenue FROM " . $this->table() . " h WHERE " . $where . " GROUP BY `session`) s GROUP BY k ORDER BY sessions DESC LIMIT " . (int)$limit;

		return array_map(array($this, 'sessionRow'), $this->db->query($sql)->rows);
	}

	private function sessionRow($row) {
		$sessions = max(1, (int)$row['sessions']);

		return array(
			'name'            => $row['name'],
			'sessions'        => (int)$row['sessions'],
			'users'           => (int)$row['users'],
			'pages_per_session' => round($row['pageviews'] / $sessions, 2),
			'bounce_rate'     => round($row['bounces'] / $sessions * 100, 1),
			'carts'           => (int)$row['carts'],
			'purchases'       => (int)$row['purchases'],
			'conversion_rate' => round($row['purchases'] / $sessions * 100, 2),
			'revenue'         => round((float)$row['revenue'], 2)
		);
	}

	// ------------------------------------------------------------------------------------------- behaviour

	public function pages($limit = 50) {
		$rows = $this->db->query("SELECT `path`, MIN(`page_type`) AS page_type, MIN(`item_id`) AS item_id, COUNT(*) AS views, COUNT(DISTINCT `visitor`) AS users, SUM(`landing`) AS entrances FROM " . $this->table() . " h WHERE " . $this->hits() . " GROUP BY `path` ORDER BY views DESC LIMIT " . (int)$limit)->rows;

		return $this->withNames($rows);
	}

	/**
	 * Landing pages: the sessions they started, how many left at once, how many bought.
	 */
	public function landings($limit = 30) {
		$rows = $this->db->query("SELECT l.`path`, MIN(l.`page_type`) AS page_type, MIN(l.`item_id`) AS item_id, COUNT(*) AS sessions, SUM(s.pv = 1) AS bounces, SUM(s.buys > 0) AS purchases, SUM(s.revenue) AS revenue FROM " . $this->table() . " l JOIN (SELECT `session`, SUM(`type` = 'page_view') AS pv, SUM(`type` = 'purchase') AS buys, SUM(IF(`type` = 'purchase', `value`, 0)) AS revenue FROM " . $this->table() . " h WHERE " . $this->hits('h', '') . " GROUP BY `session`) s ON (s.`session` = l.`session`) WHERE " . $this->hits('l') . " AND l.`landing` = 1 GROUP BY l.`path` ORDER BY sessions DESC LIMIT " . (int)$limit)->rows;

		foreach ($rows as &$row) {
			$row['bounce_rate'] = $row['sessions'] ? round($row['bounces'] / $row['sessions'] * 100, 1) : 0;
			$row['conversion_rate'] = $row['sessions'] ? round($row['purchases'] / $row['sessions'] * 100, 2) : 0;
			$row['revenue'] = round((float)$row['revenue'], 2);
		}

		return $this->withNames($rows);
	}

	public function pageTypes() {
		return $this->db->query("SELECT `page_type` AS name, COUNT(*) AS views FROM " . $this->table() . " h WHERE " . $this->hits() . " GROUP BY `page_type` ORDER BY views DESC")->rows;
	}

	public function searches($limit = 30) {
		return $this->db->query("SELECT `search` AS term, COUNT(*) AS searches, COUNT(DISTINCT `visitor`) AS users FROM " . $this->table() . " h WHERE " . $this->hits() . " AND `search` <> '' GROUP BY `search` ORDER BY searches DESC LIMIT " . (int)$limit)->rows;
	}

	/**
	 * Sessions that saw a product page, added to cart, reached the checkout, bought.
	 */
	public function funnel() {
		$row = $this->db->query("SELECT COUNT(*) AS sessions, SUM(products > 0) AS product, SUM(carts > 0) AS cart, SUM(checkout > 0) AS checkout, SUM(buys > 0) AS purchase FROM (SELECT `session`, SUM(`type` = 'page_view' AND `page_type` = 'product') AS products, SUM(`type` = 'add_to_cart') AS carts, SUM(`type` = 'page_view' AND `page_type` = 'checkout') AS checkout, SUM(`type` = 'purchase') AS buys FROM " . $this->table() . " h WHERE " . $this->hits('h', '') . " GROUP BY `session`) s")->row;

		return array_map('intval', $row);
	}

	// ------------------------------------------------------------------------------------------- audience

	public function breakdown($column, $limit = 12) {
		$allowed = array('device', 'browser', 'os', 'language', 'country', 'channel');

		if (!in_array($column, $allowed)) {
			return array();
		}

		return $this->db->query("SELECT `" . $column . "` AS name, COUNT(DISTINCT `visitor`) AS users, COUNT(DISTINCT `session`) AS sessions FROM " . $this->table() . " h WHERE " . $this->hits() . " AND `" . $column . "` <> '' GROUP BY `" . $column . "` ORDER BY sessions DESC LIMIT " . (int)$limit)->rows;
	}

	public function newReturning() {
		$row = $this->db->query("SELECT COUNT(DISTINCT IF(`new_visitor` = 1, `visitor`, NULL)) AS new_users, COUNT(DISTINCT IF(`new_visitor` = 0, `visitor`, NULL)) AS returning_users, COUNT(DISTINCT IF(`consent` = 1, `visitor`, NULL)) AS consented, COUNT(DISTINCT IF(`customer` = 1, `visitor`, NULL)) AS customers FROM " . $this->table() . " h WHERE " . $this->hits())->row;

		return array_map('intval', $row);
	}

	// sessions per weekday (1 Monday … 7 Sunday) and hour
	public function heatmap() {
		$out = array();

		foreach ($this->db->query("SELECT WEEKDAY(`date_added`) + 1 AS d, HOUR(`date_added`) AS h, COUNT(DISTINCT `session`) AS sessions FROM " . $this->table() . " h WHERE " . $this->hits() . " GROUP BY d, h")->rows as $row) {
			$out[(int)$row['d']][(int)$row['h']] = (int)$row['sessions'];
		}

		return $out;
	}

	// ------------------------------------------------------------------------------------------- products (visits + sales)

	/**
	 * Per product: page views, add-to-carts, units sold and revenue — which products people look at but do not buy.
	 */
	public function products($limit = 30) {
		$lang = (int)$this->config->get('config_language_id');
		$rows = array();

		foreach ($this->db->query("SELECT `item_id`, SUM(`type` = 'page_view') AS views, COUNT(DISTINCT IF(`type` = 'page_view', `visitor`, NULL)) AS viewers, SUM(IF(`type` = 'add_to_cart', `quantity`, 0)) AS carts FROM " . $this->table() . " h WHERE " . $this->hits('h', '') . " AND `item_id` > 0 AND (`type` = 'add_to_cart' OR `page_type` = 'product') GROUP BY `item_id`")->rows as $row) {
			$rows[(int)$row['item_id']] = array('views' => (int)$row['views'], 'viewers' => (int)$row['viewers'], 'carts' => (int)$row['carts'], 'sold' => 0, 'revenue' => 0);
		}

		foreach ($this->db->query("SELECT op.`product_id`, SUM(op.`quantity`) AS sold, SUM(op.`total` + op.`tax` * op.`quantity`) AS revenue FROM `" . DB_PREFIX . "order_product` op JOIN `" . DB_PREFIX . "order` o ON (o.`order_id` = op.`order_id`) WHERE " . $this->orders() . " GROUP BY op.`product_id`")->rows as $row) {
			$id = (int)$row['product_id'];

			if (!isset($rows[$id])) {
				$rows[$id] = array('views' => 0, 'viewers' => 0, 'carts' => 0);
			}

			$rows[$id]['sold'] = (int)$row['sold'];
			$rows[$id]['revenue'] = round((float)$row['revenue'], 2);
		}

		uasort($rows, function ($a, $b) {
			return ($b['revenue'] <=> $a['revenue']) ?: ($b['views'] <=> $a['views']);
		});

		$rows = array_slice($rows, 0, $limit, true);
		$names = array();

		if ($rows) {
			foreach ($this->db->query("SELECT `product_id`, `name` FROM `" . DB_PREFIX . "product_description` WHERE `language_id` = '" . $lang . "' AND `product_id` IN (" . implode(',', array_keys($rows)) . ")")->rows as $row) {
				$names[$row['product_id']] = html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8');
			}
		}

		$out = array();

		foreach ($rows as $id => $row) {
			$row['product_id'] = $id;
			$row['name'] = isset($names[$id]) ? $names[$id] : '#' . $id;
			$row['cart_rate'] = $row['viewers'] ? round($row['carts'] / $row['viewers'] * 100, 1) : 0;
			$out[] = $row;
		}

		return $out;
	}

	// ------------------------------------------------------------------------------------------- sales (orders)

	public function salesBy($by, $limit = 12) {
		switch ($by) {
			case 'payment':
				$sql = "SELECT o.`payment_method` AS name, COUNT(*) AS orders, SUM(o.`total`) AS revenue FROM `" . DB_PREFIX . "order` o WHERE " . $this->orders() . " GROUP BY o.`payment_method`";
				break;
			case 'shipping':
				$sql = "SELECT o.`shipping_method` AS name, COUNT(*) AS orders, SUM(o.`total`) AS revenue FROM `" . DB_PREFIX . "order` o WHERE " . $this->orders() . " GROUP BY o.`shipping_method`";
				break;
			case 'category':
				// each product under its first top category, so nothing counts twice
				$lang = (int)$this->config->get('config_language_id');
				$sql = "SELECT COALESCE(cd.`name`, '—') AS name, COUNT(DISTINCT o.`order_id`) AS orders, SUM(op.`total` + op.`tax` * op.`quantity`) AS revenue, SUM(op.`quantity`) AS items FROM `" . DB_PREFIX . "order_product` op JOIN `" . DB_PREFIX . "order` o ON (o.`order_id` = op.`order_id`) LEFT JOIN (SELECT p2c.`product_id`, MIN(cp.`path_id`) AS top_id FROM `" . DB_PREFIX . "product_to_category` p2c JOIN `" . DB_PREFIX . "category_path` cp ON (cp.`category_id` = p2c.`category_id` AND cp.`level` = 0) GROUP BY p2c.`product_id`) t ON (t.`product_id` = op.`product_id`) LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (cd.`category_id` = t.`top_id` AND cd.`language_id` = '" . $lang . "') WHERE " . $this->orders() . " GROUP BY name";
				break;
			case 'customer':
				// first order of the customer (by e-mail) inside the range = a new buyer
				$sql = "SELECT IF(f.`first_order` >= '" . $this->db->escape(self::$from) . "', 'new', 'returning') AS name, COUNT(*) AS orders, SUM(o.`total`) AS revenue FROM `" . DB_PREFIX . "order` o JOIN (SELECT `email`, MIN(`date_added`) AS first_order FROM `" . DB_PREFIX . "order` WHERE `order_status_id` > 0 GROUP BY `email`) f ON (f.`email` = o.`email`) WHERE " . $this->orders() . " GROUP BY name";
				break;
			case 'source':
				$sql = "SELECT h.`channel` AS name, COUNT(*) AS orders, SUM(h.`value`) AS revenue FROM " . $this->table() . " h WHERE " . $this->hits('h', 'purchase') . " GROUP BY h.`channel`";
				break;
			case 'hour':
				$sql = "SELECT HOUR(o.`date_added`) AS name, COUNT(*) AS orders, SUM(o.`total`) AS revenue FROM `" . DB_PREFIX . "order` o WHERE " . $this->orders() . " GROUP BY name ORDER BY name";
				return array_map(array($this, 'moneyRow'), $this->db->query($sql)->rows);
			case 'coupon':
				$sql = "SELECT ot.`title` AS name, COUNT(*) AS orders, -SUM(ot.`value`) AS revenue FROM `" . DB_PREFIX . "order_total` ot JOIN `" . DB_PREFIX . "order` o ON (o.`order_id` = ot.`order_id`) WHERE " . $this->orders() . " AND ot.`code` IN ('coupon', 'voucher', 'reward') GROUP BY ot.`title`";
				break;
			default:
				return array();
		}

		return array_map(array($this, 'moneyRow'), $this->db->query($sql . " ORDER BY revenue DESC LIMIT " . (int)$limit)->rows);
	}

	private function moneyRow($row) {
		$row['revenue'] = round((float)$row['revenue'], 2);
		$row['orders'] = (int)$row['orders'];

		// names come HTML-escaped from OpenCart (&amp;); the page escapes them itself
		if (isset($row['name']) && is_string($row['name'])) {
			$row['name'] = html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8');
		}

		return $row;
	}

	// ------------------------------------------------------------------------------------------- real time

	public function realtime() {
		// dekor: one store's rows when the store filter is set
		$table = isset(self::$filter['store_id']) ? "(SELECT * FROM " . $this->table() . " WHERE `store_id` = '" . (int)self::$filter['store_id'] . "') rt" : $this->table();
		$minutes = array();

		foreach ($this->db->query("SELECT TIMESTAMPDIFF(MINUTE, `date_added`, NOW()) AS m, COUNT(*) AS views, COUNT(DISTINCT `visitor`) AS users FROM " . $table . " WHERE `date_added` > DATE_SUB(NOW(), INTERVAL 30 MINUTE) AND `date_added` <= NOW() AND `type` = 'page_view' GROUP BY m")->rows as $row) {
			$minutes[(int)$row['m']] = array('views' => (int)$row['views'], 'users' => (int)$row['users']);
		}

		$per_minute = array();

		for ($m = 29; $m >= 0; $m--) {
			$per_minute[] = isset($minutes[$m]) ? $minutes[$m]['views'] : 0;
		}

		$now = $this->db->query("SELECT COUNT(DISTINCT `visitor`) AS users FROM " . $table . " WHERE `date_added` > DATE_SUB(NOW(), INTERVAL 5 MINUTE) AND `date_added` <= NOW()")->row;
		$half = $this->db->query("SELECT COUNT(DISTINCT `visitor`) AS users FROM " . $table . " WHERE `date_added` > DATE_SUB(NOW(), INTERVAL 30 MINUTE) AND `date_added` <= NOW()")->row;

		// what already happened (a clock running ahead on the shop side must not show «active» visits from the future)
		$where = "`date_added` > DATE_SUB(NOW(), INTERVAL 30 MINUTE) AND `date_added` <= NOW()";

		return array(
			'active'     => (int)$now['users'],
			'active_30'  => (int)$half['users'],
			'per_minute' => $per_minute,
			'pages'      => $this->withNames($this->db->query("SELECT `path`, MIN(`page_type`) AS page_type, MIN(`item_id`) AS item_id, COUNT(*) AS views FROM " . $table . " WHERE " . $where . " AND `type` = 'page_view' GROUP BY `path` ORDER BY views DESC LIMIT 8")->rows),
			'channels'   => $this->db->query("SELECT `channel` AS name, COUNT(DISTINCT `visitor`) AS users FROM " . $table . " WHERE " . $where . " GROUP BY `channel` ORDER BY users DESC")->rows,
			'devices'    => $this->db->query("SELECT `device` AS name, COUNT(DISTINCT `visitor`) AS users FROM " . $table . " WHERE " . $where . " GROUP BY `device` ORDER BY users DESC")->rows,
			'events'     => $this->db->query("SELECT `type`, `date_added`, TIMESTAMPDIFF(MINUTE, `date_added`, NOW()) AS ago, `item_id`, `value`, `order_id`, `device`, `channel` FROM " . $table . " WHERE " . $where . " AND `type` <> 'page_view' ORDER BY `hit_id` DESC LIMIT 12")->rows
		);
	}

	// ------------------------------------------------------------------------------------------- names

	// product / category / brand / page names for the paths that point to them
	private function withNames($rows) {
		$ids = array('product' => array(), 'category' => array(), 'info' => array());

		foreach ($rows as $row) {
			if (!empty($row['item_id']) && isset($ids[$row['page_type']])) {
				$ids[$row['page_type']][] = (int)$row['item_id'];
			}
		}

		$lang = (int)$this->config->get('config_language_id');
		$names = array();
		$tables = array('product' => array('product_description', 'product_id', 'name'), 'category' => array('category_description', 'category_id', 'name'), 'info' => array('information_description', 'information_id', 'title'));

		foreach ($ids as $type => $list) {
			if ($list) {
				list($table, $key, $field) = $tables[$type];

				foreach ($this->db->query("SELECT `" . $key . "` AS id, `" . $field . "` AS name FROM `" . DB_PREFIX . $table . "` WHERE `language_id` = '" . $lang . "' AND `" . $key . "` IN (" . implode(',', array_unique($list)) . ")")->rows as $r) {
					$names[$type][$r['id']] = html_entity_decode($r['name'], ENT_QUOTES, 'UTF-8');
				}
			}
		}

		foreach ($rows as &$row) {
			$row['name'] = isset($names[$row['page_type']][$row['item_id']]) ? $names[$row['page_type']][$row['item_id']] : '';
		}

		return $rows;
	}
}
