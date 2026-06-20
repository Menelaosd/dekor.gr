<?php
class ModelExtensionModuleHoNewsletter extends Model {
	public function getSubscribers(){
		
		$query = $this->db->query("SELECT a.*, c.firstname as firstname, c.lastname as lastname FROM ".DB_PREFIX."ho_newsletter_subscribe as a LEFT JOIN ".DB_PREFIX."customer AS c ON a.customer_id = c.customer_id ");
			
		return $query->rows;
	}

	public function getTotalSubscribers(){
		
		$query = $this->db->query("SELECT COUNT(*) AS total FROM ".DB_PREFIX."ho_newsletter_subscribe");

		return $query->row['total'];
	}
}