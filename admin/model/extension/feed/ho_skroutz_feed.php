<?php
class ModelExtensionFeedHoSkroutzFeed extends Model {
	public function install() {
		$check = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "product LIKE 'is_skroutz';");
		if ($check->num_rows == 0) {
			$this->db->query("ALTER TABLE " . DB_PREFIX . "product ADD is_skroutz TINYINT NOT NULL DEFAULT '1' AFTER status;");
		}
	}
}