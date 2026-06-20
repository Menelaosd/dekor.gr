<?php
class ControllerCatalogHoRecipes extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/ho_recipes');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/ho_recipes');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/ho_recipes');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/ho_recipes');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_ho_recipes->addRecipe($this->request->post);

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

			$this->response->redirect($this->url->link('catalog/ho_recipes', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/ho_recipes');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/ho_recipes');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_ho_recipes->editRecipe($this->request->get['recipe_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('catalog/ho_recipes', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/ho_recipes');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/ho_recipes');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $recipe_id) {
				$this->model_catalog_ho_recipes->deleteRecipe($recipe_id);
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

			$this->response->redirect($this->url->link('catalog/ho_recipes', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'id.title';
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
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/ho_recipes', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/ho_recipes/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/ho_recipes/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['recipes'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$recipes_total = $this->model_catalog_ho_recipes->getTotalRecipes();

		$results = $this->model_catalog_ho_recipes->getRecipes($filter_data);

		foreach ($results as $result) {
			$data['recipes'][] = array(
				'recipe_id' => $result['recipe_id'],
				'title'          => $result['title'],
				'sort_order'     => $result['sort_order'],
				'edit'           => $this->url->link('catalog/ho_recipes/edit', 'user_token=' . $this->session->data['user_token'] . '&recipe_id=' . $result['recipe_id'] . $url, true)
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

		$data['sort_title'] = $this->url->link('catalog/ho_recipes', 'user_token=' . $this->session->data['user_token'] . '&sort=id.title' . $url, true);
		$data['sort_sort_order'] = $this->url->link('catalog/ho_recipes', 'user_token=' . $this->session->data['user_token'] . '&sort=i.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $recipes_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/ho_recipes', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($recipes_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($recipes_total - $this->config->get('config_limit_admin'))) ? $recipes_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $recipes_total, ceil($recipes_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/ho_recipes_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['recipe_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = array();
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = array();
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
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
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/ho_recipes', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['recipe_id'])) {
			$data['action'] = $this->url->link('catalog/ho_recipes/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/ho_recipes/edit', 'user_token=' . $this->session->data['user_token'] . '&recipe_id=' . $this->request->get['recipe_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/ho_recipes', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['recipe_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$recipe_info = $this->model_catalog_ho_recipes->getRecipe($this->request->get['recipe_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['recipe_description'])) {
			$data['recipe_description'] = $this->request->post['recipe_description'];
		} elseif (isset($this->request->get['recipe_id'])) {
			$data['recipe_description'] = $this->model_catalog_ho_recipes->getRecipeDescriptions($this->request->get['recipe_id']);
		} else { 
			$data['recipe_description'] = array();
		}
		

		$this->load->model('tool/image');
		// Image
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($recipe_info)) {
			$data['image'] = $recipe_info['image'];
		} else {
			$data['image'] = '';
		}
		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($recipe_info) && is_file(DIR_IMAGE . $recipe_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($recipe_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		// Author Image
		if (isset($this->request->post['author_image'])) {
			$data['author_image'] = $this->request->post['author_image'];
		} elseif (!empty($recipe_info)) {
			$data['author_image'] = $recipe_info['author_image'];
		} else {
			$data['author_image'] = '';
		}
	
		if (isset($this->request->post['author_image']) && is_file(DIR_IMAGE . $this->request->post['author_image'])) {
			$data['author_thumb'] = $this->model_tool_image->resize($this->request->post['author_image'], 100, 100);
		} elseif (!empty($recipe_info) && is_file(DIR_IMAGE . $recipe_info['author_image'])) {
			$data['author_thumb'] = $this->model_tool_image->resize($recipe_info['author_image'], 100, 100);
		} else {
			$data['author_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['recipe_related'])) {
			$recipes = $this->request->post['recipe_related'];
		} elseif (isset($this->request->get['recipe_id'])) {
			$recipes = $this->model_catalog_ho_recipes->getRecipRelatedRecipes($this->request->get['recipe_id']);
		} else {
			$recipes = array();
		}

		$data['recipe_relateds'] = array();

		foreach ($recipes as $recipe_id) {
			$related_info = $this->model_catalog_ho_recipes->getRecipe($recipe_id);
			if ($related_info) {
				$data['recipe_relateds'][] = array(
					'recipe_id' => $related_info['recipe_id'],
					'title'       => $related_info['title']
				);
			}
		}

		$this->load->model('catalog/product');

		if (isset($this->request->post['product_related'])) {
			$products = $this->request->post['product_related'];
		} elseif (isset($this->request->get['recipe_id'])) {
			$products = $this->model_catalog_ho_recipes->getRecipRelatedProducts($this->request->get['recipe_id']);
		} else {
			$products = array();
		}

		$data['product_relateds'] = array();

		foreach ($products as $product_id) {
			$related_info = $this->model_catalog_product->getProduct($product_id);

			if ($related_info) {
				$data['product_relateds'][] = array(
					'product_id' => $related_info['product_id'],
					'name'       => $related_info['name']
				);
			}
		}

		$this->load->model('setting/store');

		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}
		
		if (isset($this->request->post['recipe_store'])) {
			$data['recipe_store'] = $this->request->post['recipe_store'];
		} elseif (isset($this->request->get['recipe_id'])) {
			$data['recipe_store'] = $this->model_catalog_ho_recipes->getRecipeStores($this->request->get['recipe_id']);
		} else {
			$data['recipe_store'] = array(0);
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($recipe_info)) {
			$data['status'] = $recipe_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($recipe_info)) {
			$data['sort_order'] = $recipe_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
		if (isset($this->request->post['recipe_seo_url'])) {
			$data['recipe_seo_url'] = $this->request->post['recipe_seo_url'];
		} elseif (isset($this->request->get['recipe_id'])) {
			$data['recipe_seo_url'] = $this->model_catalog_ho_recipes->getRecipeSeoUrls($this->request->get['recipe_id']);
		} else {
			$data['recipe_seo_url'] = array();
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/ho_recipes_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/ho_recipes')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['recipe_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 1) || (utf8_strlen($value['title']) > 64)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}

			if (utf8_strlen($value['description']) < 3) {
				$this->error['description'][$language_id] = $this->language->get('error_description');
			}

			if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}

		if ($this->request->post['recipe_seo_url']) {
			$this->load->model('design/seo_url');
			
			foreach ($this->request->post['recipe_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}						
						
						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
						
						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['recipe_id']) || ($seo_url['query'] != 'recipe_id=' . $this->request->get['recipe_id']))) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
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

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/ho_recipes')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/ho_recipes');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter_name'  => $filter_name,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_catalog_ho_recipes->getRecipes($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'recipe_id' => $result['recipe_id'],
					'title'       => strip_tags(html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}