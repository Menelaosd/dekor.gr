<?php
class ControllerExtensionShippingBoxnow extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/shipping/boxnow');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['heading_title'] 	= $this->language->get('heading_title');
		$data['text_edit'] 		= $this->language->get('text_edit');

		$this->load->model('setting/setting');
		$this->load->model('setting/store');

		// Multistore: which store are we editing? (0 = default)
		$store_id = isset($this->request->get['store_id']) ? (int)$this->request->get['store_id'] : 0;

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_boxnow', $this->request->post, $store_id);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/shipping/boxnow', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id, true));
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		// Effective settings for the selected store: default (0) overridden by store-specific
		$boxnow_setting = $this->model_setting_setting->getSetting('shipping_boxnow', 0);

		if ($store_id) {
			$boxnow_setting = array_merge($boxnow_setting, $this->model_setting_setting->getSetting('shipping_boxnow', $store_id));
		}

		// Store selector
		$data['store_id'] = $store_id;
		$data['user_token'] = $this->session->data['user_token'];

		$data['stores'] = array();
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name') . ' ' . $this->language->get('text_default')
		);

		foreach ($this->model_setting_store->getStores() as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/boxnow', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/shipping/boxnow', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id, true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);

		$fields = array(
			'shipping_boxnow_api_url',
			'shipping_boxnow_client_id',
			'shipping_boxnow_client_secret',
			'shipping_boxnow_warehouse_number',
			'shipping_boxnow_partner_id',
			'shipping_boxnow_cost',
			'shipping_boxnow_free_shipping',
			'shipping_boxnow_max_weight',
			'shipping_boxnow_tax_class_id',
			'shipping_boxnow_geo_zone_id',
			'shipping_boxnow_status',
			'shipping_boxnow_sort_order',
			'shipping_boxnow_payment_modules'
		);

		foreach ($fields as $field) {
			if (isset($this->request->post[$field])) {
				$data[$field] = $this->request->post[$field];
			} elseif (isset($boxnow_setting[$field])) {
				$data[$field] = $boxnow_setting[$field];
			} else {
				$data[$field] = '';
			}
		}

		// Default max weight (kg) when not configured yet
		if ($data['shipping_boxnow_max_weight'] === '' || $data['shipping_boxnow_max_weight'] === null) {
			$data['shipping_boxnow_max_weight'] = 10;
		}

		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->load->model('setting/extension');
		
		// Payment
		$files = glob(DIR_APPLICATION . 'controller/extension/payment/*.php');
		
		$data['payment_modules'] = array();
		
		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');

				if ($this->config->get('payment_' . $extension . '_status')) {
					$this->load->language('extension/payment/' . $extension);

					$data['payment_modules'][] = array(
						'name'		=> $this->language->get('heading_title'),
						'code'		=> $extension
					);
				}
			}
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/boxnow', $data));
	}
	
	public function report() {
		
		$this->load->language('extension/shipping/boxnow');
		$this->load->language('sale/order');
		
		$this->load->model('extension/shipping/boxnow');
		$this->load->model('sale/order');

		$this->document->setTitle($this->language->get('heading_title_report'));

		$this->load->model('setting/setting');
		
		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}		
		
		$data['orders'] = array();

		$filter_data = array(
			'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                  => $this->config->get('config_limit_admin')
		);
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title_report'),
			'href' => $this->url->link('extension/shipping/boxnow/report', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$order_total = $this->model_extension_shipping_boxnow->getBoxNowTotalOrders($filter_data);

		$results = $this->model_extension_shipping_boxnow->getBoxNowOrders($filter_data);

		foreach ($results as $result) {
			
			$boxnow_info = $this->model_extension_shipping_boxnow->getBoxNowStatus($result['order_id']);
			
			if($boxnow_info) {
				$boxnow_request_id 		= $boxnow_info['request_id'];
				$boxnow_parcels 		= json_decode($boxnow_info['parcels'],TRUE);
				$boxnow_status_message	= $boxnow_info['status_message'];
				$boxnow_locker_id		= $boxnow_info['locker_id'];
				$boxnow_locker_address	= isset($boxnow_info['locker_address']) ? $boxnow_info['locker_address'] : '';
				$boxnow_locker_name		= isset($boxnow_info['locker_name']) ? $boxnow_info['locker_name'] : '';
				$boxnow_status			= $boxnow_info['status'];
			} else {
				$boxnow_request_id 		= '';
				$boxnow_parcels 		= '';
				$boxnow_status_message	= '';
				$boxnow_locker_id		= '';
				$boxnow_locker_address	= '';
				$boxnow_locker_name		= '';
				$boxnow_status			= '';
			}
			
			$order_total_products 	= 0;
			$order_products 		= $this->model_sale_order->getOrderProducts($result['order_id']);
			
			foreach($order_products as $order_product) {
				$order_total_products += $order_product['quantity'];
			}
			
			$data['orders'][] = array(
				'order_id'      		=> $result['order_id'],
				'customer'      		=> $result['customer'],
				'order_status'  		=> $result['order_status'] ? $result['order_status'] : $this->language->get('text_missing'),
				'total'         		=> $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added'    		=> date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' 		=> date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'shipping_code' 		=> $result['shipping_code'],
				'products' 				=> $order_total_products,
				'boxnow_request_id' 	=> $boxnow_request_id,
				'boxnow_parcels' 		=> $boxnow_parcels,
				'boxnow_status_message' => $boxnow_status_message,
				'boxnow_locker_id' 		=> $boxnow_locker_id,
				'boxnow_locker_address' => $boxnow_locker_address,
				'boxnow_locker_name' 	=> $boxnow_locker_name,
				'boxnow_status' 		=> $boxnow_status,
				'$boxnow_status_message'=> $boxnow_status_message,
				'boxnow_submit' 		=>  $this->url->link('extension/shipping/boxnow/deliveryRequests', 'user_token=' . $this->session->data['user_token'].'&order_id='.$result['order_id'], true),
				'boxnow_cancel' 		=>  $this->url->link('extension/shipping/boxnow/cancelVoucher', 'user_token=' . $this->session->data['user_token'].'&order_id='.$result['order_id'], true),
				'view'          		=> $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'].'&quantity=1'. $url, true)
			);
		}

		$warehouse_number = $this->config->get('shipping_boxnow_warehouse_number');
		
		$warehouse_number = array_filter(array_map('trim', explode(PHP_EOL, $warehouse_number)));
		$warehouse_number_array = [];
		foreach($warehouse_number as $row) {
			$parts = array_map('trim', explode(':', $row));
			$warehouse_number_array[$parts[0]] = isset($parts[1]) ? $parts[1] : 'Warehouse #'.$parts[0];
		}
		$data['warehouse_number'] = $warehouse_number_array;

		// Manual locker-reselect widget uses the default store's partner id
		$data['partner_id'] = $this->getStoreSettingValue('shipping_boxnow_partner_id', 0);
		
		$url = '';
		
		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/shipping/boxnow/report', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

		$data['user_token'] = $this->session->data['user_token'];
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		if (isset($this->session->data['error'])) {
			$data['error'] = $this->session->data['error'];

			unset($this->session->data['error']);
		} else {
			$data['error'] = '';
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('extension/shipping/boxnow_list', $data));
	}
	
	public function deliveryRequests() {
		if (isset($this->request->get['order_id']) && $this->request->get['order_id']) {
			
			$this->load->language('extension/shipping/boxnow');
			$this->load->model('sale/order');
			$this->load->model('extension/shipping/boxnow');
			
			$order = $this->model_sale_order->getOrder( $this->request->get['order_id'] );
			$boxnow_data = $this->model_extension_shipping_boxnow->getBoxNowStatus( $this->request->get['order_id'] );
			
			$quantity = 1;
			
			if( isset($this->request->get['quantity']) && $this->request->get['quantity'] > 1 ) {
				$quantity = $this->request->get['quantity'];
			};

			// Multistore: load BoxNow credentials for the order's store
			$store_id      = (int)$order['store_id'];
			$client_id     = $this->getStoreSettingValue('shipping_boxnow_client_id', $store_id);
			$client_secret = $this->getStoreSettingValue('shipping_boxnow_client_secret', $store_id);
			$api_url       = $this->getStoreSettingValue('shipping_boxnow_api_url', $store_id);

			$locker_id =$this->request->get['locker_id'];
			if (!$locker_id) $locker_id = $boxnow_data['locker_id'];
			$warehouse_number = $this->request->get['warehouse_number'];
			if (!$warehouse_number) {
				$warehouse_number = $this->getStoreSettingValue('shipping_boxnow_warehouse_number', $store_id);
				$warehouse_number = array_filter(array_map('trim', explode(PHP_EOL, $warehouse_number)));
				$warehouse_number_array = [];
				foreach($warehouse_number as $row) {
					$parts = array_map('trim', explode(':', $row));
					$warehouse_number_array[$parts[0]] = isset($parts[1]) ? $parts[1] : 'Warehouse #'.$parts[0];
				}
				$warehouse_keys = array_keys($warehouse_number_array);
				$warehouse_number = reset($warehouse_keys);
			}

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $api_url.'/api/v1/auth-sessions',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS =>'{
					"grant_type": "client_credentials",
					"client_id": "'.$client_id.'",
					"client_secret": "'.$client_secret.'"
				}',
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json'
				),
			));

			// Get API response
			$response = curl_exec($curl);
			
			curl_close($curl);

			// Decode API response
			$json = json_decode($response, true);

			// Initiate Delivery Request CURL
			$post = curl_init();

			// Pass bearer token
			$authorization = "Authorization: Bearer " . $json['access_token'];
			
			// Set the Number of Vouchers relative to the quantity selected by user
			$items = array();
			$x = 1;
			while($x <= $quantity) {
			  $items[] = array(
				"value"				=> number_format(0, 2, '.', ''),
				"compartmentSize" 	=> 3
			  );
			  $x++;
			}	
			
            $phone = $order['telephone'];
            
            $re = '/^(?:\+?30|0)?/m';
            $str = $phone;
            $subst = '+30';
        
            $phone_box = preg_replace($re, $subst, $str);

			$cod = false;
			if ($order['payment_code'] == 'cod') $cod = true;

			// Create a JSON with all necessary fields
			
			$email = $this->getStoreSettingValue('config_email', $store_id);
			if (!$email) {
				$email = $this->config->get('config_email');
			}

			$data = array(
				"orderNumber" 			=> $order['order_id'],
				"invoiceValue" 			=> number_format($order['total'], 2, '.', ''),
				"paymentMode" 			=> $cod ? "cod" : "prepaid",
				"amountToBeCollected" 	=> number_format($order['total'], 2, '.', ''),
				"allowReturn" 			=> true,
				"origin" 				=> array (
					"contactEmail" 	=> $email,
					"locationId" 	=> $warehouse_number,
				),
				"destination" 	=> array (
					"contactNumber" => $phone_box,
					"contactEmail" 	=> $order['email'],
					"contactName" 	=> $order['shipping_firstname'].' '.$order['shipping_lastname'],
					"locationId" 	=> $locker_id,
				),
				"items" => $items
			);

			// Create JSON
			$data_json = json_encode($data);
			 
			// Prepare CURL for delivery request
			curl_setopt_array($post, array(
				CURLOPT_URL => $api_url.'/api/v1/delivery-requests',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_HTTPHEADER => array(
					$authorization,
					"Content-Type: application/json",
					"Content-Length:" . strlen($data_json)
				),
				CURLOPT_POSTFIELDS => $data_json
			));

			//
			$response = curl_exec($post);
			curl_close($post);
			
			if(isset($response) && $response) {
				$response_data = JSON_DECODE($response,TRUE);
				if( !isset($response_data['status']) && isset($response_data['id']) ) {
					$response_data['status_id'] 			= 1;
					$response_data['status_message'] 		= NULL;
					$response_data['locker_id'] 	    	= $locker_id;
					$response_delivery = $this->model_extension_shipping_boxnow->updateRequest($order, $response_data);
					$this->session->data['success'] = $this->language->get('text_voucher_status_success');
				} else {
					
					$error_codes = [
						'P400' => 'Invalid request data. Make sure are sending the request according to this documentation.',
						'P401' => 'Invalid request origin location reference. Make sure you are referencing a valid location ID from Origins endpoint or valid address.',
						'P402' => 'Invalid request destination location reference. Make sure you are referencing a valid location ID from Destinations endpoint or valid address.',
						'P403' => 'You are not allowed to use AnyAPM-SameAPM delivery. Contact support if you believe this is a mistake.',
						'P404' => 'Invalid import CSV. See error contents for additional info.',
						'P405' => 'Invalid phone number. Make sure you are sending the phone number in full international format, e.g. +30 xx x xxx xxxx.',
						'P406' => 'Invalid compartment/parcel size. Make sure you are sending one of required sizes 1, 2 or 3. Size is required when sending from AnyAPM directly.',
						'P407' => 'Invalid country code. Make sure you are sending country code in ISO 3166-1 alpha-2 format, e.g. GR.',
						'P408' => 'Invalid amountToBeCollected amount. Make sure you are sending amount in the valid range of (0, 5000>',
						'P409' => 'Invalid delivery partner reference. Make sure you are referencing a valid delivery partner ID from Delivery partners endpoint.',
						'P410' => 'Order number conflict. You are trying to create a delivery request for order ID that has already been created. Choose another order id.',
						'P411' => 'You are not eligible to use Cash-on-delivery payment type. Use another payment type or contact our support.',
						'P412' => 'You are not allowed to create customer returns deliveries. Contact support if you believe this is a mistake.',
						'P420' => 'Parcel not ready for cancel. You can cancel only new, undelivered, or parcels that are not returned or lost. Make sure parcel is in transit and try again.',
						'P430' => 'Parcel not ready for AnyAPM confirmation. Parcel is probably already confirmed or being delivered. Contact support if you believe this is a mistake.',
					];
					
					$response_data['id'] 				= 0;
					$response_data['status_id'] 		= 2;
					$response_data['parcels'] 			= array();
					$response_data['status_message'] 	= sprintf('Voucher was not created (Error Code: %s). '.(!empty($error_codes[$response_data['code']]) ? $error_codes[$response_data['code']] : 'You can refer to the relevant link for <a href="https://boxnow.gr/docs/api/partner-api/troubleshooting/" target="_blank">help</a> or contact us at <a href="mailto:info@boxnow.gr">info@boxnow.gr</a>'), $response_data['code']);
					$response_delivery = $this->model_extension_shipping_boxnow->updateRequest($order, $response_data);
					$this->session->data['error'] 	= $response_data['status_message'];
				};
			};
	
			$this->response->redirect($this->url->link('extension/shipping/boxnow/report', 'user_token=' . $this->session->data['user_token'], true));
		}	
	}
	
	public function getParcel() {

		// Multistore: resolve the store from the order so we use the right credentials
		$store_id = 0;
		if (isset($this->request->get['order_id']) && (int)$this->request->get['order_id']) {
			$this->load->model('sale/order');
			$order = $this->model_sale_order->getOrder((int)$this->request->get['order_id']);
			if ($order) {
				$store_id = (int)$order['store_id'];
			}
		}

		$client_id     = $this->getStoreSettingValue('shipping_boxnow_client_id', $store_id);
		$client_secret = $this->getStoreSettingValue('shipping_boxnow_client_secret', $store_id);
		$api_url       = $this->getStoreSettingValue('shipping_boxnow_api_url', $store_id);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $api_url.'/api/v1/auth-sessions',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{
				"grant_type": "client_credentials",
				"client_id": "'.$client_id.'",
				"client_secret": "'.$client_secret.'"
			}',
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json'
			),
		));

		// Get API response
		$response = curl_exec($curl);
		
		curl_close($curl);

		// Decode API response
		$json = json_decode($response, true);

		// Initiate Delivery Request CURL
		$post = curl_init();

		// Pass bearer token
		$authorization = "Authorization: Bearer " . $json['access_token'];
		
		$parcel_id = '';
		if(isset($this->request->get['parcel_id']) && $this->request->get['parcel_id'] ) {
			$parcel_id = $this->request->get['parcel_id'];
		};
		
		header("Content-type:application/pdf");
		header("Content-Disposition:attachment;filename=".$parcel_id.".pdf");			

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $api_url.'/api/v1/parcels/'.$parcel_id.'/label.pdf',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				$authorization,
				"Content-Type: application/pdf"
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		echo $response;
	}	
	
	public function install() {
		$this->load->model('extension/shipping/boxnow');

		$this->model_extension_shipping_boxnow->install();
	}	
	
	public function uninstall() {
		$this->load->model('extension/shipping/boxnow');

		$this->model_extension_shipping_boxnow->uninstall();
	}	

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/boxnow')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	// Multistore helper: returns the store-specific setting value, falling back to the default store (0).
	private function getStoreSettingValue($key, $store_id = 0) {
		$query = $this->db->query("SELECT value, store_id FROM `" . DB_PREFIX . "setting` WHERE `key` = '" . $this->db->escape($key) . "' AND store_id IN ('0', '" . (int)$store_id . "') ORDER BY store_id DESC");

		if ($query->num_rows) {
			return $query->row['value'];
		}

		return '';
	}

	// Request a BoxNow OAuth bearer token; returns the access token or '' on failure.
	private function authenticate($api_url, $client_id, $client_secret) {
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $api_url . '/api/v1/auth-sessions',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => '{
				"grant_type": "client_credentials",
				"client_id": "' . $client_id . '",
				"client_secret": "' . $client_secret . '"
			}',
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json'
			),
		));

		$response = curl_exec($curl);
		curl_close($curl);

		$json = json_decode($response, true);

		return isset($json['access_token']) ? $json['access_token'] : '';
	}

	// Cancel the BoxNow voucher(s) for an order via POST /api/v1/parcels/{id}:cancel
	public function cancelVoucher() {
		$this->load->language('extension/shipping/boxnow');
		$this->load->model('sale/order');
		$this->load->model('extension/shipping/boxnow');

		$report_url = $this->url->link('extension/shipping/boxnow/report', 'user_token=' . $this->session->data['user_token'], true);

		if (!$this->user->hasPermission('modify', 'extension/shipping/boxnow')) {
			$this->session->data['error'] = $this->language->get('error_permission');
			$this->response->redirect($report_url);
			return;
		}

		$order_id    = isset($this->request->get['order_id']) ? (int)$this->request->get['order_id'] : 0;
		$order       = $order_id ? $this->model_sale_order->getOrder($order_id) : false;
		$boxnow_info = $order_id ? $this->model_extension_shipping_boxnow->getBoxNowStatus($order_id) : false;

		// Only cancel orders with a created voucher (status 1) that still has parcels
		$parcels = ($boxnow_info && !empty($boxnow_info['parcels'])) ? json_decode($boxnow_info['parcels'], true) : array();

		if (!$order || !$boxnow_info || (int)$boxnow_info['status'] !== 1 || empty($parcels)) {
			$this->session->data['error'] = $this->language->get('error_cancel_nothing');
			$this->response->redirect($report_url);
			return;
		}

		// Multistore: credentials for the order's store
		$store_id      = (int)$order['store_id'];
		$client_id     = $this->getStoreSettingValue('shipping_boxnow_client_id', $store_id);
		$client_secret = $this->getStoreSettingValue('shipping_boxnow_client_secret', $store_id);
		$api_url       = $this->getStoreSettingValue('shipping_boxnow_api_url', $store_id);

		$token = $this->authenticate($api_url, $client_id, $client_secret);

		if (!$token) {
			$this->session->data['error'] = $this->language->get('error_cancel_auth');
			$this->response->redirect($report_url);
			return;
		}

		$error_codes = array(
			'P420' => 'Parcel not ready for cancel. You can only cancel new, undelivered parcels that are not returned or lost.',
		);

		$errors = array();

		foreach ($parcels as $parcel) {
			if (empty($parcel['id'])) {
				continue;
			}

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $api_url . '/api/v1/parcels/' . rawurlencode($parcel['id']) . ':cancel',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => '{}',
				CURLOPT_HTTPHEADER => array(
					'Authorization: Bearer ' . $token,
					'Content-Type: application/json'
				),
			));

			$response  = curl_exec($curl);
			$http_code = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);

			if ($http_code < 200 || $http_code >= 300) {
				$decoded = json_decode($response, true);
				$code    = isset($decoded['code']) ? $decoded['code'] : ('HTTP ' . $http_code);
				$detail  = isset($error_codes[$code]) ? (' - ' . $error_codes[$code]) : '';
				$errors[] = $parcel['id'] . ': ' . $code . $detail;
			}
		}

		if ($errors) {
			$this->session->data['error'] = sprintf($this->language->get('error_cancel_failed'), implode(' | ', $errors));
		} else {
			$this->model_extension_shipping_boxnow->cancelRequest($order_id, $this->language->get('text_voucher_cancelled'));
			$this->session->data['success'] = $this->language->get('text_voucher_cancel_success');
		}

		$this->response->redirect($report_url);
	}
}