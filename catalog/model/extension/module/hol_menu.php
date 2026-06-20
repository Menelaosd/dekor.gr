<?php
class ModelExtensionModuleHolMenu extends Model {

	public function getMenu($menu_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ho_menu_item m LEFT JOIN " . DB_PREFIX . "ho_menu_item_description md ON (m.menu_item_id = md.menu_item_id) WHERE m.menu_id = '" . (int)$menu_id . "' AND md.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY m.sort_order");

		$tree = array();
		$pre_tree = array();
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('catalog/manufacturer');
		$this->load->model('extension/module/ho_filter_banners');
		$this->load->model('tool/image');
		foreach ($query->rows as $row) {
			$megamenu = array();
			if($row['banner_image']){
				$megamenuClean = json_decode($row['banner_image'],true);
				$megamenuClean = $megamenuClean[(int)$this->config->get('config_language_id')];
			} else {
				$megamenuClean = array();
			};
			if($megamenuClean) {
				foreach($megamenuClean as $megamenuBlock) {
					$megamenuModule = array();
					$megamemenuCategories = array();
					$megamemenuCategoriesChildren = array();
					$megamemenuBrands = array();
					$megamemenuFilters = array();
					$megamemenuCustomLinks = array();
					
					if($megamenuBlock['menutype'] == 'type1') {
						$megamemenuCategoriesChildren = $this->model_catalog_category->getCategories($megamenuBlock['menucategory']);
						$category_info = $this->model_catalog_category->getCategory($megamenuBlock['menucategory']);
						
						$megamenuCategoryChildren = array();
						
						if($megamemenuCategoriesChildren) {
							foreach($megamemenuCategoriesChildren as $result) {
								$megamenuCategoryChildren[] = array(
									'name' => $result['name'],
									'href' => $this->url->link('product/category', 'path=' . $category_info['category_id'].'_'.$result['category_id'])
					
								);
							};
						};
						$megamemenuCategories = array(
							'title' => $category_info['name'],
							'href' => $this->url->link('product/category', 'path=' . $category_info['category_id']),
							'children' => $megamenuCategoryChildren
						);
					};
					if($megamenuBlock['menutype'] == 'type2') {
						$filter_data = array(
							'start'              => 0,
							'limit'              => 9
						);
						$manufacturers = $this->model_catalog_manufacturer->getManufacturers($filter_data);
						foreach($manufacturers as $manufacturer) {
							if (is_file(DIR_IMAGE . $manufacturer['image'])) {
								$image = $this->model_tool_image->resize($manufacturer['image'],100,100);
							} else {
								$image = $this->model_tool_image->resize('no_image.png',100,100);
							};
							$megamemenuBrands[] = array(
								'name' => $manufacturer['name'],
								'image' => $image,
								'manufacturer_id' => $manufacturer['manufacturer_id'],
								'href' => $this->url->link('product/product', 'manufacturer_id=' . $manufacturer['manufacturer_id'])
							);
						};
					};
					if($megamenuBlock['menumodule'] && $megamenuBlock['menutype'] == 'type3') {
						if ($megamenuBlock['menumodule']) {
							$part = explode('.', $megamenuBlock['menumodule']);
							if (isset($part[0]) && $this->config->get('module_' . $part[0] . '_status')) {
								$module_data = $this->load->controller('extension/module/' . $part[0]);

								if ($module_data) {
									$megamenuModule = $module_data;
								}
							}
							if (isset($part[1])) {
								$setting_info = $this->model_setting_module->getModule($part[1]);

								if ($setting_info && $setting_info['status']) {
									$output = $this->load->controller('extension/module/' . $part[0], $setting_info);

									if ($output) {
										$megamenuModule  = $output;
									}
								}
							}
						};
					};
					if($megamenuBlock['filtergroup'] && $megamenuBlock['menutype'] == 'type4') {
						if ($megamenuBlock['filtergroup']) {
							$filters = $this->model_extension_module_ho_filter_banners->getFilters($megamenuBlock['filtergroup'],9999);
							foreach($filters as $filter) {
								if (is_file(DIR_IMAGE . $filter['image'])) {
									$filterimage = $this->model_tool_image->resizeCrop($filter['image'],100,100);
								} else {
									$filterimage = $this->model_tool_image->resizeCrop('no_image.png',100,100);
								};
								$megamemenuFilters[] = array(
									'name' => $filter['name'],
									'image' => $filterimage,
									'filter_group_id' => $filter['filter_group_id'],
									'sort_order' => $filter['sort_order'],
									'href' => $this->url->link('product/category', 'bfilter=' . 'f'.$filter['filter_group_id'].':'.$filter['filter_id'].';')
								);
							};
						};
					};
					
					if($megamenuBlock['custom_links'] && $megamenuBlock['menutype'] == 'type5') {
						if ($megamenuBlock['custom_links']) {
							foreach($megamenuBlock['custom_links'] as $custom_link) {
								$megamemenuCustomLinks[] =  array (
									'title' => $custom_link['title'],
									'href'  => $custom_link['url']
								);
							};
						};
					};
					
					$megaMenuImage = false;
					
					if($megamenuBlock['image']) {
						$megaMenuImage = $this->model_tool_image->resizeCrop($megamenuBlock['image'],200,200);
					};
					
					if($this->config->get('config_store_id') == 2) {
						if($megamenuBlock['menutype'] == 'type1') {
							if($category_info['image']) {
								//$megaMenuImage = $this->model_tool_image->resizeCrop($category_info['image'],400,200);
							}
						}
					}
					
					$megamenu[$megamenuBlock['row']][$megamenuBlock['row'].'_'.$megamenuBlock['col']][] = array(
						'type' => $megamenuBlock['menutype'],
						'title' => $megamenuBlock['hypertitle'],
						'class' => $megamenuBlock['menuclass'],
						'image' => $megaMenuImage,
						'categories' => $megamemenuCategories,
						'module' => $megamenuModule,
						'brands' => $megamemenuBrands,
						'filters' => $megamemenuFilters,
						'custom_links' => $megamemenuCustomLinks
					);
				};
			};
			
			$pre_tree[$row['menu_item_id']] = $row;
			$pre_tree[$row['menu_item_id']]['megamenu'] = $megamenu;
		}

		foreach ($pre_tree as $row) {
			if ($row['parent_id'] == 0) {
				$tree[$row['menu_item_id']] = array(
					'title' => $row['title'],
					'url' 	=> $this->getMenuItemLink($row),
					'order' => $row['sort_order'],
					'parent' => $row['parent_id'],
					'icon_class' => $row['icon_class'],
					'megamenu' => $row['megamenu']
				);
			}
		}

		foreach ($query->rows as $row) {
			$megamenu = array();
			if ($row['parent_id'] != 0) {
				if (isset($tree[$row['parent_id']])) {
					$tree[$row['parent_id']]['children'][$row['menu_item_id']] = array(
						'title' => $row['title'],
						'url' 	=> $this->getMenuItemLink($row),
						'order' => $row['sort_order'],
						'parent' => $row['parent_id'],
						'icon_class' => $row['icon_class'],
						'megamenu' => $row['megamenu']
					);
				}
			}			
		}

		foreach ($tree as $parent_key => $item) {
			if (isset($item['children'])) {
				foreach ($item['children'] as $child_key => $child_item) {
					foreach ($query->rows as $row) {
						if($row['parent_id'] == $child_key){
							$tree[$parent_key]['children'][$child_key]['children'][$row['menu_item_id']] = array(
								'title' => $row['title'],
								'url' 	=> $this->getMenuItemLink($row),
								'order' => $row['sort_order'],
								'megamenu' => $row['megamenu']
							);
						}
					}
				}
			}
		}
		
		return $tree;
	}

	protected function getMenuItemLink($row){
		$id = (int)$row['url_id'];
		switch( $row['route'] ){
			case 'category':
				$parent = $this->getParentCategory($id);
				if( $parent && isset($parent['parent_id']) && $parent['parent_id'] ){  
					$id = $parent['parent_id'].'_'.$id;
				}
				return $this->url->link('product/category', 'path=' . $id);
			case 'product':
				return $this->url->link('product/product', 'product_id=' . $id);
			case 'information':
				return $this->url->link('information/information', 'information_id=' . $id);
			case 'manufacturers' :
				return $this->url->link('product/manufacturer');
			case 'manufacturer' :
				return $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $id);
			case 'showrooms' :
				return $this->url->link('product/showroom');
			case 'showroom' :
				return $this->url->link('product/showroom/info', 'showroom_id=' . $id);
			case 'url' :
				return $row['url'];
			case 'blog' :
				return $this->url->link('blog/blog');
			case 'contact' :
				return $this->url->link('information/contact');
			case 'checkout' :
				return $this->url->link('checkout/cart');
			case 'cart' :
				return $this->url->link('checkout/checkout');
			case 'home' :
				return $this->url->link('common/home');
			default: 
				return $row['url'];
		}
	}

	public function getParentCategory($id_child){
		$result = $this->db->query("SELECT `parent_id` FROM `" . DB_PREFIX . "category` WHERE `category_id` = '".$id_child."'");
 		return $result->row;
	}
}