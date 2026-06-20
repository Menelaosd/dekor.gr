<?php /* v:9.6 06042022*/ if (!isset($tagmanager)) { $tagmanager = $this->gtm->settings; } if (isset($tagmanager['code']) && $tagmanager['status']=='1') { $GLOBALS['tm'] = array();

if(substr(VERSION,0,1)=='1' ) { $_data = (isset($this->data) ? $this->data : null); } else { $_data = (isset($data) ? $data : null); }

if(isset($this->request->get['product_id']) && $this->request->get['product_id']) {

$url_link = ''; $image_link = '';

if (isset($tagmanager['admitad_retag_status']) && $tagmanager['admitad_retag_status']) { if (!isset($product_info['image'])) { $img = $this->gtm->getProductImages($this->request->get['product_id']); foreach ($img as $im) { $image_link = '//image/' . $im['image']; } } else { $image_link = '//image/' . $product_info['image']; } $url_link = $this->url->link('product/product', '&product_id=' . (isset($_data['product_id']) ? $_data['product_id'] : '')); }

if (isset($_data['pid'])){

$tmproduct = array( 'id'		=>		(isset($_data['pid']) ? $_data['pid'] : ''), 'name'		=>		(isset($_data['title']) ? $_data['title'] : ''), 'price'		=>		$_data['pprice'], 'brand'		=>		(isset($_data['brand']) ? $_data['brand'] : ''), 'category'	=>		(isset($_data['category_name']) ? $_data['category_name'] : ''), 'sku'		=> 		(isset($_data['sku']) ? $_data['sku'] : ''), 'gtin'		=> 		(isset($_data['gtin']) ? $_data['gtin'] : ''), 'variant'	=>		'', 'currency'	=>		$tagmanager['currency'] );

$ga4_products[] = array( 'item_id'			=> (isset($_data['pid']) ? $_data['pid'] : ''), 'item_name'			=> (isset($_data['title']) ? $_data['title'] : ''), 'item_brand'		=> (isset($_data['brand']) ? $_data['brand'] : ''), 'item_list_name'	=> (isset($_data['item_list_name']) ? $_data['item_list_name'] : ''), 'item_list_id'		=> (isset($_data['item_list_id'])   ? $_data['item_list_id'] : ''), 'item_category'		=> (isset($_data['item_category'])  ? $_data['item_category'] : ''), 'item_category2'	=> (isset($_data['item_category2']) ? $_data['item_category2'] : ''), 'item_category3'	=> (isset($_data['item_category3']) ? $_data['item_category3'] : ''), 'item_category4'	=> (isset($_data['item_category4']) ? $_data['item_category4'] : ''), 'item_category5'	=> (isset($_data['item_category5']) ? $_data['item_category5'] : ''), 'item_variant'		=> '', 'affiliation'		=> '', 'discount'			=> (isset($discount) ? number_format((float)$discount, 2, '.', '') : '0'), 'coupon'			=> '', 'price'				=> $_data['pprice'], 'curency'			=> $tagmanager['currency'], 'item_image'		=> $image_link, 'item_url'			=> $url_link, );

$remarketing_ids = array( 'id'		=>		(isset($_data['pid']) ? $_data['pid'] : ''), 'google_business_vertical'	=> 'retail' );

$GLOBALS['tm'] = array ( 'type'				=>	'product', 'ecproduct'			=>	$tmproduct, 'ecproducts'		=>	(isset($_data['ecproducts']) ? $_data['ecproducts'] : null), 'ecom_prodid'		=>	(isset($_data['pid']) ? $_data['pid'] : ''), 'listname'			=>	'Related items', 'catname'			=>	(isset($_data['category']) ? $_data['category'] : ''), 'brandname'			=>	(isset($_data['brand']) ? $_data['brand'] : ''), 'remarketing_ids'	=>  $remarketing_ids, 'ecom_pagetype'		=>	'Product Details', 'ecom_totalvalue'	=>	$_data['pprice'], 'dynx_itemid'		=>	(isset($_data['pid']) ? $_data['pid'] : ''), 'dynx_itemid2'		=>	'', 'fprice'			=>	$_data['fprice'], 'ga4_products'		=>  $ga4_products ); }

} else { if (isset($_data['ecproducts'])) {

$GLOBALS['tm'] = array ( 'type'				=>	'listing', 'ecproducts'		=>	$_data['ecproducts'], 'ga4_products'		=>  $_data['ga4_products'], 'ecom_prodid'		=>	$_data['ecom_prodid'], 'listname'			=>	$_data['listname'], 'catname'			=>	$_data['catname'], 'brandname'			=>	$_data['brandname'], 'remarketing_ids'	=>  $_data['remarketing_ids'], 'ecom_pagetype'		=>	$_data['ecom_pagetype'], 'ecom_totalvalue'	=>	$_data['ecom_totalvalue'], 'dynx_itemid'		=>	$_data['dynx_itemid'], 'dynx_itemid2'		=>	$_data['dynx_itemid2'] );

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { if(substr(VERSION,0,1)=='1' ) { $this->data['tmdata'] = $this->gtm->prepareProducts($GLOBALS['tm']); $this->data['xhr'] = true; } else { $data['tmdata'] = $this->gtm->prepareProducts($GLOBALS['tm']); $data['xhr'] = true; } } } } } ?>