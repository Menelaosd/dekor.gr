<?php
/******************************************************
 * @package Google Tag Manager for OC1.5x,OC2,OC3x
 * @version 5.1
 * @author https://aits.xyz
 * @copyright Copyright (C)2020 aits.xyz All rights reserved.
 * @email:info@aits.xyz 
 * $date: 31 January 2020
*******************************************************/

class ModelExtensionModuleTagmanager extends Model {

public function getTagmanger() {

$tagmanager = array(); $PREFIX = '';

$cid = (isset($_COOKIE['_ga']) ? $_COOKIE['_ga'] : ''); $cid = preg_replace('/GA[0-9]+\.[0-9]+\./', '', $cid);

if(substr(VERSION,0,1)=='3' ) { $PREFIX = 'analytics_'; }

$ver = VERSION . '- 5.1';

/* Manual Fixed Config */ $order_total_plus = array('cod_fee', 'handling', 'klarna_fee', 'low_order_fee');  $order_total_minus = array('credit' , 'reward', 'voucher'); $manual_tax = 1.24; $maunal_tax_status = false;

if (isset($this->session->data['tagmanager'])) { if (isset($this->session->data['currency']) && $this->session->data['currency'] == $this->session->data['tagmanager']['currency'] && $cid == $this->session->data['tagmanager']['cid'] ) { $tagmanager = $this->session->data['tagmanager']; return $tagmanager; } unset($this->session->data['tagmanager']); }

$tagmanager = array ( 'code' 					=> $this->config->get($PREFIX . 'tagmanager_code'), 'ampcode' 				=> $this->config->get($PREFIX . 'tagmanager_ampcode'), 'gid' 					=> $this->config->get($PREFIX . 'tagmanager_gid'), 'mp' 					=> $this->config->get($PREFIX . 'tagmanager_mp'), 'status'				=> $this->config->get($PREFIX . 'tagmanager_status'), 'ampstatus'				=> $this->config->get($PREFIX . 'tagmanager_ampstatus'), 'admin'					=> $this->config->get($PREFIX . 'tagmanager_admin'), 'eu_cookie'				=> $this->config->get($PREFIX . 'tagmanager_eu_cookie'), 'eu_cookie_enforce'		=> $this->config->get($PREFIX . 'tagmanager_eu_cookie_enforce'), 'cookie_position'		=> $this->config->get($PREFIX . 'tagmanager_cookie_position'), 'cookie_title'			=> $this->config->get($PREFIX . 'tagmanager_cookie_title'), 'cookie_text'			=> $this->config->get($PREFIX . 'tagmanager_cookie_text'), 'cookie_text2'			=> $this->config->get($PREFIX . 'tagmanager_cookie_text2'), 'cookie_link'			=> $this->config->get($PREFIX . 'tagmanager_cookie_link'), 'cookie_button1'		=> $this->config->get($PREFIX . 'tagmanager_cookie_button1'), 'cookie_button2'		=> $this->config->get($PREFIX . 'tagmanager_cookie_button2'), 'cookie_button3'		=> $this->config->get($PREFIX . 'tagmanager_cookie_button3'), 'cookie_bg_popup'		=> $this->config->get($PREFIX . 'tagmanager_cookie_bg_popup'), 'cookie_text_popup'		=> $this->config->get($PREFIX . 'tagmanager_cookie_text_popup'), 'cookie_bg_button'		=> $this->config->get($PREFIX . 'tagmanager_cookie_bg_button'), 'cookie_text_button'	=> $this->config->get($PREFIX . 'tagmanager_cookie_text_button'), 'cookie_heading_color'	=> $this->config->get($PREFIX . 'tagmanager_cookie_heading_color'), 'cookie_badge'			=> $this->config->get($PREFIX . 'tagmanager_cookie_badge'), 'cookie_badge_position'	=> $this->config->get($PREFIX . 'tagmanager_cookie_badge_position'), 'cookie_badge_color'	=> $this->config->get($PREFIX . 'tagmanager_cookie_badge_color'), 'adword'				=> $this->config->get($PREFIX . 'tagmanager_adword'), 'userid_status'			=> $this->config->get($PREFIX . 'tagmanager_userid_status'), 'userid'				=> (isset($this->session->data['userid']) ? $this->session->data['userid'] : ''), 'conversion_id'			=> $this->config->get($PREFIX . 'tagmanager_conversion_id'), 'conversion_label'		=> $this->config->get($PREFIX . 'tagmanager_conversion_label'), 'remarketing'			=> $this->config->get($PREFIX . 'tagmanager_remarketing'), 'custom'				=> $this->config->get($PREFIX . 'tagmanager_custom'), 'dynx_itemid'			=> $this->config->get($PREFIX . 'tagmanager_dynx_itemid'), 'dynx_itemid2'			=> $this->config->get($PREFIX . 'tagmanager_dynx_itemid2'), 'dynx_pagetype'			=> $this->config->get($PREFIX . 'tagmanager_dynx_pagetype'), 'dynx_totalvalue'		=> $this->config->get($PREFIX . 'tagmanager_dynx_totalvalue'), 'ecomm_pagetype'		=> $this->config->get($PREFIX . 'tagmanager_ecomm_pagetype'), 'ecomm_prodid'			=> $this->config->get($PREFIX . 'tagmanager_ecomm_prodid'), 'ecomm_totalvalue'		=> $this->config->get($PREFIX . 'tagmanager_ecomm_totalvalue'), 'goptimize'				=> $this->config->get($PREFIX . 'tagmanager_google_optimize'), 'goptimize_status'		=> $this->config->get($PREFIX . 'tagmanager_google_optimize_status'), 'pixel'					=> $this->config->get($PREFIX . 'tagmanager_pixel'), 'pixelcode'				=> $this->config->get($PREFIX . 'tagmanager_pixelcode'), 'fb_catalog_id'			=> $this->config->get($PREFIX . 'tagmanager_fb_catalog_id'), 'pmap'					=> $this->config->get($PREFIX . 'tagmanager_product'), 'ptitle'				=> $this->config->get($PREFIX . 'tagmanager_ptitle'), 'hotjar_status'			=> $this->config->get($PREFIX . 'tagmanager_hotjar_status'), 'hotjar_siteid'			=> $this->config->get($PREFIX . 'tagmanager_hotjar_siteid'), 'skroutz_status'		=> $this->config->get($PREFIX . 'tagmanager_skroutz_status'), 'skroutz_siteid'		=> $this->config->get($PREFIX . 'tagmanager_skroutz_siteid'), 'yandex_status'			=> $this->config->get($PREFIX . 'tagmanager_yandex_status'), 'yandex_code'			=> $this->config->get($PREFIX . 'tagmanager_yandex_code'), 'zenchat_status'		=> $this->config->get($PREFIX . 'tagmanager_zenchat_status'), 'zenchat_code'			=> $this->config->get($PREFIX . 'tagmanager_zenchat_code'), 'bing_status'			=> $this->config->get($PREFIX . 'tagmanager_bing_status'), 'bing_uetid'			=> $this->config->get($PREFIX . 'tagmanager_bing_uetid'), 'cache'					=> $this->config->get($PREFIX . 'tagmanager_cache'), 'route_checkout'		=> $this->config->get($PREFIX . 'tagmanager_route_checkout'), 'route_confirm'			=> $this->config->get($PREFIX . 'tagmanager_route_confirm'), 'route_success'			=> $this->config->get($PREFIX . 'tagmanager_route_success'), 'ver'					=> $ver, 'cid'					=> $cid, 'language'				=> (isset($_COOKIE['language']) ? $_COOKIE['language'] : ''), 'vs'					=> base64_encode($this->config->get($PREFIX . 'tagmanager_code').$this->config->get($PREFIX . 'tagmanager_gid').$cid), 'host'					=> $_SERVER['SERVER_NAME'], 'currency'				=> (isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency')), 'alt_currency'			=> $this->config->get($PREFIX . 'tagmanager_alt_currency'), 'alt_currency_status'	=> $this->config->get($PREFIX . 'tagmanager_alt_currency_status'), 'total_plus'			=> $order_total_plus, 'total_minus'			=> $order_total_minus, 'tax'					=> $manual_tax, 'override_tax'			=> $maunal_tax_status, 'affiliate_gateway'		=> 0, 'limit'					=> 25, 'max_list_items'		=> 50, 'max_module_items'		=> 50, ); if (empty($tagmanager['alt_currency']) || $tagmanager['alt_currency_status'] != '1' ) { $tagmanager['alt_currency'] = (isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency')); }

if (!empty($tagmanager['route_checkout'])) {	$route_checkout = explode(PHP_EOL, $tagmanager['route_checkout']); $tagmanager['route_checkout'] = array_filter($route_checkout, 'strlen'); } else { $tagmanager['route_checkout'] = array('extension/quickcheckout/checkout'); }

if (!empty($tagmanager['route_confirm'])) {	$route_confirm = explode(PHP_EOL, $tagmanager['route_confirm']); $tagmanager['route_confirm'] = array_filter($route_confirm, 'strlen'); } else { $tagmanager['route_confirm'] = array('extension/quickcheckout/confirm'); }

if (!empty($tagmanager['route_success'])) {	$route_success = explode(PHP_EOL, $tagmanager['route_success']); $tagmanager['route_success'] = array_filter($route_success, 'strlen'); } else { $tagmanager['route_success'] = array('extension/ordersuccess','extension/checkout/eghlresponse/success'); } $this->session->data['tagmanager'] = $tagmanager;

return $tagmanager;

}

public function tagmangerPmap($model='',$sku='',$product_id='') { $tagmanager = $this->getTagmanger(); $pmap = $tagmanager['pmap']; $curr = $this->config->get('config_currency'); $supported_currencies = array('GBP', 'USD', 'EUR', 'AUD', 'BRL', 'CZK', 'JPY', 'CHF', 'CAD', 'DKK', 'INR', 'MXN', 'NOK', 'PLN', 'RUB', 'SEK', 'TRY');

if (!in_array($curr, $supported_currencies)) { $curr = 'GBP'; }

if($curr == 'GBP'){ $currency = 'gb'; }elseif($curr == 'USD'){ $currency = 'us'; }elseif($curr == 'AUD'){ $currency = 'au'; }elseif($curr == 'CAD'){ $currency = 'ca'; }elseif($curr == 'CHF'){ $currency = 'ch'; }elseif($curr == 'MXN'){ $currency = 'mx'; }elseif($curr == 'INR'){ $currency = 'in'; }

if ($pmap == 'product_id') { $pid = $product_id;      } elseif ($pmap == 'model') { $pid = $model; } elseif ($pmap == 'sku') { $pid = $sku; } elseif ($pmap == 'model_product_id') { $pid = $model . '_' . $product_id; } elseif ($pmap == 'product_id_currency') { $pid = $product_id . '_' . $currency; } elseif ($pmap == 'product_id_language') { $pid = $product_id . '_' . $this->config->get('config_language');      } else { $pid = $product_id; } return (string)$pid; }

public function tagmangerPtitle($name='', $brand='',$model='',$product_id='') { $tagmanager = $this->getTagmanger(); $ptitle = $tagmanager['ptitle'];

if ($ptitle == 'name') { $ptitle = $name;      } elseif ($ptitle == 'brand_model') { $ptitle = $brand . ' ' . $model; } else { $ptitle = $name;     }

$ptitle = $this->cleanStr($ptitle); $ptitle = utf8_substr(trim(strip_tags(html_entity_decode($ptitle, ENT_QUOTES, 'UTF-8'))), 0, 50);

return htmlspecialchars($ptitle, ENT_QUOTES); }

public function getProductCatName($product_id) { $tagmanager = $this->getTagmanger(); if (isset($product_id) && !empty($product_id)) { $cat_data = false; if (isset($this->session->data['tagmanager']['cache']) && $this->session->data['tagmanager']=='1'){ $cat_data = $this->cache->get('tagmanager.cat.'.$product_id); } if (!$cat_data) { $return_data = array(); $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' ORDER BY category_id DESC LIMIT 1 ");

if($query->num_rows == 1){ $return_data = $this->getparent($query->row['category_id']); $return_data = array_reverse($return_data); } $cat = ''; $i=1; foreach ($return_data as $result) { if ($i>1) { $cat .= ' > '; } $cat .= $result['name'] ; $i++; } $cat_data = $this->cleanStr($cat); if (isset($tagmanager['cache']) && $tagmanager['cache']=='1'){ $this->cache->set('tagmanager.cat.'.$product_id, $cat_data); } } return $cat_data; } }

public function getProductCatID($product_id) { $tagmanager = $this->getTagmanger(); if (isset($product_id) && !empty($product_id)) { $cat_data = false; if (isset($tagmanager['cache']) && $tagmanager['cache']=='1'){ $cat_data = $this->cache->get('tagmanager.cat.'.$product_id); } if (!$cat_data) { $return_data = array(); $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' ORDER BY category_id DESC LIMIT 1 ");

if($query->num_rows == 1){ $cat_data = $query->row['category_id']; } else { $cat_data = 0; } } return $cat_data; } }

public function getparent($cid) { $data = array(); $temp  = $this->db->query("SELECT c.category_id, cd1.name AS name, c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (c.category_id = cd1.category_id)  WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c.category_id = '".(int)$cid."'");

if($temp->num_rows == 1) { $data[] = $temp->row;

if($temp->row['parent_id'] != 0) { $data = array_merge($data,  $this->getparent($temp->row['parent_id'])); } } else { return $data; } return $data; }

public function getProductCatNameEXT($product_id) { $cat_data = false; if (isset($tagmanager['cache']) && $tagmanager['cache']=='1'){ $cat_data = $this->cache->get('tagmanager.cat.'.$product_id); } if (!$cat_data) { $query = $this->db->query("SELECT (SELECT DISTINCT GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' > ') FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE c1.category_id = pc.category_id) AS category FROM " . DB_PREFIX . "category_description cd INNER JOIN " . DB_PREFIX . "product_to_category pc ON pc.category_id = cd.category_id INNER JOIN " . DB_PREFIX . "product p ON pc.product_id = p.product_id  WHERE p.product_id = '".$product_id."' GROUP BY p.product_id");

$cat_data = (isset($query->row['category']) && $query->row['category']) ? $query->row['category'] : '';

if (isset($this->session->data['tagmanager']['cache']) && $this->session->data['tagmanager']=='1'){ $this->cache->set('tagmanager.cat.'.$product_id, $cat_data); } } return $cat_data; }

public function getProductBrandName($product_id) { $brand_data = false; if (isset($product_id) && !empty($product_id)) { if (isset($this->session->data['tagmanager']['cache']) && $this->session->data['tagmanager']=='1'){ $brand_data = $this->cache->get('tagmanager.brand.'.$product_id); } if (!$brand_data) { $query = $this->db->query("SELECT m.name from " . DB_PREFIX . "manufacturer m left join " . DB_PREFIX . "product p on m.manufacturer_id = p.manufacturer_id  WHERE p.product_id = ".$product_id); if (isset($query->row['name'])) { $brand = $query->row['name']; } else { $brand = ''; } $brand_data = $this->cleanStr($brand); if (isset($this->session->data['tagmanager']['cache']) && $this->session->data['tagmanager']=='1'){ $this->cache->set('tagmanager.brand.'.$product_id, $brand_data); } } return $brand_data; } }

public function escapeJsonString($value) { $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c"); $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b"); $result = str_replace($escapers, $replacements, $value); return $result; }

public function cleanStr($data) { $data = str_replace('"', "", $data); $data = str_replace("'", "", $data); $data = str_replace("&#039;", "", $data); $data = str_replace("quot;", "", $data); $data = str_replace("&amp;", "", $data); $data = str_replace("&", "", $data); return $data; }

/* outputs */

public function prepareSteps($pageurl,$pagename) {

$data = array();

if (!isset($this->session->data['steps'])) { $this->session->data['steps'] = 1; }

$actionField = array ( 'step'	 => $this->session->data['steps'], 'option' => $pagename );

$ecommerce = array ( 'checkout' => $actionField );

if (!isset($this->session->data['reload_check'])) { $this->session->data['reload_check'] = array(); } else { foreach ($this->session->data['reload_check'] as $check) { if ($check['pageurl'] == $pageurl) { $error = true; } } }

if (!isset($error)) {

$this->session->data['steps'] ++; $data['gadata_ec'] = array ( 'event'			=> 'checkoutOption', 'eventAction'	=> 'checkout', 'eventLabel'	=> $pagename, 'ecommerce'		=> $ecommerce ); $data['gadata_goals'] = array ( 'event'			=> 'goalUrl', 'eventAction'	=> 'checkout', 'eventLabel'	=> $pagename, 'goalPageUrl'   => '/checkout/'. $pageurl, 'goalPageTitle' => $pagename );

$this->session->data['reload_check'][] = array( 'pageurl' => $pageurl, 'step'	  => $this->session->data['steps'] ); }

return $data;

}

public function prepareAddtoCart($product_id, $product_info, $quantity, $option, $product_options) {

$tagmanager = $this->getTagmanger(); $op_text = ''; if (isset($option) && isset($product_options)) { $op = array(); $keys = array_keys($option); $arraySize = count($option); for($i=0;$i<$arraySize;$i++){ if (is_array($option[$keys[$i]])) { foreach ($option[$keys[$i]] as $opv) { $op[] =  array( 'option_id' => $keys[$i], 'option_values' => $opv ); } } else {

$op[] =  array( 'option_id' => $keys[$i], 'option_values' => $option[$keys[$i]] ); } }

foreach ($product_options as $product_option) { foreach ($op as $po) { if ($product_option['product_option_id'] == $po['option_id']) { if(substr(VERSION,0,1)=='1' ) { $tmp_opv = $product_option['option_value']; } else { $tmp_opv = $product_option['product_option_value']; } foreach ($tmp_opv as $value) { if ($po['option_values'] == $value['product_option_value_id']) { $op_text .= $value['name'] .", "; } } } }

} }

$pprice = 0; $fprice = 0;

$unit_price = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')); $pprice = $unit_price * $quantity; $pprice = $this->currency->format($pprice, $this->session->data['currency'],'',false); $fprice = $this->currency->format($pprice, $tagmanager['alt_currency'],'',false); if (!isset($product_info['sku'])) { $product_info['sku'] = $product_info['model']; } $pid = $this->tagmangerPmap($product_info['model'],$product_info['sku'],$product_info['product_id']); $brand = $this->getProductBrandName($product_info['product_id']); $cat = $this->getProductCatName($product_info['product_id']); $title = $this->tagmangerPtitle($product_info['name'], $brand, $product_info['model'],$product_info['product_id']);

if ($tagmanager['alt_currency_status']) { $ftotal = $fprice; $fcurrency = $tagmanager['alt_currency']; } else { $ftotal =  $pprice; $fcurrency = $tagmanager['currency']; }

$ecproduct = array( 'name'		=> $title, 'id'		=> $pid, 'price'		=> number_format((float)$pprice, 2, '.', ''), 'brand'		=> $brand, 'category'	=> $cat, 'quantity'	=> $quantity, 'variant'	=> $op_text, 'currency'	=> $tagmanager['currency'], 'fprice'	=> number_format((float)$ftotal, 2, '.', ''), 'fcurrency' => $fcurrency );

$ecdata = array( 'tmerror'		=> 'false', 'action'		=> 'addToCart', 'data'			=> $ecproduct );

return $ecdata; }

public function prepareRemoveCart($product_id, $product_info, $quantity) {

$tagmanager = $this->getTagmanger();

$pprice = 0; $fprice = 0;

$unit_price = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')); $pprice = $unit_price * $quantity; $pprice = $this->currency->format($pprice, $this->session->data['currency'],'',false); $fprice = $this->currency->format($pprice, $tagmanager['alt_currency'],'',false); if (!isset($product_info['sku'])) { $product_info['sku'] = $product_info['model']; } $pid = $this->tagmangerPmap($product_info['model'],$product_info['sku'],$product_info['product_id']); $brand = $this->getProductBrandName($product_info['product_id']); $cat = $this->getProductCatName($product_info['product_id']); $title = $this->tagmangerPtitle($product_info['name'], $brand, $product_info['model'],$product_info['product_id']);

if ($tagmanager['alt_currency_status']) { $ftotal = $fprice; $fcurrency = $tagmanager['alt_currency']; } else { $ftotal =  $pprice; $fcurrency = $tagmanager['currency']; }

$ecproduct = array( 'name'		=> $title, 'id'		=> $pid, 'price'		=> number_format((float)$pprice, 2, '.', ''), 'brand'		=> $brand, 'category'	=> $cat, 'quantity'	=> $quantity, 'currency'	=> $tagmanager['currency'], 'fprice'	=> number_format((float)$ftotal, 2, '.', ''), 'fcurrency' => $fcurrency );

$ecdata = array( 'tmerror'		=> 'false', 'action'		=> 'RemoveCart', 'data'			=> $ecproduct );

return $ecdata; }

public function prepareAddtoWishlist($product_id, $product_info) {

$tagmanager = $this->getTagmanger();

$pprice = 0; $fprice = 0;

$pprice = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')); $pprice = $this->currency->format($pprice, $this->session->data['currency'],'',false); $fprice = $this->currency->format($pprice, $tagmanager['alt_currency'],'',false); if (!isset($product_info['sku'])) { $product_info['sku'] = $product_info['model']; } $pid = $this->tagmangerPmap($product_info['model'],$product_info['sku'],$product_info['product_id']); $brand = $this->getProductBrandName($product_info['product_id']); $cat = $this->getProductCatName($product_info['product_id']); $title = $this->tagmangerPtitle($product_info['name'], $brand, $product_info['model'],$product_info['product_id']);

if ($tagmanager['alt_currency_status']) { $ftotal = $fprice; $fcurrency = $tagmanager['alt_currency']; } else { $ftotal =  $pprice; $fcurrency = $tagmanager['currency']; }

$ecproduct = array( 'name'		=> $title, 'id'		=> $pid, 'price'		=> number_format((float)$pprice, 2, '.', ''), 'brand'		=> $brand, 'quantity'  => 1, 'category'	=> $cat, 'currency'	=> $tagmanager['currency'], 'fprice'	=> number_format((float)$ftotal, 2, '.', ''), 'fcurrency' => $fcurrency );

$ecdata = array( 'tmerror'		=> 'false', 'action'		=> 'addToWishlist', 'data'			=> $ecproduct );

return $ecdata; }

public function prepareAddtoCompare($product_id, $product_info) {

$tagmanager = $this->getTagmanger();

$pprice = 0; $fprice = 0;

$pprice = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')); $pprice = $this->currency->format($pprice, $this->session->data['currency'],'',false); $fprice = $this->currency->format($pprice, $tagmanager['alt_currency'],'',false); if (!isset($product_info['sku'])) { $product_info['sku'] = $product_info['model']; } $pid = $this->tagmangerPmap($product_info['model'],$product_info['sku'],$product_info['product_id']); $brand = $this->getProductBrandName($product_info['product_id']); $cat = $this->getProductCatName($product_info['product_id']); $title = $this->tagmangerPtitle($product_info['name'], $brand, $product_info['model'],$product_info['product_id']);

if ($tagmanager['alt_currency_status']) { $ftotal = $fprice; $fcurrency = $tagmanager['alt_currency']; } else { $ftotal =  $pprice; $fcurrency = $tagmanager['currency']; }

$ecproduct = array( 'name'		=> $title, 'id'		=> $pid, 'price'		=> number_format((float)$pprice, 2, '.', ''), 'brand'		=> $brand, 'quantity'  => 1, 'category'	=> $cat, 'currency'	=> $tagmanager['currency'], 'fprice'	=> number_format((float)$ftotal, 2, '.', ''), 'fcurrency' => $fcurrency );

$ecdata = array( 'tmerror'		=> 'false', 'action'		=> 'addToCompare', 'data'			=> $ecproduct );

return $ecdata; }

public function prepareProduct($data) {

$tagmanager = $this->getTagmanger(); $ecproduct = array(); $ecproduct[] = $data['ecproduct']; $ecproducts = $data['ecproducts']; $fprice = $data['fprice']; $listname = (!empty($data['listname']) ? $data['listname'] : 'Category'); $catname = (!empty($data['catname']) ? $data['catname'] : ''); $brandname = (!empty($data['brandname']) ? $data['brandname'] : ''); $ecom_prodid = $data['ecom_prodid']; $remarketing_ids[] = $data['remarketing_ids']; $ecom_pagetype = $data['ecom_pagetype']; $ecom_totalvalue = $data['ecom_totalvalue']; $dynx_itemid = $data['dynx_itemid']; $dynx_itemid2 = $data['dynx_itemid2']; $dynx_pagetype = $data['listname']; $dynx_totalvalue = $data['ecom_totalvalue']; $limit = $tagmanager['limit']; $max_list_items = $tagmanager['max_list_items']; $max_module_items = $tagmanager['max_module_items'];



$actionField = array( 'Product-View'			=> $data['ecproduct']['name']

);

$detail = array ( 'actionField'	=> $actionField, 'products'		=> $ecproduct );



$ecommerce = array ( 'detail'	=>	$detail );



$result = array( 'event'			        =>		'productDetailView', 'eventAction'	        =>		'view_item', 'eventLabel'	        =>		'view_item', 'ecommerce'		        =>		$ecommerce, 'Value'                 =>      number_format((float)$ecom_totalvalue, 2, '.', ''), 'RemarketingItems'      =>      $remarketing_ids, 'RemarketingCategory'   =>      $catname, 'RemarketingBrand'      =>      $brandname, 'dynx_itemid'           =>      ($tagmanager['dynx_itemid'] ? $dynx_itemid : '' ), 'dynx_itemid2'          =>      ($tagmanager['dynx_itemid2'] ? $dynx_itemid2 : '' ), 'dynx_pagetype'         =>      ($tagmanager['dynx_pagetype'] ? 'view_item' : '' ), 'ecomm_pagetype'        =>      ($tagmanager['ecomm_pagetype'] ? 'view_item' : '' ), 'dynx_totalvalue'       =>      ($tagmanager['dynx_totalvalue'] ? number_format((float)$ecom_totalvalue, 2, '.', '') : '' ), 'ecomm_totalvalue'      =>      ($tagmanager['ecomm_totalvalue'] ? number_format((float)$ecom_totalvalue, 2, '.', '') : '' ), 'ecomm_prodid'          =>      ($tagmanager['ecomm_prodid'] ? $ecom_prodid : '' ), );

if ($tagmanager['alt_currency_status']) { $ftotal = $fprice; $fcurrency = $tagmanager['alt_currency']; } else { $ftotal =  $data['ecproduct']['price']; $fcurrency = $tagmanager['currency']; }

$fbpixel = array( 'event'				=> 'pixel_viewcontent', 'eventAction'		=> 'ViewContent', 'eventLabel'		=> 'ViewContent', 'content_name'		=>	$data['ecproduct']['name'], 'content_category'	=>	$data['ecproduct']['category'], 'content_ids'		=>	$data['ecproduct']['id'], 'content_type'		=>	'product', 'pixel_value'				=>	number_format((float)$ftotal, 2, '.', ''), 'currency'			=>  $fcurrency );

$recommerce = array ( 'currencyCode'	=>	$tagmanager['currency'], 'impressions'	=>  $ecproducts );



$related = array( 'event'			=>		'productImpression', 'eventAction'	=>		'view_item_list', 'eventLabel'	=>		'view_item_list', 'ecommerce'		=>		$recommerce );



if (isset($ecproducts) && sizeof($ecproducts) > 0) { $ecdata = array( 'tmerror'		=> 'false', 'type'			=> 'product', 'google_ec'		=> $result, 'fbpixel'		=> $fbpixel, 'related'		=>	$related ); } else { $ecdata = array( 'tmerror'		=> 'false', 'page_type'		=> 'product', 'google_ec'		=> $result, 'fbpixel'		=> $fbpixel ); }

return $ecdata;

}

public function prepareProducts($data) {

$tagmanager = $this->getTagmanger(); $products = $data['ecproducts']; $fbproducts = $data['ecom_prodid']; $listname = (!empty($data['listname']) ? $data['listname'] : 'Category'); $catname = (!empty($data['catname']) ? $data['catname'] : ''); $brandname = (!empty($data['brandname']) ? $data['brandname'] : ''); $ecom_prodid = $data['ecom_prodid']; $remarketing_ids = $data['remarketing_ids']; $ecom_pagetype = $data['ecom_pagetype']; $ecom_totalvalue = $data['ecom_totalvalue']; $dynx_itemid = $data['dynx_itemid']; $dynx_itemid2 = $data['dynx_itemid2']; $dynx_pagetype = $data['listname']; $dynx_totalvalue = $data['ecom_totalvalue']; $limit = $tagmanager['limit']; $max_list_items = $tagmanager['max_list_items']; $max_module_items = $tagmanager['max_module_items'];

if (isset($this->request->get['search'])) { $search = $this->request->get['search']; } else { $search = ''; }

if (strtolower($listname) == 'search') { $remarketing_page = 'view_search_result'; $pixelpage = 'viewsearch'; } else { $remarketing_page = 'view_item_list'; $pixelpage = 'ViewCategory'; }

$i = 1; $count = 0; $google_ec = array(); $ecproducts = array();

foreach ($products as $product) { if ($i > $max_list_items) { break; } if ($count < $limit ) { $ecproducts[] = $product; } else { $count = 0; $ecommerce = array ( 'currencyCode'	=>	$tagmanager['currency'], 'impressions'	=>  $ecproducts );

$result = array( 'event'			=>		'productImpression', 'eventAction'	=>		'Product Listing', 'eventLabel'	=>		$remarketing_page, 'ecommerce'		=>		$ecommerce ); $google_ec[] = $result;

if (isset($ecproducts)) { unset($ecproducts); } $ecproducts[] = $product;

}

$count++; $i++;

} if (isset($ecproducts) && !empty($ecproducts)) { $ecommerce = array ( 'currencyCode'	=>	$tagmanager['currency'], 'impressions'	=>  $ecproducts );

$result = array( 'event'			=>		'productImpression', 'eventAction'	=>		'view_item_list', 'eventLabel'	=>		$remarketing_page, 'ecommerce'		=>		$ecommerce ); $google_ec[] = $result; }

$fbpixel = array( 'event'				=> 'pixel_' . $pixelpage, 'eventAction'		=> $pixelpage, 'eventLabel'		=> $pixelpage, 'content_name'		=> $catname, 'content_category'	=> $catname, 'content_ids'		=> $fbproducts, 'content_type'		=> 'product', 'search'			=> $search );

$remarketing = array(); $remarketing['event'] = 'remarketing'; $remarketing['eventAction']	=	$remarketing_page; $remarketing['eventLabel']  = $remarketing_page; $remarketing['Value'] = number_format((float)$ecom_totalvalue, 2, '.', ''); $remarketing['RemarketingItems']  = $remarketing_ids; $remarketing['RemarketingCategory'] = $catname; $remarketing['RemarketingBrand'] = $brandname;

if ($tagmanager['custom']){ if ($tagmanager['dynx_itemid']){ $remarketing['dynx_itemid'] = $dynx_itemid; } if ($tagmanager['dynx_itemid2']){ $remarketing['dynx_itemid2'] = $dynx_itemid2; } if ($tagmanager['dynx_pagetype']){ $remarketing['dynx_pagetype'] = $listname; } if ($tagmanager['dynx_totalvalue']){ $remarketing['dynx_totalvalue'] = number_format((float)$ecom_totalvalue, 2, '.', ''); } if ($tagmanager['ecomm_totalvalue']){ $remarketing['ecomm_totalvalue'] = number_format((float)$ecom_totalvalue, 2, '.', ''); } if ($tagmanager['ecomm_pagetype']){ $remarketing['ecomm_pagetype'] = $listname; } if ($tagmanager['ecomm_prodid']){ $remarketing['ecomm_prodid'] = $ecom_prodid; }

}

$ecdata = array( 'tmerror'			=> 'false', 'page_type'		=> 'listing', 'google_ec'		=> $google_ec, 'google_ads'	=> $remarketing, 'fbpixel'		=> $fbpixel );

return $ecdata;

}

public function prepareModuleProducts($data) {

$tagmanager = $this->getTagmanger(); $limit = $tagmanager['limit']; $max_list_items = $tagmanager['max_list_items']; $max_module_items = $tagmanager['max_module_items']; $products   = $data;

$i = 1; $count = 0;

$counter = 0; $google_ec = array(); $ecproducts = array();

foreach ($products as $product) { if ($i > $max_module_items) { break; } if ($count < $limit ) { $ecproducts[] = array ( 'name'		=> $product['name'], 'id'		=> $product['id'], 'price'		=> $product['price'], 'brand'		=> $product['brand'], 'category'	=> $product['category'], 'list'		=> $product['list'], 'position'	=> $i ); } else { $count = 0; $ecommerce = array ( 'currencyCode'	=>	$tagmanager['currency'], 'impressions'	=>  $ecproducts );

$result = array( 'event'			=>		'productImpression', 'eventAction'	=>		'view_item_list'.($counter > 0 ? $counter : ''), 'eventLabel'	=>		'view_item_list'.($counter > 0 ? $counter : ''), 'ecommerce'		=>		$ecommerce ); $google_ec[] = $result;

if (isset($ecproducts)) { unset($ecproducts); } $ecproducts[] = array ( 'name'		=> $product['name'], 'id'		=> $product['id'], 'price'		=> $product['price'], 'brand'		=> $product['brand'], 'category'	=> $product['category'], 'list'		=> $product['list'], 'position'	=> $i );

$counter++; }

$count++; $i++;

} if (isset($ecproducts) && !empty($ecproducts)) { $ecommerce = array ( 'currencyCode'	=>	$tagmanager['currency'], 'impressions'	=>  $ecproducts );

$result = array( 'event'			=>		'productImpression', 'eventAction'	=>		'view_item_list', 'eventLabel'	=>		'view_item_list', 'ecommerce'		=>		$ecommerce ); $google_ec[] = $result; }

return $google_ec; }

public function prepareCart() {

$data = $this->getCartProducts(); $tagmanager = $this->getTagmanger();

$data_error = array(); if (!isset($data['ec_cartproducts'])) { $data_error = array ('tmerror' => 'true'); return $data_error; }

$ecproducts = $data['ec_cartproducts']; $ecom_prodid = $data['ecom_prodid']; $remarketing_ids = $data['remarketing_ids']; $ecom_pagetype = 'cart'; $ecom_totalvalue = number_format((float)$data['ecom_totalvalue'], 2, '.', ''); $dynx_itemid = $data['dynx_itemid']; $dynx_itemid2 = $data['dynx_itemid2']; $dynx_pagetype = 'cart'; $dynx_totalvalue = number_format((float)$data['ecom_totalvalue'], 2, '.', '');



$remarketing = array(); $remarketing['event'] = 'remarketing'; $remarketing['eventAction']	=	'remarketing'; $remarketing['eventLabel']  = 'add_to_cart'; $remarketing['Value'] = number_format((float)$ecom_totalvalue, 2, '.', ''); $remarketing['RemarketingItems']  = $remarketing_ids;

if ($tagmanager['custom']){ if ($tagmanager['dynx_itemid']){ $remarketing['dynx_itemid'] = $dynx_itemid; } if ($tagmanager['dynx_itemid2']){ $remarketing['dynx_itemid2'] = $dynx_itemid2; } if ($tagmanager['dynx_pagetype']){ $remarketing['dynx_pagetype'] = $dynx_pagetype; } if ($tagmanager['dynx_totalvalue']){ $remarketing['dynx_totalvalue'] = $ecom_totalvalue; } if ($tagmanager['ecomm_totalvalue']){ $remarketing['ecomm_totalvalue'] = $ecom_totalvalue; } if ($tagmanager['ecomm_pagetype']){ $remarketing['ecomm_pagetype'] = $ecom_pagetype; } if ($tagmanager['ecomm_prodid']){ $remarketing['ecomm_prodid'] = $ecom_prodid; }

}

$ecdata = array( 'tmerror'		=> 'false', 'page_type'		=> 'cart', 'google_ads'	=> $remarketing );

return $ecdata;

}

public function prepareCheckout($prepare=null) {

$data = $this->getCartProducts(); $data_error = array(); if (!isset($data['ec_cartproducts'])) { $data_error = array ('tmerror' => 'true'); return $data_error; } $tagmanager = $this->getTagmanger(); $result = array(); $orderProducts = array(); $remarketing_ids = array(); $ecom_prodid = array(); $ecom_totalvalue =0;

$i = 1;

if (!isset($prepare)) { $prepare = array( 'page' => 'checkout', 'step' => '1', 'mode' => 'onecheckout' ); }

$actionField = array( 'step'			=> (int)$prepare['step'], 'option'		=> $prepare['page'] );

$cart = array ( 'actionField'	=> $actionField, 'products'		=> $data['ec_cartproducts'] );

$ecommerce = array ( 'checkout' =>	$cart );



$result = array( 'event'			=>		'checkout', 'eventAction'	=>		'checkout', 'eventLabel'	=>		'checkout', 'ecommerce'		=>		$ecommerce );

$remarketing = array(); $remarketing['event'] = 'remarketing'; $remarketing['eventAction']	=	'remarketing'; $remarketing['eventLabel']  = 'add_to_cart'; $remarketing['Value'] = number_format((float)$data['ecom_totalvalue'], 2, '.', ''); $remarketing['RemarketingItems']  = $data['remarketing_ids']; if ($tagmanager['custom']){ if ($tagmanager['dynx_itemid']){ $remarketing['dynx_itemid'] = $data['dynx_itemid']; } if ($tagmanager['dynx_itemid2']){ $remarketing['dynx_itemid2'] = $data['dynx_itemid2']; } if ($tagmanager['dynx_pagetype']){ $remarketing['dynx_pagetype'] = 'add_to_cart'; } if ($tagmanager['dynx_totalvalue']){ $remarketing['dynx_totalvalue'] = number_format((float)$data['ecom_totalvalue'], 2, '.', ''); } if ($tagmanager['ecomm_totalvalue']){ $remarketing['ecomm_totalvalue'] = number_format((float)$data['ecom_totalvalue'], 2, '.', ''); } if ($tagmanager['ecomm_pagetype']){ $remarketing['ecomm_pagetype'] = 'add_to_cart'; } if ($tagmanager['ecomm_prodid']){ $remarketing['ecomm_prodid'] = $data['ecom_prodid']; }

}

if ($tagmanager['alt_currency_status']) { $ftotal = $data['ftotal']; $fcurrency = $tagmanager['alt_currency']; } else { $ftotal =  $data['ecom_totalvalue']; $fcurrency = $tagmanager['currency']; }

$fbpixel = array( 'event'				=> 'pixel_checkout', 'eventAction'		=> 'InitiateCheckout', 'eventLabel'		=> 'InitiateCheckout', 'content_name'		=>	'Checkout', 'content_category'	=>	'Checkout', 'content_ids'		=>	$data['ecom_prodid'], 'contents'			=>  $data['fb_contents'], 'pixel_value'		=>	number_format((float)$ftotal, 2, '.', ''), 'num_of_items'		=>  $data['fb_items'], 'currency'			=>  $fcurrency );

$ecdata = array( 'tmerror'			=> 'false', 'gadata'		=> $result, 'remarketing'	=> $remarketing, 'fbpixel'		=> $fbpixel, 'currency'		=> $tagmanager['currency'] );

return $ecdata; }

public function prepareConfirm($prepare=null) {

$data = $this->getCartProducts(); $data_error = array(); if (!isset($data['ec_cartproducts'])) { $data_error = array ('tmerror' => 'true'); return $data_error; } $tagmanager = $this->getTagmanger(); $result = array(); $orderProducts = array(); $remarketing_ids = array(); $ecom_prodid = array(); $ecom_totalvalue =0;

$i = 1;

if (!isset($prepare)) { $prepare = array( 'page' => 'checkout', 'step' => (isset($this->session->data['steps']) ? $this->session->data['steps'] +1 : 2), 'mode' => 'onecheckout' ); }

$actionField = array( 'step'			=> (int)$prepare['step'], 'option'		=> $prepare['page'] );

$cart = array ( 'actionField'	=> $actionField, 'products'		=> $data['ec_cartproducts'] );

$ecommerce = array ( 'checkout' =>	$cart );



$result = array( 'event'			=>		'checkout', 'eventAction'	=>		'checkout', 'eventLabel'	=>		'checkout', 'ecommerce'		=>		$ecommerce );

$remarketing = array(); $remarketing['event'] = 'remarketing'; $remarketing['eventAction']	=	'remarketing'; $remarketing['eventLabel']  = 'add_to_cart'; $remarketing['Value'] = number_format((float)$data['ecom_totalvalue'], 2, '.', ''); $remarketing['RemarketingItems']  = $data['remarketing_ids']; if ($tagmanager['custom']){ if ($tagmanager['dynx_itemid']){ $remarketing['dynx_itemid'] = $data['dynx_itemid']; } if ($tagmanager['dynx_itemid2']){ $remarketing['dynx_itemid2'] = $data['dynx_itemid2']; } if ($tagmanager['dynx_pagetype']){ $remarketing['dynx_pagetype'] = 'add_to_cart'; } if ($tagmanager['dynx_totalvalue']){ $remarketing['dynx_totalvalue'] = number_format((float)$data['ecom_totalvalue'], 2, '.', ''); } if ($tagmanager['ecomm_totalvalue']){ $remarketing['ecomm_totalvalue'] = number_format((float)$data['ecom_totalvalue'], 2, '.', ''); } if ($tagmanager['ecomm_pagetype']){ $remarketing['ecomm_pagetype'] = 'add_to_cart'; } if ($tagmanager['ecomm_prodid']){ $remarketing['ecomm_prodid'] = $data['ecom_prodid']; }

}

if ($tagmanager['alt_currency_status']) { $ftotal = $data['ftotal']; $fcurrency = $tagmanager['alt_currency']; } else { $ftotal =  $data['ecom_totalvalue']; $fcurrency = $tagmanager['currency']; }

$fbpixel = array( 'event'				=> 'pixel_paymentinfo', 'eventAction'		=> 'AddPaymentInfo', 'eventLabel'		=> 'AddPaymentInfo', 'content_name'		=>	'Checkout', 'content_category'	=>	'Confirm', 'content_ids'		=>	$data['ecom_prodid'], 'contents'			=>  $data['fb_contents'], 'pixel_value'		=>	number_format((float)$ftotal, 2, '.', ''), 'num_of_items'		=>  $data['fb_items'], 'currency'			=>  $fcurrency );

$ecdata = array( 'tmerror'			=> 'false', 'gadata'		=> $result, 'remarketing'	=> $remarketing, 'fbpixel'		=> $fbpixel, 'currency'		=> $tagmanager['currency'] );

return $ecdata; }

public function prepareOrder($order_id) {

if (empty($order_id)) { unset($this->session->data['tm_order_id']); $this->log->write('Tagmanager: Procedure Call prepareorder. Result: Order Id Empty'); return $data['tmerror'] = 'Empty Order'; }

$data = $this->getOrder($order_id); $tagmanager = $this->getTagmanger(); $fb_contents = array(); $result = array(); $orderProducts = array(); $affiliate_gateway = array(); $remarketing_ids = array(); $ecom_prodid = array(); $ecom_totalvalue =0; $dynx_itemid = ''; $dynx_itemid2 = ''; $skroutz_items = array(); $i = 1;

foreach ($data['ec_orderProducts'] as $product) {

$optext = '';

foreach ($product['option'] as $option) { if (isset($option['type']) && $option['type'] != 'file') { $value = (isset($option['value']) ? $option['value'] : ''); } else { $value = ''; } $optext .= $option['name'] . ': ' . (utf8_strlen($value) > 50 ? utf8_substr($value, 0, 50) . '..' : $value) . ' '; } $optext = utf8_substr($optext, 0, 499);

$ecom_prodid[] = $product['pid']; $remarketing_ids[] = array('id' => $product['pid'], 'google_business_vertical' => 'retail'); $ecom_totalvalue += number_format((float)$product['price'], 2, '.', '') ;

$orderProducts[] = array( 'id'	   => (string)$product['pid'], 'name'     => $product['title'], 'category' => $product['category'], 'brand'    => $product['brand'], 'variant'  => $optext, 'quantity' => $product['quantity'], 'price'    => $product['price'], 'currency' => $data['ec_currency'] );

if (isset($tagmanager['affiliate_gateway']) && $tagmanager['affiliate_gateway']) { $affiliate_gateway[] = array( 'id'	   => (string)$product['pid'], 'name'     => $product['title'], 'category' => $product['category'], 'brand'    => $product['brand'], 'cat'	   => $this->getProductCatID($product['pid']), 'quantity' => $product['quantity'], 'price'    => $product['price'], 'currency' => $data['ec_currency'] ); }

$skroutz_items[] = array( 'order_id'    => $data['ec_orderDetails']['order_id'], 'product_id'  => (string)$product['pid'], 'name'        => $product['title'], 'price'       => $product['price'], 'quantity'    => $product['quantity'] );

if ($i == 1) { $dynx_itemid = (string)$product['pid']; } elseif ($i == 2) { $dynx_itemid2 = (string)$product['pid']; } $i++; } $actionField = array( 'id'			=> $data['ec_orderDetails']['order_id'], 'affiliation'	=> (isset($data['ec_affiliate_code'])? $data['ec_affiliate_code'] : ''), 'revenue'		=> $data['ec_orderValue'], 'tax'			=> $data['ec_orderTax'], 'shipping'		=> $data['ec_orderShipping'], 'coupon'		=> (isset($data['ec_orderCoupon'])? $data['ec_orderCoupon'] : ''), 'currency'		=> $data['ec_currency'] );

$purchase = array ( 'actionField'	=> $actionField, 'products'		=> $orderProducts );

$ecommerce = array ( 'purchase' =>	$purchase );



$result = array( 'event'			=>		'ecommerceComplete', 'eventAction'	=>		'New Order', 'eventLabel'	=>		'purchase', 'ecommerce'	=>		$ecommerce );



if(isset($data['ec_orderProducts'])) { $fb_items = 0; foreach ($data['ec_orderProducts'] as $product) { $price =$product['price']; if ($tagmanager['alt_currency_status']) { $price = $this->currency->format($product['price'],$tagmanager['alt_currency'], '' ,false); } $fb_contents[] = array( 'id' 	   => (string)$product['pid'], 'quantity' => $product['quantity'], 'item_price' => number_format($price, 2, '.', '') ); $fb_items = $fb_items + $product['quantity']; } }

if ($tagmanager['alt_currency_status']) { $ftotal = number_format($this->currency->format($data['ec_orderValue'], $tagmanager['alt_currency'],'',false), 2, '.', ''); $fcurrency = $tagmanager['alt_currency']; } else { $ftotal =  $data['ec_orderValue']; $fcurrency = $data['ec_currency']; }

$fbpixel = array( 'event'				=> 'pixel_purchase', 'eventAction'		=> 'Purchase', 'eventLabel'		=> 'Purchase', 'content_name'		=>	'Purchase', 'content_category'	=>	'Confirm', 'content_ids'		=>	$ecom_prodid, 'contents'			=>  $fb_contents, 'pixel_value'		=>	number_format((float)$ftotal, 2, '.', ''), 'num_of_items'		=>  $fb_items, 'currency'			=>  $fcurrency );

$remarketing = array(); $remarketing['event'] = 'remarketing'; $remarketing['eventAction']	=	'remarketing'; $remarketing['eventLabel']  = 'purchase'; $remarketing['Value'] = number_format((float)$ecom_totalvalue, 2, '.', ''); $remarketing['RemarketingItems']  = $remarketing_ids; if ($tagmanager['custom']){ if ($tagmanager['dynx_itemid']){ $remarketing['dynx_itemid'] = (string)$dynx_itemid; } if ($tagmanager['dynx_itemid2']){ $remarketing['dynx_itemid2'] = (string)$dynx_itemid2; } if ($tagmanager['dynx_pagetype']){ $remarketing['dynx_pagetype'] = 'purchase'; } if ($tagmanager['dynx_totalvalue']){ $remarketing['dynx_totalvalue'] = number_format((float)$ecom_totalvalue, 2, '.', ''); } if ($tagmanager['ecomm_totalvalue']){ $remarketing['ecomm_totalvalue'] = number_format((float)$ecom_totalvalue, 2, '.', ''); } if ($tagmanager['ecomm_pagetype']){ $remarketing['ecomm_pagetype'] = 'purchase'; } if ($tagmanager['ecomm_prodid']){ $remarketing['ecomm_prodid'] = $ecom_prodid; }

}

$google_adwords = array( 'event'			=>		'conversion', 'eventAction'	=>		'conversion', 'eventLabel'	=>		'purchase', 'adwordCurrency'		=> $data['ec_currency'], 'adwordOrderID'			=> $data['ec_orderDetails']['order_id'], 'adwordConversionValue'	=> $data['ec_orderValue'] );

/* Routine to handle tax, discount etc for Skroutz */



$skroutz_order_tax = $data['ec_orderTax']; $skroutz_order_shipping = $data['ec_orderShipping']; $skroutz_revenue = $data['ec_orderValue'];

if (isset($data['adjustment']['plus'])) { $skroutz_revenue = $skroutz_revenue - $data['adjustment']['plus']; }

if (isset($tagmanager['override_tax']) && $tagmanager['override_tax']) { $skroutz_order_tax = $skroutz_revenue - ($skroutz_revenue / $tagmanager['tax']); $skroutz_order_shipping_tax = $skroutz_order_shipping - ($skroutz_order_shipping / $tagmanager['tax']); }

$skroutz_order = array( 'order_id'  => $data['ec_orderDetails']['order_id'], 'revenue'   => $skroutz_revenue, 'shipping'  => number_format((float)$skroutz_order_shipping, 2, '.', ''), 'tax'       => number_format((float)$skroutz_order_tax, 2, '.', '') );

/* end skroutz */

/* Google Review Survery */

$estimate = $this->DeliveryEstimate('15:00:00',5, $data['ec_orderDetails']['shipping_code']); 

if (isset($estimate) && !empty($estimate)) { $estimate = date('Y-m-d', $estimate); }

$g_review = array( 'order_id'=> $data['ec_orderDetails']['order_id'], 'email'	=> $data['ec_orderDetails']['email'], 'country' => $data['ec_orderDetails']['shipping_iso_code_2'], 'estimate' => $estimate );



/* end Google Review Survey */

$ecdata = array( 'tmerror'			=> 'false',	'tagmanager'	=> $tagmanager, 'fbpixel'		=> $fbpixel, 'gadata'		=> $result, 'adword'		=> $google_adwords, 'remarketing'	=> $remarketing, 'currency'		=> $data['ec_currency'], 'revenue'		=> $data['ec_orderValue'], 'tax'			=> $data['ec_orderTax'], 'shipping'		=> $data['ec_orderShipping'], 'order_id'		=> $data['ec_orderDetails']['order_id'], 'coupon'		=> (isset($data['ec_orderCoupon'])? $data['ec_orderCoupon'] : ''), 'affiliation'	=> (isset($data['ec_affiliate_code'])? $data['ec_affiliate_code'] : ''), 'skroutz_order'	=> $skroutz_order, 'skroutz_items'	=> $skroutz_items, 'greview'		=> $g_review, 'affiliate_gateway' => $affiliate_gateway, 'hit'			=> $data['hit'] ); return $ecdata; }

public function getCartProducts() { $products = $this->cart->getProducts(); $this->load->model('catalog/product'); $tagmanager = $this->getTagmanger(); $data = array();

$data['ec_shipping_total'] = isset($this->session->data['shipping_method']['cost']) ? $this->session->data['shipping_method']['cost'] : 0; $data['ec_coupon'] = isset($this->session->data['coupon']) ? $this->session->data['coupon'] : false;

$data['ecom_prodid'] = array(); $data['fb_contents'] = array(); $data['remarketing_ids'] = array(); $data['ecom_pagetype']='purchase'; $data['ecom_totalvalue'] =0; $data['dynx_itemid'] =''; $data['dynx_itemid2'] =''; $data['ftotal'] = 0; $data['fb_items'] = 0; $i=1;

$orderProducts = array();

foreach ($products as $product) {

$optext = '';

foreach ($product['option'] as $option) { if (isset($option['type']) && $option['type'] != 'file') { $value = (isset($option['value']) ? $option['value'] : ''); } else { $value = ''; } $optext .= $option['name'] . ': ' . (utf8_strlen($value) > 50 ? utf8_substr($value, 0, 50) . '..' : $value) . ' '; } 

$optext = utf8_substr($optext, 0, 499);

$model = $product['model']; $sku = (isset($product['sku'])? $product['sku'] : false);

$pid = $this->tagmangerPmap($model,$sku,$product['product_id']); $brand = $this->getProductBrandName($product['product_id']); $cat = $this->getProductCatName($product['product_id']); $title = $this->tagmangerPtitle($product['name'], $brand, $model,$product['product_id']); $unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')); $total_price = $unit_price * $product['quantity']; $total_price = $this->currency->format($total_price, $this->session->data['currency'],'',false); $fprice = $this->currency->format($total_price,$tagmanager['alt_currency'], '' ,false); $data['ftotal'] = $data['ftotal'] + $fprice;

$data['ecom_prodid'][] = $pid; $data['remarketing_ids'][] = array('id' => $pid, 'google_business_vertical' => 'retail'); $data['ecom_totalvalue'] += number_format((float)$total_price, 2, '.', '') ; $data['fb_contents'][] = array ('id' => $pid, 'quantity' => $product['quantity']); $data['fb_items'] = $data['fb_items'] + $product['quantity'];

if ($i == 1) { $data['dynx_itemid'] = $pid; } elseif ($i == 2) { $data['dynx_itemid2'] = $pid; }

$data['ec_cartproducts'][] = array( 'id'	   => (string)$pid, 'name'     => $title, 'category' => $cat, 'brand'    => $brand, 'variant'  => $optext, 'quantity' => $product['quantity'], 'price'    => number_format((float)$this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'],'',false), 2, '.', ''), 'currency' => $this->session->data['currency'] ); $i++;



} return $data; }

public function getOrder($order_id) {

$this->load->model('checkout/order'); $this->load->model('account/customer'); $tagmanager = $this->getTagmanger();

if (!isset($order_id) || empty($order_id)) { return false; }

$data['ec_language'] = $this->config->get('config_language'); $data['ec_orderCoupon'] =  $this->getOrderCoupon($order_id); $data['ec_orderDetails'] = $this->model_checkout_order->getOrder($order_id); $data['ec_currency'] = $data['ec_orderDetails']['currency_code']; 

$data['ec_orderShipping'] = $this->getOrderShipping($order_id) * $data['ec_orderDetails']['currency_value']; $data['ec_orderValue'] = $data['ec_orderDetails']['total'] * $data['ec_orderDetails']['currency_value']; $data['ec_orderValue'] = number_format((float)$data['ec_orderValue'], 2, '.', ''); $data['ec_orderTax'] = $this->getOrderTax($order_id) * $data['ec_orderDetails']['currency_value']; $data['adjustment'] = $this->getOrderTotalAdjustment($order_id, $data['ec_orderDetails']['currency_value']);

$data['ec_orderProducts'] = $this->getOrderProducts($order_id, $data['ec_orderDetails']); $data['ec_orderDetails']['coupon'] =  $this->getOrderCoupon($order_id);

$data['ec_orderTax'] = number_format($data['ec_orderTax'], 2, '.', ''); $data['ec_orderShipping'] = number_format((float)$data['ec_orderShipping'], 2, '.', ''); $data['ec_affiliate_code'] = '';

if (isset($data['ec_orderDetails']['affiliate_id']) && !empty($data['ec_orderDetails']['affiliate_id']) ) { $data['ec_affiliate_code'] = $data['ec_orderDetails']['affiliate_id']; }

$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "analytics_tracking WHERE order_id = '" . (int)$order_id . "'" );

$data['hit'] = 0;

if ($query->num_rows) { $data['hit'] = $query->row['hit']; } else { $data['hit'] = 0; }

return $data;

}

public function getOrderProducts($order_id,$order_info) { $order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

$products = array();

$tagmanager = $this->getTagmanger();

foreach ($order_product_query->rows as $product) { $product_id = $product['product_id']; $option_data = array(); $options = $this->getOrderOptions($order_id, $product['order_product_id']); foreach ($options as $option) { $option_data[] = array( 'name'  => $option['name'] . " " . (utf8_strlen($option['value']) > 100 ? utf8_substr($option['value'], 0, 100) . '..' : $option['value']) ); }

$brand = $this->getProductBrandName($product['product_id']); $cat = $this->getProductCatName($product['product_id']);

$price = $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value'],false); $fprice = $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $tagmanager['alt_currency'], '',false); $total = $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'],false); $ftotal = $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0),$tagmanager['alt_currency'], '' ,false);

$pid = $this->tagmangerPmap($product['model'],$product['model'],$product['product_id']);

$title = $this->tagmangerPtitle($product['name'], $brand, $product['model'],$product['product_id']);



$products[] = array( 'name'     => $product['name'], 'title'    => $title, 'model'    => $product['model'], 'pid'      => $pid, 'category' => (isset($cat) ? $cat : ''), 'brand'    => (isset($brand) ? $brand : ''), 'option'   => $option_data, 'quantity' => $product['quantity'], 'price'    => number_format((float)$price, 2, '.', ''), 'fprice'    => number_format((float)$fprice, 2, '.', ''), 'ftotal'    => number_format((float)$ftotal, 2, '.', ''), 'total'    => number_format((float)$total, 2, '.', '') ); }

return $products;

}    

public function getOrderOptions($order_id, $order_product_id) { $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

return $query->rows; }

public function getOrderTax($order_id) { $tax_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'tax'"); $order_tax = '0.00'; if ($tax_query->num_rows) { $order_tax = $tax_query->row['value']; } return $order_tax; }

public function getOrderShipping($order_id) { $shipping_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'shipping'"); $order_shipping = '0.00'; if ($shipping_query->num_rows) { $order_shipping = $shipping_query->row['value']; } return $order_shipping;

}

public function getOrderCoupon($order_id) { $coupon_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'coupon'"); $order_coupon = ''; if ($coupon_query->num_rows) { $order_coupon = $coupon_query->row['title']; } return $order_coupon; }

public function getOrderTotalAdjustment($order_id,$value) { $tagmanager = $this->getTagmanger(); $plus_value = 0; $minus_value = 0;

if (!isset($tagmanager['total_plus']) || !isset($tagmanager['total_minus'])) { $order_total_plus = array('cod_fee', 'handling', 'klarna_fee', 'low_order_fee');  $order_total_minus = array('credit' , 'reward', 'voucher'); } else { $order_total_plus = $tagmanager['total_plus'];  $order_total_minus = $tagmanager['total_minus']; }

foreach ($order_total_plus as $code) {

if (!empty($code)) { $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = '" . $this->db->escape($code) ."'"); if ($query->num_rows) { $plus_value = $plus_value + $query->row['value']; } } } 

foreach ($order_total_minus as $code) { if (!empty($code)) { $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = '" . $this->db->escape($code) ."'"); if ($query->num_rows) { $minus_value = $minus_value + $query->row['value']; } } } 

$data = array ( 'plus' =>  $plus_value * $value, 'minus' => $minus_value * $value, );

return $data; }

public function GAorderAdd($order_id, $data) { $cid = ''; $tagmanager = $this->getTagmanger(); $this->db->query("INSERT INTO `" . DB_PREFIX . "analytics_tracking` SET order_id = '" . (int)$order_id . "', cid = '" . $this->db->escape($tagmanager['cid']) . "', currency_code = '" . $this->db->escape($data['currency_code']) . "', currency_id = '" . $this->db->escape($data['currency_id']) . "', uid = '" . $this->db->escape($tagmanager['userid']) . "', ul = '" . $this->db->escape($tagmanager['language']) . "', ip = '" . $this->db->escape($data['ip']) . "', user_agent = '" . $this->db->escape($data['user_agent']) . "', tid = '" . $this->db->escape($tagmanager['gid']) . "'" ); }

public function GAorder($order_id) {

$this->load->model('checkout/order'); $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "analytics_tracking WHERE order_id = '" . (int)$order_id . "' AND hit = '0'" ); $data = array();

$tagmanager = $this->getTagmanger();

if (!isset($tagmanager['gid']) || $tagmanager['mp'] == '0') { $this->log->write('Tagmanager Debug Log: Measurement Protocol call [ Order: ' . $order_id . ' ] Result: error analytics id or mp not set'); return 'error analytics id or mp not set'; }

if ($query->num_rows) { $data['cid'] = $query->row['cid']; $data['currency_code'] = $query->row['currency_code']; $data['ip'] = $query->row['ip']; $data['user_agent'] = $query->row['user_agent']; } else { $this->log->write('Tagmanager Debug Log: Measurement Protocol call [ Order: ' . $order_id . ' ] Result: Order not found or already hit'); return 'error order not found or already hit'; }

$order_status_id = $this->OrderStatusCheck($order_id);

if ($order_status_id == '0') { unset($this->session->data['tm_order_id']); $this->log->write('Tagmanager Debug Log: Measurement Protocol call [ Order: ' . $order_id . ' ] Result: Order Status Id is 0 / Missing'); return 'Incomplete or Missing Order'; }

$result = 	$this->getOrder($order_id);

$data = array_merge($data, $result);

$para  = ''; $para .= "v=1"; $para .= "&tid=" . $tagmanager['gid'] ; $para .= "&cid=" . $data['cid']; $para .= "&t=event&ec=Purchase&ea=sale"; $para .= "&dh=" . $tagmanager['host']; $para .= "&dp=checkout/success"; $para .= "&dt=Order%20Complete"; $para .= "&ti=" . $order_id; $para .= "&ta="; $para .= "&cu=" .$data['currency_code'] ; $para .= "&tr=" . $data['ec_orderValue']; $para .= "&tt=" . $data['ec_orderTax']; $para .= "&ts=" . $data['ec_orderShipping'] ; $para .= (!empty($data['ec_orderCoupon']) ? "&tc=" . $data['ec_orderCoupon'] :'') ; $para .= "&aip=1&ds=web&uip=" . $data['ip']; $para .= "&pa=purchase";

$i = 1;

foreach ($data['ec_orderProducts'] as $product) { $product['category'] = str_replace(">", "/", $product['category']); $product['category'] = str_replace("&", "and", $product['category']); $product['category'] = str_replace("amp;", "", $product['category']);

$para .= "&pr" . $i . "id=" . $product['pid'] . "&pr" . $i . "nm=" . $product['title'] . "&pr" . $i . "ca=" . $product['category'] . "&pr" . $i . "br=" . $product['brand']; $para .= "&pr" . $i . "qt=" . $product['quantity'] . "&pr" . $i . "pr=" . $product['price'] ; if (isset($product['option'])) { $optext = ''; foreach ($product['option'] as $op) { $optext .= $op['name']; } $para .= "&pr" . $i . "va=" . utf8_substr($optext, 0, 499); }

$i++; }

parse_str($para, $orderdata);

$response = $this->GApost($orderdata);

$this->GAupdateorder($order_id);

$this->log->write('Tagmanager Debug Log: Measurement Protocol call [ Order: ' . $order_id . ' ] Result: success order manaully hit');

return 'success'; }

public function GArefund($order_id) {

$this->load->model('checkout/order'); $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "analytics_tracking WHERE order_id = '" . (int)$order_id . "' AND hit = '1'" ); $data = array();

$tagmanager = $this->getTagmanger();

if (!isset($tagmanager['gid']) || $tagmanager['mp'] == '0') { $this->log->write('Tagmanager Debug Log: Measurement Protocol Refund Order id: '.$order_id.' Result: error analytics id or mp not set'); return 'error analytics id or mp not set'; }

if ($query->num_rows) { $data['cid'] = $query->row['cid']; $data['currency_code'] = $query->row['currency_code']; $data['ip'] = $query->row['ip']; $data['user_agent'] = $query->row['user_agent']; } else { $this->log->write('Tagmanager Debug Log: Measurement Protocol Refund Order id: '.$order_id.'  Result: error order not found or not hit'); return 'error order not found or not hit'; }

$order_status_id = $this->OrderStatusCheck($order_id);

if ($order_status_id == '0') { unset($this->session->data['tm_order_id']); $this->log->write('Tagmanager Debug Log: Measurement Protocol Refund Order id: '.$order_id.' Result: Incomplete or Missing Order'); return $data['tmerror'] = 'Incomplete or Missing Order'; }

$result = 	$this->getOrder($order_id);

$data = array_merge($data, $result);

$para  = ''; $para .= "v=1"; $para .= "&tid=" . $tagmanager['gid'] ; $para .= "&cid=" . $data['cid']; $para .= "&t=event&ec=Purchase&ea=sale"; $para .= "&dh=" . $tagmanager['host']; $para .= "&dp=refund"; $para .= "&dt=Refund"; $para .= "&ti=" . $order_id; $para .= "&cu=" .$data['currency_code'] ; $para .= "&ta="; $para .= "&ni=1"; $para .= "&tr=-" . $data['ec_orderValue']; $para .= "&tt=-" . $data['ec_orderTax']; $para .= "&ts=-" . $data['ec_orderShipping'] ; $para .= "&aip=1&ds=web&uip=" . $data['ip']; $para .= "&pa=purchase";

$i = 1;

foreach ($data['ec_orderProducts'] as $product) { $product['category'] = str_replace(">", "/", $product['category']); $product['category'] = str_replace("&", "and", $product['category']); $product['category'] = str_replace("amp;", "", $product['category']);

$para .= "&pr" . $i . "id=" . $product['pid'] . "&pr" . $i . "nm=" . $product['title'] . "&pr" . $i . "ca=" . $product['category'] . "&pr" . $i . "br=" . $product['brand']; $para .= "&pr" . $i . "qt=-" . $product['quantity'] . "&pr" . $i . "pr=" . $product['price'] ; if (isset($product['option'])) { $optext = ''; foreach ($product['option'] as $op) { $optext .= $op['name']; } $para .= "&pr" . $i . "va=" . utf8_substr($optext, 0, 499); }

$i++; }

parse_str($para, $orderdata);

$response = $this->GApost($orderdata);

$this->db->query("UPDATE `" . DB_PREFIX . "analytics_tracking` SET hit = '2' WHERE order_id = '" . (int)$order_id . "'");

$this->log->write('Tagmanager Debug Log: Measurement Protocol Refund Order id: '.$order_id.' Result: Success');

return $response; }

public function GAContact() {

$tagmanager = $this->getTagmanger();

$data = array ( 'v'			=> '1', 'tid'		=> $tagmanager['gid'], 'cid'		=> $tagmanager['cid'], 't'			=> 'event', 'ec'		=> 'contact', 'ea'		=> 'contact', 'el'		=> 'contact', 'ni'		=> '0', 'dp'		=> '/conact', 'dt'		=> 'Contact Form' );

$curl = curl_init('https://www.google-analytics.com/collect'); curl_setopt($curl, CURLOPT_POST, true); curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data)); curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); curl_setopt($curl, CURLOPT_HEADER, false); curl_setopt($curl, CURLOPT_TIMEOUT, 30); curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($curl);

curl_close($curl);

$json = json_decode($response,true);

$this->log->write('Tagmanager Debug Log: Measurement Protocol Contact Event. Result: Success');

return $json; }

public function GApost($data, $debug=false) {

if (!isset($data)) { return; }

if (!$debug) { $curl = curl_init('https://www.google-analytics.com/collect');  } else { $curl = curl_init('https://www.google-analytics.com/debug/collect'); }

curl_setopt($curl, CURLOPT_POST, true); curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data)); curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); curl_setopt($curl, CURLOPT_HEADER, false); curl_setopt($curl, CURLOPT_TIMEOUT, 30); curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($curl);

curl_close($curl);

$json = json_decode($response,true);

if ($debug) { echo '<pre>'; print_r($response); print_r($data); echo '</pre>'; }

return $json;

}

public function GAupdateorder($order_id) { if (isset($order_id) && !empty($order_id)) { $this->db->query("UPDATE `" . DB_PREFIX . "analytics_tracking` SET hit = '1' WHERE order_id = '" . (int)$order_id . "'"); if (isset($this->session->data['tm_order_id'])) { unset($this->session->data['tm_order_id']);	 }; } return; }

public function OrderStatusCheck($order_id) { if (isset($order_id) && !empty($order_id)) { $query = $this->db->query("SELECT order_id, order_status_id from `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");

$order_status_id = 0;

if ($query->num_rows) { $order_status_id = $query->row['order_status_id']; } } return $order_status_id; }

public function DeliveryEstimate($cutoftime,$days=7,$shipping_code=null) {

date_default_timezone_set("Europe/London");

$dayofweek=date("N", time()); /* get today day */

if ($dayofweek<5) { /* IS THIS WEEKDAYS (MON-THU) */ if (time() <= strtotime($cutoftime)) { $dispathtoday=true; $addday=0; } else { $dispathtoday=false; $addday=1; } } else if ($dayofweek==5) { /* Friday */ if (time() <= strtotime($cutoftime)) { $dispathtoday=true; $addday=0; } else { $dispathtoday=false; $addday=3; } } else if ($dayofweek==6) { /* SAT */ $dispathtoday=false; $addday=2; } else if ($dayofweek==7) { /* SUN */ $dispathtoday=false; $addday=1; }

$dispathdate= time() + ($addday * 24 * 60 * 60);

if (isset($shipping_code) && $shipping_code) { /* Custom Shipping Code */ if ($shipping_code=='customshipping.customshipping0'){ $scode = '3-5 days'; $estdelivery=$dispathdate + (7 * 24 * 60 * 60); } else if ($shipping_code=='customshipping.customshipping1'){ $scode = '2 days'; $estdelivery=$dispathdate + (3 * 24 * 60 * 60); } else if ($shipping_code=='customshipping.customshipping2'){ $scode = '1 day'; $estdelivery=$dispathdate + (2 * 24 * 60 * 60); } else if ($shipping_code=='customshipping.customshipping3'){ $scode = '1 days'; $estdelivery=$dispathdate + (2 * 24 * 60 * 60); } else if ($shipping_code=='customshipping.customshipping4'){ $scode = '1 days'; $estdelivery=$dispathdate + (2 * 24 * 60 * 60); }else { $scode = '5 days'; $estdelivery=$dispathdate + (7 * 24 * 60 * 60); } } else {

$estdelivery=$dispathdate + ($days * 24 * 60 * 60); }



return $estdelivery; } 

public function getSizeAndColorOptionMap($product_id, $store_id) { $color_id = $this->getOptionId($product_id, $store_id, 'color'); $size_id = $this->getOptionId($product_id, $store_id, 'size');

$groups = $this->googleshopping->getGroups($product_id, $this->config->get('config_language_id'), $color_id, $size_id);

$colors = $this->googleshopping->getProductOptionValueNames($product_id, $this->config->get('config_language_id'), $color_id); $sizes = $this->googleshopping->getProductOptionValueNames($product_id, $this->config->get('config_language_id'), $size_id);

$map = array( 'groups' => $groups, 'colors' => count($colors) > 1 ? $colors : null, 'sizes' => count($sizes) > 1 ? $sizes : null, );

return $map; }

protected function writeTMLog($line, $alsoEcho = false) { $log = new Log('tagmanager.log'); $log->write($line); if ($alsoEcho) echo $line; } }
?>