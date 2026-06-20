<?php
class ControllerExtensionModuleHOSlideshow extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/ho_slideshow');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');
		

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				
				$this->model_setting_module->addModule('ho_slideshow', $this->request->post);
				$redirect = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

				$this->session->data['success'] = $this->language->get('text_success');
			} else {

				if (isset($this->request->post['action'])) {
					if ($this->request->post['action'] == 'save_stay') {
						$redirect = $this->url->link('extension/module/ho_slideshow', '&user_token='.$this->session->data['user_token'].'&module_id='.$this->request->get['module_id'], true);
						$this->session->data['success'] = $this->language->get('text_success_edit');
					} else {
						$redirect = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
						$this->session->data['success'] = $this->language->get('text_success_edit');
					}
				}
				
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->response->redirect($redirect);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_banner'] = $this->language->get('entry_banner');
		$data['entry_width'] = $this->language->get('entry_width');
		$data['entry_height'] = $this->language->get('entry_height');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_active_template'] = $this->language->get('entry_active_template');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_save_continue'] = $this->language->get('button_save_continue');

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

		if (isset($this->error['width'])) {
			$data['error_width'] = $this->error['width'];
		} else {
			$data['error_width'] = '';
		}

		if (isset($this->error['height'])) {
			$data['error_height'] = $this->error['height'];
		} else {
			$data['error_height'] = '';
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
				'href' => $this->url->link('extension/module/ho_slideshow', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/ho_slideshow', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/ho_slideshow', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/ho_slideshow', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
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

		if (isset($this->request->post['banner_id'])) {
			$data['banner_id'] = $this->request->post['banner_id'];
		} elseif (!empty($module_info)) {
			$data['banner_id'] = $module_info['banner_id'];
		} else {
			$data['banner_id'] = '';
		}

		$this->load->model('design/banner');

		$data['banners'] = $this->model_design_banner->getBanners();

		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($module_info)) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = '';
		}

		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($module_info)) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['directories'] = array();
		$data['templates'] = array();

		$directories = glob(DIR_CATALOG . 'view/theme/*', GLOB_ONLYDIR);
		foreach ($directories as $directory) {
			$data['theme_directories'][] = basename($directory);
		}
		
		foreach ($data['theme_directories'] as $theme_directory) {
			$template_folder = DIR_CATALOG.'view/theme/'.$theme_directory.'/template/extension/module/ho_slideshow_tpl/';
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

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ho_slideshow', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ho_slideshow')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!$this->request->post['width']) {
			$this->error['width'] = $this->language->get('error_width');
		}

		if (!$this->request->post['height']) {
			$this->error['height'] = $this->language->get('error_height');
		}

		return !$this->error;
	}
}
