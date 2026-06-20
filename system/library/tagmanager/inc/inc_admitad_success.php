<?php
/* v:9.6 06042022*/
$tmanalytics .= "<script type=\"text/javascript\" nitro-exclude=\"\">
				ADMITAD = window.ADMITAD || {};	ADMITAD.Invoice = ADMITAD.Invoice || {};if (!getSourceCookie(cookie_name)) {ADMITAD.Invoice.broker = 'na';	} else if (getSourceCookie(cookie_name) != deduplication_cookie_value) {ADMITAD.Invoice.broker = getSourceCookie(cookie_name);
				} else {ADMITAD.Invoice.broker = '" . $tagmanager['admitad_invoice_broker'] . "';}ADMITAD.Invoice.category = '" . $tagmanager['admitad_invoice_category'] . "';var orderedItem = [];  ";
foreach ($_data['admitad_items'] as $item) { 
$tmanalytics .= "orderedItem.push({	Product: {productID: '" . $item['product_id'] ."',category: '" . $item['category'] ."',  price: '" . $item['price'] . "',priceCurrency: '" . $item['currency'] . "',},orderQuantity: '" . $item['quantity'] . "',additionalType: '" . $item['type'] . "' });";
}	
$tmanalytics .= "ADMITAD.Invoice.referencesOrder = ADMITAD.Invoice.referencesOrder || [];ADMITAD.Invoice.referencesOrder.push({	orderNumber: '" . $_data['datalayer']['order_id'] ."',  orderedItem: orderedItem });</script>" . "\n";
?>