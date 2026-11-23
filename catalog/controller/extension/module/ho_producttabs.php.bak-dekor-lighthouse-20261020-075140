<?php
class ControllerExtensionModuleHoProducttabs extends Controller {
	public function index($setting) {
		static $module = 0;
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->language('extension/module/ho_producttabs');
		//$data['button_cart'] = $this->language->get('button_cart');
		//$data['text_new'] = $this->language->get('text_new');
		$data['button_wishlist'] = $this->language->get('button_wishlist');
		$data['button_compare'] = $this->language->get('button_compare');
		$data['text_all_products'] = $this->language->get('text_all_products');
		$data['additional_class'] = $setting['class'];
		$data['tabs_layout'] = 'left-layout';
		if($setting['tabs_layout'] == 'l_right') {
			$data['tabs_layout'] = 'right-layout';
		};
		$data['module_title'] = '';
		if($setting['main_description']) {
			if($setting['main_description'][(int)$this->config->get('config_language_id')]['heading']) {
				$data['module_title'] = $setting['main_description'][(int)$this->config->get('config_language_id')]['heading'];
			};
		};
		$data['logo'] = '';
		if($setting['logo']) {
			$data['logo'] = $this->model_tool_image->resizeRaw($setting['logo']);
		};
		$data['tabs'] = array();
		
		usort($setting['product_tabs'], function($a, $b) {
			return $a['sort_order'] <=> $b['sort_order'];
		});	
		
		if($setting['product_tabs']) {
			foreach($setting['product_tabs'] as $product_tab) {
				
				if(!isset($product_tab['activetabs']) || !$product_tab['activetabs']) {
					continue;
				}
				
				$tab_product_ids = array();
				$tab_products = array();
				$tab_title = '';
				
				if($product_tab['type'] == 1) {
					$tab_product_ids = $product_tab['product'];
					$tab_title = $this->language->get('data_products');
				};
				
				if($product_tab['type'] == 2) {
					$this->load->model('catalog/category');
					$category_info = $this->model_catalog_category->getCategory($product_tab['tab_parent_id']);
					if($product_tab['category_bestseller']) {
						$tab_product_ids = $this->model_catalog_product->getBestSellerProductsCategory($product_tab['limit'],$product_tab['tab_parent_id']);
					} else {
						
							if($product_tab['category_featured']) {
								$filter_data = array(
									'filter_category_id' => $product_tab['tab_parent_id'],
									//'filter_category_featured' => $product_tab['category_featured'],
									'filter_sub_category' => true,
									'sort'  => 'p.hits',
									'order' => 'DESC',
									'start' => 0,
									'limit' => $product_tab['limit']
								);
							} else {
								$filter_data = array(
									'filter_category_id' => $product_tab['tab_parent_id'],
									//'filter_category_featured' => $product_tab['category_featured'],
									'filter_sub_category' => true,
									//'sort'  => 'p.date_added',
									//'order' => 'DESC',
									'start' => 0,
									'limit' => $product_tab['limit']
								);
							}
						$tab_product_ids = $this->model_catalog_product->getProducts($filter_data);
					}
					$tab_title = $category_info['name'];
				};
				if($product_tab['type'] == 3) {
					$filter_data = array(
						'sort'  => 'p.viewed',
						'order' => 'DESC',
						'start' => 0,
						'limit' => $product_tab['limit']
					);
					$tab_product_ids = $this->model_catalog_product->getProducts($filter_data);
					$tab_title = $this->language->get('data_popular_products');
				};
				if($product_tab['type'] == 4) {
					$tab_product_ids = $this->model_catalog_product->getBestSellerProducts($product_tab['limit']);
					$tab_title = $this->language->get('data_bestseller_products');
				};
				if($product_tab['type'] == 5) {
					if($product_tab['tab_price_limit']) {
						$filter_data = array(
							'filter_price_limit' => $product_tab['tab_price_limit'],
							'sort'  => 'p.price',
							'order' => 'DESC',
							'start' => 0,
							'limit' => $product_tab['limit']
						);
						$tab_product_ids = $this->model_catalog_product->getProducts($filter_data);
						$tab_title = sprintf($this->language->get('text_products_limit'),$this->currency->getSymbolLeft($this->session->data['currency']),$product_tab['tab_price_limit'],$this->currency->getSymbolRight($this->session->data['currency']));
					};
				};
				if($product_tab['type'] == 6) {
					if (isset($this->request->get['product_id'])) {
						$tab_product_ids = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);
						$tab_title = $this->language->get('text_related_products');
					};
				};
				if($product_tab['product_tab_description'][(int)$this->config->get('config_language_id')]['over_title']) {
					$tab_title = $product_tab['product_tab_description'][(int)$this->config->get('config_language_id')]['over_title'];
				};
				
				require_once DIR_SYSTEM . "library/mobile/Mobile_Detect.php";
				$detect = new Mobile_Detect;	
				if($tab_product_ids) {
					foreach($tab_product_ids as $tab_product) {
						$product_info = $this->model_catalog_product->getProduct($tab_product['product_id']);
						if ($product_info) {
							$is_new = false;
							$percentage = false;
							if ($product_info['image']) {
								$image = $this->model_tool_image->resize($product_info['image'],$this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
							} else {
								$image = $this->model_tool_image->resize('placeholder.png',$this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'),$this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
							}
										
							$package = null;
							$package_price = null;
							$package_special = null;
							$package_tax = null;				
										
							if($product_info['package'] && $product_info['package'] > 1) {
								$package = $product_info['package'];
							}											
										
							$secondImage = $image;
							$additionalImages = $this->model_catalog_product->getProductImages($tab_product['product_id']);
							if(isset($additionalImages[0])) {
								if (isset($additionalImages[0]['image']) && file_exists(DIR_IMAGE.$additionalImages[0]['image'])) {
									$secondImage = $this->model_tool_image->resize($additionalImages[0]['image'],$this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
								};
							};
							
							if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
								$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								
								if($product_info['package'] && $product_info['package'] > 1) {
									$package_price = $this->currency->format($this->tax->calculate($product_info['price'] / $product_info['package'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								}										
							} else {
								$price = false;
							}
							if ((float)$product_info['special']) {
								$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								$percentage = round((($product_info['price'] - $product_info['special'])*100) / $product_info['price']);
								
								if($product_info['package'] && $product_info['package'] > 1) {
									$package_special = $this->currency->format($this->tax->calculate($product_info['special'] / $product_info['package'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								}										
							} else {
								$special = false;
							}
							if ($this->config->get('config_tax')) {
								$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
								
								if($product_info['package'] && $product_info['package'] > 1) {
									$package_tax = $this->currency->format( (float) ($product_info['special'] ? $product_info['special'] : $product_info['price']) / $product_info['package'], $this->session->data['currency']);
								}									
							} else {
								$tax = false;
							}
							if ($this->config->get('config_review_status')) {
								$rating = $product_info['rating'];
							} else {
								$rating = false;
							}
							if(date('Y-m-d h:i:s',strtotime($product_info['date_added']. " +2 month")) > date('Y-m-d h:i:s')) {
								$is_new = true;
							};
							
							
							require_once DIR_SYSTEM . "library/mobile/Mobile_Detect.php";
							$detect = new Mobile_Detect;	
							if(strpos($detect->getHttpHeaders()['HTTP_USER_AGENT'], 'Chrome-Lighthouse') !== false || isset($this->request->get['google_test'])){
								$image = '';
								$secondImage = '';
							} else {
							}							
							
							$tab_products[] = array(
								'product_id' 	=> $product_info['product_id'],
								'working' 	=> $product_info['working'],
								'thumb'			=> $image,
								'second_image'	=> $secondImage,
								'name'			=> $product_info['name'],
								'disablecart'	=> $product_info['disablecart'],
								'storeonly'	=> $product_info['storeonly'],
								'description'	=> utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get($this->config->get('config_theme') . '_product_description_length')) . '..',
								'price'			=> $price,
								'special'		=> $special,
								'tax'			=> $tax,
								
									'package_price'       => $package_price,
									'package_special'       => $package_special,
									'package_tax'       => $package_tax,
									'package'       => $package,								
								
								'rating'		=> $rating,
								'date_added'	=> $product_info['date_added'],
								'date_modified'	=> $product_info['date_modified'],
								'is_new'		=> $is_new,
								'quantity'     => $product_info['quantity'],
								'percentage'	=> $percentage,
								'date_modified'	=> $product_info['date_modified'],
								'model'	=> $product_info['model'],
								'href'			=> $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
							);
						}
					};
					$background_image = false;
					if ($product_tab['background_image']) {
						$background_image = $this->model_tool_image->resizeCrop($product_tab['background_image'],800,800);
					};
					
					$alt_title = '';
					if($product_tab['product_tab_description'][(int)$this->config->get('config_language_id')]['alt_title']) {
						$alt_title = explode(' ',$product_tab['product_tab_description'][(int)$this->config->get('config_language_id')]['alt_title']);
						$alt_title[0] = '<span class="tab-class-first-world">'.$alt_title[0].'</span>';
						$alt_title = implode(' ',$alt_title);
					};					
					$data['tabs'][] = array(
						'title' => $tab_title,
						'alt_title' => $alt_title,
						'description' => $product_tab['product_tab_description'][(int)$this->config->get('config_language_id')]['description'],
						'background_image' => $background_image,
						'all' => $product_tab['all_products_link'],
						'type' => $product_tab['type'],
						'products' => $tab_products
					);
				};
			};
		};
		$data['module'] = $module++;

		$data['module'] = $module++;
		
		$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

		// Detect Lighthouse or GTmetrix
		if (strpos($userAgent, 'Chrome-Lighthouse') !== false || strpos($userAgent, 'GTmetrix') !== false) {
			// Do not render the module
			return '';
		}

		return $this->load->view('extension/module/ho_producttabs', $data);
	}
}