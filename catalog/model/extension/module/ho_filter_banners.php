<?php
class ModelExtensionModuleHoFilterBanners extends Model {

	public function getFilters($filter_group,$limit = 6, $filters = array()){
		$result = $this->db->query("
			SELECT * FROM " . DB_PREFIX . "filter f
			LEFT JOIN " . DB_PREFIX . "filter_description fd
			ON (f.filter_id = fd.filter_id)
			WHERE f.filter_group_id = '".(int)$filter_group."'
			AND
			fd.language_id = ".(int)$this->config->get('config_language_id')."
			ORDER BY f.sort_order ASC
			LIMIT 0,".$limit."
			");
 		return $result->rows;
	}
	public function getFiltersPerGroupIdMenu($ids=array()){
		$result = $this->db->query("
			SELECT 	
			* ,
			fd.name as name,
			fgd.name AS filter_group_name
			FROM " . DB_PREFIX . "filter f
			LEFT JOIN " . DB_PREFIX . "filter_description fd
			ON (f.filter_id = fd.filter_id)
			LEFT JOIN " . DB_PREFIX . "filter_group_description fgd 
			ON (f.filter_group_id = fgd.filter_group_id)  
			WHERE f.filter_group_id IN (".implode(',',$ids).")
			AND
			fd.language_id = '".(int)$this->config->get('config_language_id')."'
			AND 
			fgd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
			ORDER BY f.sort_order ASC
			");
 		return $result->rows;
	}
	public function getProductFilters($product_id){
		$result = $this->db->query("
			SELECT * FROM " . DB_PREFIX . "filter f
			LEFT JOIN " . DB_PREFIX . "filter_description fd
			ON (f.filter_id = fd.filter_id)
			LEFT JOIN " . DB_PREFIX . "product_filter pf
			ON (f.filter_id = pf.filter_id)
			WHERE
				fd.language_id = ".(int)$this->config->get('config_language_id')."
			AND
				pf.product_id = ".(int)$product_id."
			ORDER BY f.sort_order ASC
			");
 		return $result->rows;
	}
}