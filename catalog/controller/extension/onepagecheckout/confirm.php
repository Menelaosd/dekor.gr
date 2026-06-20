<?php
class ControllerExtensionOnepagecheckoutConfirm extends Controller {
	public function index(){
		$this->load->language('extension/onepagecheckout/checkout');
		
			$redirect = '';
			$data['missingsgipping'] = false;
			if ($this->cart->hasShipping()) {
				// Validate if shipping address has been set.
				if (!isset($this->session->data['shipping_address'])) {
					$redirect = $this->url->link('checkout/checkout', '', true);
					$data['missingsgipping'] = true;
				}

				// Validate if shipping method has been set.								
				if (!isset($this->session->data['shipping_method'])) {
					$redirect = $this->url->link('checkout/checkout', '', true);					
					$data['missingsgipping'] = true;
				}
			} else {
				unset($this->session->data['shipping_address']);
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
			}
			$data['missingpayment'] = false;
			// Validate if payment address has been set.
			if (!isset($this->session->data['payment_address'])) {
				$data['missingpayment'] = true;
				$redirect = $this->url->link('checkout/checkout', '', true);
				
			}

			// Validate if payment method has been set.						
			
			if (!isset($this->session->data['payment_method'])) {		
$data['missingpayment'] = true;			
				$redirect = $this->url->link('checkout/checkout', '', true);
			}

			// Validate cart has products and has stock.
			if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
				$redirect = $this->url->link('checkout/checkout');
				
			}

			// Validate minimum quantity requirements.
			$products = $this->cart->getProducts();

			foreach($products as $product){
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$redirect = $this->url->link('checkout/cart');

					break;
				}
			}
			
			
						$data['logged'] = $this->customer->isLogged();
			
			$this->load->model('setting/setting');
				
			$onepagecheckout_info = $this->model_setting_setting->getSetting('onepagecheckout', $this->config->get('config_store_id'));
			
			$onepagecheckout_manage = (!empty($onepagecheckout_info['onepagecheckout_manage'])) ? $onepagecheckout_info['onepagecheckout_manage'] : array();
			
				if(!empty($onepagecheckout_manage['delivery']['delivery_status'])){
				$data['delivery_status'] = $onepagecheckout_manage['delivery']['delivery_status'];
			}else{
				$data['delivery_status'] = false;
			}
			
			
			
			$data['button_checkout_order'] = (isset($onepagecheckout_manage['confirm_order']['confirm_button'][$this->config->get('config_language_id')]) ? $onepagecheckout_manage['confirm_order']['confirm_button'][$this->config->get('config_language_id')] : $this->language->get('button_checkout_order'));
			
			$data['button_shopping'] = (isset($onepagecheckout_manage['confirm_order']['shopping_button'][$this->config->get('config_language_id')]) ? $onepagecheckout_manage['confirm_order']['shopping_button'][$this->config->get('config_language_id')] : $this->language->get('button_shopping'));
			
			$data['comment_placeholder'] = (isset($onepagecheckout_manage['confirm_order']['comment_placeholder'][$this->config->get('config_language_id')]) ? $onepagecheckout_manage['confirm_order']['comment_placeholder'][$this->config->get('config_language_id')] : 'Add Comment');
			
			$data['comment_label'] = (isset($onepagecheckout_manage['confirm_order']['comment_label'][$this->config->get('config_language_id')]) ? $onepagecheckout_manage['confirm_order']['comment_label'][$this->config->get('config_language_id')] : '');
			
			$data['autotrigger'] = $this->config->get('onepagecheckout_autotrigger');
			
			$data['continue'] = $this->url->link('common/home');
			
			//$data['button_shopping'] = $this->language->get('button_shopping');
			
			$data['button_checkout_register'] = $this->language->get('button_checkout_register');
			$data['button_checkout_guest'] = $this->language->get('button_checkout_guest');
			
			$data['comment']  = (isset($this->session->data['comment']) ? $this->session->data['comment'] : '');
			$data['agree']    = (isset($this->session->data['agree']) ? $this->session->data['agree'] : 0);
			
			//$data['button_checkout_order'] = $this->language->get('button_checkout_order');
			
			if (!empty($onepagecheckout_manage['confirm_order']['terms'])) {
				$this->load->model('catalog/information');

				$information_info = $this->model_catalog_information->getInformation($onepagecheckout_manage['confirm_order']['terms']);

				if ($information_info) {
					$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $information_info['information_id'], true), $information_info['title'], $information_info['title']);
				} else {
					$data['text_agree'] = '';
				}
			} else {
				$data['text_agree'] = '';
			}
			
			$data['checkout_terms'] = (!empty($onepagecheckout_manage['confirm_order']['checkout_terms']) ? 'checked=checked' : '');
			
			if (!empty($onepagecheckout_manage['confirm_order']['comment_status'])) {
			 $data['comment_status'] = $onepagecheckout_manage['confirm_order']['comment_status'];
			}else{
			 $data['comment_status'] = '';	
			}
			
			if (!empty($onepagecheckout_manage['confirm_order']['shopping_button_status'])) {
			 $data['shopping_button_status'] = $onepagecheckout_manage['confirm_order']['shopping_button_status'];
			}else{
			 $data['shopping_button_status'] = '';	
			}
			
			$data['logged'] = $this->customer->isLogged();
			
			if(!empty($this->request->post['account_type'])) {
				$account_type = $this->request->post['account_type'];
			} else {
				$account_type = 'logged';
			}
			
			if($account_type == 'register'){
				$data['button_type'] 	= 'register';
			}else if($account_type == 'guest'){
				$data['button_type'] 	= 'guest';
			}else if($account_type == 'login'){
				$data['button_type'] 	= 'login';
			}else if($account_type == 'logged'){
				$data['button_type'] 	= 'logged';
			}else if($account_type == 'confirm'){
				$data['button_type'] 	= 'confirm';
			}else{
				$data['button_type'] 	= 'confirm';
			}
			
			if(!$redirect && $account_type=='confirm'){
					$order_data = array();
				
					$totals = array();
					$taxes = $this->cart->getTaxes();
					$total = 0;

					// Because __call can not keep var references so we put them into an array. 
					$total_data = array(
						'totals' => &$totals,
						'taxes'  => &$taxes,
						'total'  => &$total
					);
					
					$this->load->model('setting/extension');

					$sort_order = array();

					$results = $this->model_setting_extension->getExtensions('total');

					foreach ($results as $key => $value){
						$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
					}

					array_multisort($sort_order, SORT_ASC, $results);

					foreach($results as $result){
						if ($this->config->get('total_' . $result['code'] . '_status')) {
							// We have to put the totals in an array so that they pass by reference.
							
							$this->load->model('extension/total/' . $result['code']);
							$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
						}
					}

					$sort_order = array();

					foreach ($totals as $key => $value){
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $totals);
				
					$order_data['totals'] = $totals;
				
				

				$this->load->language('checkout/checkout');

				$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
				$order_data['store_id'] = $this->config->get('config_store_id');
				$order_data['store_name'] = $this->config->get('config_name');

				if ($order_data['store_id']) {
					$order_data['store_url'] = $this->config->get('config_url');
				} else {
					$order_data['store_url'] = HTTP_SERVER;
				}
$this->load->model('account/customer');
				if($this->customer->isLogged()){
					

					$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

					$order_data['customer_id'] = $this->customer->getId();
					$order_data['customer_group_id'] = $customer_info['customer_group_id'];
					$order_data['firstname'] = $customer_info['firstname'];
					$order_data['lastname'] = $customer_info['lastname'];
					$order_data['email'] = $customer_info['email'];
					$order_data['telephone'] = $customer_info['telephone'];
					$order_data['fax'] = $customer_info['fax'];
					$order_data['custom_field'] = json_decode($customer_info['custom_field'], true);
				} elseif (isset($this->session->data['guest'])) {
					$order_data['customer_id'] = 0;
					$order_data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
					$order_data['firstname'] = $this->session->data['guest']['firstname'];
					$order_data['lastname'] = $this->session->data['guest']['lastname'];
					$order_data['email'] = $this->session->data['guest']['email'];
					$order_data['telephone'] = $this->session->data['guest']['telephone'];
					$order_data['fax'] = $this->session->data['guest']['fax'];
					$order_data['custom_field'] = $this->session->data['guest']['custom_field'];
				}

				$order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
				$order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
				$order_data['payment_company'] = $this->session->data['payment_address']['company'];
				$order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
				$order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
				$order_data['payment_city'] = $this->session->data['payment_address']['city'];
				$order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
				$order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
				$order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
				$order_data['payment_country'] = $this->session->data['payment_address']['country'];
				$order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
				$order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
				$order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());

				if (isset($this->session->data['payment_method']['title'])) {
					$order_data['payment_method'] = $this->session->data['payment_method']['title'];
				} else {
					$order_data['payment_method'] = '';
				}

				if (isset($this->session->data['payment_method']['code'])) {
					$order_data['payment_code'] = $this->session->data['payment_method']['code'];
				} else {
					$order_data['payment_code'] = '';
				}

				if ($this->cart->hasShipping()) {
					$order_data['shipping_firstname'] = $this->session->data['shipping_address']['firstname'];
					$order_data['shipping_lastname'] = $this->session->data['shipping_address']['lastname'];
					$order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
					$order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
					$order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];
					$order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
					$order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
					$order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
					$order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
					$order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
					$order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
					$order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
					$order_data['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : array());

					if (isset($this->session->data['shipping_method']['title'])) {
						$order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
					} else {
						$order_data['shipping_method'] = '';
					}

					if (isset($this->session->data['shipping_method']['code'])) {
						$order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
					} else {
						$order_data['shipping_code'] = '';
					}
				} else {
					$order_data['shipping_firstname'] = '';
					$order_data['shipping_lastname'] = '';
					$order_data['shipping_company'] = '';
					$order_data['shipping_address_1'] = '';
					$order_data['shipping_address_2'] = '';
					$order_data['shipping_city'] = '';
					$order_data['shipping_postcode'] = '';
					$order_data['shipping_zone'] = '';
					$order_data['shipping_zone_id'] = '';
					$order_data['shipping_country'] = '';
					$order_data['shipping_country_id'] = '';
					$order_data['shipping_address_format'] = '';
					$order_data['shipping_custom_field'] = array();
					$order_data['shipping_method'] = '';
					$order_data['shipping_code'] = '';
				}

				$order_data['products'] = array();

				foreach ($this->cart->getProducts() as $product) {
					$option_data = array();

					foreach ($product['option'] as $option) {
						$option_data[] = array(
							'product_option_id'       => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
							'option_id'               => $option['option_id'],
							'option_value_id'         => $option['option_value_id'],
							'name'                    => $option['name'],
							'value'                   => $option['value'],
							'type'                    => $option['type']
						);
					}

					$order_data['products'][] = array(
						'product_id' => $product['product_id'],
						'name'       => $product['name'],
						'model'      => $product['model'],
						'option'     => $option_data,
						'download'   => $product['download'],
						'quantity'   => $product['quantity'],
						'subtract'   => $product['subtract'],
						'price'      => $product['price'],
						'total'      => $product['total'],
						'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
						'reward'     => $product['reward']
					);
				}

				// Gift Voucher
				$order_data['vouchers'] = array();

				if (!empty($this->session->data['vouchers'])) {
					foreach ($this->session->data['vouchers'] as $voucher) {
						$order_data['vouchers'][] = array(
							'description'      => $voucher['description'],
							'code'             => token(10),
							'to_name'          => $voucher['to_name'],
							'to_email'         => $voucher['to_email'],
							'from_name'        => $voucher['from_name'],
							'from_email'       => $voucher['from_email'],
							'voucher_theme_id' => $voucher['voucher_theme_id'],
							'message'          => $voucher['message'],
							'amount'           => $voucher['amount']
						);
					}
				}

				$order_data['comment'] = $this->session->data['comment'];

				if(!empty($this->session->data['delivery_date'])){
					$order_data['comment'] .= '<br/> <br/><h4>Estimate Shipping Date: '.date('d/m/Y',strtotime($this->session->data['delivery_date'])). '</h4>';
				}

				// BOXNOW: record the chosen locker on the order so it shows in the order details
				if (
					isset($this->session->data['shipping_method']['code']) &&
					$this->session->data['shipping_method']['code'] === 'boxnow.boxnow' &&
					!empty($this->session->data['boxnow_locker_id'])
				) {
					$this->load->language('extension/shipping/boxnow');

					$locker_line = $this->language->get('selected_boxnow') . ' ';
					if (!empty($this->session->data['boxnow_address'])) {
						$locker_line .= $this->session->data['boxnow_address'] . ' ';
					}
					if (!empty($this->session->data['boxnow_name'])) {
						$locker_line .= '[' . $this->session->data['boxnow_name'] . '] ';
					}
					$locker_line .= '(Locker ID: ' . $this->session->data['boxnow_locker_id'] . ')';

					$order_data['comment'] = !empty($order_data['comment']) ? $order_data['comment'] . '<br/>' . $locker_line : $locker_line;
				}
				
				$data['payment_trigger_button'] = $this->config->get('onepagecheckout_payment_trigger_button');
				
				
				$order_data['total'] = $total_data['total'];
				

				if (isset($this->request->cookie['tracking'])) {
				$order_data['tracking'] = $this->request->cookie['tracking'];

				$subtotal = $this->cart->getSubTotal();

				// Affiliate
				$affiliate_info = $this->model_account_customer->getAffiliateByTracking($this->request->cookie['tracking']);

				if ($affiliate_info) {
					$order_data['affiliate_id'] = $affiliate_info['customer_id'];
					$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
				} else {
					$order_data['affiliate_id'] = 0;
					$order_data['commission'] = 0;
				}

				// Marketing
				$this->load->model('checkout/marketing');

				$marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

				if ($marketing_info) {
					$order_data['marketing_id'] = $marketing_info['marketing_id'];
				} else {
					$order_data['marketing_id'] = 0;
				}
			} else {
				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
				$order_data['marketing_id'] = 0;
				$order_data['tracking'] = '';
			}

				$order_data['language_id'] = $this->config->get('config_language_id');
				$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
				$order_data['currency_code'] = $this->session->data['currency'];
				$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
				$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

				if(!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
					$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
				} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
					$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
				} else {
					$order_data['forwarded_ip'] = '';
				}

				if (isset($this->request->server['HTTP_USER_AGENT'])) {
					$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
				} else {
					$order_data['user_agent'] = '';
				}

				if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
					$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
				} else {
					$order_data['accept_language'] = '';
				}

				$this->load->model('checkout/order');

				$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);
				
				// BOXNOW CONFIRM
				if (
					isset($this->session->data['shipping_method']['code']) &&
					$this->session->data['shipping_method']['code'] === 'boxnow.boxnow'
				) {
					$this->load->model('extension/shipping/boxnow');

					$order_info = $this->model_checkout_order->getOrder(
						$this->session->data['order_id']
					);

					$boxnow_data = array();

					if (!empty($this->session->data['boxnow_locker_id'])) {
						$boxnow_data['locker_id'] = $this->session->data['boxnow_locker_id'];
					}
					if (!empty($this->session->data['boxnow_address'])) {
						$boxnow_data['locker_address'] = $this->session->data['boxnow_address'];
					}
					if (!empty($this->session->data['boxnow_name'])) {
						$boxnow_data['locker_name'] = $this->session->data['boxnow_name'];
					}

					$this->model_extension_shipping_boxnow->setRequest(
						$order_info,
						$boxnow_data
					);

					unset($this->session->data['boxnow_locker_id']);
					unset($this->session->data['boxnow_address']);
					unset($this->session->data['boxnow_name']);
				}
				// BOXNOW CONFIRM				

				$data['text_recurring_item'] = $this->language->get('text_recurring_item');
				$data['text_payment_recurring'] = $this->language->get('text_payment_recurring');

				$data['column_name'] = $this->language->get('column_name');
				$data['column_model'] = $this->language->get('column_model');
				$data['column_quantity'] = $this->language->get('column_quantity');
				$data['column_price'] = $this->language->get('column_price');
				$data['column_total'] = $this->language->get('column_total');

				$this->load->model('tool/upload');

				$data['products'] = array();

				foreach ($this->cart->getProducts() as $product) {
					$option_data = array();

					foreach ($product['option'] as $option) {
						if ($option['type'] != 'file') {
							$value = $option['value'];
						} else {
							$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

							if ($upload_info) {
								$value = $upload_info['name'];
							} else {
								$value = '';
							}
						}

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
						);
					}

					$recurring = '';

					if ($product['recurring']) {
						$frequencies = array(
							'day'        => $this->language->get('text_day'),
							'week'       => $this->language->get('text_week'),
							'semi_month' => $this->language->get('text_semi_month'),
							'month'      => $this->language->get('text_month'),
							'year'       => $this->language->get('text_year'),
						);

						if ($product['recurring']['trial']) {
							$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
						}

						if ($product['recurring']['duration']) {
							$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
						} else {
							$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
						}
					}
					
					$data['products'][] = array(
						'cart_id'    => $product['cart_id'],
						'product_id' => $product['product_id'],
						'name'       => $product['name'],
						'model'      => $product['model'],
						'option'     => $option_data,
						'recurring'  => $recurring,
						'quantity'   => $product['quantity'],
						'subtract'   => $product['subtract'],
						'price'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
						'total'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']),
						'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id'])
					);
				}

					// Gift Voucher
					$data['vouchers'] = array();

					if (!empty($this->session->data['vouchers'])) {
						foreach ($this->session->data['vouchers'] as $voucher) {
							$data['vouchers'][] = array(
								'description' => $voucher['description'],
								'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency'])
							);
						}
					}

					$data['totals'] = array();

					foreach ($order_data['totals'] as $total) {
						$data['totals'][] = array(
							'title' => $total['title'],
							'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
						);
					}
					
					$data['trigger_payment_method'] = ($this->config->get('onepagecheckout_trigger_payment_method') ? $this->config->get('onepagecheckout_trigger_payment_method') : array());
			$data['selectedtriggers'] = '';
			if(isset($this->session->data['payment_method']['code'])) {
				$data['selectedtriggers'] = $this->session->data['payment_method']['code'];
				$data['payment'] = $this->load->controller('extension/payment/' . $this->session->data['payment_method']['code']);
			}else{
				$data['payment'] = '';
			}			
				
				}else{
					$data['redirect'] = $redirect;
				}
				
				
			$this->response->setOutput($this->load->view('extension/onepagecheckout/confirm', $data));
				
	}
	
	//Update 20-02-2017
	public function comment(){
		if(isset($this->request->post['comment'])){
			$this->session->data['comment'] = $this->request->post['comment'];
		}
	}
	
	public function ordertrem(){
		if(isset($this->session->data['agree'])){
			unset($this->session->data['agree']); 
		}
		
		if(isset($this->request->post['agree'])){
			$this->session->data['agree'] = $this->request->post['agree'];
		}
	}
}