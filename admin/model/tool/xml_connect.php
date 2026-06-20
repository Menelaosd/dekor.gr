<?php
class ModelToolXmlConnect extends Model {

	public function getMessages($data = array()){
		$sql = "SELECT * FROM " . DB_PREFIX . "xml_connect_msg ORDER BY date_created DESC";

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

	public function getTotalMessages() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "xml_connect_msg");

		return $query->row['total'];
	}


}