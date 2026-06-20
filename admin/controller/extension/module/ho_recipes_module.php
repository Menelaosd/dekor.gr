<?php
class ControllerExtensionModuleHoRecipesModule extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('ho_recipes/ho_recipes');
		$this->load->language('extension/module/ho_recipes_module');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/module');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_extension_module->addModule('ho_recipes_module', $this->request->post);
				$redirect = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
				$this->session->data['success'] = $this->language->get('text_success');
			} else {
				if (isset($this->request->post['action'])) {
					if ($this->request->post['action'] == 'save_stay') {
						$redirect = $this->url->link('extension/module/ho_recipes_module', '&user_token='.$this->session->data['user_token'].'&module_id='.$this->request->get['module_id'], true);
						$this->session->data['success'] = $this->language->get('text_success_edit');
					} else {
						$redirect = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
						$this->session->data['success'] = $this->language->get('text_success_edit');
					}
				}
				$this->model_extension_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->response->redirect($redirect);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_limit'] = $this->language->get('entry_limit');
		$data['entry_readmore'] = $this->language->get('entry_readmore');

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
				'href' => $this->url->link('extension/module/ho_recipes_module', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/ho_recipes_module', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/ho_recipes_module', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/ho_recipes_module', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_extension_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}
		
		if (isset($this->request->post['imagewidth'])) {
			$data['imagewidth'] = $this->request->post['imagewidth'];
		} elseif (!empty($module_info)) {
			$data['imagewidth'] = $module_info['imagewidth'];
		} else {
			$data['imagewidth'] = 330;
		}
		
		if (isset($this->request->post['imageheight'])) {
			$data['imageheight'] = $this->request->post['imageheight'];
		} elseif (!empty($module_info)) {
			$data['imageheight'] = $module_info['imageheight'];
		} else {
			$data['imageheight'] = 280;
		}
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}
		
		if (isset($this->request->post['readmore'])) {
			$data['readmore'] = $this->request->post['readmore'];
		} elseif (!empty($module_info)) {
			$data['readmore'] = $module_info['readmore'];
		} else {
			$data['readmore'] = $this->language->get('entry_readmore');;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($module_info)) {
			$data['sort_order'] = $module_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
		if (isset($this->request->post['limit'])) {
			$data['limit'] = $this->request->post['limit'];
		} elseif (!empty($module_info)) {
			$data['limit'] = $module_info['limit'];
		} else {
			$data['limit'] = 4;
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ho_recipes_module', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ho_recipes_module')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}