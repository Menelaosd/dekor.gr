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
if (file_exists($root . 'config.php')) {require_once($root . 'config.php');};
if (file_exists($root . 'system/startup.php')) {require_once($root . 'system/startup.php');};
global $loader,$registry,$config;
$registry = new Registry();	
$loader = new Loader($registry);
$registry->set('load', $loader);
$config = new Config();
$registry->set('config', $config);
$cache = new Cache('file');
$registry->set('cache', $cache);
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

$finalfile='dekor_products.csv';		
$mainTableParts = array();
$file = fopen($finalfile, 'r');	

while (($result = fgetcsv($file,0,'^','"')) !== false){
	$mainTableParts[] = $result;	
};

foreach($mainTableParts as $i => $product) {
	
	$product_info = $db->query("
		SELECT 
		*
		FROM " . DB_PREFIX . "product p
		WHERE 
		p.model = '" . trim($product[0]). "'"
	)->rows;
	
	if(!$product_info) {
		echo $i.') NOT FOUND '.$product[0].'<br>';
	}
	
	if($product_info) {
		foreach($product_info as $prod_info) {
			$db->query("
					UPDATE " . DB_PREFIX . "product 
					SET
					jan = '" . trim($product[1]). "'
					WHERE product_id = '".$prod_info['product_id']."'
				"
			);
		}
	}
}