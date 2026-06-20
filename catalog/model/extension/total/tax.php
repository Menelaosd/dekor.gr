<?php
class ModelExtensionTotalTax extends Model {
	public function getTotal($total) {
		// Reduced VAT mapping
		$reduced_vat_map = array(
			'821' => 1,
			'831' => 1,
			'832' => 1,
			'854' => 1,
			'853' => 1,
			'811' => 1
		);

		// Get the postcode	
		
		$attach = '(24%)';
		
		// echo $this->session->data['payment_address']['postcode'];
		//echo '<BR>POST3'.print_r($this->request->post).'<BR>';
		
		if (!empty($this->session->data['payment_address']['postcode'])) {
			$postcode = trim($this->session->data['payment_address']['postcode']);
			$postcode = str_replace(' ', '', $postcode); // Remove all spaces
			$postcode_prefix = substr($postcode, 0, 3); // Get first 3 characters
		} else {
			$postcode_prefix = ''; // Default in case of missing postcode
		}	
		//echo $postcode_prefix;
		
		//echo $this->session->data['payment_address']['postcode'];
		
		//echo 'test'.$this->customer->getGroupId();

		
		if (isset($reduced_vat_map[$postcode_prefix]) && ($this->session->data['customer_group_id'] == 3 || $this->customer->getGroupId() == 3)) {
			$attach = '(17%)';
		}	

		if (isset($this->session->data['payment_address']['country_id']) && $this->session->data['payment_address']['country_id'] != 84 && $this->session->data['customer_group_id'] == 3) {
			$attach = '(0%)';
		}
		
		
		// echo '<pre>';
		// print_r($this->session->data);
		// echo '</pre>';
		
		//echo $attach;

		foreach ($total['taxes'] as $key => $value) {
			$total['totals'][] = array(
				'code'       => 'tax',
				'title'      => $this->tax->getRateName($key).' '.$attach,
				'value'      => $value,
				'sort_order' => $this->config->get('total_tax_sort_order')
			);

			$total['total'] += $value;
		}
	}
}
