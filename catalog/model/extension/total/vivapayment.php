<?php
class ModelOpencartgreeceTotalVivapayment extends Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
			$sum = $total * (1+($this->config->get('vivapayment_fee_amount')/100));
            $value = $sum - $total;
            if ($value > 0) {
				$total_data[] = array(
					'code'       => 'vivapayment',
					'title'      => 'Viva Payment Κόστος ('.$this->config->get('vivapayment_fee_amount').'%)', 
					'terms'		=> '',
					'text'       => $this->currency->format($value),
					'value'      => $value,
					'sort_order' => $this->config->get('tax_sort_order')
				);

				$total += $value;
			}
	}
}
?>