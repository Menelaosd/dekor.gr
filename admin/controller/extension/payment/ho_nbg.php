<?php 
class ControllerExtensionPaymentHoNbg extends Controller {
	private $error = array();
	
	public function index() {
		$this->load->language('extension/payment/ho_nbg');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_ho_nbg', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['merchant'])) {
			$data['error_merchant'] = $this->error['merchant'];
		} else {
			$data['error_merchant'] = '';
		}

		$data['http_catalog'] = HTTP_CATALOG;
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/ho_nbg', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/ho_nbg', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);	

		if (isset($this->request->post['payment_ho_nbg_merchant_name'])) {
			$data['payment_ho_nbg_merchant_name'] = $this->request->post['payment_ho_nbg_merchant_name'];
		} else {
			$data['payment_ho_nbg_merchant_name'] = $this->config->get('payment_ho_nbg_merchant_name');
		}
		
		if (isset($this->request->post['payment_ho_nbg_client'])) {
			$data['payment_ho_nbg_client'] = $this->request->post['payment_ho_nbg_client'];
		} else {
			$data['payment_ho_nbg_client'] = $this->config->get('payment_ho_nbg_client');
		}
		
		if (isset($this->request->post['payment_ho_nbg_password'])) {
			$data['payment_ho_nbg_password'] = $this->request->post['payment_ho_nbg_password'];
		} else {
			$data['payment_ho_nbg_password'] = $this->config->get('payment_ho_nbg_password');
		}
		
		if (isset($this->request->post['payment_ho_nbg_merchant_id'])) {
			$data['payment_ho_nbg_merchant_id'] = $this->request->post['payment_ho_nbg_merchant_id'];
		} else {
			$data['payment_ho_nbg_merchant_id'] = $this->config->get('payment_ho_nbg_merchant_id');
		}
		
		if (isset($this->request->post['payment_ho_nbg_pageid'])) {
			$data['payment_ho_nbg_pageid'] = $this->request->post['payment_ho_nbg_pageid'];
		} else {
			$data['payment_ho_nbg_pageid'] = $this->config->get('payment_ho_nbg_pageid');
		}

		if (isset($this->request->post['payment_ho_nbg_total'])) {
			$data['payment_ho_nbg_total'] = $this->request->post['payment_ho_nbg_total'];
		} else {
			$data['payment_ho_nbg_total'] = $this->config->get('payment_ho_nbg_total');
		}

		if (isset($this->request->post['payment_ho_nbg_testmode'])) {
			$data['payment_ho_nbg_testmode'] = $this->request->post['payment_ho_nbg_testmode'];
		} else {
			$data['payment_ho_nbg_testmode'] = $this->config->get('payment_ho_nbg_testmode');
		}	

		if (isset($this->request->post['payment_ho_nbg_order_status_id'])) {
			$data['payment_ho_nbg_order_status_id'] = $this->request->post['payment_ho_nbg_order_status_id'];
		} else {
			$data['payment_ho_nbg_order_status_id'] = $this->config->get('payment_ho_nbg_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_ho_nbg_geo_zone_id'])) {
			$data['payment_ho_nbg_geo_zone_id'] = $this->request->post['payment_ho_nbg_geo_zone_id'];
		} else {
			$data['payment_ho_nbg_geo_zone_id'] = $this->config->get('payment_ho_nbg_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_ho_nbg_status'])) {
			$data['payment_ho_nbg_status'] = $this->request->post['payment_ho_nbg_status'];
		} else {
			$data['payment_ho_nbg_status'] = $this->config->get('payment_ho_nbg_status');
		}

		if (isset($this->request->post['payment_ho_nbg_sort_order'])) {
			$data['payment_ho_nbg_sort_order'] = $this->request->post['payment_ho_nbg_sort_order'];
		} else {
			$data['payment_ho_nbg_sort_order'] = $this->config->get('payment_ho_nbg_sort_order');
		}

		if (isset($this->request->post['payment_ho_nbg_debug'])) {
			$data['payment_ho_nbg_debug'] = $this->request->post['payment_ho_nbg_debug'];
		} else {
			$data['payment_ho_nbg_debug'] = $this->config->get('payment_ho_nbg_debug');
		}

		if ($data['payment_ho_nbg_debug'] == 1) {
			if(file_exists(DIR_LOGS . 'ho_ngb_debug.log')) {
				$data['debug_text'] = file_get_contents(DIR_LOGS . 'ho_ngb_debug.log');
			} else {
				$data['debug_text'] = '';
			}
		} else {
			$data['debug_text'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('extension/payment/ho_nbg', $data));
	}

	public function install() {
		//Δε χρειάζεται κάτι για την εγκατάσταση :) 
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/ho_nbg')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_ho_nbg_merchant_id']) {
			$this->error['merchant'] = $this->language->get('error_merchant');
		}

		return !$this->error;
	}
}