<?php
/**
 * BoxNow multistore configuration tool (safe to commit — contains NO credentials).
 *
 * Run it ON THE LIVE SERVER, paste the credentials into the form, apply.
 *
 *   https://YOUR-DOMAIN/boxnow_autoconfig.php?token=YOUR_TOKEN
 *
 * WHAT IT DOES
 *   - Ensures {prefix}boxnow_requests has the locker_address / locker_name columns.
 *   - Saves per-store BoxNow settings (Store 0 = DEKOR, Store 2 = APOKRIFA):
 *       API URL, Client ID, Client Secret, Partner ID, Warehouse, Max weight, Status.
 *   - Idempotent: re-running just rewrites the same keys.
 *
 * SECURITY
 *   - No secrets live in this file, so it is safe to keep in git.
 *   - Access is gated by ACCESS_TOKEN below — change it before deploying.
 *   - Delete the file after use:  ?token=YOUR_TOKEN&cleanup=1  (self-delete).
 */

// ---------------------------------------------------------------------------
// CHANGE THIS before deploying. It is only an access gate, not a credential.
// ---------------------------------------------------------------------------
define('ACCESS_TOKEN', 'CHANGE_ME_BEFORE_DEPLOY');

// Stores this tool manages: store_id => label shown in the form.
$MANAGED_STORES = array(
    0 => 'DEKOR (default store)',
    2 => 'APOKRIFA',
);

// Fields managed per store.
$STORE_FIELDS = array(
    'shipping_boxnow_api_url'          => array('label' => 'API URL',       'default' => 'https://api-production.boxnow.gr'),
    'shipping_boxnow_client_id'        => array('label' => 'Client ID',     'default' => ''),
    'shipping_boxnow_client_secret'    => array('label' => 'Client Secret', 'default' => ''),
    'shipping_boxnow_partner_id'       => array('label' => 'Partner ID',    'default' => ''),
    'shipping_boxnow_warehouse_number' => array('label' => 'Warehouse / origin location ID', 'default' => ''),
);

// ---------------------------------------------------------------------------
// Boot
// ---------------------------------------------------------------------------
$token = isset($_REQUEST['token']) ? (string)$_REQUEST['token'] : '';
if (!hash_equals(ACCESS_TOKEN, $token)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Forbidden. Append ?token=YOUR_TOKEN (set ACCESS_TOKEN inside the file first).\n");
}
if (ACCESS_TOKEN === 'CHANGE_ME_BEFORE_DEPLOY') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Refusing to run: change ACCESS_TOKEN inside this file before using it.\n");
}
if (isset($_GET['cleanup'])) {
    header('Content-Type: text/plain; charset=utf-8');
    exit(@unlink(__FILE__) ? "Deleted. Done.\n" : "Could not self-delete; remove the file manually.\n");
}

// DB connection from OpenCart config.php
$config_path = __DIR__ . '/config.php';
if (!is_file($config_path)) {
    exit('Could not find config.php next to this script.');
}
require_once($config_path);
foreach (array('DB_HOSTNAME', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'DB_PREFIX') as $c) {
    if (!defined($c)) { exit("config.php missing $c."); }
}
$port = defined('DB_PORT') && DB_PORT ? (int)DB_PORT : 3306;
mysqli_report(MYSQLI_REPORT_OFF);
$db = @new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, $port);
if ($db->connect_errno) { exit('DB connection failed: ' . $db->connect_error); }
$db->set_charset('utf8');
$prefix = DB_PREFIX;

// Helpers
function get_setting($db, $prefix, $store_id, $key) {
    $store_id = (int)$store_id;
    $k = $db->real_escape_string($key);
    $r = $db->query("SELECT value FROM `{$prefix}setting` WHERE store_id = {$store_id} AND `key` = '{$k}' LIMIT 1");
    return ($r && $r->num_rows) ? $r->fetch_assoc()['value'] : '';
}
function set_setting($db, $prefix, $store_id, $key, $value) {
    $store_id = (int)$store_id;
    $k = $db->real_escape_string($key);
    $v = $db->real_escape_string($value);
    $db->query("DELETE FROM `{$prefix}setting` WHERE store_id = {$store_id} AND `key` = '{$k}'");
    $db->query("INSERT INTO `{$prefix}setting` SET store_id = {$store_id}, `code` = 'shipping_boxnow', `key` = '{$k}', `value` = '{$v}', serialized = 0");
}
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$log = array();

// Ensure schema (always safe)
$db->query("
    CREATE TABLE IF NOT EXISTS `{$prefix}boxnow_requests` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `order_id` int(11) NOT NULL,
      `request_id` int(11) NOT NULL,
      `parcels` text NOT NULL,
      `locker_id` int(11) NOT NULL,
      `locker_address` varchar(255) NOT NULL DEFAULT '',
      `locker_name` varchar(255) NOT NULL DEFAULT '',
      `status_message` text DEFAULT NULL,
      `status` int(11) NOT NULL,
      PRIMARY KEY (`id`), KEY `order_id` (`order_id`), KEY `request_id` (`request_id`),
      KEY `status` (`status`), KEY `locker_id` (`locker_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
");
foreach (array('locker_address', 'locker_name') as $col) {
    $r = $db->query("SHOW COLUMNS FROM `{$prefix}boxnow_requests` LIKE '{$col}'");
    if ($r && $r->num_rows === 0) {
        $db->query("ALTER TABLE `{$prefix}boxnow_requests` ADD `{$col}` varchar(255) NOT NULL DEFAULT '' AFTER `locker_id`");
        $log[] = "Added column `{$col}`.";
    }
}

// ---------------------------------------------------------------------------
// Apply on POST
// ---------------------------------------------------------------------------
$applied = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($MANAGED_STORES as $store_id => $label) {
        foreach ($STORE_FIELDS as $key => $meta) {
            $field = "s{$store_id}_{$key}";
            if (isset($_POST[$field])) {
                $val = trim((string)$_POST[$field]);
                // Don't wipe an existing value with a blank field.
                if ($val === '') { continue; }
                set_setting($db, $prefix, $store_id, $key, $val);
            }
        }
        $log[] = "[{$label} / store {$store_id}] settings saved.";
    }

    // Global (default store) options
    $max_weight = isset($_POST['max_weight']) ? (float)$_POST['max_weight'] : 10;
    set_setting($db, $prefix, 0, 'shipping_boxnow_max_weight', (string)$max_weight);
    set_setting($db, $prefix, 0, 'shipping_boxnow_status', isset($_POST['status']) ? '1' : '0');
    $log[] = "Default store: max weight = {$max_weight} kg, status = " . (isset($_POST['status']) ? 'ENABLED' : 'disabled') . '.';
    $applied = true;
}

// ---------------------------------------------------------------------------
// Render
// ---------------------------------------------------------------------------
header('Content-Type: text/html; charset=utf-8');
$action = h($_SERVER['PHP_SELF']) . '?token=' . urlencode($token);
$cur_max = get_setting($db, $prefix, 0, 'shipping_boxnow_max_weight');
if ($cur_max === '') { $cur_max = '10'; }
$cur_status = get_setting($db, $prefix, 0, 'shipping_boxnow_status') === '1';
?>
<!doctype html>
<html lang="el">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>BoxNow multistore configuration</title>
<style>
  body{font-family:system-ui,Arial,sans-serif;max-width:760px;margin:30px auto;padding:0 16px;color:#222}
  h1{font-size:20px} h2{font-size:16px;margin-top:28px;border-bottom:1px solid #eee;padding-bottom:6px}
  label{display:block;font-size:13px;margin:10px 0 3px;font-weight:600}
  input[type=text],input[type=password],input[type=number]{width:100%;padding:8px;border:1px solid #bbb;border-radius:6px;font-size:14px;box-sizing:border-box}
  .row{display:flex;gap:8px;align-items:center}
  .ok{background:#e7f6e7;border:1px solid #9bd49b;padding:10px 14px;border-radius:8px;margin:14px 0}
  .note{background:#fff7e6;border:1px solid #f0d088;padding:10px 14px;border-radius:8px;margin:14px 0;font-size:13px}
  button{margin-top:20px;background:#7d1f7a;color:#fff;border:0;padding:12px 22px;border-radius:8px;font-size:15px;cursor:pointer}
  small{color:#666}
</style>
</head>
<body>
<h1>BoxNow multistore configuration</h1>

<?php if ($applied): ?>
  <div class="ok"><strong>Applied.</strong><br><?php echo implode('<br>', array_map('h', $log)); ?></div>
  <div class="note">Done. Now <strong>delete this file</strong> for security:
    <a href="<?php echo $action; ?>&amp;cleanup=1">click here to self-delete</a>, or remove it via FTP.</div>
<?php else: ?>
  <div class="note">No secrets are stored in this file. Paste the credentials below; blank fields keep the current value.
    Delete this file after you are done.</div>
<?php endif; ?>

<form method="post" action="<?php echo $action; ?>" autocomplete="off">
<?php foreach ($MANAGED_STORES as $store_id => $label): ?>
  <h2><?php echo h($label); ?> &mdash; store_id <?php echo (int)$store_id; ?></h2>
  <?php foreach ($STORE_FIELDS as $key => $meta):
        $current = get_setting($db, $prefix, $store_id, $key);
        $value   = $current !== '' ? $current : $meta['default'];
        $type    = (strpos($key, 'secret') !== false) ? 'password' : 'text';
  ?>
    <label for="s<?php echo $store_id . '_' . $key; ?>"><?php echo h($meta['label']); ?></label>
    <input type="<?php echo $type; ?>" id="s<?php echo $store_id . '_' . $key; ?>"
           name="s<?php echo $store_id . '_' . $key; ?>" value="<?php echo h($value); ?>">
  <?php endforeach; ?>
<?php endforeach; ?>

  <h2>Global options (default store)</h2>
  <label for="max_weight">Max weight (kg) — hide BoxNow above this</label>
  <input type="number" step="0.1" id="max_weight" name="max_weight" value="<?php echo h($cur_max); ?>">
  <label class="row" style="margin-top:14px;font-weight:600">
    <input type="checkbox" name="status" value="1" style="width:auto;margin-right:8px" <?php echo $cur_status ? 'checked' : ''; ?>>
    Enable BoxNow shipping method
  </label>

  <button type="submit">Save configuration</button>
</form>

<p><small>Note: per-store cost, free-shipping threshold, tax class and geo zone are managed in
Admin &rsaquo; Extensions &rsaquo; Shipping &rsaquo; BoxNow (with the store selector).</small></p>
</body>
</html>
<?php $db->close(); ?>
