<?php
class ControllerExtensionFeedHoSkroutzFeed extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/feed/ho_skroutz_feed');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('feed_ho_skroutz', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true));
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
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/feed/ho_skroutz_feed', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/feed/ho_skroutz_feed', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true);

		$data['user_token'] = $this->session->data['user_token'];

		$data['data_feed'] = HTTP_CATALOG . 'index.php?route=extension/feed/ho_skroutz_feed';

		if (isset($this->request->post['feed_ho_skroutz_mode'])) {
			$data['feed_ho_skroutz_mode'] = $this->request->post['feed_ho_skroutz_mode'];
		} else {
			$data['feed_ho_skroutz_mode'] = $this->config->get('feed_ho_skroutz_mode');
		}

		if (isset($this->request->post['feed_ho_skroutz_feed_status'])) {
			$data['feed_ho_skroutz_feed_status'] = $this->request->post['feed_ho_skroutz_feed_status'];
		} else {
			$data['feed_ho_skroutz_feed_status'] = $this->config->get('feed_ho_skroutz_feed_status');
		}

		if (isset($this->request->post['feed_ho_skroutz_feed_display_weight'])) {
			$data['feed_ho_skroutz_feed_display_weight'] = $this->request->post['feed_ho_skroutz_feed_display_weight'];
		} else {
			$data['feed_ho_skroutz_feed_display_weight'] = $this->config->get('feed_ho_skroutz_feed_display_weight');
		}

		if (isset($this->request->post['feed_ho_skroutz_feed_additional_images'])) {
			$data['feed_ho_skroutz_feed_additional_images'] = $this->request->post['feed_ho_skroutz_feed_additional_images'];
		} else {
			$data['feed_ho_skroutz_feed_additional_images'] = $this->config->get('feed_ho_skroutz_feed_additional_images');
		}

		if (isset($this->request->post['feed_ho_skroutz_feed_display_manufacturer'])) {
			$data['feed_ho_skroutz_feed_display_manufacturer'] = $this->request->post['feed_ho_skroutz_feed_display_manufacturer'];
		} else {
			$data['feed_ho_skroutz_feed_display_manufacturer'] = $this->config->get('feed_ho_skroutz_feed_display_manufacturer');
		}

		if (isset($this->request->post['feed_ho_skroutz_availability_1'])) {
			$data['feed_ho_skroutz_availability_1'] = $this->request->post['feed_ho_skroutz_availability_1'];
		} else {
			$data['feed_ho_skroutz_availability_1'] = $this->config->get('feed_ho_skroutz_availability_1');
		}

		if (isset($this->request->post['feed_ho_skroutz_availability_2'])) {
			$data['feed_ho_skroutz_availability_2'] = $this->request->post['feed_ho_skroutz_availability_2'];
		} else {
			$data['feed_ho_skroutz_availability_2'] = $this->config->get('feed_ho_skroutz_availability_2');
		}

		if (isset($this->request->post['feed_ho_skroutz_availability_3'])) {
			$data['feed_ho_skroutz_availability_3'] = $this->request->post['feed_ho_skroutz_availability_3'];
		} else {
			$data['feed_ho_skroutz_availability_3'] = $this->config->get('feed_ho_skroutz_availability_3');
		}

		if (isset($this->request->post['feed_ho_skroutz_availability_4'])) {
			$data['feed_ho_skroutz_availability_4'] = $this->request->post['feed_ho_skroutz_availability_4'];
		} else {
			$data['feed_ho_skroutz_availability_4'] = $this->config->get('feed_ho_skroutz_availability_4');
		}

		if (isset($this->request->post['feed_ho_skroutz_option_size'])) {
			$data['feed_ho_skroutz_option_size'] = $this->request->post['feed_ho_skroutz_option_size'];
		} else {
			$data['feed_ho_skroutz_option_size'] = $this->config->get('feed_ho_skroutz_option_size');
		}

		if (isset($this->request->post['feed_ho_skroutz_option_color'])) {
			$data['feed_ho_skroutz_option_color'] = $this->request->post['feed_ho_skroutz_option_color'];
		} else {
			$data['feed_ho_skroutz_option_color'] = $this->config->get('feed_ho_skroutz_option_color');
		}

		if (isset($this->request->post['feed_ho_skroutz_analytics_id'])) {
			$data['feed_ho_skroutz_analytics_id'] = $this->request->post['feed_ho_skroutz_analytics_id'];
		} else {
			$data['feed_ho_skroutz_analytics_id'] = $this->config->get('feed_ho_skroutz_analytics_id');
		}

		if (isset($this->request->post['feed_ho_skroutz_analytics'])) {
			$data['feed_ho_skroutz_analytics'] = $this->request->post['feed_ho_skroutz_analytics'];
		} else {
			$data['feed_ho_skroutz_analytics'] = $this->config->get('feed_ho_skroutz_analytics');
		}

		$this->load->model('catalog/option');
		$results = $this->model_catalog_option->getOptions();

		foreach($results as $result){
			$data['product_options'][] = array	(
				'option_id'	=> $result['option_id'],
				'name'		=> $result['name']
			);
		}

		$this->load->model('localisation/stock_status');
		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/feed/ho_skroutz_feed', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/feed/ho_skroutz_feed')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install() {
		$this->load->model('user/user_group');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/feed/ho_skroutz_feed');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/feed/ho_skroutz_feed');

		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('feed_ho_skroutz', array('feed_ho_skroutz_feed_status' => '1'));		

		$this->load->model('extension/feed/ho_skroutz_feed');
		$this->model_extension_feed_ho_skroutz_feed->install();
	}

	public function uninstall() {
		$this->load->model('user/user_group');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/feed/ho_skroutz_feed');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/feed/ho_skroutz_feed');
		
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('feed_ho_skroutz');		
    }

}