<?php
class ControllerExtensionModuleHolMenu extends Controller {
	public function index($menu_id) {
		$this->load->model('extension/module/hol_menu');

		$data['nav'] = $this->model_extension_module_hol_menu->getMenu($menu_id);
		
		$data['store_id'] = $this->config->get('config_store_id');
		
		// Load the category model
		$this->load->model('catalog/category');
		$this->load->model('catalog/product'); // if needed for link generation
		$this->load->model('tool/image');      // optional, for thumbnails

		$data['store_id'] = $this->config->get('config_store_id');

		$data['current_route'] = $this->request->get['route'] ?? '';
		$data['current_path'] = $this->request->get['path'] ?? '';

		if ($data['store_id'] == 2 || true == true) {

				$query = $this->db->query("
					SELECT c.category_id, cd.name, c.parent_id, c.sort_order, c.status
					FROM " . DB_PREFIX . "category c
					LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
					LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id)
					WHERE c.status = 1 AND c2s.store_id = '".$data['store_id']."' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
					ORDER BY c.parent_id, c.sort_order, cd.name
				");

				$categories = $query->rows;

				// Index categories by ID
				$categoryMap = [];
				foreach ($categories as $category) {
					$categoryMap[$category['category_id']] = [
						'category_id' => $category['category_id'],
						'name'        => $category['name'],
						'parent_id'   => $category['parent_id'],
						'sort_order'  => $category['sort_order'],
						'href'        => $this->url->link('product/category', 'path=' . ( $category['parent_id'] != 0 ? $category['parent_id'].'_' : '' ) . $category['category_id']),
						'children'    => []
					];
				}

				// Build the tree
				$tree = [];
				foreach ($categoryMap as $id => &$node) {
					if ($node['parent_id'] && isset($categoryMap[$node['parent_id']])) {
						$categoryMap[$node['parent_id']]['children'][] = &$node;
					} else {
						$tree[] = &$node;
					}
				}
				unset($node); // Break reference


			// Replace top-level nav
			$data['nav'] = $tree;
		}
		
		if ($data['store_id'] == 2) {
			return $this->load->view('extension/module/hol_menu_nav_simple', $data);
		} else {
			return $this->load->view('extension/module/hol_menu_nav_simple', $data);
		}
	}
}