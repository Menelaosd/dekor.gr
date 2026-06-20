<?php
class ControllerExtensionModuleHolMenu extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/hol_menu');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_hol_menu', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
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
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/hol_menu', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/hol_menu', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_hol_menu_status'])) {
			$data['module_hol_menu_status'] = $this->request->post['module_hol_menu_status'];
		} else {
			$data['module_hol_menu_status'] = $this->config->get('module_hol_menu_status');
		}

		if (isset($this->request->post['module_hol_menu_mainmenu_id'])) {
			$data['module_hol_menu_mainmenu_id'] = $this->request->post['module_hol_menu_mainmenu_id'];
		} else {
			$data['module_hol_menu_mainmenu_id'] = $this->config->get('module_hol_menu_mainmenu_id');
		}

		$this->load->model('extension/menu/hol_menu');

		$results = $this->model_extension_menu_hol_menu->getMenus();

		foreach ($results as $result) {
			$data['menus'][] = array(
				'menu_id'   	=> $result['menu_id'],
				'title'      	=> $result['title']
			);
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/hol_menu_module', $data));
	}

	public function menus() {
		$this->load->language('extension/module/hol_menu');

		$this->document->setTitle($this->language->get('heading_title_menus'));

		$this->load->model('extension/menu/hol_menu');

		$this->getMenuList();
	}

	public function add_menu() {
		$this->load->language('extension/module/hol_menu');

		$this->document->setTitle($this->language->get('heading_title_add_menu'));

		$this->load->model('extension/menu/hol_menu');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateMenuForm()) {
			$this->model_extension_menu_hol_menu->addMenu($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/hol_menu/menus', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->getMenuForm();
	}

	public function edit_menu() {
		$this->load->language('extension/module/hol_menu');

		$this->document->setTitle($this->language->get('heading_title_edit_menu'));

		$this->load->model('extension/menu/hol_menu');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateMenuForm()) {
			$this->model_extension_menu_hol_menu->editMenu($this->request->get['menu_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/hol_menu/menus', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->getMenuForm();
	}

	public function delete_menu() {
		$this->load->language('extension/module/hol_menu');

		$this->document->setTitle($this->language->get('heading_title_delete_menu'));

		$this->load->model('extension/menu/hol_menu');

		if (isset($this->request->post['selected']) && $this->validateMenuDelete()) {
			foreach ($this->request->post['selected'] as $item_id) {
				$this->model_extension_menu_hol_menu->deleteMenu($item_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/hol_menu/menus', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->getMenuList();
	}

	protected function getMenuList() {
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title_menus'),
			'href' => $this->url->link('extension/module/hol_menu/menus', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['add'] = $this->url->link('extension/module/hol_menu/add_menu', 'user_token=' . $this->session->data['user_token'], true);
		$data['delete'] = $this->url->link('extension/module/hol_menu/delete_menu', 'user_token=' . $this->session->data['user_token'], true);

		$data['items'] = array();

		$results = $this->model_extension_menu_hol_menu->getMenus();

		foreach ($results as $result) {
			$data['menus'][] = array(
				'menu_id'   	=> $result['menu_id'],
				'title'      	=> $result['title'],
				'menu_alias' 	=> $result['menu_alias'],
				'description' 	=> $result['description'],
				'count' 		=> $this->model_extension_menu_hol_menu->getTotalMenuItemsByMenuId($result['menu_id']),
				'view'      	=> $this->url->link('extension/module/hol_menu/items', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $result['menu_id'], true),
				'edit'      	=> $this->url->link('extension/module/hol_menu/edit_menu', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $result['menu_id'], true)
			);
		}
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/hol_menu_list', $data));
	}

	protected function getMenuForm() {
		$data['text_form'] = !isset($this->request->get['menu_id']) ? $this->language->get('text_add_menu') : $this->language->get('text_edit_menu');
		$data['heading_title'] = !isset($this->request->get['menu_id']) ? $this->language->get('heading_title_add_menu') : $this->language->get('heading_title_edit_menu');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = array();
		}

		if (isset($this->error['menu_alias'])) {
			$data['error_menu_alias'] = $this->error['menu_alias'];
		} else {
			$data['error_menu_alias'] = array();
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title_menus'),
			'href' => $this->url->link('extension/module/hol_menu/menu', 'user_token=' . $this->session->data['user_token'], true)
		);

		if (!isset($this->request->get['menu_id'])) {
			$data['action'] = $this->url->link('extension/module/hol_menu/add_menu', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/hol_menu/edit_menu', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $this->request->get['menu_id'], true);
		}

		$data['cancel'] = $this->url->link('extension/module/hol_menu/menus', 'user_token=' . $this->session->data['user_token'], true);
		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['menu_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$item_info = $this->model_extension_menu_hol_menu->getMenu($this->request->get['menu_id']);
		}
		
		if (isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		} elseif (!empty($item_info)) {
			$data['title'] = $item_info['title'];
		} else {
			$data['title'] = "";
		}
		
		if (isset($this->request->post['menu_alias'])) {
			$data['menu_alias'] = $this->request->post['menu_alias'];
		} elseif (!empty($item_info)) {
			$data['menu_alias'] = $item_info['menu_alias'];
		} else {
			$data['menu_alias'] = "";
		}

		if (isset($this->request->post['description'])) {
			$data['description'] = $this->request->post['description'];
		} elseif (!empty($item_info)) {
			$data['description'] = $item_info['description'];
		} else {
			$data['description'] = "";
		}
		
		$this->load->model('setting/extension');

		$this->load->model('setting/module');

		$data['extensions'] = array();
		
		// Get a list of installed modules
		$extensions = $this->model_setting_extension->getInstalled('module');

		// Add all the modules which have multiple settings for each module
		foreach ($extensions as $code) {
			$this->load->language('extension/module/' . $code, 'extension');

			$module_data = array();

			$modules = $this->model_setting_module->getModulesByCode($code);

			foreach ($modules as $module) {
				$module_data[] = array(
					'name' => strip_tags($module['name']),
					'code' => $code . '.' .  $module['module_id']
				);
			}

			if ($this->config->has('module_' . $code . '_status') || $module_data) {
				$data['extensions'][] = array(
					'name'   => strip_tags($this->language->get('extension')->get('heading_title')),
					'code'   => $code,
					'module' => $module_data
				);
			}
		};

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/hol_menu_form', $data));
	}

	protected function validateMenuForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/hol_menu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['title']) < 1) || (utf8_strlen($this->request->post['title']) > 2055)) {
			$this->error['title'] = $this->language->get('error_title');
		}

		if ((utf8_strlen($this->request->post['menu_alias']) < 1) || (utf8_strlen($this->request->post['menu_alias']) > 2055)) {
			$this->error['menu_alias'] = $this->language->get('error_menu_alias');
		}		

		return !$this->error;
	}

	protected function validateMenuDelete() {
		if (!$this->user->hasPermission('modify', 'extension/module/hol_menu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['selected'] as $item_id) {
			$menu_items_total = $this->model_extension_menu_hol_menu->getTotalMenuItemsByMenuId($item_id);

			if ($menu_items_total) {
				$this->error['warning'] = sprintf($this->language->get('error_menu_items'), $menu_items_total);
			}
		}

		return !$this->error;
	}

	public function items() {
		$this->load->language('extension/module/hol_menu');

		$this->document->setTitle($this->language->get('heading_title_items'));

		$this->load->model('extension/menu/hol_menu');

		$this->getMenuItemsList();
	}

	public function add_item() {
		$this->load->language('extension/module/hol_menu');

		$this->document->setTitle($this->language->get('text_add_menu_item'));

		$this->load->model('extension/menu/hol_menu');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateMenuItemForm()) {
			$this->model_extension_menu_hol_menu->addMenuItem($this->request->post, $this->request->get['menu_id']);

			$this->session->data['success'] = $this->language->get('text_success_item');

			$this->response->redirect($this->url->link('extension/module/hol_menu/items', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $this->request->get['menu_id'], true));
		}

		$this->getMenuItemsForm();
	}

	public function edit_item() {
		$this->load->language('extension/module/hol_menu');

		$this->document->setTitle($this->language->get('text_edit_menu_item'));

		$this->load->model('extension/menu/hol_menu');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateMenuItemForm()) {	
			
			$this->model_extension_menu_hol_menu->editMenuItem($this->request->get['item_id'], $this->request->get['menu_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success_item');

			$this->response->redirect($this->url->link('extension/module/hol_menu/items', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $this->request->get['menu_id'], true));
		}

		$this->getMenuItemsForm();
	}

	public function delete_item() {
		$this->load->language('extension/module/hol_menu');

		$this->document->setTitle($this->language->get('text_delete_menu_item'));

		$this->load->model('extension/menu/hol_menu');

		if (isset($this->request->post['selected']) && $this->validateMenuItemDelete()) {
			foreach ($this->request->post['selected'] as $item_id) {
				$this->model_extension_menu_hol_menu->deleteMenuItem($item_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/hol_menu/items', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $this->request->get['menu_id'], true));
		}

		$this->getMenuItemsList();
	}

	protected function getMenuItemsList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'a.name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title_menus'),
			'href' => $this->url->link('extension/module/hol_menu/menus', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_menu'),
			'href' => $this->url->link('extension/module/hol_menu/items', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $this->request->get['menu_id'] . $url, true)
		);

		$data['add'] = $this->url->link('extension/module/hol_menu/add_item', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $this->request->get['menu_id'] . $url, true);
		$data['delete'] = $this->url->link('extension/module/hol_menu/delete_item', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $this->request->get['menu_id'] . $url, true);
		$data['cancel'] = $this->url->link('extension/module/hol_menu/menus', 'user_token=' . $this->session->data['user_token'], true);

		$data['items'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin'),
			'filter_parent_id' => '',
			'filter_name' => ''
		);

		$item_total = $this->model_extension_menu_hol_menu->getTotalMenuItemsByMenuId($this->request->get['menu_id']);

		$results = $this->model_extension_menu_hol_menu->getMenuTree($this->request->get['menu_id']);
		$data['menu_items'] = $results;

		$data['edit'] = $this->url->link('extension/module/hol_menu/edit_item', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $this->request->get['menu_id'] . $url, true);
		$data['user_token'] = $this->session->data['user_token'];
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('blog/item', 'user_token=' . $this->session->data['user_token'] . '&sort=ad.name' . $url, true);
		$data['sort_item_group'] = $this->url->link('blog/item', 'user_token=' . $this->session->data['user_token'] . '&sort=item_group' . $url, true);
		$data['sort_sort_order'] = $this->url->link('blog/item', 'user_token=' . $this->session->data['user_token'] . '&sort=a.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $item_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('blog/item', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($item_total - $this->config->get('config_limit_admin'))) ? $item_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $item_total, ceil($item_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/hol_menu_item_list', $data));
	}

	protected function getMenuItemsForm() {
		$data['text_form'] = !isset($this->request->get['item_id']) ? $this->language->get('text_add_menu_item') : $this->language->get('text_edit_menu_item');
		$data['heading_title'] = !isset($this->request->get['item_id']) ? $this->language->get('text_add_menu_item') : $this->language->get('text_edit_menu_item');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = array();
		}

		if (isset($this->error['menu_alias'])) {
			$data['error_menu_alias'] = $this->error['menu_alias'];
		} else {
			$data['error_menu_alias'] = array();
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title_menus'),
			'href' => $this->url->link('extension/module/hol_menu/menus', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_menu'),
			'href' => $this->url->link('extension/module/hol_menu/items', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $this->request->get['menu_id'], true)
		);

		if (!isset($this->request->get['item_id'])) {
			$data['action'] = $this->url->link('extension/module/hol_menu/add_item', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $this->request->get['menu_id'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/hol_menu/edit_item', 'user_token=' . $this->session->data['user_token'] . '&item_id=' . $this->request->get['item_id'] . '&menu_id=' . $this->request->get['menu_id'], true);
		}

		$data['cancel'] = $this->url->link('extension/module/hol_menu/items', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $this->request->get['menu_id'], true);
		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['item_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$item_info = $this->model_extension_menu_hol_menu->getMenuItem($this->request->get['item_id']);
		}

		if (isset($this->request->post['url'])) {
			$data['url'] = $this->request->post['url'];
		} elseif (!empty($item_info)) {
			$data['url'] = $item_info['url'];
		} else {
			$data['url'] = '';
		}

		if (isset($this->request->post['url_id'])) {
			$data['url_id'] = $this->request->post['url_id'];
		} elseif (!empty($item_info)) {
			$data['url_id'] = $item_info['url_id'];
		} else {
			$data['url_id'] = '';
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($item_info)) {
			$data['sort_order'] = $item_info['sort_order'];
		} else {
			$data['sort_order'] = 1;
		}

		if (isset($this->request->post['route'])) {
			$data['route'] = $this->request->post['route'];
		} elseif (!empty($item_info)) {
			$data['route'] = $item_info['route'];
		} else {
			$data['route'] = '';
		}

		if (isset($this->request->post['icon_class'])) {
			$data['icon_class'] = $this->request->post['icon_class'];
		} elseif (!empty($item_info)) {
			$data['icon_class'] = $item_info['icon_class'];
		} else {
			$data['icon_class'] = '';
		}

		if (isset($this->request->post['target'])) {
			$data['target'] = $this->request->post['target'];
		} elseif (!empty($item_info)) {
			$data['target'] = $item_info['target'];
		} else {
			$data['target'] = '';
		}

		if (isset($this->request->post['parent_id'])) {
			$data['parent_id'] = $this->request->post['parent_id'];
		} elseif (!empty($item_info)) {
			$data['parent_id'] = $item_info['parent_id'];
		} else {
			$data['parent_id'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($item_info)) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($item_info)) {
			$data['image'] = $item_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($item_info) && is_file(DIR_IMAGE . $item_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($item_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['item_description'])) {
			$data['item_description'] = $this->request->post['item_description'];
		} elseif (isset($this->request->get['item_id'])) {
			$data['item_description'] = $this->model_extension_menu_hol_menu->getMenuItemDescriptions($this->request->get['item_id']);
		} else {
			$data['item_description'] = array();
		}

		$data['parents'] = array();
		
		$parents = $this->model_extension_menu_hol_menu->getMenuItemsByMenuId($this->request->get['menu_id'], array());

		foreach ($parents as $parent) {
			$data['parents'][] = array(
				'parent_id' => $parent['menu_item_id'],
				'title' => $parent['title']
			);
		}

		$this->load->model('catalog/information');

		$informations = $this->model_catalog_information->getInformations();

		foreach ($informations as $information) {
			$data['informations'][] = array(
				'information_id' => $information['information_id'],
				'title'          => $information['title']
			);
		}

		if ($data['route'] == 'category') {

			$this->load->model('catalog/category');
			$category_info = $this->model_catalog_category->getCategory($data['url_id']);

			$data['category'] = $category_info['name'];
			$data['category_id'] = $category_info['category_id'];

		} elseif ($data['route'] == 'product') {
			
			$this->load->model('catalog/product');
			$product_info = $this->model_catalog_product->getProduct($data['url_id']);

			$data['product'] = $product_info['name'];
			$data['product_id'] = $product_info['product_id'];

		} elseif ($data['route'] == 'manufacturer') {

			$this->load->model('catalog/manufacturer');
			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($data['url_id']);

			$data['manufacturer'] = $manufacturer_info['name'];
			$data['manufacturer_id'] = $manufacturer_info['manufacturer_id'];
		
		} elseif ($data['route'] == 'information') {
			$data['information_id'] = $data['url_id'];
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('setting/extension');

		$this->load->model('setting/module');

		$data['extensions'] = array();
		
		// Get a list of installed modules
		$extensions = $this->model_setting_extension->getInstalled('module');

		// Add all the modules which have multiple settings for each module
		foreach ($extensions as $code) {
			$this->load->language('extension/module/' . $code, 'extension');

			$module_data = array();

			$modules = $this->model_setting_module->getModulesByCode($code);

			foreach ($modules as $module) {
				$module_data[] = array(
					'name' => strip_tags($module['name']),
					'code' => $code . '.' .  $module['module_id']
				);
			}

			if ($this->config->has('module_' . $code . '_status') || $module_data) {
				$data['extensions'][] = array(
					'name'   => strip_tags($this->language->get('extension')->get('heading_title')),
					'code'   => $code,
					'module' => $module_data
				);
			}
		}
		
		
		if (isset($this->request->post['banner_image'])) {
			$banner_images = $this->request->post['banner_image'];
		} elseif (isset($this->request->get['item_id'])) {
			$banner_images = json_decode($item_info['banner_image'],true);
		} else {
			$banner_images = array();
		}

		$data['banner_images'] = array();
		if($banner_images) {
			foreach ($banner_images as $key => $value) {
				foreach ($value as $banner_image) {
					if (is_file(DIR_IMAGE . $banner_image['image'])) {
						$image = $banner_image['image'];
						$thumb = $banner_image['image'];
					} else {
						$image = '';
						$thumb = 'no_image.png';
					}
					
					$custom_blocks_clean = array();
					
					foreach($banner_image['custom_links'] as $custom_block) {
						if($custom_block['title']){
							$custom_blocks_clean[] = array(
								'title' => $custom_block['title'],
								'url' => $custom_block['url']
							);
						};
					};
					
					$data['banner_images'][$key][$banner_image['row']][$banner_image['col']][] = array(
						'hypertitle' => $banner_image['hypertitle'],
						'menutype'      => $banner_image['menutype'],
						'menumodule'      => $banner_image['menumodule'],
						'filtergroup'      => $banner_image['filtergroup'],
						'custom_links'      => $custom_blocks_clean,
						'menucategoryname'      => $banner_image['menucategoryname'],
						'menucategory'      => $banner_image['menucategory'],
						'menuclass'      => $banner_image['menuclass'],
						'image'      => $image,
						'thumb'      => $this->model_tool_image->resize($thumb, 500, 500),
						'sort_order' => $banner_image['sort_order']
					);
			

				}
			}
		};

		
		$this->load->model('catalog/filter');
		$data['filters'] = $this->model_catalog_filter->getFilterGroups();
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/hol_menu_item_form', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/hol_menu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateMenuItemForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/hol_menu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['item_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 3) || (utf8_strlen($value['title']) > 32)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}
		}

		return !$this->error;
	}

	protected function validateMenuItemDelete() {
		if (!$this->user->hasPermission('modify', 'extension/module/hol_menu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['selected'] as $item_id) {
			$menu_items_total = $this->model_extension_menu_hol_menu->getChildren($item_id);

			if ($menu_items_total) {
				$this->error['warning'] = sprintf($this->language->get('error_menu_items'), count($menu_items_total));
			}
		}

		return !$this->error;
	}

	public function install(){
        $this->load->model('extension/menu/hol_menu');
        $this->model_extension_menu_hol_menu->createTables();

        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_hol_menu', ['module_hol_menu_status' => 1]);

        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission(1, 'access', 'extension/module/hol_menu');
        $this->model_user_user_group->addPermission(1, 'modify', 'extension/module/hol_menu');
    }
    
	public function uninstall(){
		$this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('hol_menu');
    }
}