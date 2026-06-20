<?php
/******************************************************
 * @package Google Tag Manager for OC1.5x, OC2x,3x
 * @version 4.4.4
 * @author Muhammad Akram
 * @link https://aits.xyz
 * @copyright Copyright (C)2018 aits.xyz All rights reserved.
 * @email:info@aits.pk. 
 * $date: 14 November 2019
*******************************************************/
class ControllerExtensionModuleTagmanager extends Controller {
	public function sendorder() {

		if (isset($this->request->get['oid'])) {
			$order_id = $this->request->get['oid'];
		}

		if (isset($this->request->get['v'])) {
			$v= $this->request->get['v'];
		}

		$this->load->model('extension/module/tagmanager');
		$tagmanager = $this->model_extension_module_tagmanager->getTagmanger();

		if (!$this->validate($v)) {
			$json = 'error processing ->';
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} 

		if ($this->validate($v)) { 
			if (isset($order_id) && !empty($order_id)) {
				$output = $this->model_extension_module_tagmanager->GAorder($order_id) ;
			}
		}
		$json = $output;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);

	}

	public function hitorder() {
		if (isset($this->request->get['v'])) {
			$v= $this->request->get['v'];
		}

		if (!$this->validate($v)) {
			$json = 'error processing ->';
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} 

		if ($this->validate($v)) { 
			
			$json = 'error';
			if (isset($this->request->get['oid'])) {
				$this->load->model('extension/module/tagmanager');
				$order_id = $this->request->get['oid'];
				$result = $this->model_extension_module_tagmanager->GAupdateorder($order_id);

				$json = 'success';
			}
		}

	}

	public function refund() {
		if (isset($this->request->get['v'])) {
			$v= $this->request->get['v'];
		}

		if (!$this->validate($v)) {
			$json = 'error processing ->';
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} 

		if (isset($this->request->get['order_status_id'])) {
			$order_status_id = $this->request->get['order_status_id'];

		} else {
			$json = 'error processing ->';
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		}

		$status = array ('7','11');

		if (in_array($order_status_id, $status, true) && $this->validate($v)) {
			$json = 'error';
			if (isset($this->request->get['oid'])) {
				$this->load->model('extension/module/tagmanager');
				$order_id = $this->request->get['oid'];
				$result = $this->model_extension_module_tagmanager->GArefund($order_id);
				$json = 'success';
			}
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} else {
			$json = 'error processing ->';
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		}
	}



	
	  public function validate($str) {
		$this->load->model('extension/module/tagmanager');
		$tagmanager = $this->model_extension_module_tagmanager->getTagmanger();
		if ($str != $tagmanager['vs']) {
		  return false;
		} else {
		  return true;
		}
	  }
}