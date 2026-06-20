<?php
class ControllerExtensionShippingBoxnow extends Controller {
	
	public function index() {
	}
	
	public function setLockerSession() {
		if( isset($this->request->post['locker_id']) ) {
			$this->session->data['boxnow_locker_id'] 	= $this->request->post['locker_id'];
			$this->session->data['boxnow_address'] 		= $this->request->post['locker_address'];
			$this->session->data['boxnow_name'] 		= $this->request->post['locker_name'];
		};
	}
	
}
