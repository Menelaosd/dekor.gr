<?php
class ControllerExtensionModuleHoProducttabs extends Controller {
	private $error = array();

	public function index() {

		$this->load->language('extension/module/ho_producttabs');
		
		$this->document->setTitle($this->language->get('heading_title')); 
		$data['product_tab_row'] = 0;
		$this->load->model('setting/module');

		$data['heading_title'] = $this->language->get('heading_title');

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['entry_logo'] = $this->language->get('entry_logo');
		$data['entry_heading'] = $this->language->get('entry_heading');

		$data['entry_type'] = $this->language->get('entry_type');
		$data['entry_source'] = $this->language->get('entry_source');
		$data['entry_alt_title'] = $this->language->get('entry_alt_title');
		$data['entry_description'] = $this->language->get('entry_description');
		$data['entry_background_image'] = $this->language->get('entry_background_image');
		$data['entry_all_products_link'] = $this->language->get('entry_all_products_link');
		$data['entry_limit'] = $this->language->get('entry_limit');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_category'] = $this->language->get('entry_category');
		$data['entry_product_tabs'] = $this->language->get('entry_product_tabs');
		
		$data['entry_category_bestseller'] = $this->language->get('entry_category_bestseller');
		$data['entry_category_featured'] = $this->language->get('entry_category_featured');


		$data['text_edit'] = $this->language->get('text_edit');

		$data['text_type1'] = $this->language->get('text_type1');
		$data['text_type2'] = $this->language->get('text_type2');
		$data['text_type3'] = $this->language->get('text_type3');
		$data['text_type4'] = $this->language->get('text_type4');
		$data['text_type5'] = $this->language->get('text_type5');
		$data['text_type6'] = $this->language->get('text_type6');
		
		$data['text_type6'] = $this->language->get('text_type6');
		
		$data['entry_class'] = $this->language->get('entry_class');
		$data['entry_over_title'] = $this->language->get('entry_over_title');

		$data['text_layout_left'] = $this->language->get('text_layout_left');
		$data['text_layout_right'] = $this->language->get('text_layout_right');

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
				'href' => $this->url->link('extension/module/ho_producttabs', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/ho_producttabs', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/ho_producttabs', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/ho_producttabs', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
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
		
		if (isset($this->request->post['class'])) {
			$data['class'] = $this->request->post['class'];
		} elseif (!empty($module_info)) {
			$data['class'] = $module_info['class'];
		} else {
			$data['class'] = '';
		}

		if (isset($this->request->post['tabs_layout'])) {
			$data['tabs_layout'] = $this->request->post['tabs_layout'];
		} elseif (!empty($module_info)) {
			$data['tabs_layout'] = $module_info['tabs_layout'];
		} else {
			$data['tabs_layout'] = '';
		}

		if (isset($this->request->post['logo'])) {
			$data['logo'] = $this->request->post['logo'];
		} elseif (!empty($module_info)) {
			$data['logo'] = $module_info['logo'];
		} else {
			$data['logo'] = '';
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
	
		if (isset($this->request->post['logo']) && is_file(DIR_IMAGE . $this->request->post['logo'])) {
			$data['logo_thumb'] = $this->model_tool_image->resize($this->request->post['logo'], 100, 100);
		} elseif (!empty($module_info) && is_file(DIR_IMAGE . $module_info['logo'])) {
			$data['logo_thumb'] = $this->model_tool_image->resize($module_info['logo'], 100, 100);
		} else {
			$data['logo_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		if (isset($this->request->post['main_description'])) {
			$data['main_description'] = $this->request->post['main_description'];
		} elseif (!empty($module_info)) {
			$data['main_description'] = $module_info['main_description'];
		} else {
			$data['main_description'] = array();
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		$this->load->model('catalog/category');
		$this->load->model('catalog/product');

		$data['product_tabs'] = array();


		if (isset($this->request->post['product_tabs'])) {
			foreach ($this->request->post['product_tabs'] as $product_tab) {
				$tab_products = array();
				$tab_category = array();
				$tab_parent_id = '';
				$tab_price_limit = 0;
				$tab_path = '';
				$background_image_thumb = '';
				if (isset($product_tab['background_image']) && is_file(DIR_IMAGE . $product_tab['background_image'])) {
					$background_image_thumb = $this->model_tool_image->resize($product_tab['background_image'], 100, 100);
				} else {
					$background_image_thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
				};
				if(isset($product_tab['product_tab_description'])) {
					$product_tab_description  = $product_tab['product_tab_description'];
				} else {
					$product_tab_description = array();
				};
				
				if(isset($product_tab['category_bestseller'])) {
					$category_bestseller  = $product_tab['category_bestseller'];
				} else {
					$category_bestseller = false;
				};
				
				if(isset($product_tab['category_featured'])) {
					$category_featured  = $product_tab['category_featured'];
				} else {
					$category_featured = false;
				};

				if(isset($product_tab['product'])) {
					foreach ($product_tab['product'] as $product_id) {
						$product_info = $this->model_catalog_product->getProduct($product_id);
						if ($product_info) {
							$tab_products[] = array(
								'product_id' => $product_info['product_id'],
								'name'       => $product_info['name']
							);
						}
					}
				};
				if(isset($product_tab['path'])) {
					$tab_path = $product_tab['path'];
				};
				if(isset($product_tab['parent_id'])) {
					$tab_parent_id = $product_tab['parent_id'];
				};
				if(isset($product_tab['products_price_limit'])) {
					$tab_price_limit = $product_tab['products_price_limit'];
				};
				$data['product_tabs'][] = array(
					'type' => $product_tab['type'],
					'activetabs' => $product_tab['activetabs'],
					'thumb' => $background_image_thumb,
					'background_image' => $product_tab['background_image'],
					'product' => $tab_products,
					'path' => $tab_path,
					'tab_parent_id' => $tab_parent_id,
					'product_tab_description' => $product_tab_description,
					'all_products_link' => $product_tab['all_products_link'],
					'limit' => $product_tab['limit'],
					'tab_price_limit' => $tab_price_limit,
					'category_bestseller' => $category_bestseller,
					'category_featured' => $category_featured,
					'sort_order' => $product_tab['sort_order']
				);
			};
			$this->request->post['product_tabs'] = $data['product_tabs'];
		} elseif (!empty($module_info)) {
			$data['product_tabs'] = $module_info['product_tabs'];
		} else {
			$data['product_tabs'] = array();
		};

		$data['user_token'] = $this->session->data['user_token'];

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('ho_producttabs', $this->request->post);
				$redirect = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
				$this->session->data['success'] = $this->language->get('text_success_edit');		
			} else {
				if (isset($this->request->post['action'])) {
					if ($this->request->post['action'] == 'save_stay') {
						$redirect = $this->url->link('extension/module/ho_producttabs', '&user_token='.$this->session->data['user_token'].'&module_id='.$this->request->get['module_id'], true);
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

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ho_producttabs', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ho_producttabs')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}