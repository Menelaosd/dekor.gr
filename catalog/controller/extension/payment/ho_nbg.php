<?php 
class ControllerExtensionPaymentHoNbg extends Controller {

	public function index() {

		$this->load->language('extension/payment/ho_nbg');

		$data['testmode'] = $this->config->get('payment_ho_nbg_testmode');

		$data['action'] = $this->url->link('extension/payment/ho_nbg/send','', true);
		$data['fields'] = array();

		return $this->load->view('extension/payment/ho_nbg', $data);
	}

	public function send() {
		
		$this->load->language('extension/payment/ho_nbg');
		$this->load->model('checkout/order');

		$action = $this->url->link('extension/payment/ho_nbg/callback','', true);
		$return = $this->url->link('extension/payment/ho_nbg/return','', true);
		$error = $this->url->link('extension/payment/ho_nbg/error','', true);

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$output = '
			<div>
				<h3>You will be transfered in NBG\'s Safe Payment Environment</h3>
			</div>
			<form action="'.$action.'" id="ngbform" name="ngbform" method="post">
				<input type="hidden" name="amount" value="'.$order_info['order_id'].'">
				<input type="hidden" name="amount" value="'.$order_info['total'].'">
				<input type="hidden" name="return" value="'.$return.'">
				<input type="hidden" name="error" value="'.$error.'">
				<div class="buttons">
					<div class="pull-right">
						<input type="submit" value="Confirm Order" class="btn btn-primary">
					</div>
				</div>
			</form>
		';

		$output .= '<script type="text/javascript">document.forms["ngbform"].submit();</script>';
		
		echo $output;
	}

	public function callback() {

		$this->load->model('checkout/order');
		$this->load->language('extension/payment/ho_nbg');

		$testmode  = $this->config->get('payment_ho_nbg_testmode');
		
		$return = $this->url->link('extension/payment/ho_nbg/return','', true);
		$error 	= $this->url->link('extension/payment/ho_nbg/error','', true);
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if (!$order_info) {
			$this->session->data['error'] = $this->language->get('error_no_order')." - error_no_order #0";
			$this->fail();
		}

		if ($testmode == 1) {
    		$url 	= 'https://accreditation.datacash.com/Transaction/acq_a';
    	} else {
    		$url 	= "https://mars.transaction.datacash.com/Transaction";
    	}

    	$request_data = '<?xml version="1.0" encoding="UTF-8"?>
			<Request version="2">
  				<Authentication>    
      				<password>'.$this->config->get('payment_ho_nbg_password').'</password>
      				<client>'.$this->config->get('payment_ho_nbg_client').'</client>
   				</Authentication>
   				<Transaction>
   					<TxnDetails>
   						<merchantreference>ORDER'.str_pad($order_info['order_id'], 7, "0", STR_PAD_LEFT).'-'.time().'</merchantreference>
   						<amount currency="EUR">'.$order_info['total'].'</amount>
   						<capturemethod>ecomm</capturemethod>
   						<ThreeDSecure>
						<Browser>
						<device_category>0</device_category>
						<accept_headers>*/*</accept_headers>
						<user_agent>IE/6.0</user_agent>
						</Browser>
						<purchase_datetime>'.date('Ymd H:i:s', strtotime($order_info['date_added'])).'</purchase_datetime>
						<merchant_url>'.$order_info['store_url'].'</merchant_url>
						<purchase_desc>Transaction Description</purchase_desc>
						<verify>yes</verify>                                                                                     
						</ThreeDSecure>
   					</TxnDetails>
   					<CardTxn>
						<method>auth</method>
					</CardTxn>
					<HpsTxn>
						<page_set_id>'.$this->config->get('payment_ho_nbg_pageid').'</page_set_id>
						<method>setup_full</method>
						<DynamicData>
						<dyn_data_2>
							<![CDATA[{
								"lang": "el",				            	     
								"merchantName": "'.$this->config->get('payment_ho_nbg_merchant_name').'",
								"backUrl": "'.$return.'"	             
							}]]>                                    
						</dyn_data_2>
						</DynamicData>
						<return_url>'.$return.'</return_url>
						<expiry_url>'.$return.'</expiry_url>
						<error_url>'.$error.'</error_url>
					</HpsTxn>
   				</Transaction>
			</Request>
		';

		$response_data = $this->postData($request_data, $url);

        if (isset($response_data->HpsTxn)) {
        	
        	$redirect_link = $response_data->HpsTxn->hps_url.'?HPS_SessionID='.$response_data->HpsTxn->session_id;
        	
        	$this->response->redirect($redirect_link);

        } else {
        	$this->response->redirect($this->url->link('checkout/checkout'));
        }

    }

    public function error() {

    	$this->session->data['error'] = $this->language->get('error_generic');
	    	
	    $this->response->redirect($this->url->link('checkout/checkout'));
    }

    public function return() {

    	$this->load->language('extension/payment/ho_nbg');

    	if (isset($this->request->get['dts_reference'])) {
    		
	    	$dts_reference = $this->request->get['dts_reference'];
	    	$pm = $this->request->get['pm'];

	    	$request_data = '<?xml version="1.0" encoding="UTF-8"?>
				<Request version="2">
	  				<Authentication>    
	      				<password>'.$this->config->get('payment_ho_nbg_password').'</password>
	      				<client>'.$this->config->get('payment_ho_nbg_client').'</client>
	   				</Authentication>
	   				<Transaction>
						<HistoricTxn>
							<method>query</method>
							<reference>'.$dts_reference.'</reference>
						</HistoricTxn>
					</Transaction>
	    		</Request>';
	    	$query_response = $this->postData($request_data);

	 		$new_refNo =  $query_response->HpsTxn->AuthAttempts->Attempt->datacash_reference;
	 		
	 		if ($new_refNo) {
	 			$request_data = '<?xml version="1.0" encoding="UTF-8"?>
					<Request version="2">
		  				<Authentication>    
		      				<password>'.$this->config->get('payment_ho_nbg_password').'</password>
	      					<client>'.$this->config->get('payment_ho_nbg_client').'</client>
		   				</Authentication>
		   				<Transaction>
							<HistoricTxn>
								<method>query</method>
								<reference>'.$new_refNo.'</reference>
							</HistoricTxn>
						</Transaction>
		    		</Request>';

		    	$query_response2 = $this->postData($request_data);
	 		}
	 		
	 		$amount = $query_response2->QueryTxnResult->amount;
	 		$reason = $query_response2->QueryTxnResult->reason;
	 		$merchant_reference = $query_response2->QueryTxnResult->merchant_reference;
	 		$merchant_reference2 = substr($merchant_reference, 0, strpos($merchant_reference, '-'));
	 		$order_id = ltrim( str_replace('ORDER', '', $merchant_reference2), '0');

 			if ($this->config->get('payment_ho_nbg_debug') == 1) {
 				$xLog = new Log('ho_ngb_debug.log');
 				$xLog->write("NGB SUCCESS: Order ID: " . $order_id . " -- Reference ID:" . $dts_reference);
 			}

	 		if ($reason == "ACCEPTED") {

	 			$this->load->model('checkout/order');
	 			$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_ho_nbg_order_status_id'));

	 			$this->response->redirect($this->url->link('checkout/success'));
	 		} else {
	 			
	    		$this->session->data['error'] = $this->language->get('error_declined');
	    		
	 			$this->response->redirect($this->url->link('checkout/checkout'));
	 		}    	
    	} else {
    		
	    	$this->session->data['error'] = $this->language->get('error_user_cancel');
	    	
	    	$this->response->redirect($this->url->link('checkout/checkout'));
    	}
    }

	private function postData($request_data, $url = NULL) {

		if ($url == NULL) {
			if ($this->config->get('payment_ho_nbg_testmode') == 1) {
	    		$url 	= 'https://accreditation.datacash.com/Transaction/acq_a';
	    	} else {
	    		$url 	= "https://mars.transaction.datacash.com/Transaction";
	    	}
		}

		$url;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response_data = curl_exec($ch);
		$xml_data = simplexml_load_string($response_data);

        curl_close($ch);

        return $xml_data;
    }
}