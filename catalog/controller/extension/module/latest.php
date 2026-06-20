<?php
class ControllerExtensionModuleLatest extends Controller {
	public function index($setting) {
		//static $module = 0;
				$data['lang'] = $this->language->get('code');
		
		$data['lang_id'] = (int)$this->config->get('config_language_id'); 
		$this->load->language('extension/module/latest');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$data['products'] = array();

		$filter_data = array(
			'sort'  => 'p.date_added',
			'order' => 'DESC',
			'start' => 0,
			'limit' => $setting['limit']
		);

		if ($setting['name']) {
			$data['heading_title'] = $setting['name'];
		}
			
		$results = $this->model_catalog_product->getProducts($filter_data);

		if ($results) {
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$price_net = $result['price'];
				} else {
					$price = false;
					$price_net = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$special_net = $result['special'];
				} else {
					$special = false;
					$special_net = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}
				$data['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($result['product_id']) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						

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
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix'],
							'pid'					  => $option_value['p_id'],
							'pid_link'				  => $pid_link,
							'pid_image'				  => $pid_image,
						);
					}
				}
				

				$data['options'][] = array(
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
				
				if(!empty($data['options'])){
				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'option'      => $product_option_value_data,
					'type_option' => $option['type'],
					'model'		  => $result['model'],
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'percentage'  => $special ? number_format((($special_net/$price_net-1)*100 ) ,0) : false,
					'tax'         => $tax,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			
		}else{
				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'model'		  => $result['model'],
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'percentage'  => $special ? number_format((($special_net/$price_net-1)*100 ) ,0) : false,
					'tax'         => $tax,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);

		}
			}

		$data['products_featured'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if (!empty($setting['product'])) {
			$products_featured = array_slice($setting['product'], 0, (int)$setting['limit']);

			foreach ($products_featured as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$product_info['special']) {
						$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $product_info['rating'];
					} else {
						$rating = false;
					}
					$data['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($product_id) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						

						if ($option['type'] == 'link') {
							$p_image = $this->model_catalog_product->getProductMainImage($option_value['p_id']);
							$pid_image = $this->model_tool_image->resize($p_image['image'], 100, 120);
							$pid_link = $this->url->link('product/product', '&product_id=' . $option_value['p_id']);
						} else {
							$pid_image = false;
							$pid_link = false;
						}

						$product_featured_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix'],
							'pid'					  => $option_value['p_id'],
							'pid_link'				  => $pid_link,
							'pid_image'				  => $pid_image,
						);
					}
				}
				

				$data['options_featured'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_featured_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'show_label'           => $option['show_label'],
					'required'             => $option['required']
				);
			}

					$data['products_featured'][] = array(
						'product_id'  => $product_info['product_id'],
						'thumb'       => $image,
						'option'      => $product_option_value_data,
						'type_option' => $option['type'],
						'name'        => $product_info['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'model'		  => $product_info['model'],
						'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
					);
				}
			}
		}

			//var_dump($products_featured);
			$data['module'] = $module++;

			return $this->load->view('extension/module/latest', $data);
		}
	}
}
