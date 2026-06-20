<?php
class ModelExtensionShippingHocourier extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/hocourier');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('flat_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('hocourier_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		if ($this->cart->getSubTotal() > $this->config->get('shipping_hocourier_freebove')) {
			$that_cost = 0;
			$that_text = $this->currency->format($that_cost, $this->session->data['currency']).' - '.$this->language->get('text_free_shippings');
		} else {
			
			if ($address['zone_id'] == 1280) { // DEFAULT ATTICA LOCATION 
				
				$that_cost = $this->config->get('shipping_hocourier_cost');	
				$that_text = $this->currency->format($this->tax->calculate($that_cost, $this->config->get('shipping_hocourier_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']);
			} else {

				$that_cost =  5; // $this->config->get('shipping_hocourier_cost');
				$that_text = $this->currency->format($this->tax->calculate($that_cost, $this->config->get('shipping_hocourier_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']);
			}

		}

		$method_data = array();

		if ($status) {
			$quote_data = array();

			$quote_data['hocourier'] = array(
				'code'         => 'hocourier.hocourier',
				'title'        => $this->language->get('text_description'),
				'cost'         => $that_cost,
				'tax_class_id' => $this->config->get('shipping_hocourier_tax_class_id'),
				'text'         => $that_text
			);

			$method_data = array(
				'code'       => 'hocourier',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_hocourier_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}