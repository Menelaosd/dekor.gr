<?php
class ControllerExtensionModuleHOSimpleBox extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/ho_simple_box');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');
		

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				
				$this->model_setting_module->addModule('ho_simple_box', $this->request->post);
				$redirect = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

				$this->session->data['success'] = $this->language->get('text_success');
			} else {

				if (isset($this->request->post['action'])) {
					if ($this->request->post['action'] == 'save_stay') {
						$redirect = $this->url->link('extension/module/ho_simple_box', '&user_token='.$this->session->data['user_token'].'&module_id='.$this->request->get['module_id'], true);
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
		$data['entry_subtitle'] = $this->language->get('entry_subtitle');
		$data['entry_text'] = $this->language->get('entry_text');
		$data['entry_text2'] = $this->language->get('entry_text2');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_image2'] = $this->language->get('entry_image2');
		$data['entry_link'] = $this->language->get('entry_link');
		$data['entry_link2'] = $this->language->get('entry_link2');
		$data['entry_enable_btn'] = $this->language->get('entry_enable_btn');
		$data['entry_btn_text'] = $this->language->get('entry_btn_text');
		$data['entry_btn_link'] = $this->language->get('entry_btn_link');

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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/ho_simple_box', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/ho_simple_box', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/ho_simple_box', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/ho_simple_box', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

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

		if (isset($this->request->post['subtitle'])) {
			$data['subtitle'] = $this->request->post['subtitle'];
		} elseif (!empty($module_info)) {
			$data['subtitle'] = $module_info['subtitle'];
		} else {
			$data['subtitle'] = '';
		}

		if (isset($this->request->post['text'])) {
			$data['text'] = $this->request->post['text'];
		} elseif (!empty($module_info)) {
			$data['text'] = $module_info['text'];
		} else {
			$data['text'] = '';
		}

		if (isset($this->request->post['text2'])) {
			$data['text2'] = $this->request->post['text2'];
		} elseif (!empty($module_info)) {
			$data['text2'] = $module_info['text2'];
		} else {
			$data['text2'] = '';
		}

		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($module_info)) {
			$data['image'] = $module_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image_thumb']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['image_thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($module_info) && is_file(DIR_IMAGE . $module_info['image'])) {
			$data['image_thumb'] = $this->model_tool_image->resize($module_info['image'], 100, 100);
		} else {
			$data['image_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		if (isset($this->request->post['image2'])) {
			$data['image2'] = $this->request->post['image2'];
		} elseif (!empty($module_info)) {
			$data['image2'] = $module_info['image2'];
		} else {
			$data['image2'] = '';
		}

		if (isset($this->request->post['image2_thumb']) && is_file(DIR_IMAGE . $this->request->post['image2'])) {
			$data['image2_thumb'] = $this->model_tool_image->resize($this->request->post['image2'], 100, 100);
		} elseif (!empty($module_info) && is_file(DIR_IMAGE . $module_info['image2'])) {
			$data['image2_thumb'] = $this->model_tool_image->resize($module_info['image2'], 100, 100);
		} else {
			$data['image2_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		if (isset($this->request->post['link'])) {
			$data['link'] = $this->request->post['link'];
		} elseif (!empty($module_info)) {
			$data['link'] = $module_info['link'];
		} else {
			$data['link'] = '';
		}

		if (isset($this->request->post['link2'])) {
			$data['link2'] = $this->request->post['link2'];
		} elseif (!empty($module_info)) {
			$data['link2'] = $module_info['link2'];
		} else {
			$data['link2'] = '';
		}

		if (isset($this->request->post['enable_btn'])) {
			$data['enable_btn'] = $this->request->post['enable_btn'];
		} elseif (!empty($module_info)) {
			$data['enable_btn'] = $module_info['enable_btn'];
		} else {
			$data['enable_btn'] = '';
		}

		if (isset($this->request->post['btn_text'])) {
			$data['btn_text'] = $this->request->post['btn_text'];
		} elseif (!empty($module_info)) {
			$data['btn_text'] = $module_info['btn_text'];
		} else {
			$data['btn_text'] = '';
		}

		if (isset($this->request->post['btn_link'])) {
			$data['btn_link'] = $this->request->post['btn_link'];
		} elseif (!empty($module_info)) {
			$data['btn_link'] = $module_info['btn_link'];
		} else {
			$data['btn_link'] = '';
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
			$template_folder = DIR_CATALOG.'view/theme/'.$theme_directory.'/template/extension/module/ho_simple_box_tpl/';
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

		$this->response->setOutput($this->load->view('extension/module/ho_simple_box', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ho_simple_box')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}