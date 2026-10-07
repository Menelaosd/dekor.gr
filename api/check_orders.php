<?php
//ERROR REPORTING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('max_input_time', 30000);
ini_set('max_execution_time', 30000);
//ini_set('memory_limit','16M');
header( 'Content-type: text/html; charset=utf-8' );
set_time_limit(0);
//OPENCART FRAMEWORK
$root = $_SERVER['DOCUMENT_ROOT'].'/';  

//$root = '/home/qpharma/public_html/';

if (file_exists($root . 'config.php')) {require_once($root . 'config.php');};
if (file_exists($root . 'system/startup.php')) {require_once($root . 'system/startup.php');};
global $loader,$registry,$config;
$registry = new Registry();	

// Route
$route = new Router($registry);

$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$config->load('default');

$config->load('admin');
$config->load('catalog');

$registry->set('config', $config);

// Log
$log = new Log($config->get('error_filename'));
$registry->set('log', $log);

date_default_timezone_set($config->get('date_timezone'));

$cache = new Cache('file');
$registry->set('cache', $cache);

$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

// Settings
$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");
foreach ($query->rows as $setting) {
	if (!$setting['serialized']) {
		$config->set($setting['key'], $setting['value']);
	} else {
		$config->set($setting['key'], json_decode($setting['value'], true));
	}
};
// Request
$request = new Request();
$registry->set('request', $request);

$event = new Event($registry);
$registry->set('event', $event);

// Event Register
if ($config->has('action_event')) {
	foreach ($config->get('action_event') as $key => $value) {
		foreach ($value as $priority => $action) {
			$event->register($key, new Action($action), $priority);
		}
	}
};

$document = new Document();
$registry->set('document', $document);

// The Google Tag Manager OCMOD calls $this->gtm->saveOrderID() inside addOrderHistory(). It needs a browser
// session and only matters on the checkout success page, so the cron gets a do-nothing stand-in
// (without it the script crashed on the first order and never cancelled anything).
$registry->set('gtm', new class {
	public function __call($name, $args) {
		return false;
	}
});

//GET OPENCART ORDER DATA
$loader->model('checkout/order');
$orderModel = $registry->get('model_checkout_order');

$loader->model('setting/setting');
$settingModel = $registry->get('model_setting_setting');

$language = new Language('el-gr');
$registry->set('language', $language);
$language->load('el-gr');
$language->load('mail/order_edit');

// Only orders placed after the first run of this fixed script are auto-cancelled: the script was broken for
// months, and the old pending orders must not be cancelled / emailed in one go now that it works again.
$since = $db->query("SELECT `value` FROM " . DB_PREFIX . "setting WHERE store_id = '0' AND `key` = 'dekor_check_orders_since' LIMIT 1");

if (!$since->num_rows) {
	$db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `code` = 'dekor_check_orders', `key` = 'dekor_check_orders_since', `value` = NOW(), serialized = '0'");
	echo 'First run: only orders placed from now on will be auto-cancelled.';
	exit;
}

$orders = $db->query("SELECT * FROM " . DB_PREFIX . "order WHERE order_status_id = '1' AND date_added >= '" . $db->escape($since->row['value']) . "' AND DATE(date_added) < DATE_SUB(CURDATE(), INTERVAL 10 DAY) ORDER BY date_added ASC")->rows;

// no customer data in the output: the script is reachable over the web
echo count($orders) . ' order(s) auto-cancelled';

$comment 			= 'Η Παραγγελία σας ακυρώθηκε αυτόματα μετά το πέρας των 10 ημερών';

foreach($orders as $order) {
	
	$from = 'info@dekor.gr';
	
	if($order['store_id'] == 2) {
		$from = 'info@apokrifa.gr';
	};
	
	$data = 'Αριθμός Παραγγελίας: '.$order['order_id'].'

	Η Παραγγελία σας ακυρώθηκε αυτόματα μετά το πέρας των 10 ημερών.

	Κατάσταση Παραγγελίας: ΑΚΥΡΩΜΕΝΗ

	Παρακαλώ απαντήστε σε αυτό το e-mail αν έχετε ερωτήσεις.';	
	
	$orderModel->addOrderHistory($order['order_id'],7,$comment,true);
	
	$mail = new Mail($config->get('config_mail_engine'));
	$mail->parameter = $config->get('config_mail_parameter');
	$mail->smtp_hostname = $config->get('config_mail_smtp_hostname');
	$mail->smtp_username = $config->get('config_mail_smtp_username');
	$mail->smtp_password = html_entity_decode($config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
	$mail->smtp_port = $config->get('config_mail_smtp_port');
	$mail->smtp_timeout = $config->get('config_mail_smtp_timeout');

	$mail->setTo($order['email']);
	$mail->setFrom($from);
	$mail->setSender(html_entity_decode($order['store_name'], ENT_QUOTES, 'UTF-8'));
	$mail->setSubject(html_entity_decode(sprintf($language->get('text_subject'), $order['store_name'], $order['order_id']), ENT_QUOTES, 'UTF-8'));
	$mail->setHtml(nl2br($data));
	
	$mail->send();
};
?>