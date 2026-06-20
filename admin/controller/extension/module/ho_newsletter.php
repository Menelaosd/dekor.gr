<?php
class ControllerExtensionModuleHoNewsletter extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/ho_newsletter');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('ho_newsletter', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/ho_newsletter', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/ho_newsletter', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['coupon_status'])) {
			$data['coupon_status'] = $this->request->post['coupon_status'];
		} elseif (!empty($module_info)) {
			$data['coupon_status'] = $module_info['coupon_status'];
		} else {
			$data['coupon_status'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['directories'] = array();
		$data['templates'] = $templates = array();

		$directories = glob(DIR_CATALOG . 'view/theme/*', GLOB_ONLYDIR);
		foreach ($directories as $directory) {
			$data['theme_directories'][] = basename($directory);
		}
		
		foreach ($data['theme_directories'] as $theme_directory) {
			$template_folder = DIR_CATALOG.'view/theme/'.$theme_directory.'/template/extension/module/ho_newsletter_tpl/';
			if (file_exists($template_folder)) {
				$templates[$theme_directory] = array_diff(scandir($template_folder), array('..', '.'));
			}
		}

		$data['slideshow_templates'] = $templates;

		if (isset($this->request->post['active_template'])) {
			$data['active_template'] = $this->request->post['active_template'];
		} elseif (!empty($module_info)) {
			$data['active_template'] = $module_info['active_template'];
		} else {
			$data['active_template'] = '';
		}

		$this->load->model('marketing/coupon');

		$coupon_data = array(
			'start' => 0,
			'limit' => 1000
		);

		$data['coupons'] = $this->model_marketing_coupon->getCoupons($coupon_data);

		if (isset($this->request->post['active_coupon'])) {
			$data['active_coupon'] = $this->request->post['active_coupon'];
		} elseif (!empty($module_info)) {
			$data['active_coupon'] = $module_info['active_coupon'];
		} else {
			$data['active_coupon'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ho_newsletter', $data));
	}

	public function display() {
		$this->load->language('extension/module/ho_newsletter');
		
		$this->load->model('extension/module/ho_newsletter');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->getList();
	}

	protected function getList() {

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$subscribers_total = $this->model_extension_module_ho_newsletter->getTotalSubscribers();
		$results = $this->model_extension_module_ho_newsletter->getSubscribers();

		foreach ($results as $result) {
			$data['subscribers'][] = array(
				'email' => $result['email'],
				'name' => $result['firstname'].' '.$result['firstname'],
			);
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/ho_newsletter/display', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);


		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$pagination = new Pagination();
		$pagination->total = $subscribers_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('extension/module/ho_newsletter/display', 'user_token=' . $this->session->data['user_token'] . '&page={page}' . $url, true);

		$data['pagination'] = $pagination->render();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ho_newsletter_list', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ho_newsletter')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}