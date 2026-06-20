<?php
/**
 * @payment-module	Vivapayment
 * @author-name 	Anastasios Daskalopoulos
 * @copyright		Copyright (C) 2017 ITcore
 * @license			GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */
class ControllerExtensionPaymentVivapayment extends Controller {
    public function index()
    {
        $this->language->load('extension/payment/vivapayment');
        $this->load->model('extension/payment/vivapayment');
        $this->load->model('checkout/order');
        $baseApiUrl = (($this->config->get('payment_vivapayment_test') == 1)? 'https://demo':'https://www').".vivapayments.com";
        $data['baseUrl'] = $baseApiUrl;
        $order_info=$this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['orderid'] = $order_info['order_id'];
        $data['payment_vivapayment_publickey'] = $this->config->get('payment_vivapayment_public_key');
		$store_id = $this->config->get('config_store_id');
        if (!$this->isMultistore() || $store_id == '0'){
            $transaction_type = $this->config->get('payment_vivapayment_paymethod');
            $data['inallow'] = $this->config->get('payment_vivapayment_disable_instaqllments');
        } else {
            $multistore_data = $this->config->get('payment_vivapayment_multistore');
            $transaction_type = $multistore_data[$store_id]['method'];
            $data['inallow'] = $multistore_data[$store_id]['installments'];			
        }
        $data['button_confirm']=$this->language->get('button_confirm');
        $data['info_redirect_message'] = $this->language->get('info_redirect_message');
        $data['text_credit_card_expiration_date'] = $this->language->get('credit_card_expiration_date');
        $data['text_credit_card_number'] = $this->language->get('text_credit_card_number');
        $data['text_credit_card_holder_name'] = $this->language->get('text_credit_card_holder_name');
        $data['text_credit_card_verification_code'] = $this->language->get('text_credit_card_verification_code');   
        $data['text_credit_card_installments'] = $this->language->get('text_credit_card_installments');
        $data['text_credit_card_table_header'] = $this->language->get('text_credit_card_table_header'); 
        $data['action'] = $baseApiUrl.'/web/checkout?ref=';
        $installmentsData = $this->model_extension_payment_vivapayment->getInstallments($order_info['total']);
        $data['indata'] = $installmentsData;
        $data['amount'] = $order_info['total'];
        //$data['debug'] = $this->session->data;
        if ($transaction_type == 'native') {
            return $this->load->view('extension/payment/vivapayment_native', $data);          
        } else {
            return $this->load->view('extension/payment/vivapayment', $data);      	
		}
    }
    private function isMultistore(){
        $this->load->model('setting/store');
        $stores = $this->model_setting_store->getStores();
        $multistore = ((count($stores) == 0)? false: true);
        return $multistore;
    }
    public function NewOrder(){
        $baseApiUrl = (($this->config->get('payment_vivapayment_test') == 1)? 'https://demo':'https://www').".vivapayments.com";
        $paymentsCreateOrderUrl = "/api/orders";
        if ($this->isMultistore() || $this->config->get('config_store_id') != '0'){
            $multistore_data = $this->config->get('payment_vivapayment_multistore');
            $source_code = $multistore_data[$this->config->get('config_store_id')]['redirect'];
            $installments = ($multistore_data[$this->config->get('config_store_id')]['installmets'])? '0': $this->request->post['installments'];
        } else {
            $source_code = $this->config->get('payment_vivapayment_payment_source');
            $installments = ($this->config->get('payment_vivapayment_disable_instaqllments'))? '0': $this->request->post['installments'];
        }
		$obj=new OrderRequest();
		//echo round($this->request->post['amount']);
		//exit;
		
		$obj->Amount=round($this->request->post['amount']);
		$obj->SourceCode= $source_code;
		//$obj->MaxInstallments=$this->request->post['installments'];
        $obj->MaxInstallments=$installments;
		$obj->Tags=array("");
        $obj->MerchantTrns=$this->request->post['ocorderid'];
        $obj->CustomerTrns=$this->config->get('config_title');
        $obj->DisableIVR='true';
        $obj->DisableCash='true';
        $obj->DisablePayAtHome='true';
		$resultObj = $this->ExecuteCall($baseApiUrl.$paymentsCreateOrderUrl,$obj);
			//echo 'The following error occured: ' . $resultObj->ErrorText;
			echo json_encode($resultObj);
    }
    public function makePayment() {
        $baseApiUrl = (($this->config->get('payment_vivapayment_test') == 1)? 'https://demo':'https://www').".vivapayments.com";
        $paymentsUrl = "/api/transactions";
        $merchantId = $this->config->get('payment_vivapayment_username');
        $apiKey = $this->config->get('payment_vivapayment_password');
        if ($this->isMultistore() || $this->config->get('config_store_id') != '0'){
            $multistore_data = $this->config->get('payment_vivapayment_multistore');
            $nativeCheckoutSourceCode = $multistore_data[$this->config->get('config_store_id')]['native'];
        } else {
            $nativeCheckoutSourceCode = $this->config->get('payment_vivapayment_hosted_payment_source');
        }
        $token = $this->request->post['hidToken'];
        $amount = ($this->request->post['amount']*100);
        $installments = $this->request->post['installments'];
        $this->load->model('checkout/order');
        $oc_order_info=$this->model_checkout_order->getOrder($this->session->data['order_id']);
        $orderCode=$this->CreateOrder($amount,$installments,$nativeCheckoutSourceCode, $oc_order_info['order_id']);
        $obj=new PaymentRequest();
		$obj->Amount=$amount;
		$obj->OrderCode=$orderCode;
		$obj->SourceCode=$nativeCheckoutSourceCode;
		$obj->CreditCard['Token']=$token;
		$obj->Installments=$installments;        
        $resultObj = $this->ExecuteCall($baseApiUrl.$paymentsUrl,$obj);
        //$this->log->write(print_r($resultObj));
        $json = array();
        if ($resultObj->ErrorCode==0){
            $this->load->model('checkout/order');
            $this->load->model('extension/payment/vivapayment');
            $this->model_checkout_order->addOrderHistory($this->request->post['orderid'], $this->config->get('payment_vivapayment_order_status_id'));
            $trdata = $this->model_extension_payment_vivapayment->getTransaction($resultObj->TransactionId);
            $capdata = array();
            $capdata['viva_orderid'] = $trdata->Transactions['0']->Order->OrderCode;
            $capdata['oc_orderid'] = $trdata->Transactions['0']->MerchantTrns;
            $capdata['transaction_id'] = $trdata->Transactions['0']->TransactionId;
            $this->model_extension_payment_vivapayment->saveTransactionToDb($capdata);
            $json['success'] = $this->url->link('checkout/success');
		} else {
		    $json['error'] =  $resultObj->ErrorCode;
            $this->session->data['error'] = "Error Code: ".$resultObj->ErrorCode;
            $json['failure'] = $this->url->link('checkout/checkout');
		}
        $this->response->setOutput(json_encode($json));
    }
    private function CreateOrder($amount,$installments,$nativeCheckoutSourceCode, $ocorderid){
        $baseApiUrl = (($this->config->get('payment_vivapayment_test') == 1)? 'https://demo':'https://www').".vivapayments.com";
        $paymentsCreateOrderUrl = "/api/orders";	
		$obj=new OrderRequest();
		$obj->Amount=$amount;
		$obj->SourceCode= $nativeCheckoutSourceCode;
		$obj->MaxInstallments=$installments;
		$obj->Tags=array("");
        $obj->MerchantTrns=$ocorderid;
        $obj->CustomerTrns=$this->config->get('config_title');
		$resultObj = $this->ExecuteCall($baseApiUrl.$paymentsCreateOrderUrl,$obj);
		if ($resultObj->ErrorCode==0){	//success when ErrorCode = 0
			return $resultObj->OrderCode;
		}
		else{
			echo 'The following error occured: ' . $resultObj->ErrorText;
			return 0;
		}	
	}
	private function ExecuteCall($postUrl,$postobject){
        $merchantId=$this->config->get('payment_vivapayment_username');
        $apiKey=$this->config->get('payment_vivapayment_password');
		$postargs=json_encode($postobject);
		// Get the curl session object
		$session = curl_init($postUrl);
		// Set the POST options.
		curl_setopt($session, CURLOPT_POST, true);
		curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
		curl_setopt($session, CURLOPT_HTTPHEADER, array(                                                                          
			'Content-Type: application/json',                                                                                
			'Content-Length: ' . strlen($postargs))                                                                       
		);   
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_USERPWD, $merchantId.':'.$apiKey);
		//curl_setopt($session, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
        defined('CURL_SSLVERSION_TLSv1') or define('CURL_SSLVERSION_TLSv1', 1);
        curl_setopt( $session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		curl_setopt($session, CURLOPT_HEADER, true);
		// Do the POST and then close the session
		$response = curl_exec($session);
		// Separate Header from Body
		$header_len = curl_getinfo($session, CURLINFO_HEADER_SIZE);
		$resHeader = substr($response, 0, $header_len);
		$resBody =  substr($response, $header_len);
		// Parse the JSON response
		try {
			if(is_object(json_decode($resBody))){
				$resultObj = json_decode($resBody);
			}else{
				preg_match('#^HTTP/1.(?:0|1) [\d]{3} (.*)$#m', $resHeader, $match);
				throw new Exception(trim($match[1]));
			}
		} catch( Exception $e ) {
			echo $e->getMessage();
		}
		curl_close($session);
		return $resultObj;
	}
    public function success(){
        $this->language->load('extension/payment/vivapayment');
        $this->load->model('extension/payment/vivapayment');
        $this->load->model('checkout/order');
        $transaction = $this->model_extension_payment_vivapayment->getTransaction($this->request->get['t']);
        
        $order_id = $transaction->Transactions['0']->MerchantTrns;
        
        $order_status_id = $this->config->get('payment_vivapayment_order_status_id');
        $this->model_checkout_order->addOrderHistory($order_id, $order_status_id);
        $capdata = array();
        $capdata['viva_orderid'] = $transaction->Transactions['0']->Order->OrderCode;
        $capdata['oc_orderid'] = $order_id;
        $capdata['transaction_id'] = $transaction->Transactions['0']->TransactionId;
        $this->model_extension_payment_vivapayment->saveTransactionToDb($capdata);
        $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
    }
    public function failure(){
        $this->language->load('extension/payment/vivapayment');
        $this->load->model('extension/payment/vivapayment');
        $this->response->redirect($this->url->link('checkout/failure', '', 'SSL'));
    }

    public function getHooks(){
        $this->load->model('extension/payment/vivapayment');
        $data['data'] = $this->model_extension_payment_vivapayment->createToken();
        $result = file_get_contents('php://input');
        $res = json_decode($result);
        $this->log->write("START Hooks");
        $this->log->write($res);
        $this->log->write("START Hooks");

        //return $this->load->view('extension/payment/vivapayment_hooks', $data);
        
//  		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/vivapayment_hooks.tpl')) {
//    		return $this->load->view($this->config->get('config_template') . '/template/payment/vivapayment_hooks.tpl', $data);
//    	} else {
//    		return $this->load->view('default/template/payment/vivapayment_hooks.tpl', $data);
//    	}
        $this->response->setOutput($data['data']);        
    } 
}
class OrderRequest {}
class PaymentRequest {}
?>