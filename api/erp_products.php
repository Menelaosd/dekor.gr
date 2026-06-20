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

$erp_username = 'dekor';
$erp_password = 'dekor1919';
$erp_appid = '10001';
$charset = 'windows-1253';

//LOGIN
$login_data = cloud_soft1_request(array(
    "service" => "login",
    'username' => $erp_username,
    'password' => $erp_password,
    'appId' => $erp_appid,
));


//LOGIN
$login_data = cloud_soft1_request(array(
    "service" => "login",
    'username' => $erp_username,
    'password' => $erp_password,
    'appId' => $erp_appid,
));

if ($login_data['success'] == 1) {

	$client_id = $login_data['clientID'];
    $authenticate_data = cloud_soft1_request(array(
        "service" => "authenticate",
        'clientID' => $client_id,
        'COMPANY' => $login_data['objs'][0]['COMPANY'],
        'BRANCH' => $login_data['objs'][0]['BRANCH'],
        'MODULE' => $login_data['objs'][0]['MODULE'],
        'REFID' => $login_data['objs'][0]['REFID'],
    ));	
	
    if ($authenticate_data['success'] == 1) {
		$client_id = $authenticate_data['clientID'];
	}
}

$productsInfo = cloud_soft1_request(array(
        "service" => "getBrowserInfo",
        'clientID' => $client_id,
		'appId' => $erp_appid,
        "LIST" => "",
		"OBJECT" => "ITEM"
    ));	
	
$productsData = cloud_soft1_request(array(
        "service" => "getBrowserData",
        'clientID' => $client_id,
		'appId' => $erp_appid,
		'reqID' => $productsInfo['reqID'],
		"START" => 0,
		"LIMIT" => 30000
    ));	
	
$finalfile='dekor_products.csv';		
$mainTableParts = array();
$file = fopen($finalfile, 'r');	

while (($result = fgetcsv($file,0,'^','"')) !== false){
	$mainTableParts[] = $result;	
};

	
if($productsData['rows']) {
	foreach($productsData['rows'] as $product) {
		$db->query("
				UPDATE " . DB_PREFIX . "product 
				SET
				erp_id = '" . trim(str_replace('ITEM;','',$product['0'])). "'
				WHERE (jan = '".$db->escape(trim($product[2]))."' OR model = '".$db->escape(trim($product[2]))."')
			"
		);
	}
}

function cloud_soft1_request($data) {
    $data = json_encode($data);
    $url = 'http://dekor.oncloud.gr/s1services';
    $ch = curl_init();
    $format="json";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_ENCODING, ''); //gzip,deflate
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept-encoding: gzip',
            'Content-Length: ' . strlen($data))
    );
    $result = curl_exec($ch);
    $result_data = json_decode(trim(mb_convert_encoding($result,'utf-8','iso-8859-7')),true);
    curl_close($ch);
    return $result_data;
}
?>