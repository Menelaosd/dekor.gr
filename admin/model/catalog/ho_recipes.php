<?php
class ModelCatalogHoRecipes extends Model {
	public function addRecipe($data) {
		$this->db->query("
		INSERT INTO " . DB_PREFIX . "recipe 
		SET 
		sort_order = '" . (int)$data['sort_order'] . "', 
		image = '" . $this->db->escape($data['image']) . "', 
		author_image = '" . $this->db->escape($data['author_image']) . "', 
		status = '" . (int)$data['status'] . "
		
		'");
		$recipe_id = $this->db->getLastId();

		foreach ($data['recipe_description'] as $language_id => $value) {
			$this->db->query("
			INSERT INTO " . DB_PREFIX . "recipe_description 
			SET recipe_id = '" . (int)$recipe_id . "', 
			language_id = '" . (int)$language_id . "', 
			title = '" . $this->db->escape($value['title']) . "',
			intro = '" . $this->db->escape($value['intro']) . "',
			description = '" . $this->db->escape($value['description']) . "', 
			servings = '" . $this->db->escape($value['servings']) . "', 
			preptime = '" . $this->db->escape($value['preptime']) . "', 
			diff = '" . $this->db->escape($value['diff']) . "', 
			tip = '" . $this->db->escape($value['tip']) . "', 
			author_name = '" . $this->db->escape($value['author_name']) . "', 
			author_title = '" . $this->db->escape($value['author_title']) . "', 
			author_bio = '" . $this->db->escape($value['author_bio']) . "', 
			meta_title = '" . $this->db->escape($value['meta_title']) . "', 
			meta_description = '" . $this->db->escape($value['meta_description']) . "', 
			meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "recipe_to_store WHERE recipe_id = '" . (int)$recipe_id . "'");

		if (isset($data['recipe_store'])) {
			foreach ($data['recipe_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "recipe_to_store SET recipe_id = '" . (int)$recipe_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['recipe_related'])) {
			foreach ($data['recipe_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "recipe_related_recipe WHERE recipe_id = '" . (int)$recipe_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "recipe_related_recipe SET recipe_id = '" . (int)$recipe_id . "', related_id = '" . (int)$related_id . "'");
			}
		}

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "recipe_related_product WHERE recipe_id = '" . (int)$recipe_id . "' AND product_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "recipe_related_product SET recipe_id = '" . (int)$recipe_id . "', product_id = '" . (int)$related_id . "'");
			}
		}

		// SEO URL
		if (isset($data['recipe_seo_url'])) {
			foreach ($data['recipe_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET language_id = '" . (int)$language_id . "', query = 'recipe_id=" . (int)$recipe_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		$this->cache->delete('recipes');
		return $recipe_id;
	}

	public function editRecipe($recipe_id, $data) {
		$this->db->query("
			UPDATE " . DB_PREFIX . "recipe 
			SET 
			image = '" . $this->db->escape($data['image']) . "', 
			author_image = '" . $this->db->escape($data['author_image']) . "', 
			sort_order = '" . (int)$data['sort_order'] . "', 
			status = '" . (int)$data['status'] . "' 
			WHERE recipe_id = '" . (int)$recipe_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "recipe_description WHERE recipe_id = '" . (int)$recipe_id . "'");

		foreach ($data['recipe_description'] as $language_id => $value) {
			$this->db->query("
			INSERT INTO " . DB_PREFIX . "recipe_description 
			SET recipe_id = '" . (int)$recipe_id . "', 
			language_id = '" . (int)$language_id . "', 
			title = '" . $this->db->escape($value['title']) . "', 
			intro = '" . $this->db->escape($value['intro']) . "',
			description = '" . $this->db->escape($value['description']) . "', 
			servings = '" . $this->db->escape($value['servings']) . "', 
			preptime = '" . $this->db->escape($value['preptime']) . "', 
			diff = '" . $this->db->escape($value['diff']) . "', 
			tip = '" . $this->db->escape($value['tip']) . "',
			author_name = '" . $this->db->escape($value['author_name']) . "', 
			author_title = '" . $this->db->escape($value['author_title']) . "', 
			author_bio = '" . $this->db->escape($value['author_bio']) . "', 
			meta_title = '" . $this->db->escape($value['meta_title']) . "', 
			meta_description = '" . $this->db->escape($value['meta_description']) . "', 
			meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "recipe_to_store WHERE recipe_id = '" . (int)$recipe_id . "'");

		if (isset($data['recipe_store'])) {
			foreach ($data['recipe_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "recipe_to_store SET recipe_id = '" . (int)$recipe_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['recipe_related'])) {
			foreach ($data['recipe_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "recipe_related_recipe WHERE recipe_id = '" . (int)$recipe_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "recipe_related_recipe SET recipe_id = '" . (int)$recipe_id . "', related_id = '" . (int)$related_id . "'");
			}
		}

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "recipe_related_product WHERE recipe_id = '" . (int)$recipe_id . "' AND product_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "recipe_related_product SET recipe_id = '" . (int)$recipe_id . "', product_id = '" . (int)$related_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'recipe_id=" . (int)$recipe_id . "'");

		if (isset($data['recipe_seo_url'])) {
			foreach ($data['recipe_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (trim($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET language_id = '" . (int)$language_id . "', query = 'recipe_id=" . (int)$recipe_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		$this->cache->delete('recipe');
	}

	public function deleteRecipe($recipe_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "recipe` WHERE recipe_id = '" . (int)$recipe_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "recipe_description` WHERE recipe_id = '" . (int)$recipe_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "recipe_to_store` WHERE recipe_id = '" . (int)$recipe_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'recipe_id=" . (int)$recipe_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "recipe_related_product` WHERE recipe_id = '" . (int)$recipe_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "recipe_related_recipe` WHERE recipe_id = '" . (int)$recipe_id . "'");

		$this->cache->delete('recipe');
	}

	public function getRecipe($recipe_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recipe r LEFT JOIN " . DB_PREFIX . "recipe_description rd ON (r.recipe_id = rd.recipe_id) WHERE rd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		return $query->row;
	}

	public function getRecipes($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "recipe r LEFT JOIN " . DB_PREFIX . "recipe_description rd ON (r.recipe_id = rd.recipe_id) WHERE rd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			if (!empty($data['filter_name'])) {
				$sql .= " AND rd.title LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}

			$sort_data = array(
				'rd.title',
				'r.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY rd.title";
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
		} else {
			$recipe_data = $this->cache->get('recipe.' . (int)$this->config->get('config_language_id'));

			if (!$recipe_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recipe r LEFT JOIN " . DB_PREFIX . "recipe_description rd ON (r.recipe_id = rd.recipe_id) WHERE rd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY rd.title");
				$recipe_data = $query->rows;
				$this->cache->set('recipe.' . (int)$this->config->get('config_language_id'), $recipe_data);
			}

			return $recipe_data;
		}
	}

	public function getRecipeDescriptions($recipe_id) {
		$recipe_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recipe_description WHERE recipe_id = '" . (int)$recipe_id . "'");

		foreach ($query->rows as $result) {
			$recipe_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'intro'            => $result['intro'],
				'servings'            => $result['servings'],
				'preptime'            => $result['preptime'],
				'diff'            => $result['diff'],
				'tip'            => $result['tip'],
				'author_name'            => $result['author_name'],
				'author_title'            => $result['author_title'],
				'author_bio'            => $result['author_bio'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			);
		}

		return $recipe_description_data;
	}
	
	public function getRecipeStores($recipe_id) {
		$recipe_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recipe_to_store WHERE recipe_id = '" . (int)$recipe_id . "'");

		foreach ($query->rows as $result) {
			$recipe_store_data[] = $result['store_id'];
		}

		return $recipe_store_data;
	}

	public function getRecipRelatedProducts($recipe_id) {
		$recipe_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recipe_related_product WHERE recipe_id = '" . (int)$recipe_id . "'");

		foreach ($query->rows as $result) {
			$recipe_related_data[] = $result['product_id'];
		}

		return $recipe_related_data;
	}

	public function getRecipRelatedRecipes($recipe_id) {
		$recipe_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recipe_related_recipe WHERE recipe_id = '" . (int)$recipe_id . "'");

		foreach ($query->rows as $result) {
			$recipe_related_data[] = $result['related_id'];
		}

		return $recipe_related_data;
	}

	public function getRecipeSeoUrls($recipe_id) {
		$recipe_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'recipe_id=" . (int)$recipe_id . "'");

		foreach ($query->rows as $result) {
			$recipe_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}
		return $recipe_seo_url_data;
	}

	public function getTotalRecipes() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "recipe");
		return $query->row['total'];
	}
}