<?php
class ModelCatalogHoRecipes extends Model {
	public function getRecipe($recipe_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "recipe r LEFT JOIN " . DB_PREFIX . "recipe_description rd ON (r.recipe_id = rd.recipe_id) LEFT JOIN " . DB_PREFIX . "recipe_to_store r2s ON (r.recipe_id = r2s.recipe_id) WHERE r.recipe_id = '" . (int)$recipe_id . "' AND rd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND r2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND r.status = '1'");

		return $query->row;
	}

	public function getRecipes() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recipe r LEFT JOIN " . DB_PREFIX . "recipe_description rd ON (r.recipe_id = rd.recipe_id) LEFT JOIN " . DB_PREFIX . "recipe_to_store r2s ON (r.recipe_id = r2s.recipe_id) WHERE rd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND r2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND r.status = '1' ORDER BY r.sort_order, LCASE(rd.title) ASC");
		return $query->rows;
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
	
	public function getRandomRecipes() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recipe r LEFT JOIN " . DB_PREFIX . "recipe_description rd ON (r.recipe_id = rd.recipe_id) LEFT JOIN " . DB_PREFIX . "recipe_to_store r2s ON (r.recipe_id = r2s.recipe_id) WHERE rd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND r2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND r.status = '1' ORDER BY RAND()");

		return $query->rows ;
	}
}