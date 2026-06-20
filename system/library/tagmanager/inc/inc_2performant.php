<?php
/* v:9.6 06042022*/
if (isset($_data['affiliate_gateway'])) {
$desc_text = '';
foreach ($_data['affiliate_gateway'] as $result){
	$desc_text = $result['name'];
}
$tmanalytics .= "<iframe height='1' width='1' scrolling='no' marginheight='0' marginwidth='0' frameborder='0' src='https://event.2performant.com/events/salecheck?amount="
				. $_data['datalayer']['permonat_value'] . "&campaign_unique=" . $tagmanager['performant_code'] . "&confirm=" . $tagmanager['performant_confirm'] . "&description=" . $desc_text . 
				"&transaction_id=" . $_data['order_id'] . "'></iframe>";
}