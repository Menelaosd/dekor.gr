<?php
class ControllerHoRecipesHoRecipes extends Controller {
	public function index() {
		$this->load->language('ho_recipes/ho_recipes');

		$this->load->model('catalog/ho_recipes');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['recipe_id'])) {
			$recipe_id = (int)$this->request->get['recipe_id'];
		} else {
			$recipe_id = 0;
		}

		$recipe_info = $this->model_catalog_ho_recipes->getRecipe($recipe_id);
		$data['text_tip'] = $this->language->get('text_tip');
		$data['text_instructions'] = $this->language->get('text_instructions');
		$data['text_req_products'] = $this->language->get('text_req_products');
		$data['text_add_all'] = $this->language->get('text_add_all');
		$data['text_quantity'] = $this->language->get('text_quantity');
		$data['text_related_recipes'] = $this->language->get('text_related_recipes');
		$data['text_share'] = $this->language->get('text_share');
		$icons = array(
			'recipe_simple' => '<i class="far fa-star"></i>',
			'recipe_easy' => '<i class="far fa-star"></i>',
			'recipe_medium' => '<i class="fas fa-star-half"></i>',
			'recipe_hard' => '<i class="fas fa-star"></i>'
		);
		if ($recipe_info) {
			$this->document->setTitle($recipe_info['meta_title']);
			$this->document->setDescription($recipe_info['meta_description']);
			$this->document->setKeywords($recipe_info['meta_keyword']);

			$data['breadcrumbs'][] = array(
				'text' => $recipe_info['title'],
				'href' => $this->url->link('ho_recipes/ho_recipes', 'recipe_id=' .  $recipe_id)
			); 
			$this->load->model('catalog/product');

			$related_products = $this->model_catalog_ho_recipes->getRecipRelatedProducts($recipe_id);
			$related_recipes = $this->model_catalog_ho_recipes->getRecipRelatedRecipes($recipe_id);

			$data['products'] = array();

			$this->load->model('tool/image');
			foreach($related_products as $related_product) {
				$result = $this->model_catalog_product->getProduct($related_product);

				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}
				$product_options  = array();
				foreach ($this->model_catalog_product->getProductOptions($result['product_id']) as $option) {
					$product_option_value_data = array();
					foreach ($option['product_option_value'] as $option_value) {
						if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
							if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
								$option_price = $this->currency->format($this->tax->calculate($option_value['price'], $result['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
							} else {
								$option_price = false;
							}

							if ($option['type'] == 'link') {
								$p_image = $this->model_catalog_product->getProductMainImage($option_value['p_id']);
								$pid_image = $this->model_tool_image->resize($p_image['image'], 100, 120);
								$pid_link = $this->url->link('product/product', '&product_id=' . $option_value['p_id']);
							} else {
								$pid_image = false;
								$pid_link = false;
							}
							$product_option_value_data[] = array(
								'product_option_value_id' => $option_value['product_option_value_id'],
								'option_value_id'         => $option_value['option_value_id'],
								'name'                    => $option_value['name'],
								'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
								'price'                   => $option_price,
								'price_prefix'            => $option_value['price_prefix'],
								'pid'					  => $option_value['p_id'],
								'pid_link'				  => $pid_link,
								'pid_image'				  => $pid_image,
							);
						}
					}
					$product_options[] = array(
						'product_option_id'    => $option['product_option_id'],
						'product_option_value' => $product_option_value_data,
						'option_id'            => $option['option_id'],
						'name'                 => $option['name'],
						'type'                 => $option['type'],
						'value'                => $option['value'],
						'show_label'           => $option['show_label'],
						'required'             => $option['required']
					);
				}
				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,				
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'meta_description' => utf8_substr(trim(strip_tags(html_entity_decode($result['meta_description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
					'product_options'	=> $product_options,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			};
			$data['recipes'] = array();

			foreach($related_recipes as $related_recipe) {
				$result = $this->model_catalog_ho_recipes->getRecipe($related_recipe);
				if ($result['image']) {
					$image = $this->model_tool_image->resizeCrop($result['image'], 340, 280);
				} else {
					$image = $this->model_tool_image->resizeCrop('placeholder.png', 340, 280);
				}
				$data['recipes'][] = array(
					'recipe_id'            => $result['recipe_id'],
					'title'            => $result['title'],
					'intro'            => $result['intro'],
					'servings'            => $result['servings'],
					'preptime'            => $result['preptime'],
					'diff'            => $icons[$result['diff']].' '.$this->language->get($result['diff']),
					'tip'            => $result['tip'],
					'image'       => $image,
					'author_name'            => $result['author_name'],
					'author_title'            => $result['author_title'],
					'author_bio'            => $result['author_bio'],
					'description'      => $result['description'],
					'meta_title'       => $result['meta_title'],
					'meta_description' => $result['meta_description'],
					'meta_keyword'     => $result['meta_keyword']
				);
			};

			if ($recipe_info['image']) {
				$data['image'] = $this->model_tool_image->resize($recipe_info['image'], 1440, 485);
			} else {
				$data['image'] = '';
			}

			if ($recipe_info['author_image']) {
				$data['author_image'] = $this->model_tool_image->resize($recipe_info['author_image'], 150, 150);
			} else {
				$data['author_image'] = '';
			}

			if ($recipe_info['servings']) {
				$data['servings'] = $recipe_info['servings'];
			} else {
				$data['servings'] = '';
			}

			if ($recipe_info['preptime']) {
				$data['preptime'] = $recipe_info['preptime'];
			} else {
				$data['preptime'] = '';
			}

			if ($recipe_info['diff']) {
				$data['diff'] = $icons[$recipe_info['diff']].' '.$this->language->get($recipe_info['diff']);
			} else {
				$data['diff'] = '';
			}

			if ($recipe_info['tip']) {
				$data['tip'] = $recipe_info['tip'];
			} else {
				$data['tip'] = '';
			}

			if ($recipe_info['author_name']) {
				$data['author_name'] = $recipe_info['author_name'];
			} else {
				$data['author_name'] = '';
			}

			if ($recipe_info['author_title']) {
				$data['author_title'] = $recipe_info['author_title'];
			} else {
				$data['author_title'] = '';
			}

			if ($recipe_info['author_bio']) {
				$data['author_bio'] = $recipe_info['author_bio'];
			} else {
				$data['author_bio'] = '';
			}

			$data['heading_title'] = $recipe_info['title'];

			$data['description'] = html_entity_decode($recipe_info['description'], ENT_QUOTES, 'UTF-8');

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('ho_recipes/ho_recipes', $data));
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('ho_recipes/ho_recipes', 'recipe_id=' . $recipe_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}