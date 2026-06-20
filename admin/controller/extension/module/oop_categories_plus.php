<?php
class ControllerExtensionModuleOopCategoriesPlus extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/oop_categories_plus');
		
	

		$this->document->setTitle($this->language->get('heading_title'));		

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_oop_categories_plus', $this->request->post);

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
			'href' => $this->url->link('extension/module/oop_categories_plus', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/oop_categories_plus', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		$data['text_oop_display_menu'] = $this->language->get('text_oop_display_menu');
		$data['text_oop_collapsed'] = $this->language->get('text_oop_collapsed');	
		$data['text_oop_partially_opened'] = $this->language->get('text_oop_partially_opened');			
		$data['text_oop_opened'] = $this->language->get('text_oop_opened');		
		$data['text_oop_open_levels_count'] = $this->language->get('text_oop_open_levels_count');	
		
		$this->load->model('extension/module/oop_categories_plus');
		$data['stores'] = $this->model_extension_module_oop_categories_plus->getStores();
		
		if (isset($this->request->post['module_oop_categories_plus_display'])) {
			$data['module_oop_categories_plus_display'] = $this->request->post['module_oop_categories_plus_display'];
		} else {
			$data['module_oop_categories_plus_display'] = $this->config->get('module_oop_categories_plus_display');
		}		
		
		if (isset($this->request->post['module_oop_categories_plus_partially_opened'])) {
			$data['module_oop_categories_plus_partially_opened'] = $this->request->post['module_oop_categories_plus_partially_opened'];
		} else {
			$data['module_oop_categories_plus_partially_opened'] = $this->config->get('module_oop_categories_plus_partially_opened');
		}		
		
		if (isset($this->request->post['module_oop_categories_plus_subcats_products'])) {
			$data['module_oop_categories_plus_subcats_products'] = $this->request->post['module_oop_categories_plus_subcats_products'];
		} else {
			$data['module_oop_categories_plus_subcats_products'] = $this->config->get('module_oop_categories_plus_subcats_products');
		}		
		
		if (isset($this->request->post['module_oop_categories_plus_max_height'])) {
			$data['module_oop_categories_plus_max_height'] = (int)$this->request->post['module_oop_categories_plus_max_height'];
		} else {
			$data['module_oop_categories_plus_max_height'] = (int)$this->config->get('module_oop_categories_plus_max_height');
		}		
		if(!$data['module_oop_categories_plus_max_height']) {
		      $data['module_oop_categories_plus_max_height'] = "";
		}
		
		if (isset($this->request->post['module_oop_categories_plus_store'])) {
			$data['module_oop_categories_plus_store'] = $this->request->post['module_oop_categories_plus_store'];
		} else {
			$data['module_oop_categories_plus_store'] = $this->config->get('module_oop_categories_plus_store');
		}		
		
		if (isset($this->request->post['module_oop_categories_plus_status'])) {
			$data['module_oop_categories_plus_status'] = $this->request->post['module_oop_categories_plus_status'];
		} else {
			$data['module_oop_categories_plus_status'] = $this->config->get('module_oop_categories_plus_status');
		}

		$data['setting_mod'] = 1;
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$data['categories_ignore'] = $this->model_extension_module_oop_categories_plus->getCategoryOrderIgnore();
		
		$this->response->setOutput($this->load->view('extension/module/oop_categories_plus', $data));
	}
	
	public function list() {
		$this->load->language('extension/module/oop_categories_plus');

		$this->document->setTitle($this->language->get('heading_title'));		

		$this->getList();	
	}
	
	public function correct_col_left_before (&$route, &$data) {	
	      if($this->config->get("module_oop_categories_plus_status")) {
		    $this->load->language('extension/module/oop_categories_plus');
		    if ($this->user->hasPermission('access', 'extension/module/oop_categories_plus')) {
			  foreach($data['menus'] as &$menu) {
			      if(isset($menu['id']) && $menu['id'] == 'menu-catalog') {
				    array_splice($menu['children'], 1, 0, array(
					    array(
						'name' => $this->language->get('text_oop_categories_plus'),
						'href' => $this->url->link('extension/module/oop_categories_plus/list&path=-1', 'user_token=' . $this->session->data['user_token'], true),
						'children' => array()
					    )
					)
				    );
				    break;
			       }
			  }
		    }
	      }
	}
	
	public function correct_category_add_before (&$route, &$data) {
	      $arr_uri = explode("&", parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY));
		if(in_array('mod=oop', $arr_uri)) {
		    if(isset($this->request->get['path'])) {
			$arr_parent_id = explode("_", $this->request->get['path']);
			$parent_id = end($arr_parent_id);
			$this->load->model('extension/module/oop_categories_plus');
			$path = $this->model_extension_module_oop_categories_plus->getCategoryPath($parent_id);
			$this->request->post['path'] = $path;
			$this->request->post['parent_id'] = $parent_id;	
		    }
		}
	}
	
	public function correct_category_before (&$route, &$data) {
		$arr_uri = explode("&", parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY));
		if(in_array('mod=oop', $arr_uri)) {
		      $url_arg = "";
		      foreach($arr_uri as $au) {
			  $arr_au = explode("=", $au);
			  if((sizeof($arr_au)>1) && in_array($arr_au[0], array("path","ooptab"))) {
				switch($arr_au[0]) {
				    case 'path':
					$url_arg = "&path=" . $arr_au[1];
				    break;
				    case 'ooptab':
					$url_arg = "&ooptab=" . $arr_au[1];
				    break;
				}
			  }	
		      }
						
		      $data['action'] = str_replace(array('route=catalog/category/add','route=catalog/category/edit'), array('route=extension/module/oop_categories_plus/add_category','route=extension/module/oop_categories_plus/edit_category'), $data['action']) . $url_arg;
		      $data['cancel'] = str_replace('route=catalog/category', 'route=extension/module/oop_categories_plus/list', $data['cancel']) . $url_arg;
		}
	}		
	
	public function correct_product_before (&$route, &$data) {
		$arr_uri = explode("&", parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY));
		if(in_array('mod=oop', $arr_uri)) {
		    $url_arg = "";
		    foreach($arr_uri as $au) {
			$arr_au = explode("=", $au);
			if((sizeof($arr_au)>1) && in_array($arr_au[0], array("path","ooptab"))) {
			      switch($arr_au[0]) {
				  case 'path':
				      $url_arg = "&path=" . $arr_au[1];
				  break;
				  case 'ooptab':
				      $url_arg = "&ooptab=" . $arr_au[1];
				  break;
			      }
			}	
		    }
					      
		    $data['action'] = str_replace('route=catalog/product/', 'route=extension/module/oop_categories_plus/', $data['action']) . $url_arg;
		    $data['cancel'] = str_replace('route=catalog/product', 'route=extension/module/oop_categories_plus/list', $data['cancel']) . $url_arg;		  		
		}
	}	

	
	public function add_category() {
		$this->load->language('catalog/category');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateFormCategory()) {
			$this->model_catalog_category->addCategory($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

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
			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}	
			if (isset($this->request->get['ooptab'])) {
				$url .= '&ooptab=' . $this->request->get['ooptab'];
			}
			
			$this->response->redirect($this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}	
	
	public function add() {
		$this->load->language('extension/module/oop_categories_plus');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/product');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_product->addProduct($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}
			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}	
			if (isset($this->request->get['ooptab'])) {
				$url .= '&ooptab=' . $this->request->get['ooptab'];
			}
			
			$this->response->redirect($this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		} 
		
		$this->getList();
	}

	public function edit_category() {
		$this->load->language('catalog/category');
		$this->document->setTitle($this->language->get('heading_title'));		
		$this->load->model('catalog/category');	
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateFormCategory()) {
			$this->model_catalog_category->editCategory($this->request->get['category_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			
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
			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}	
			if (isset($this->request->get['ooptab'])) {
				$url .= '&ooptab=' . $this->request->get['ooptab'];
			}			

			$this->response->redirect($this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		} 

		$this->getList();
	}
	
	public function edit() {
		$this->load->language('catalog/product');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/product');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_product->editProduct($this->request->get['product_id'], $this->request->post, $current_category_id);

			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}
			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}	
			if (isset($this->request->get['ooptab'])) {
				$url .= '&ooptab=' . $this->request->get['ooptab'];
			}				

			$this->response->redirect($this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		
		$this->getList();
	}

	public function delete() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_product->deleteProduct($product_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}
			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}	
			if (isset($this->request->get['ooptab'])) {
				$url .= '&ooptab=' . $this->request->get['ooptab'];
			}			

			$this->response->redirect($this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function delete_category() {
		$this->load->language('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/category');

		if (isset($this->request->post['selected']) && $this->validateDeleteCategory()) {
			foreach ($this->request->post['selected'] as $category_id) {
				$this->model_catalog_category->deleteCategory($category_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

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
			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}	
			if (isset($this->request->get['ooptab'])) {
				$url .= '&ooptab=' . $this->request->get['ooptab'];
			}
			
			$this->response->redirect($this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}	
	
	public function copy() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_product->copyProduct($product_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}
			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}	
			if (isset($this->request->get['ooptab'])) {
				$url .= '&ooptab=' . $this->request->get['ooptab'];
			}			

			$this->response->redirect($this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		$this->document->addLink("view/stylesheet/oop_categories_plus.css","stylesheet");	
		
        $this->document->addStyle('https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
        $this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.min.js');			
	
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = '';
		}

		if (isset($this->request->get['filter_price'])) {
			$filter_price = $this->request->get['filter_price'];
		} else {
			$filter_price = '';
		}

		if (isset($this->request->get['filter_quantity'])) {
			$filter_quantity = $this->request->get['filter_quantity'];
		} else {
			$filter_quantity = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		$sort = 'p2c.sort_order_ppc';

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$data['catpath'] = '';

		if (isset($this->request->get['path'])) {
			$path = $this->request->get['path'];
			$data['catpath'] = $path;
		} else {
			$path = '';
		}	
		
		
		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		if (isset($this->request->get['path'])) {
			$url .= '&path=' . $this->request->get['path'];
		}
		if (isset($this->request->get['ooptab'])) {
			$url .= '&ooptab=' . $this->request->get['ooptab'];
		}		
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		
		$data['add_cat'] = html_entity_decode($this->url->link('catalog/category/add', 'user_token=' . $this->session->data['user_token'] . $url . '&mod=oop', true));
		$data['edit_cat'] = html_entity_decode($this->url->link('catalog/category/edit', 'user_token=' . $this->session->data['user_token'] . $url . '&mod=oop', true));
		$data['del_cat'] = $this->url->link('extension/module/oop_categories_plus/delete_category', 'user_token=' . $this->session->data['user_token'] . $url . '&mod=oop', true);		
		
		$data['add'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . $url . '&mod=oop', true);
		$data['copy'] = $this->url->link('extension/module/oop_categories_plus/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/module/oop_categories_plus/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['savelist'] = html_entity_decode($this->url->link('extension/module/oop_categories_plus/savelist', 'user_token=' . $this->session->data['user_token'] . $url, true));		

		if (isset($this->request->get['ooptab'])) {
			$data['ooptab'] = $this->request->get['ooptab'];
		} else {
			$data['ooptab'] = 0;
		}		

		if (isset($this->request->post['module_oop_categories_plus_display'])) {
			$data['module_oop_categories_plus_display'] = $this->request->post['module_oop_categories_plus_display'];
		} else {
			$data['module_oop_categories_plus_display'] = $this->config->get('module_oop_categories_plus_display');
		}		
		if (isset($this->request->post['module_oop_categories_plus_partially_opened'])) {
			$data['module_oop_categories_plus_partially_opened'] = $this->request->post['module_oop_categories_plus_partially_opened'];
		} else {
			$data['module_oop_categories_plus_partially_opened'] = $this->config->get('module_oop_categories_plus_partially_opened');
		}
		if (isset($this->request->post['module_oop_categories_plus_subcats_products'])) {
			$data['module_oop_categories_plus_subcats_products'] = (int)$this->request->post['module_oop_categories_plus_subcats_products'];
		} else {
			$data['module_oop_categories_plus_subcats_products'] = (int)$this->config->get('module_oop_categories_plus_subcats_products');
		}
		if (isset($this->request->post['module_oop_categories_plus_max_height'])) {
			$data['module_oop_categories_plus_max_height'] = (int)$this->request->post['module_oop_categories_plus_max_height'];
		} else {
			$data['module_oop_categories_plus_max_height'] = (int)$this->config->get('module_oop_categories_plus_max_height');
		}
		if(!$data['module_oop_categories_plus_max_height']) {
		      $data['module_oop_categories_plus_max_height'] = "";
		}		
		if (isset($this->request->post['module_oop_categories_plus_store'])) {
			$data['module_oop_categories_plus_store'] = (int)$this->request->post['module_oop_categories_plus_store'];
		} else {
			$data['module_oop_categories_plus_store'] = (int)$this->config->get('module_oop_categories_plus_store');
		}		
		$data['module_oop_categories_plus_status'] = $this->config->get('module_oop_categories_plus_status');		
		
		//$limit = $this->config->get('config_limit_admin');
		$limit_override = 1200;

		$data['products'] = array();

		$filter_data = array(
			'filter_name'	  => $filter_name,
			'filter_model'	  => $filter_model,
			'filter_price'	  => $filter_price,
			'filter_quantity' => $filter_quantity,
			'filter_status'   => $filter_status,
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $limit_override,
			'limit'           => $limit_override,
			'path'		  => $path
		);

		$this->load->model('tool/image');
		
		$this->load->model('extension/module/oop_categories_plus');

		$product_total = $this->model_extension_module_oop_categories_plus->getTotalProducts($filter_data);

		$results = $this->model_extension_module_oop_categories_plus->getProducts($filter_data);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 210, 210);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 210, 210);
			}

			$special = false;

			$product_specials = $this->model_extension_module_oop_categories_plus->getProductSpecials($result['product_id']);

			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00 00:00:00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00 00:00:00' || strtotime($product_special['date_end']) > time())) {
					$special = $this->currency->format($product_special['price'], $this->config->get('config_currency'));
					break;
				}
			}

			$data['products'][] = array(
				'product_id' => $result['product_id'],
				'image'      => $image,
				'name'       => htmlspecialchars($result['name']),
				'model'      => $result['model'],
				'price'      => $this->currency->format($result['price'], $this->config->get('config_currency')),
				'price_d'    => $result['price'],
				'special'    => $special,
				'quantity'   => $result['quantity'],
				'sort_order'   => $result['sort_order'],
				'status'     => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'status_d'   => $result['status'],
				'sort_order_ppc'   => $result['sort_order_ppc'],
				'edit'       => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . $url . '&mod=oop', true)
			);
		}

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

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		if (isset($this->request->get['path'])) {
			$url .= '&path=' . $this->request->get['path'];
		}
		
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $url, true);
		$data['sort_model'] = $this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . '&sort=p.model' . $url, true);
		$data['sort_price'] = $this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . '&sort=p.price' . $url, true);
		$data['sort_quantity'] = $this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . '&sort=p.quantity' . $url, true);
		$data['sort_status'] = $this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . '&sort=p.status' . $url, true);
		// $data['sort_order'] = $this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . '&sort=p.sort_order' . $url, true);
		$data['sort_order'] = $this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . '&sort=p2c.sort_order_ppc' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		if (isset($this->request->get['path'])) {
			$url .= '&path=' . $this->request->get['path'];
		}		
		if (isset($this->request->get['ooptab'])) {
			$url .= '&ooptab=' . $this->request->get['ooptab'];
		}		
		
		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $limit_override;
		$pagination->url = $this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit_override) + 1 : 0, ((($page - 1) * $limit_override) > ($product_total - $limit_override)) ? $product_total : ((($page - 1) * $limit_override) + $limit_override), $product_total, ceil($product_total / $limit_override));

		$data['filter_name'] = $filter_name;
		$data['filter_model'] = $filter_model;
		$data['filter_price'] = $filter_price;
		$data['filter_quantity'] = $filter_quantity;
		$data['filter_status'] = $filter_status;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		if (isset($this->request->get['path'])) {
			$data['parts'] = explode('_', (string)$this->request->get['path']);
		} else {
			$data['parts'] = array();
		}
		$data['categories'] = $this->model_extension_module_oop_categories_plus->getCategories();
		$data['categories_ignore'] = $this->model_extension_module_oop_categories_plus->getCategoryOrderIgnore();
		
		$data['oop_display'] = (int)$this->config->get('module_oop_categories_plus_display');
		$data['oop_opened'] = (int)$this->config->get('module_oop_categories_plus_partially_opened');
		$data['url_d2c'] = html_entity_decode($this->url->link('extension/module/oop_categories_plus/drop2cat', 'user_token=' . $this->session->data['user_token'], true));
		
		$data['text_oop_settings'] = $this->language->get('text_oop_settings');
		$data['text_oop_display_menu'] = $this->language->get('text_oop_display_menu');
		$data['text_oop_collapsed'] = $this->language->get('text_oop_collapsed');	
		$data['text_oop_partially_opened'] = $this->language->get('text_oop_partially_opened');			
		$data['text_oop_opened'] = $this->language->get('text_oop_opened');		
		$data['text_oop_open_levels_count'] = $this->language->get('text_oop_open_levels_count');	
		$data['text_oop_apply_button'] = $this->language->get('text_oop_apply_button');			
		$data['action_frm_oop_c2p'] = $this->url->link('extension/module/oop_categories_plus/savesett', 'user_token=' . $this->session->data['user_token'] . $url, true);		

		$data['stores'] = $this->model_extension_module_oop_categories_plus->getStores();
		
		$data['current_category_id'] = 0;
		if($path != "") {
		    $arr_ccid = explode("_", $path);
		    if(sizeof($arr_ccid)) {
			$data['current_category_id'] = (int)end($arr_ccid);
		    }
		}
		
		$data['setting_mod'] = 0;
		
		$this->response->setOutput($this->load->view('extension/module/oop_categories_plus', $data));
	}

	public function savesett() {
	        $this->load->language('extension/module/oop_categories_plus');
	        $this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('module_oop_categories_plus', $this->request->post);
		$this->response->redirect($this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'], true));
	}
	
	public function savelist() {
		$path = $this->request->get['path'];
		$current_category_id = 0;
		if($path != "") {
		    $arr_ccid = explode("_", $path);
		    if(sizeof($arr_ccid)) {
			$current_category_id = (int)end($arr_ccid);
		    }
		}

		if(isset($this->request->post['oopdata'])) {
		      $this->load->model('extension/module/oop_categories_plus');	
		      $this->model_extension_module_oop_categories_plus->savelist($this->request->post['oopdata'], $current_category_id);
		}
		
		$url = '';
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}		
		if (isset($this->request->get['path'])) {
			$url .= '&path=' . $this->request->get['path'];
		}	
		if (isset($this->request->get['ooptab'])) {
			$url .= '&ooptab=' . $this->request->get['ooptab'];
		}

		$this->response->redirect($this->url->link('extension/module/oop_categories_plus/list', 'user_token=' . $this->session->data['user_token'] . $url, true));
	}

	protected function validateFormCategory() {
		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['category_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}

			if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}

		if (isset($this->request->get['category_id']) && $this->request->post['parent_id']) {
			$results = $this->model_catalog_category->getCategoryPath($this->request->post['parent_id']);

			foreach ($results as $result) {
				if ($result['path_id'] == $this->request->get['category_id']) {
					$this->error['parent'] = $this->language->get('error_parent');

					break;
				}
			}
		}

		if ($this->request->post['category_seo_url']) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['category_id']) || ($seo_url['query'] != 'category_id=' . $this->request->get['category_id']))) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');

								break;
							}
						}
					}
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}	
	
	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['product_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}

			if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}

		if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
			$this->error['model'] = $this->language->get('error_model');
		}

		if ($this->request->post['product_seo_url']) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['product_id']) || (($seo_url['query'] != 'product_id=' . $this->request->get['product_id'])))) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');

								break;
							}
						}
					}
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/oop_categories_plus')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}	
	
	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateCopy() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			$this->load->model('catalog/product');
			$this->load->model('catalog/option');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = (int)$this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				$option_data = array();

				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

				foreach ($product_options as $product_option) {
					$option_info = $this->model_catalog_option->getOption($product_option['option_id']);

					if ($option_info) {
						$product_option_value_data = array();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$option_value_info = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);

							if ($option_value_info) {
								$product_option_value_data[] = array(
									'product_option_value_id' => $product_option_value['product_option_value_id'],
									'option_value_id'         => $product_option_value['option_value_id'],
									'name'                    => $option_value_info['name'],
									'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
									'price_prefix'            => $product_option_value['price_prefix']
								);
							}
						}

						$option_data[] = array(
							'product_option_id'    => $product_option['product_option_id'],
							'product_option_value' => $product_option_value_data,
							'option_id'            => $product_option['option_id'],
							'name'                 => $option_info['name'],
							'type'                 => $option_info['type'],
							'value'                => $product_option['value'],
							'required'             => $product_option['required']
						);
					}
				}

				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'model'      => $result['model'],
					'option'     => $option_data,
					'price'      => $result['price']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	protected function validateDeleteCategory() {
		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}	
	
	public function drop2cat() {
	      $current_category_id = 0;
	      $category_id = 0;
	      $product_id = 0;
	      
	      if(isset($this->request->post['current_category_id'])) {
		    $current_category_id = (int)$this->request->post['current_category_id'];
	      }	      
	      if(isset($this->request->post['category_id'])) {
		    $category_id = (int)$this->request->post['category_id'];
	      }
	      if(isset($this->request->post['product_id'])) {
		    $product_id = (int)$this->request->post['product_id'];	      
	      }	      
	      if($category_id && $product_id) {
		    $this->load->model('extension/module/oop_categories_plus');
		    $this->model_extension_module_oop_categories_plus->drop2cat($current_category_id, $category_id, $product_id);	      
	      }
	      
	      $this->response->setOutput(json_encode(array('resp'=>'ok')));
	}
	
	public function install() {
	      $this->load->model('setting/event');
	      
	      $this->model_setting_event->deleteEventByCode('oop_cp_pr_view');
	      $this->model_setting_event->addEvent('oop_cp_pr_view', 'admin/view/catalog/product_form/before', 'extension/module/oop_categories_plus/correct_product_before');
	      
	      $this->model_setting_event->deleteEventByCode('oop_cp_cat_view');
	      $this->model_setting_event->addEvent('oop_cp_cat_view', 'admin/view/catalog/category_form/before', 'extension/module/oop_categories_plus/correct_category_before');	      
	      
	      $this->model_setting_event->deleteEventByCode('oop_cp_cl_view');
	      $this->model_setting_event->addEvent('oop_cp_cl_view', 'admin/view/common/column_left/before', 'extension/module/oop_categories_plus/correct_col_left_before');	        
	
	      $this->model_setting_event->deleteEventByCode('oop_cp_cat_add');
	      $this->model_setting_event->addEvent('oop_cp_cat_add', 'admin/controller/catalog/category/add/before', 'extension/module/oop_categories_plus/correct_category_add_before');		
	}

	public function uninstall() {
	      $this->load->model('setting/event');
	      
	      $this->model_setting_event->deleteEventByCode('oop_cp_pr_view');
	      $this->model_setting_event->deleteEventByCode('oop_cp_cat_view');	      
	      $this->model_setting_event->deleteEventByCode('oop_cp_cl_view');	    
	      $this->model_setting_event->deleteEventByCode('oop_cp_cat_add');	
	}	
}
