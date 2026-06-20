<?php
/* v:9.6 06042022*/
$tmanalytics .= '<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer nitro-exclude=""></script>
				<script nitro-exclude="">
  					window.renderOptIn = function() {window.gapi.load(\'surveyoptin\', function() {
  					window.gapi.surveyoptin.render(	{
  					"merchant_id": ' . $tagmanager['merchant_id']. ',
  					"order_id": "' . $_data['order_id'] .'",
  					"email": "' . $_data['datalayer']['order_email'] . '",
  					"delivery_country": "' . $_data['datalayer']['country_code'] .'",
  					"estimated_delivery_date": "' . $_data['datalayer']['estimated_delivery'] . '",';
  					
  		if (isset($_data['datalayer']['gtins'])) {
  			$tmanalytics .= '"products": ' . json_encode($_data['datalayer']['gtins'])  ;
  		}			
		$tmanalytics .= '			
					});	});	}</script>' . "\n";
?>