<?php
class ModelExtensionMenuHolMenu extends Model {

	public function addMenu($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "ho_menu SET menu_alias = '" . $data['menu_alias'] . "', title = '" . $this->db->escape($data['title']) . "', description = '" . $this->db->escape($data['description']) ."'");

		$menu_id = $this->db->getLastId();

		return $menu_id;
	}

	public function editMenu($menu_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "ho_menu SET menu_alias = '" . $data['menu_alias'] . "', title = '" . $this->db->escape($data['title']) . "', description = '" . $this->db->escape($data['description']) ."' WHERE menu_id = '" . (int)$menu_id . "'");

		return $menu_id;
	}

	public function deleteMenu($menu_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "ho_menu WHERE menu_id = '" . (int)$menu_id . "'");
	}

	public function getMenu($menu_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ho_menu WHERE menu_id = '" . (int)$menu_id. "'");

		return $query->row;
	}

	public function getMenus($data = array()) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ho_menu ORDER BY title ASC");

		return $query->rows;
	}

	public function getMenuItemsByMenuId($menu_id, $data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "ho_menu_item m LEFT JOIN " . DB_PREFIX . "ho_menu_item_description md ON (m.menu_item_id = md.menu_item_id) WHERE md.language_id = '" . (int)$this->config->get('config_language_id') . "' AND m.menu_id ='" . $menu_id . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND md.title LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_parent_id'])) {
			$sql .= " AND m.parent_id = '" . $this->db->escape($data['filter_parent_id']) . "'";
		}

		$sort_data = array(
			'md.title',
			'm.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY m.parent_id, md.title";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getMenuItem($menu_item_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "ho_menu_item m LEFT JOIN " . DB_PREFIX . "ho_menu_item_description md ON (m.menu_item_id = md.menu_item_id) WHERE m.menu_item_id = '" . (int)$menu_item_id . "' AND md.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getMenuItemDescriptions($menu_item_id) {
		$menu_item_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ho_menu_item_description WHERE menu_item_id = '" . (int)$menu_item_id . "'");

		foreach ($query->rows as $result) {
			$menu_item_description_data[$result['language_id']] = array(
				'title'			=> $result['title'],
				'description'	=> $result['description']
			);
		}

		return $menu_item_description_data;
	}

	public function addMenuItem($data, $menu_id) {

		if($data['route'] == 'category'){
			$data['url_id'] = $data['category_id'];
		} elseif($data['route'] == 'product'){
			$data['url_id'] = $data['product_id'];
		} elseif($data['route'] == 'manufacturer'){
			$data['url_id'] = $data['manufacturer_id'];
		} elseif($data['route'] == 'information'){
			$data['url_id'] = $data['information_id'];
		} else {
			$data['url_id'] = '';
		}

		$this->db->query("INSERT INTO " . DB_PREFIX . "ho_menu_item SET 

			menu_id =  '" . $menu_id . "', 
			target =  '" . $data['target'] . "', 
			icon_class =  '" . $data['icon_class'] . "', 
			image =  '" . $data['image'] . "', 
			parent_id =  '" . $data['parent_id'] . "', 
			route =  '" . $data['route'] . "', 
			url =  '" . $data['url'] . "', 
			url_id =  '" . $data['url_id'] . "', 
			banner_image =  '".json_encode($data['banner_image'],JSON_UNESCAPED_UNICODE)."', 
			sort_order =  '" . $data['sort_order'] . "', 
			status =  '" . $data['status'] . "', 
			date_added = NOW(), 
			date_modified = NOW()

		");

		$menu_item_id = $this->db->getLastId();

		foreach ($data['item_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "ho_menu_item_description SET menu_item_id = '" . (int)$menu_item_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}


		return $menu_item_id;
	}

	public function editMenuItem($menu_item_id, $menu_id, $data) {

		if($data['route'] == 'category'){
			$data['url_id'] = $data['category_id'];
		} elseif($data['route'] == 'product'){
			$data['url_id'] = $data['product_id'];
		} elseif($data['route'] == 'manufacturer'){
			$data['url_id'] = $data['manufacturer_id'];
		} elseif($data['route'] == 'information'){
			$data['url_id'] = $data['information_id'];
		} else {
			$data['url_id'] = '';
		}
		
		if(isset($data['banner_image'])) {
			$banner_image =  json_encode($data['banner_image'],JSON_UNESCAPED_UNICODE);
		} else {
			 $banner_image = '';
		};

		$this->db->query("UPDATE " . DB_PREFIX . "ho_menu_item SET 

			menu_id =  '" . $menu_id . "', 
			target =  '" . $data['target'] . "', 
			icon_class =  '" . $data['icon_class'] . "', 
			image =  '" . $data['image'] . "', 
			parent_id =  '" . $data['parent_id'] . "', 
			route =  '" . $data['route'] . "', 
			url =  '" . $data['url'] . "', 
			url_id =  '" . $data['url_id'] . "', 
			banner_image =  '".$banner_image."', 
			sort_order =  '" . $data['sort_order'] . "', 
			status =  '" . $data['status'] . "', 
			date_modified = NOW() WHERE menu_item_id = '" . (int)$menu_item_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "ho_menu_item_description WHERE menu_item_id = '" . (int)$menu_item_id . "'");

		foreach ($data['item_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "ho_menu_item_description SET menu_item_id = '" . (int)$menu_item_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}
	}

	public function deleteMenuItem($menu_item_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "ho_menu_item WHERE menu_item_id = '" . (int)$menu_item_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "ho_menu_item_description WHERE menu_item_id = '" . (int)$menu_item_id . "'");
	}

	public function getMenuTree($menu_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "ho_menu_item m LEFT JOIN " . DB_PREFIX . "ho_menu_item_description md ON (m.menu_item_id = md.menu_item_id) WHERE md.language_id = '" . (int)$this->config->get('config_language_id') . "' AND m.menu_id ='" . $menu_id . "' AND m.parent_id = 0 ORDER BY m.sort_order";
		
		$query = $this->db->query($sql);
		$parents = $query->rows;

		foreach ($parents as $key => $child) {
			
			$childs = $this->getChildren($child['menu_item_id']);
			$parents[$key]['children'] = $childs;
			
			if ($childs) {
				foreach ($childs as $key2 => $child2) {
					$parents[$key]['children'][$key2]['children'] = $this->getChildren($child2['menu_item_id']);
				}
			}
		}

		return $parents;
	}

	public function getChildren($parent_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "ho_menu_item m LEFT JOIN " . DB_PREFIX . "ho_menu_item_description md ON (m.menu_item_id = md.menu_item_id) WHERE md.language_id = '" . (int)$this->config->get('config_language_id') . "' AND m.parent_id = '" . $parent_id . "' ORDER BY m.sort_order";

		$query = $this->db->query($sql);

		return ($query->num_rows > 0) ? $query->rows : false;
	}

	public function getTotalMenuItemsByMenuId($menu_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ho_menu_item WHERE menu_id = '" . (int)$menu_id. "'");

		return $query->row['total'];
	}


	public function createTables() {

		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "ho_menu (
				menu_id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				menu_alias varchar(32) NOT NULL,
				title varchar(128) NOT NULL,
				description varchar(255) NOT NULL DEFAULT '',
				PRIMARY KEY (menu_id),
				UNIQUE KEY alias (menu_alias)
			) COLLATE='utf8_general_ci' ENGINE=MyISAM;");

		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "ho_menu_item (
				menu_item_id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				menu_id int(11) UNSIGNED NOT NULL,
				target varchar(64) NOT NULL DEFAULT '_self',
				icon_class varchar(64) DEFAULT NULL,
				image varchar(255) DEFAULT NULL,
				parent_id int(11) NOT NULL DEFAULT '0',
				route varchar(255) DEFAULT NULL,
				url varchar(255) DEFAULT NULL,
				url_id int(11) UNSIGNED DEFAULT NULL,
				sort_order int(3) NOT NULL DEFAULT '0',
				status tinyint(1) NOT NULL,
				date_added datetime NOT NULL,
				date_modified datetime NOT NULL,
				PRIMARY KEY (menu_item_id),
				KEY parent_id (parent_id)
			) COLLATE='utf8_general_ci' ENGINE=MyISAM;");

		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "ho_menu_item_description (
				menu_item_id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				language_id int(11) UNSIGNED NOT NULL,
				title varchar(255) NOT NULL,
				description text NOT NULL,
				PRIMARY KEY (menu_item_id,language_id),
				KEY name (title)
			) COLLATE='utf8_general_ci' ENGINE=MyISAM;");

		return;
	}
}