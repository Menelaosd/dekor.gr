<?php
class ControllerExtensionShippingBoxnow extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/shipping/boxnow');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$data['heading_title'] 	= $this->language->get('heading_title');
		$data['text_edit'] 		= $this->language->get('text_edit');

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_boxnow', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
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

		$data['action'] = $this->url->link('extension/shipping/boxnow', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);

		if (isset($this->request->post['shipping_boxnow_api_url'])) {
			$data['shipping_boxnow_api_url'] = $this->request->post['shipping_boxnow_api_url'];
		} else {
			$data['shipping_boxnow_api_url'] = $this->config->get('shipping_boxnow_api_url');
		}
		
		if (isset($this->request->post['shipping_boxnow_client_id'])) {
			$data['shipping_boxnow_client_id'] = $this->request->post['shipping_boxnow_client_id'];
		} else {
			$data['shipping_boxnow_client_id'] = $this->config->get('shipping_boxnow_client_id');
		}		
		
		if (isset($this->request->post['shipping_boxnow_client_secret'])) {
			$data['shipping_boxnow_client_secret'] = $this->request->post['shipping_boxnow_client_secret'];
		} else {
			$data['shipping_boxnow_client_secret'] = $this->config->get('shipping_boxnow_client_secret');
		}
		
		if (isset($this->request->post['shipping_boxnow_warehouse_number'])) {
			$data['shipping_boxnow_warehouse_number'] = $this->request->post['shipping_boxnow_warehouse_number'];
		} else {
			$data['shipping_boxnow_warehouse_number'] = $this->config->get('shipping_boxnow_warehouse_number');
		}

		if (isset($this->request->post['shipping_boxnow_partner_id'])) {
			$data['shipping_boxnow_partner_id'] = $this->request->post['shipping_boxnow_partner_id'];
		} else {
			$data['shipping_boxnow_partner_id'] = $this->config->get('shipping_boxnow_partner_id');
		}

		if (isset($this->request->post['shipping_boxnow_cost'])) {
			$data['shipping_boxnow_cost'] = $this->request->post['shipping_boxnow_cost'];
		} else {
			$data['shipping_boxnow_cost'] = $this->config->get('shipping_boxnow_cost');
		}

		if (isset($this->request->post['shipping_boxnow_free_shipping'])) {
			$data['shipping_boxnow_free_shipping'] = $this->request->post['shipping_boxnow_free_shipping'];
		} else {
			$data['shipping_boxnow_free_shipping'] = $this->config->get('shipping_boxnow_free_shipping');
		}

		if (isset($this->request->post['shipping_boxnow_tax_class_id'])) {
			$data['shipping_boxnow_tax_class_id'] = $this->request->post['shipping_boxnow_tax_class_id'];
		} else {
			$data['shipping_boxnow_tax_class_id'] = $this->config->get('shipping_boxnow_tax_class_id');
		}

		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (isset($this->request->post['shipping_boxnow_geo_zone_id'])) {
			$data['shipping_boxnow_geo_zone_id'] = $this->request->post['shipping_boxnow_geo_zone_id'];
		} else {
			$data['shipping_boxnow_geo_zone_id'] = $this->config->get('shipping_boxnow_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['shipping_boxnow_status'])) {
			$data['shipping_boxnow_status'] = $this->request->post['shipping_boxnow_status'];
		} else {
			$data['shipping_boxnow_status'] = $this->config->get('shipping_boxnow_status');
		}

		if (isset($this->request->post['shipping_boxnow_sort_order'])) {
			$data['shipping_boxnow_sort_order'] = $this->request->post['shipping_boxnow_sort_order'];
		} else {
			$data['shipping_boxnow_sort_order'] = $this->config->get('shipping_boxnow_sort_order');
		}
		
		if (isset($this->request->post['shipping_boxnow_payment_modules'])) {
			$data['shipping_boxnow_payment_modules'] = $this->request->post['shipping_boxnow_payment_modules'];
		} else {
			$data['shipping_boxnow_payment_modules'] = $this->config->get('shipping_boxnow_payment_modules');
		}
		
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
				$boxnow_status			= $boxnow_info['status'];
			} else {
				$boxnow_request_id 		= '';
				$boxnow_parcels 		= '';
				$boxnow_status_message	= '';
				$boxnow_locker_id		= '';
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
				'boxnow_status' 		=> $boxnow_status,
				'$boxnow_status_message'=> $boxnow_status_message,
				'boxnow_submit' 		=>  $this->url->link('extension/shipping/boxnow/deliveryRequests', 'user_token=' . $this->session->data['user_token'].'&order_id='.$result['order_id'], true),
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

		$data['partner_id'] = $this->config->get('shipping_boxnow_partner_id');
		
		$boxnow_partner_id = $this->config->get('shipping_boxnow_partner_id');
		if ((int)$this->config->get('config_store_id') === 2) {
			$boxnow_partner_id = '9019';
		}
		$data['partner_id'] = $boxnow_partner_id;		
		
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

			$locker_id =$this->request->get['locker_id'];
			if (!$locker_id) $locker_id = $boxnow_data['locker_id'];
			$warehouse_number = $this->request->get['warehouse_number'];
			if (!$warehouse_number) {
				$warehouse_number = $this->config->get('shipping_boxnow_warehouse_number');
				$warehouse_number = array_filter(array_map('trim', explode(PHP_EOL, $warehouse_number)));
				$warehouse_number_array = [];
				foreach($warehouse_number as $row) {
					$parts = array_map('trim', explode(':', $row));
					$warehouse_number_array[$parts[0]] = isset($parts[1]) ? $parts[1] : 'Warehouse #'.$parts[0];
				}
				$warehouse_number = reset(array_keys($warehouse_number_array));
			}
			
			$curl = curl_init();		

			$client_id = $this->config->get('shipping_boxnow_client_id');
			$client_secret = $this->config->get('shipping_boxnow_client_secret');

			if ($order['store_id'] == 2 ) {
				$client_id = '944d5fd2-80e4-46fa-90ed-c2ceff2e9792';
				$client_secret = '33ebbcb73c3dc8f98f4cf489ce762af345f0fe1fbd9be0ad166626a4f921a01e';
			}

			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->config->get('shipping_boxnow_api_url').'/api/v1/auth-sessions',
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
			
			$email = $this->config->get('config_email');
			
			if ((int)$this->config->get('config_store_id') === 2) {
				$email = 'info@apokrifa.gr';
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
				CURLOPT_URL => $this->config->get('shipping_boxnow_api_url').'/api/v1/delivery-requests',
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
		
		$client_id = $this->config->get('shipping_boxnow_client_id');
		$client_secret = $this->config->get('shipping_boxnow_client_secret');
		
		if ((int)$this->config->get('config_store_id') === 2) {
			$client_id = '944d5fd2-80e4-46fa-90ed-c2ceff2e9792';
			$client_secret = '33ebbcb73c3dc8f98f4cf489ce762af345f0fe1fbd9be0ad166626a4f921a01e';
		}	
		$curl = curl_init();		
				
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->config->get('shipping_boxnow_api_url').'/api/v1/auth-sessions',
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
			CURLOPT_URL => $this->config->get('shipping_boxnow_api_url').'/api/v1/parcels/'.$parcel_id.'/label.pdf',
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
}