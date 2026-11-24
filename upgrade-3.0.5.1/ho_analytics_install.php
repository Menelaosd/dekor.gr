<?php
// Reports > Analytics (ported from oc_ho_blank): switches it on (or off) for both stores.
//
//   php upgrade-3.0.5.1/ho_analytics_install.php            -> on: table, events, Administrator permission
//   php upgrade-3.0.5.1/ho_analytics_install.php --remove   -> off: events + permission out (the visits table stays)
//
// Idempotent: a second run changes nothing. No core / OCMOD file is touched: the measurement and the admin
// menu item run through OpenCart events (code ho_analytics), so no Modifications > Refresh is needed.
if (PHP_SAPI !== 'cli') {
	exit;
}

require dirname(__DIR__) . '/config.php';

$remove = in_array('--remove', $argv);
$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, (int)DB_PORT);
$db->set_charset('utf8mb4');
$p = DB_PREFIX;

$events = array(
	array('catalog/controller/common/footer/before', 'event/ho_analytics/pageView'),
	array('catalog/controller/checkout/cart/add/after', 'event/ho_analytics/addToCart'),
	array('catalog/controller/checkout/success/before', 'event/ho_analytics/purchase'),
	array('admin/view/common/column_left/before', 'report/ho_analytics/menu'),
);

$db->query("DELETE FROM `{$p}event` WHERE `code` = 'ho_analytics'");

if (!$remove) {
	foreach ($events as $e) {
		$db->query("INSERT INTO `{$p}event` SET `code` = 'ho_analytics', `trigger` = '" . $db->real_escape_string($e[0]) . "', `action` = '" . $db->real_escape_string($e[1]) . "', `status` = 1, `sort_order` = 0");
	}

	// the visits table (the admin page also creates it on first open)
	$db->query("CREATE TABLE IF NOT EXISTS `{$p}ho_analytics_hit` (
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

// Administrator (every group that can manage user permissions): access + modify report/ho_analytics
$groups = $db->query("SELECT `user_group_id`, `name`, `permission` FROM `{$p}user_group`");

while ($g = $groups->fetch_assoc()) {
	$perm = json_decode($g['permission'], true);

	if (!is_array($perm) || empty($perm['access']) || !in_array('user/user_permission', $perm['access'])) {
		continue;
	}

	foreach (array('access', 'modify') as $type) {
		$list = isset($perm[$type]) ? $perm[$type] : array();
		$list = array_values(array_diff($list, array('report/ho_analytics')));

		if (!$remove) {
			$list[] = 'report/ho_analytics';
		}

		$perm[$type] = $list;
	}

	$db->query("UPDATE `{$p}user_group` SET `permission` = '" . $db->real_escape_string(json_encode($perm)) . "' WHERE `user_group_id` = " . (int)$g['user_group_id']);
	echo "permission " . ($remove ? 'removed from' : 'given to') . " group: " . $g['name'] . "\n";
}

echo $remove ? "Analytics OFF (events removed; visits table kept)\n" : "Analytics ON: Reports > Analytics in the admin\n";
