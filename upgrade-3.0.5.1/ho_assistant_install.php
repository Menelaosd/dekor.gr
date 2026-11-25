<?php
// The shopping assistant (AI chat, ported from oc_ho_blank) for both stores.
//
//   php upgrade-3.0.5.1/ho_assistant_install.php            -> installed: module, event, Administrator permission
//   php upgrade-3.0.5.1/ho_assistant_install.php --remove   -> off: event + module out (chats / settings kept)
//
// Idempotent. It shows on the stores only once an API key is saved in Extensions > Modules > HO Shopping Assistant
// (or the model «mock» for a test); with «Μόνο για τον πελάτη» filled in, only that logged-in customer sees it.
// No core / OCMOD file is touched (the widget comes through the event ho_assistant): no Modifications > Refresh.
if (PHP_SAPI !== 'cli') {
	exit;
}

require dirname(__DIR__) . '/config.php';

$remove = in_array('--remove', $argv);
$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, (int)DB_PORT);
$db->set_charset('utf8mb4');
$p = DB_PREFIX;

$db->query("DELETE FROM `{$p}event` WHERE `code` = 'ho_assistant'");

if ($remove) {
	$db->query("DELETE FROM `{$p}extension` WHERE `type` = 'module' AND `code` = 'ho_assistant'");
	$db->query("UPDATE `{$p}setting` SET `value` = '0' WHERE `key` = 'module_ho_assistant_status'");
} else {
	$db->query("INSERT INTO `{$p}event` SET `code` = 'ho_assistant', `trigger` = 'catalog/controller/common/header/before', `action` = 'event/ho_assistant/header', `status` = 1, `sort_order` = 0");

	if (!$db->query("SELECT 1 FROM `{$p}extension` WHERE `type` = 'module' AND `code` = 'ho_assistant'")->num_rows) {
		$db->query("INSERT INTO `{$p}extension` SET `type` = 'module', `code` = 'ho_assistant'");
	}

	// first install: the package's defaults, bank transfer as the only payment completed in the chat
	if (!$db->query("SELECT 1 FROM `{$p}setting` WHERE `code` = 'module_ho_assistant' AND `store_id` = 0")->num_rows) {
		$defaults = array(
			array('module_ho_assistant_status', '1', 0),
			array('module_ho_assistant_model', 'gpt-4.1-mini', 0),
			array('module_ho_assistant_budget', '5', 0),
			array('module_ho_assistant_rate', '30', 0),
			array('module_ho_assistant_payments', '["bank_transfer"]', 1),
			array('module_ho_assistant_retention', '90', 0),
		);

		foreach ($defaults as $d) {
			$db->query("INSERT INTO `{$p}setting` SET `store_id` = 0, `code` = 'module_ho_assistant', `key` = '" . $d[0] . "', `value` = '" . $db->real_escape_string($d[1]) . "', `serialized` = " . (int)$d[2]);
		}
	} else {
		$db->query("UPDATE `{$p}setting` SET `value` = '1' WHERE `key` = 'module_ho_assistant_status'");
	}

	// the chats / daily spend tables: made by the assistant itself (Ho\Assistant::install) on first use
}

// Administrator (every group that can manage user permissions): access + modify extension/module/ho_assistant
$groups = $db->query("SELECT `user_group_id`, `name`, `permission` FROM `{$p}user_group`");

while ($g = $groups->fetch_assoc()) {
	$perm = json_decode($g['permission'], true);

	if (!is_array($perm) || empty($perm['access']) || !in_array('user/user_permission', $perm['access'])) {
		continue;
	}

	foreach (array('access', 'modify') as $type) {
		$list = array_values(array_diff(isset($perm[$type]) ? $perm[$type] : array(), array('extension/module/ho_assistant')));

		if (!$remove) {
			$list[] = 'extension/module/ho_assistant';
		}

		$perm[$type] = $list;
	}

	$db->query("UPDATE `{$p}user_group` SET `permission` = '" . $db->real_escape_string(json_encode($perm)) . "' WHERE `user_group_id` = " . (int)$g['user_group_id']);
	echo "permission " . ($remove ? 'removed from' : 'given to') . " group: " . $g['name'] . "\n";
}

echo $remove ? "Assistant OFF (event and module removed; chats and settings kept)\n" : "Assistant installed: Extensions > Modules > HO Shopping Assistant (it shows once a key is saved)\n";
