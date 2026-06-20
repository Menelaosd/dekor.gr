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

$erp_username = 'gini360';
$erp_password = 'Ab12345!';
$erp_appid = '1001';
$charset = 'windows-1253';

$payment_methods = array(
	'bank_transfer' 		=> 11,
	'ho_eurobank_gateway' 	=> 3,
	'cod' 					=> 12
);

$shipping_methods = array(
	//'xshippingpro.xshippingpro1' 	=> 106, TCS
	'xshippingpro.xshippingpro1' 	=> 104,
	'pickup.pickup' 				=> 101,
	'boxnow.boxnow' 				=> 107
);

//LOGIN
/*
$login_data = cloud_soft1_request(array(
    "service" => "login",
    'username' => $erp_username,
    'password' => $erp_password,
    'appId' => $erp_appid,
));
*/

$country_map = array(
	'GRE' 	=> 1,
	'CYP' 	=> 2,
	'ITA' 	=> 3,
	'SPA' 	=> 4,
	'GER' 	=> 5,
	'FRA' 	=> 6,
	'HOL' 	=> 7, //NETHERLANDS
	'FIN' 	=> 8,
	'BG' 	=> 9,
	'CHN' 	=> 10,
	'POL' 	=> 11,
	'ENG' 	=> 12,
	'SWE' 	=> 13,
	'RUS' 	=> 14,
	'DEN' 	=> 15,
	'ROM' 	=> 16,
	'ΗΠΑ' 	=> 17,
	'ALB' 	=> 18,
	'BG' 	=> 19,
	'KAN' 	=> 21,
	'CH' 	=> 22,
	'SW' 	=> 23, //ΕΛΒΕΤΙΑ
	'IRL' 	=> 24,
	'HU' 	=> 25,
	'ML' 	=> 26
);

$reduced_vat_map = array(
	'821' => 1,
	'831' => 1,
	'854' => 1,
	'853' => 1,
	'811' => 1
);

/*
echo '<pre>';
print_r($login_data);
echo '</pre>';			
*/

$client_id = NULL;
				
$query = $db->query("SELECT client_id FROM ".DB_PREFIX."client_id WHERE ref_id = '999' ")->row;

if($query) {
	$client_id = $query['client_id'];
};

if(!$client_id) {
	echo 'NO CLIENT ID';
	exit;
};

echo 'GETTING NEW ORDERS FROM E-SHOP DATABASE</br></hr></br>';	

//GET THE ORDERS FROM OPENCART DATABASE
$orders = $db->query("
	SELECT 
	
	*,
	CONCAT( invoice_date, LPAD(invoice_id, 4, '0') ) AS invoice_order_id			
	
	FROM " . DB_PREFIX . "order
	WHERE erp_id = '0'
	AND order_status_id != 0
	AND order_status_id != 16
	AND trash != 1
	
	ORDER BY order_id DESC
")->rows;

if (count($orders) > 0) {
	
	echo 'RETRIEVED ORDERS FROM DATABASE, ORDERS FOUND:('.count($orders).')</br></hr></br>';
	
	//********* GOT DATABASE ORDERS ********//	
	foreach ($orders as $key => $order){
		
		$order_query = $db->query("
			SELECT 
			*,
			CONCAT( invoice_date, LPAD(invoice_id, 4, '0') ) AS invoice_order_id
			
			FROM " . DB_PREFIX . "order
			WHERE order_id = '".$order['order_id']."'
		");
		
		$order_info = $order_query->row;
		
		if($order_info['erp_id']) {
			continue;
		};
		
		if ($order_info) {
			
			//********* GETTING ORDER PRODUCTS ********//	
			$order_info['order_products'] = $db->query("
				SELECT 
				op.*,
				p.erp_product_id,
				p.discvar,
				p.erp_id,
				p.package,
				p.weight,
				p.price AS init_price
				FROM " . DB_PREFIX . "order_product op
				LEFT JOIN " . DB_PREFIX . "product p
				ON (op.product_id = p.product_id)
				WHERE 
				op.order_id = '" . (int)$order['order_id']. "'"
			)->rows;
			
			//********* GETTING ORDER PRODUCTS ********//	
			
			//********* SETTING PRODUCT PRICES ********//	
			
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
		
		$PRCCATEGORY 	= 0;
		$JOBTYPE 		= 5;
		$BRANCΗ 		= 10;
		
		$VATDATA = json_decode($order_info['payment_custom_field'],TRUE);
		
		$AFM 		= '';
		$JOBTYPETRD = '';
		$IRSDATA 	= '';
		$PHONE2 	= '';
		
		$SALDOCTRDR_CUSTOMER_VATSTS = '';
		$SALDOCVATSTS  				= '';
		$MTRLINESVAT   				= '';
		
		$REDUCED_VAT = FALSE;
		
		if($VATDATA && $order_info['customer_group_id'] == 3) {
			$AFM 		= $VATDATA[3];
			$JOBTYPETRD = $VATDATA[2];
			$IRSDATA 	= $VATDATA[4];
			$PHONE2 	= $VATDATA[5];
			
			if( isset($reduced_vat_map[substr($order_info['payment_postcode'], 0, 3)]) ) {
				
				$REDUCED_VAT = true;
				$SALDOCTRDR_CUSTOMER_VATSTS = 2;
				$SALDOCVATSTS  				= 2;
				$MTRLINESVAT   				= 1170;
			}
		};
		
		if($AFM) {
			if(!$getErpCustomerData || ($getErpCustomerData && $getErpCustomerData['totalcount'] < 1) ) {	
				$getErpCustomerData = cloud_soft1_request(array(
					"service" 	=> 'getBrowserInfo',
					'clientID' 	=> $client_id,
					'appId' 	=> $erp_appid,
					"LIST" 		=> "DATAFEED3",
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
				"LIST" 		=> "DATAFEED3",
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
				"LIST" 		=> "DATAFEED3",
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
					
					echo '<pre>';
					print_r($output_customer);
					echo '</pre>';
					
					//********* CUSTOMER EXISTS, UPDATING CUSTOMER DATA IN ERP DATABASE ********//	
					if ($getErpCustomerData['success'] == 1) {
						
						echo 'CUSTOMER FOUND IN ERP DATABASE</br></hr></br>';
						
						$order_customer_data = $output_customer['rows'][0];
				

						/* UPDATE CUSTOMER
						cloud_soft1_request(array(
							"service" 	=> "setData",
							'clientID' 	=> $client_id,
							'appId' 	=> $erp_appid,
							"OBJECT" 	=> "CUSTOMER",
							"KEY" 		=> 	$order_customer_data[2],
							"data" 		=> array(
							"CUSTOMER" 	=> array(
									array(
										"NAME" 		=> $order_info['firstname'].' '.$order_info['lastname'],
										"CODE" 		=> $order_customer_data[1],
										"EMAIL" 	=> $order_info['email'],
										"PHONE01" 	=> $order_info['telephone'],
										"ADDRESS" 	=> $order_info['payment_address_1'],
										"CITY" 		=> $order_info['payment_city'],
										"ZIP" 		=> $order_info['payment_postcode']
									)
								)
							)
						));
						*/

					};
				} else {
					echo "Error on Customer reqID<br>";
					return;
				}
			} else {					
				
				echo 'INSERT CUSTOMER';
		
				if($order_info['customer_group_id'] == 3) {
					$TRDCATEGORY 	= 10;
					$CUSEXTRABOOL03 = 1;
				} else {
					$TRDCATEGORY 	= 30;
					$CUSEXTRABOOL03 = 0;
				};						

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
								"CODE" 					=> "9*",
								"EMAIL" 				=> $order_info['email'],
								"PHONE01" 				=> $order_info['telephone'],
								"PHONE02" 				=> $PHONE2,
								"ADDRESS" 				=> $order_info['payment_address_1'],
								"CITY" 					=> $order_info['payment_zone'],
								"DISTRICT" 				=> $order_info['payment_city'],
								"ZIP" 					=> $order_info['payment_postcode'],
								"COUNTRY" 				=> $order_info['payment_country'],
								"AFM" 					=> $AFM,
								"JOBTYPETRD" 			=> $JOBTYPETRD,
								"IRSDATA" 				=> $IRSDATA,
								"PRCCATEGORY" 			=> $PRCCATEGORY,
								"JOBTYPE" 				=> $JOBTYPE,
								"BRANCΗ" 				=> $BRANCΗ,									
								//"CUSEXTRA.BOOL03" 		=> $CUSEXTRABOOL03,
								//"CUSEXTRA.VARCHAR04" 	=> $order_info['customer_id'],
								"TRDCATEGORY" 			=> $TRDCATEGORY,
								"CUSTOMER.CCCTZIRENR" 	=> $order_info['date_added'], //Ημ. Εναρξης Τζίρου
								"CUSTOMER.CCCTZIRLHK" 	=> date('Y-m-d H:i:s', strtotime('+1 year', strtotime($order_info['date_added']))), //Ημ. Λήξης Τζίρου
								"CUSTOMER.CCCTZIROS" 	=> "1", //Τζίρος
								"CUSTOMER.CODE1" 		=> "1", //Τιμολογιακή Κλάση
								"CUSTOMER.CCCSTEP" 		=> $order_info['date_added'], //Ημερομηνία Step
								"CUSTOMER.CCCSTEP365" 	=> date('Y-m-d H:i:s', strtotime('+1 year', strtotime($order_info['date_added']))), //Ημερομηνία Step+365
								"CUSTOMER.CCCSYMETOXH" 	=> "1", //CUSTOMER.CCCSYMETOXH (1)
								"CUSTOMER.PRCCATEGORY" 	=> "0" //CUSTOMER.PRCCATEGORY (0)
							)
						),
						"CUSEXTRA" => array(
							array(
								"BOOL03" 		=> $CUSEXTRABOOL03,
								"VARCHAR04" 	=> $order_info['customer_id'].'_'.$order_info['order_id'],
							)
						)
					)
				));
				
				echo '<pre>';
				print_r($new_customer);
				echo '</pre>';

				if ($new_customer['success'] == 1) {
					
					$getErpCustomerData = cloud_soft1_request(array(
						"service" 	=> 'getBrowserInfo',
						'clientID' => $client_id,
						'appId' => $erp_appid,
						"LIST" 		=> "DATAFEED3",
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
			
		$order_info['erp_trdr']			 = str_replace('CUSTOMER;','',$order_customer_data[0]);
		$order_info['erp_customer_code'] = $order_customer_data[1];
		
		$payment_code 	= $payment_methods[$order_info['payment_code']];
		$shipping_code	= $shipping_methods[$order_info['shipping_code']];
		
		echo 'Payment Code:'.$payment_code.'<br>';
		
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
			$couponcheck = $db->query("
				SELECT 
				*
				FROM " . DB_PREFIX . "order_total ot
				WHERE 
				ot.order_id = '" . (int) $order_info['order_id'] . "'
				AND 
				ot.value < 0
				AND 
				ot.code = 'credit'
			")->row;
			
			if($couponcheck) {
				if(abs($couponcheck['value']) > 0) {
					$coupondisc = abs($couponcheck['value']);
				};
			};
			
			$saldoc_data = array(
				"SERIES" 					=> '30002',
				"INT01" 					=> '7069',
				"TRNDATE" 					=> $order_info['date_added'],
				//"DATE01" 					=> $order_info['date_added'],
				"TRDR" 						=> $order_info['erp_trdr'], // CUSTOMER ID
				"TRDR_CUSTOMER_CODE" 		=> $order_info['erp_customer_code'], // CUSTOMER ID
				"CMPFINCODE" 				=> $order_info['invoice_order_id'],
				"CCCWEBID" 					=> $order_info['invoice_order_id'],
				"PAYMENT" 					=> $payment_code,
				"SHIPMENT" 					=> $shipping_code,
				//"CCCREMARKS2" 			=> $order_info['comment'],
				//"CCCCLREMARKSCOURIER" 	=> $order_info['comment'],
				"COMMENTS" 					=> $order_info['order_id'],
				//"DISC1VAL" 					=> $coupondisc,						
				//"DISC1VAL" 					=> '0'   		
				//"UFTBL01" 				=> $order_type,
				//"CCCFXVCHRWGHTU" 			=> $order_info['total_weight']
			);
			
			if($VATDATA && $REDUCED_VAT) {
				$saldoc_data_vat = array(
					'TRDR_CUSTOMER_VATSTS' 	=> $SALDOCTRDR_CUSTOMER_VATSTS = 2,
					'VATSTS' 				=> $SALDOCVATSTS  
				);
				
				$saldoc_data = array_merge($saldoc_data,$saldoc_data_vat);
			}
		
			/*
			$vat_data = array(
				"VAT" => 1410,
				"SUBVAL" => $order_info['total_products_cost_without_vat'] ,
				"VATVAL" => $order_info['total_products_cost_vat'] ,
			);
			*/
			
			$item_data = array();
			foreach ($order_info['order_products'] as $order_product) {
				
				
				/*
				$discount = 0;
				$discount_result = array();
				if($order_info['customer_id'] > 0 ) {
					$discount_result = $db->query("SELECT discount FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$order_info['customer_id']. "'")->row;	
				};
				
				if($discount_result) {
					$discount = $discount_result['discount'];
					if($order_product['discvar'] <= 0 && $order_product['discvar'] != '0.0100') {
						$order_product['total'] = ( $order_product['total'] / 100 ) * (100 - $discount);
					}
				};

				echo '<pre>';
				echo '<h2>DISCOUNT</h2>';
				print_r($discount);
				echo '</pre>';
				*/
				
				if($VATDATA && $REDUCED_VAT) {
					$item_data[] = array(
						"MTRL" 		=> $order_product['erp_id'],  // PRODUCT MTRL
						"QTY" 		=> $order_product['quantity'] * $order_product['package'],
						"QTY1" 		=> $order_product['quantity'] * $order_product['package'],
						"PRICE" 	=> ( ( $order_product['init_price'] / $order_product['package']) / 1.24 ) * 1.17,
						"LINEVAL" 	=> ($order_product['total'] / 1.24 ) * 1.17,
						"VAT" 		=> $MTRLINESVAT,
					);
				} else {
					$item_data[] = array(
						"MTRL" 		=> $order_product['erp_id'],  // PRODUCT MTRL
						"QTY" 		=> $order_product['quantity'] * $order_product['package'],
						"QTY1" 		=> $order_product['quantity'] * $order_product['package'],
						"PRICE" 	=> ( $order_product['init_price'] / $order_product['package']),
						"LINEVAL" 	=> ($order_product['total'])
					);

				};
				
			};

			$mtr_doc = array();
			$mtr_doc[] = array(
				"SHIPPINGADDR" 		=> $order_info['shipping_address_1'],  // PRODUCT MTRL
				"SHPZIP" 			=> $order_info['shipping_postcode'],
				"SHPDISTRICT" 		=> $order_info['shipping_city'],
				"SHPCITY" 			=> $order_info['shipping_zone'],
				"WHOUSE" 			=> '10'
				//"CCCFXSHPNAME" 		=> $order_info['firstname'].' '.$order_info['lastname'],
				//"CCCFXSHPPHONE01" 	=> $order_info['telephone']
			);
			
			$exp_anal = array();
			
			if($order_info['payment_code'] == 'cod') {
				$exp_anal[] = array(
					"EXPN" 		=> '101',
					"LINENUM" 	=> 90001,
					"EXPVAL"	=> ($order_total_list['xcod'] / 1.24)
				);
			};
			
			if(isset($order_total_list['shipping'])) {
			
				$exp_anal[] = array(
					"EXPN" 		=> '100',
					"LINENUM" 	=> 90002,
					"EXPVAL"	=> ($order_total_list['shipping'] / 1.24)
				);	
			} else {
				$exp_anal[] = array(
					"EXPN" 		=> '100',
					"LINENUM" 	=> 90002,
					"EXPVAL"	=> 0
				);	
			}						
			
			/*
			if($order_info['payment_code'] == 'c') {
				$item_data[] = array(
					"MTRL" 				=> '64988',  // PRODUCT MTRL
					"QTY" 				=> 1,
					"QTY1" 				=> 1,
					"PRICE" 			=> $order_total_list['hocodfee'],
					"cccPickQty" 		=> 1,
					"cccPickComplete"	=> 1
				);
			};
			*/
			
			/*
			//SET SHIPMENT AS A PRODUCT
			if($order_info['shipping_code'] != 'pickup.pickup') {
				$item_data[] = array(
					"MTRL" 				=> '44364',  // PRODUCT MTRL
					"QTY" 				=> 1,
					"QTY1" 				=> 1,
					"PRICE" 			=> $order_total_list['shipping'],
					"cccPickQty" 		=> 1,
					"cccPickComplete"	=> 1
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
			
			$output_order 	= cloud_soft1_request($order_body);
			
			$new_order 		= $output_order;
			
			echo '<pre>';
			print_r($new_order);
			echo '</pre>';
			
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
	}
} else {
	echo 'NO ORDERS FOUND</br></hr></br>';
}

echo updateStock();

function cloud_soft1_request($data) {
    $data = json_encode($data);
    $url = 'http://gini.oncloud.gr/s1services';
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

function updateStock($data = array()) {
    $data = json_encode($data);
    $url = 'https://ginigroup.com/index.php?route=tool%2Ferp_connect%2Fentity&entity=stock&action=import&start=0&end=50000&ajax=1';
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
    return $result;	
}
?>