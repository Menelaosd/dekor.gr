<?php
class ModelExtensionShippingBoxnow extends Model {
		
	public function install() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "boxnow_requests` (
			  `id` int(11) NOT NULL,
			  `order_id` int(11) NOT NULL,
			  `request_id` int(11) NOT NULL,
			  `parcels` text NOT NULL,
			  `locker_id` int(11) NOT NULL,
			  `locker_address` varchar(255) NOT NULL DEFAULT '',
			  `locker_name` varchar(255) NOT NULL DEFAULT '',
			  `status_message` text DEFAULT NULL,
			  `status` int(11) NOT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
		");

		$this->db->query("
			ALTER TABLE `" . DB_PREFIX . "boxnow_requests`
			  ADD PRIMARY KEY (`id`),
			  ADD KEY `order_id` (`order_id`),
			  ADD KEY `request_id` (`request_id`),
			  ADD KEY `status` (`status`),
			  ADD KEY `locker_id` (`locker_id`)
		");
		
		$this->db->query("
			ALTER TABLE `" . DB_PREFIX . "boxnow_requests` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT
		");
	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "boxnow_requests`;");
	}	
	
	public function getBoxNowOrders($data = array()) {
		
		$sql = "SELECT o.order_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status, o.shipping_code, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified FROM `" . DB_PREFIX . "order` o";

		$sql .= " WHERE o.order_status_id > '0'";
		
		$sql .= " AND o.shipping_code = 'boxnow.boxnow'";

		$sql .= " ORDER BY o.order_id DESC";

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
	
	public function getBoxNowTotalOrders($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order`";

		$sql .= " WHERE order_status_id > '0'";
		
		$sql .= " AND shipping_code = 'boxnow.boxnow'";

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	
	public function getBoxNowStatus( $order_id ) {
		$sql = "SELECT * FROM " . DB_PREFIX . "boxnow_requests";

		$sql .= " WHERE order_id = '".(int)$order_id."' ";

		$query = $this->db->query($sql);

		return $query->row;
	}
	
	function updateRequest($order = array(), $request =  array()) {
		if($order) {
			$this->db->query("UPDATE " . DB_PREFIX . "boxnow_requests SET request_id = '" . (int)$request['id'] . "', parcels = '" . $this->db->escape(json_encode($request['parcels'])) . "', status='".(int)$request['status_id'] ."', status_message='".$this->db->escape($request['status_message']) ."' WHERE order_id = '" . (int)$order['order_id'] . "' ");
		};
	}

	// Mark a BoxNow request as cancelled (status 3) once its parcels have been cancelled via the API.
	function cancelRequest($order_id, $message = '') {
		$this->db->query("UPDATE " . DB_PREFIX . "boxnow_requests SET status = '3', parcels = '', status_message = '" . $this->db->escape($message) . "' WHERE order_id = '" . (int)$order_id . "' ");
	}
	
}
