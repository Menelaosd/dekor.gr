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

$reduced_vat_map = array(
	'821' => 1,
	'831' => 1,
	'832' => 1,
	'854' => 1,
	'853' => 1,
	'811' => 1
);

$payment_methods = array(
	'bank_transfer' 		=> 1002,
	'pp_standard' 	        => 1003,
	'stripe'				=> 1001,
	'cod' 					=> 1000,
);

$shipping_methods = array(
	'xshippingpro.xshippingpro3' 	=> 1009,
	'pickup.pickup' 				=> 1005,
	'xshippingpro.xshippingpro1'       => 1007,
	'xshippingpro.xshippingpro2'       => 1008,
	'free.free'       => 1006
);

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

echo 'GETTING NEW ORDERS FROM E-SHOP DATABASE</br></hr></br>';	

//GET THE ORDERS FROM OPENCART DATABASE
$orders = $db->query("
	SELECT 
	
	*		
	
	FROM " . DB_PREFIX . "order
	WHERE erp_id = '0'
	AND order_status_id != 0
	AND order_status_id != 11
	AND order_status_id != 7
	AND sent_flag != 1
	
	ORDER BY order_id DESC
")->rows;

if (count($orders) > 0) {
	
	echo 'RETRIEVED ORDERS FROM DATABASE, ORDERS FOUND:('.count($orders).')</br></hr></br>';
	
	//********* GOT DATABASE ORDERS ********//	
	foreach ($orders as $key => $order){
		
		$order_query = $db->query("
			SELECT 
			*
			
			FROM " . DB_PREFIX . "order
			WHERE order_id = '".$order['order_id']."'
		");
		
		$order_info = $order_query->row;
		
		if($order_info['erp_id']) {
			continue;
		};
		
		if ($order_info) {
			
			$VATSTS = 1;
			$REDUCED_VAT = false;
			if( isset($reduced_vat_map[substr($order_info['payment_postcode'], 0, 3)]) && $order_info['customer_group_id'] == 3) {
				$REDUCED_VAT = true;
				$VATSTS = 2;				
			}

			if($order_info['payment_country_id'] != 84 && $order_info['customer_group_id'] == 3) {
				$VATSTS = '0';
			}
			
			//********* GETTING ORDER PRODUCTS ********//	
			$order_info['order_products'] = $db->query("
				SELECT 
				op.*,
				p.package,
				p.erp_id,
				p.weight,
				p.price AS init_price
				FROM " . DB_PREFIX . "order_product op
				LEFT JOIN " . DB_PREFIX . "product p
				ON (op.product_id = p.product_id)
				WHERE 
				op.order_id = '" . (int)$order['order_id']. "'"
			)->rows;

			$order_info['total_products_cost'] = 0;
			$order_info['total_weight'] = 0;
			
			foreach ($order_info['order_products'] as $p_key => $order_product) {
				
				$order_info['total_products_cost'] 	+= $order_product['price'] * $order_product['quantity'];
				$order_info['total_weight'] 		+= $order_product['weight'];
				
				$product_post_data = array(
					"service" => "getData",
					'clientID' => $client_id,
					'appId' => $erp_appid,
					"OBJECT"=> "ITEM",
					"TABLE" => "MTRL",
					"FORM" => "",
					"KEY" => $order_product['erp_id'],
					"LOCATEINFO" => ""
				);
				
				$product_output = cloud_soft1_request($product_post_data);
				
				
				$search_product_result = $product_output;
				
				if ($search_product_result['success'] == 1) {
					
				} else {
					echo 'ERROR: PRODUCT <b>'.$order_product['erp_id'].'</b> NOT FOUND </br></hr></br>';
					break;
				}
			}
			//********* SETTING PRODUCT PRICES ********//	
		} else {
			break;
		}
		
		//********* GETTING CUSTOMER DATA ********//	
		
		$emailTemp 	= str_replace("@","?", $order_info['email']);
		
		$getErpCustomerData = array();
		
		//$PRCCATEGORY 	= 3099;
		// $JOBTYPE 		= 5;
		// $BRANCΗ 		= 10;
		
		$db_vat_data = json_decode($order_info['payment_custom_field'],TRUE);
		if($db_vat_data[0]) {
			$VATDATA = json_decode($order_info['payment_custom_field'],TRUE);
		} else {
			$VATDATA = json_decode($order_info['custom_field'],TRUE);
		}
		
		$AFM 		= '';
		$JOBTYPETRD = '';
		$IRSDATA 	= '';
		$comments_order = "Απόδειξη";
		$TRDCATEGORY = 3099;

		
	if($VATDATA[2] || $VATDATA && $order_info['customer_group_id'] == 3) {
			$AFM 		= $VATDATA[3];
			$JOBTYPETRD = $VATDATA[2];
			$IRSDATA 	= $VATDATA[4];
			$comments_order = "Τιμολόγιο";
			$TRDCATEGORY = "";
		};
		
		echo '<pre>';
		echo '<h2>VAT DATA</h2>';
		print_r($VATDATA);
		echo '</pre>';		
		
		if($AFM) {
			if(!$getErpCustomerData || ($getErpCustomerData && $getErpCustomerData['totalcount'] < 1) ) {	
				$getErpCustomerData = cloud_soft1_request(array(
					"service" 	=> 'getBrowserInfo',
					'clientID' 	=> $client_id,
					'appId' 	=> $erp_appid,
					"LIST" 		=> "",
					"OBJECT" 	=> "CUSTOMER",
					"FILTERS" 	=> "CUSTOMER.AFM=".$AFM."*"
				));	
			};
		};
		
		
		if(!$getErpCustomerData || ($getErpCustomerData && $getErpCustomerData['totalcount'] < 1) ) {
			$getErpCustomerData = cloud_soft1_request(array(
				"service" 	=> 'getBrowserInfo',
				'clientID' 	=> $client_id,
				'appId' 	=> $erp_appid,
				"LIST" 		=> "",
				"OBJECT" 	=> "CUSTOMER",
				"FILTERS" 	=> "CUSTOMER.EMAIL=".$emailTemp
			));
		};
			
		
		if(!$getErpCustomerData || ($getErpCustomerData && $getErpCustomerData['totalcount'] < 1) ) {
			echo $order_info['order_id'];
			$getErpCustomerData = cloud_soft1_request(array(
				"service" 	=> 'getBrowserInfo',
				'clientID' 	=> $client_id,
				'appId' 	=> $erp_appid,
				"LIST" 		=> "",
				"OBJECT" 	=> "CUSTOMER",
				"FILTERS" 	=> "CUSTOMER.PHONE01=".$order_info['telephone']."*"
			));	
		};		
		
		$order_customer_data = array();
		
		if ($getErpCustomerData['success'] == 1) {
			if ($getErpCustomerData['totalcount'] > 0) {
				if ($getErpCustomerData['reqID']) {
					$output_customer = cloud_soft1_request(array(
						"service" 	=> "getBrowserData",
						'clientID'	=> $client_id,
						'appId' 	=> $erp_appid,
						"reqID" 	=> $getErpCustomerData['reqID'],
						"START" 	=> "0",
						"LIMIT" 	=> "1"
					));
					
					// echo '<pre>';
					// print_r($output_customer);
					// echo '</pre>';
					
					//********* CUSTOMER EXISTS, UPDATING CUSTOMER DATA IN ERP DATABASE ********//	
					if ($getErpCustomerData['success'] == 1) {
						echo 'CUSTOMER FOUND IN ERP DATABASE</br></hr></br>';
						$order_customer_data = $output_customer['rows'][0];

					};
				} else {
					echo "Error on Customer reqID<br>";
					return;
				}
			} else {					
				
				echo 'INSERT CUSTOMER';					

				$new_customer = cloud_soft1_request(array(
					"service" 	=> "setData",
					'clientID' => $client_id,
					'appId' => $erp_appid,
					"OBJECT" 	=> "CUSTOMER",
					//"FORM" 	=> "E-SHOP",
					"data" 		=> array(
					"CUSTOMER" => array(
							array(
								"NAME" 					=> $order_info['lastname'].' '.$order_info['firstname'],
								"CODE" 					=> "e-*",
								"EMAIL" 				=> $order_info['email'],
								"PHONE01" 				=> $order_info['telephone'],
								"ADDRESS" 				=> $order_info['payment_address_1'],
								"CITY" 					=> $order_info['payment_zone'],
								"DISTRICT" 				=> $order_info['payment_city'],
								"ZIP" 					=> $order_info['payment_postcode'],
								"COUNTRY" 				=> $order_info['payment_country'],
								"JOBTYPETRD" 			=> $JOBTYPETRD,
								"IRSDATA" 				=> $IRSDATA,
								"AFM"					=> $AFM,
								"TRDCATEGORY" 			=> $TRDCATEGORY
								//"PRCCATEGORY" 			=> $PRCCATEGORY,
								// "JOBTYPE" 				=> $JOBTYPE,
								// "BRANCΗ" 				=> $BRANCΗ
							)
						)
					)
				));
					
		

				if ($new_customer['success'] == 1) {
					
					$getErpCustomerData = cloud_soft1_request(array(
						"service" 	=> 'getBrowserInfo',
						'clientID' => $client_id,
						'appId' => $erp_appid,
						"LIST" 		=> "",
						"OBJECT" 	=> "CUSTOMER",
						"FILTERS" 	=> "CUSTOMER.TRDR=".$new_customer['id']
					));
					
					$output_customer = cloud_soft1_request(array(
						"service" 	=> "getBrowserData",
						'clientID' 	=> $client_id,
						'appId' 	=> $erp_appid,
						"reqID" 	=> $getErpCustomerData['reqID'],
						"START" 	=> "0",
						"LIMIT" 	=> "1"
					));
					
					$order_customer_data = $output_customer['rows'][0];
					
				} else {
					echo 'FAILED TO INSERT CUSTOMER</br></hr></br>';
					continue;
				}

			}
		} else {
			echo "Error on Customer Search<br>";
			return;
		}
			
		$order_info['erp_trdr']				= str_replace('CUSTOMER;','',$order_customer_data[0]);
		$order_info['erp_customer_code']	 = $order_customer_data[1];
		
		$payment_code 	= $payment_methods[$order_info['payment_code']];
		$shipping_code	= $shipping_methods[$order_info['shipping_code']];		
		
		echo 'Payment Code:'.$payment_code.'<br>';
		echo 'shipping_code:'.$shipping_code.'<br>';
		
		//********* PREPARING ORDER DATA FROM ERP ********//	

		if($order_info['erp_customer_code'] > 0) {
			
			
			$order_total_list = array();
			$order_totals = $db->query("
				SELECT 
				* 
				FROM " . DB_PREFIX . "order_total
				WHERE order_id = '".$order_info['order_id']."'
			")->rows;
			
			foreach($order_totals as $order_total) {
				$order_total_list[$order_total['code']] = $order_total['value'];
			};
			
			$coupondisc = 0;
			
			$saldoc_data = array(
				"SERIES" 					=> '7022',
				//"INT01" 					=> '275',
				"TRNDATE" 					=> $order_info['date_added'],
				"TRDR" 						=> $order_info['erp_trdr'], // CUSTOMER ID
				"TRDR_CUSTOMER_CODE" 		=> $order_info['erp_customer_code'], // CUSTOMER ID
				"CMPFINCODE" 				=> 'eshop'.$order_info['order_id'],
				"FINCODE" 					=> 'eshop'.$order_info['order_id'],
				"PAYMENT" 					=> $payment_code,
				"VATSTS"					=> $VATSTS,
				"TRDR_CUSTOMER_VATSTS"		=> $VATSTS,
				"SHIPMENT" 					=> $shipping_code,
				"COMMENTS" 					=> $order_info['comment']
			);
			
			$item_data = array();
			foreach ($order_info['order_products'] as $order_product) {
				
				$line_price = $order_product['init_price'];
				$line_quantity = $order_product['quantity'];
				$line_total = $order_product['total'];

				// Package handling
				if ($order_product['package'] && (int)$order_product['package'] > 1) {
					$line_price = $order_product['init_price'] / $order_product['package'];
					$line_quantity = $order_product['package'] * $order_product['quantity'];
				}

				// ===== REDUCED VAT (24% -> 17%) =====
				if ($REDUCED_VAT) {
					
					echo '<pre>';
					echo '<h2>REDUCED ITEM DATA</h2>';
					print_r($REDUCED_VAT);
					echo '</pre>';	
					
					$line_price = ($line_price / 1.24) * 1.17;
					$line_total = ($line_total / 1.24) * 1.17;
				}

				$item_data[] = array(
					"MTRL"      => $order_product['erp_id'],
					"QTY"       => $line_quantity,
					"QTY1"      => $line_quantity,
					"PRICE"     => round($line_price, 4),
					"LINEVAL"   => round($line_total, 2)
				);
			}
			
		echo '<pre>';
		echo '<h2>ITEM DATA</h2>';
		print_r($item_data);
		echo '</pre>';	
			
			//ΠΡΕΠΕΙ ΝΑ ΣΕΤΑΡΩ ΑΥΤΑ
			$mtr_doc = array();
			
			$mtr_doc[] = array(
				"SHIPPINGADDR" 		=> $order_info['shipping_address_1'],  // PRODUCT MTRL
				"SHPZIP" 			=> $order_info['shipping_postcode'],
				"SHPDISTRICT" 		=> $order_info['shipping_city'],
				"SHPCITY" 			=> $order_info['shipping_zone'],
				"WHOUSE" 			=> '1000'
			);
			
			$exp_anal = array();			
			
			if(isset($order_total_list['shipping'])) {
				$exp_anal[] = array(
					"EXPN" 		=> '104',
					"EXPVAL"	=> $order_total_list['shipping']
				);	
			} else {
				$exp_anal[] = array(
					"EXPN" 		=> '104',
					"EXPVAL"	=> 0
				);	
			}	
			
			
			/*
			if($order_info['payment_code'] == 'cod') {
				$exp_anal[] = array(
					"EXPN" 		=> '101',
					"LINENUM" 	=> 90001,
					"EXPVAL"	=> ($order_total_list['xcod'] / 1.24)
				);
			};
			*/

			//********* SETTING NEW ORDER DATA IN ERP ********//	
			$order_body = array(
				"service" => "setData",
				'clientID' => $client_id,
				'appId' => $erp_appid,
				"OBJECT" => "SALDOC",
				"data" => array(
					"SALDOC" 	=> array($saldoc_data),
					"ITELINES" 	=> $item_data,
					"MTRDOC" 	=> $mtr_doc,
					"EXPANAL" 	=> $exp_anal
				)
			);
			
			$output_order = cloud_soft1_request($order_body);
			
			$new_order = $output_order;
		
			
			//********* NEW ORDER SUCCESS! ********//	
			if ($new_order['success'] == 1) {
				
				echo 'ESHOP ORDER <b>'.(int)$order_info['order_id'].'</b> SUCCESFULLY INSERTED IN ERP WITH ID <b>'.$new_order['id'].'</b></br></hr></br>';
				
				$new_order_erp_data = cloud_soft1_request(array(
					"service" => "getData",
					'clientID' => $client_id,
					'appId' => $erp_appid, 
					'OBJECT' => 'SALDOC',
					'LIST' => '',
					'KEY' => $new_order['id'],
					"FILTERS" => ""
				));
				
				$data['erp_order_id'] = $new_order['id'];
				
				
				//erp_date = '".$new_order_erp_data['data']['SALDOC'][0]['UPDDATE']."'
				$db->query("
					UPDATE " . DB_PREFIX . "order
					SET 
					erp_id = '".(int)$data['erp_order_id']."'
					
					WHERE order_id = '".(int)$order_info['order_id']."'
				");
			} else {
				echo 'ERROR INSERTING ORDER1 '.$order_info['order_id'].' ('.$new_order['errorcode'].')</b></br></hr></br>';
			}
			
		} else {
			echo 'ERROR INSERTING ORDER2 '.$order_info['order_id'].' ('.$new_order['errorcode'].')</br></hr></br>';
		}
		
				//erp_date = '".$new_order_erp_data['data']['SALDOC'][0]['UPDDATE']."'
				$db->query("
					UPDATE " . DB_PREFIX . "order
					SET 
					sent_flag = 1
					
					WHERE order_id = '".(int)$order_info['order_id']."'
				");		
	}
} else {
	echo 'NO ORDERS FOUND</br></hr></br>';
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