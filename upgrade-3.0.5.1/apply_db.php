<?php
/**
 * dekor.gr — OpenCart 3.0.2.0 -> 3.0.5.1: database changes.
 *
 * The stock install/upgrade wizard is NOT used on purpose: it drops every
 * non-stock index, adds Google Ads events, converts collations etc.
 * The 3.0.5.1 code needs no schema change on this database (checked column by
 * column), so the only change is two OCMOD searches that 3.0.5.1 renamed
 * (glob() -> safe_glob()).
 *
 * Run from the shop root, CLI only, safe to run again:
 *   php upgrade-3.0.5.1/apply_db.php          (dry run)
 *   php upgrade-3.0.5.1/apply_db.php --apply
 * Then: admin -> Extensions -> Modifications -> Refresh.
 */

if (php_sapi_name() !== 'cli') {
	http_response_code(404);
	exit;
}

require __DIR__ . '/../config.php';

$apply = in_array('--apply', $argv);

$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, (int)DB_PORT);
$db->set_charset('utf8');

$changes = array(
	// Modification Manager: refresh() now calls safe_glob()
	array(
		'name'    => 'Modification Manager',
		'search'  => '<search index="0"><![CDATA[$files = glob($path, GLOB_BRACE);]]></search>',
		'replace' => '<search index="0"><![CDATA[$files = safe_glob($path, GLOB_BRACE);]]></search>',
	),
	// d_opencart_patch: SVG in the admin file manager (3.0.5.1 also lists webp)
	array(
		'name'    => 'd_opencart_patch',
		'search'  => '<search><![CDATA[$files = glob($directory . \'/\' . $filter_name . \'*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}\', GLOB_BRACE);]]></search>',
		'replace' => '<search><![CDATA[$files = safe_glob($directory . \'/\' . $filter_name . \'*.{jpg,jpeg,png,gif,webp,JPG,JPEG,PNG,GIF,WEBP}\', GLOB_BRACE);]]></search>',
	),
	array(
		'name'    => 'd_opencart_patch',
		'search'  => '<add position="replace"><![CDATA[$files = glob($directory . \'/\' . $filter_name . \'*.{jpg,jpeg,svg,png,gif,JPG,JPEG,PNG,GIF}\', GLOB_BRACE);]]></add>',
		'replace' => '<add position="replace"><![CDATA[$files = safe_glob($directory . \'/\' . $filter_name . \'*.{jpg,jpeg,svg,png,gif,webp,JPG,JPEG,PNG,GIF,WEBP}\', GLOB_BRACE);]]></add>',
	),
);

foreach ($changes as $change) {
	$result = $db->query("SELECT modification_id, xml FROM `" . DB_PREFIX . "modification` WHERE name = '" . $db->real_escape_string($change['name']) . "'");

	if (!$result->num_rows) {
		echo "SKIP  {$change['name']}: modification not installed\n";
		continue;
	}

	while ($row = $result->fetch_assoc()) {
		if (strpos($row['xml'], $change['replace']) !== false) {
			echo "OK    {$change['name']} #{$row['modification_id']}: already applied\n";
		} elseif (strpos($row['xml'], $change['search']) !== false) {
			echo ($apply ? "APPLY" : "TODO ") . " {$change['name']} #{$row['modification_id']}\n";

			if ($apply) {
				$xml = str_replace($change['search'], $change['replace'], $row['xml']);

				$db->query("UPDATE `" . DB_PREFIX . "modification` SET xml = '" . $db->real_escape_string($xml) . "' WHERE modification_id = '" . (int)$row['modification_id'] . "'");
			}
		} else {
			echo "WARN  {$change['name']} #{$row['modification_id']}: search text not found, check by hand\n";
		}
	}
}

// Settings that 3.0.5.1 reads under a corrected name. The old value is copied, so nothing changes for the shop.
$renamed = array(
	// totals order: 3.0.2.0 read sub_total_sort_order, 3.0.5.1 reads total_sub_total_sort_order
	array('code' => 'total_sub_total', 'old' => 'sub_total_sort_order', 'new' => 'total_sub_total_sort_order'),
);

foreach ($renamed as $r) {
	$stores = $db->query("SELECT store_id, value FROM `" . DB_PREFIX . "setting` WHERE `key` = '" . $db->real_escape_string($r['old']) . "'");

	while ($row = $stores->fetch_assoc()) {
		$exists = $db->query("SELECT setting_id FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$row['store_id'] . "' AND `key` = '" . $db->real_escape_string($r['new']) . "'")->num_rows;

		if ($exists) {
			echo "OK    setting {$r['new']} (store {$row['store_id']}): already there\n";
		} else {
			echo ($apply ? "APPLY" : "TODO ") . " setting {$r['new']} = {$row['value']} (store {$row['store_id']}, copied from {$r['old']})\n";

			if ($apply) {
				$db->query("INSERT INTO `" . DB_PREFIX . "setting` SET store_id = '" . (int)$row['store_id'] . "', `code` = '" . $db->real_escape_string($r['code']) . "', `key` = '" . $db->real_escape_string($r['new']) . "', `value` = '" . $db->real_escape_string($row['value']) . "', serialized = '0'");
			}
		}
	}
}

if (!$apply) {
	echo "\nDry run. Re-run with --apply.\n";
} else {
	echo "\nDone. Now: admin -> Extensions -> Modifications -> Refresh.\n";
}
