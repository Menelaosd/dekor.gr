<?php /* v:9.3.1 20122021*/

if (isset($this->request->get['route'])) { if ($this->request->get['route'] == 'account/success' || $this->request->get['route'] == 'account/login' || $this->request->get['route'] == 'account/logout' || $this->request->get['route'] == 'account/account' || $this->request->get['route'] == 'contact/success'|| $this->request->get['route'] == 'checkout/confirm') { $this->gtm->resetCustomerData(); } }

$tagmanager = $this->gtm->config();

if (isset($tagmanager['code']) && $tagmanager['status'] == '1') {

$tmanalytics = ''; $eu_css = ''; $eu_js = ''; $cc_analytics = 0; $cc_marketing = 0; $cc_enabled = 0; $dimension_value1 = ''; $dimension_value2 = ''; $dimension_value3 = ''; $dimension_value4 = ''; $setting_tags = array(); $cc_data = array(); $tm_delay = $tagmanager['delay'];

if (isset($GLOBALS['tm'])) { $tm = $GLOBALS['tm']; }

if (isset($GLOBALS['tm_m'])) { $tm_m = $GLOBALS['tm_m']; } if (isset($GLOBALS['ga4_m'])) { $ga4_mproducts = $GLOBALS['ga4_m']; }

unset($GLOBALS['tm']); unset($GLOBALS['tm_m']); unset($GLOBALS['ga4_m']);

/*$this->load->model('account/customer'); $this->session->data['userid'] = $this->customer->getId(); */

$oc1 = false;

if (defined('JOURNAL3_ACTIVE')) { $data['ttheme'] = 'journal3'; }


if(substr(VERSION,0,1)=='3') {

$this->user = new Cart\User($this->registry);

$data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home'); $user_logged = $this->user->isLogged();


} elseif (substr(VERSION,0,3)=='2.3' || substr(VERSION,0,3)=='2.2') {

$this->user = new Cart\User($this->registry); $data['tagmanager'] = $tagmanager; $data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home'); $user_logged = $this->user->isLogged();

} elseif (substr(VERSION,0,3)=='2.1' ) {

$this->user = new User($this->registry); $user_logged = $this->user->isLogged(); $data['tagmanager'] = $tagmanager; $data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home');

} elseif (substr(VERSION,0,3)=='2.0') {

$this->load->library('user'); $this->user = new User($this->registry); $user_logged = $this->user->isLogged(); $data['tagmanager'] = $tagmanager; $data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home');

} elseif (substr(VERSION,0,1)=='1' ) {

$oc1 = true; $this->load->library('user'); $this->user = new User($this->registry); $user_logged = $this->user->isLogged(); $this->data['tagmanager'] = $tagmanager; $this->data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home');

}

$page_type = (isset($tm['type']) ? $tm['type'] : ''); $j3popup = (isset($this->request->get['popup']) ? $this->request->get['popup'] : '') ; $tmdata = array(); $route = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home'); $org_route = $route;

foreach ($tagmanager['route_success'] as $checkroute) { if (trim($checkroute) == $route){ $route = 'checkout/success'; } }

foreach ($tagmanager['route_confirm'] as $checkroute) { if (trim($checkroute) == $route){ $route = 'checkout/confirm'; } }

foreach ($tagmanager['route_checkout'] as $checkroute) { if (trim($checkroute) == $route){ $route = 'checkout/checkout'; } }

if ($route == 'extension/quickcheckout/checkout' || $route == 'quickcheckout/checkout') { $route = 'checkout/checkout'; }

if ($route == 'product/quickview' || $route == 'extension/module/pavquickview/show' || $route == 'journal2/quickview' ) { $j3popup ='quickview'; }

if ($page_type == 'product'){ $tmdata = $this->gtm->prepareProduct($tm); }

if ($page_type == 'listing'){ $tmdata = $this->gtm->prepareProducts($tm); }

if ($org_route == 'information/contact'){ $page_type = 'contact'; }

if ($org_route == 'account/success'){ $page_type = 'signup'; }

if ($org_route == 'common/home'){ $page_type = 'home'; }

switch ($route) { case "checkout/cart": if ($this->cart->hasProducts()) { $tmdata = $this->gtm->prepareCart(); $page_type = 'cart'; } break;

case "checkout/checkout": $prepare = array( 'page' => 'checkout', 'step' => '1', 'mode' => 'checkout' );

$this->session->data['steps'] = 1; $this->session->data['reload_check'] = array();

if ($this->cart->hasProducts()) { $tmdata = $this->gtm->prepareCheckout($prepare); $page_type = 'checkout'; } break;

case "checkout/confirm": if (isset($this->session->data['steps'])) { $this->session->data['steps'] ++;

} else { $this->session->data['steps'] = 2; }

$this->session->data['reload_check'] = array();

$prepare = array( 'page' => 'confirm', 'step' => $this->session->data['steps'], 'mode' => 'confirm' ); if ($this->cart->hasProducts()) { $tmdata = $this->gtm->prepareConfirm($prepare); $page_type = 'confirm'; } break;

case "checkout/success":

if (!isset($this->session->data['tm_order_id']) || empty($this->session->data['tm_order_id']) || $this->session->data['tm_order_id'] == 0) { if (isset($this->session->data['xsuccess_order_id'])) { $this->gtm->saveOrderID($this->session->data['xsuccess_order_id']); } if (isset($this->session->data['order_id'])) { $this->gtm->saveOrderID($this->session->data['order_id']); } } if (!isset($this->session->data['tm_order_id'])) { $gtm_orderid = (int)$this->gtm->readGTMCookie('gtm_orderid'); if ($gtm_orderid && !empty($gtm_orderid) && $gtm_orderid > 0) { $this->session->data['tm_order_id'] = $gtm_orderid; } }

if (isset($this->session->data['tm_order_id']) && !empty($this->session->data['tm_order_id'])) { $order_id = $this->session->data['tm_order_id']; $order_data = $this->gtm->prepareOrder($order_id); $order_status_id = $this->gtm->OrderStatusCheck($order_id);

if ($order_status_id == '0') {

if (isset($tagmanager['debug']) && $tagmanager['debug']) { $this->gtm->tmerror('TM: Order Success Called [ Order: ' . $order_id . ' ] Result: Order Status is not Complete to send hit'); }

}

if (isset($order_data['hit']) && $order_data['hit'] == '0') { $tmdata = $order_data; $page_type = 'success'; if(substr(VERSION,0,1)=='1' ) { $this->data['order_data'] = $order_data; } else { $data['order_data'] = $order_data; } } if (isset($order_data['hit']) && $order_data['hit'] == '1') { if (isset($tagmanager['debug']) && $tagmanager['debug']) { $this->gtm->tmerror('TM: Order Success Called Twice '. $order_id ); } $this->gtm->resetGTMCookie('gtm_orderid'); unset($this->session->data['tm_order_id']); } if (isset($tagmanager['debug']) && $tagmanager['debug']) { $this->gtm->tmerror('TM: Order Success Called [ Order: ' . $order_id . ' ] Result: success order'); } } else { if (isset($tagmanager['debug']) && $tagmanager['debug']) { $this->gtm->tmerror('TM: Order Success Called: Order ID Missing' ); } } break; }

if (isset($tmdata)) { if (isset($tmdata['tmerror']) && $tmdata['tmerror'] =='false') { $_data = $tmdata; } }

if (empty($j3popup)) {

$tmanalytics .= '<!-- Google Tag Manager Extension -->';

include('inc_cookiee_main.php');

$tmanalytics .= '<script type="text/javascript" nitro-exclude=""> var dataLayer = window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} var delayInMilliseconds = ' . $tm_delay . '; '. 'function whenAvailable(name, callback) {var interval = 10; window.setTimeout(function() {if (window[name]) {callback(window[name]);} else {window.setTimeout(arguments.callee, interval);}}, interval);}' . "\n";

$gdpr_analytics = 'granted'; $gdpr_marketing = 'granted'; $allowAdFeatures = 'false'; $cc_analytics = 0; $cc_marketing = 0; $tracking_block = false; $marketing_block = false;

if ($tagmanager['eu_cookie'] == '1') { if ($tagmanager['eu_cookie_enforce'] == '1') { $cc_enabled = 1; $gdpr_analytics = 'denied'; $gdpr_marketing = 'denied'; $allowAdFeatures = 'false'; $tracking_block = true; $marketing_block = true; } $tmanalytics .= $eu_js; $cc_accepted = (isset($_COOKIE["cookieControl"]) ? $_COOKIE["cookieControl"] : false);

if (isset($_COOKIE["cookieControlPrefs"])) { $cc_data = (array) json_decode($_COOKIE["cookieControlPrefs"]); foreach ($cc_data as $cc_option) { if ($cc_option == 'analytics') { $cc_analytics = 1; $gdpr_analytics = 'granted'; $allowAdFeatures = 'false'; $tracking_block = false; } if ($cc_option == 'marketing') { $cc_marketing = 1; $gdpr_marketing = 'granted'; $allowAdFeatures = 'true'; $marketing_block = false; } } }

$setting_tags[] = array( 'allowAdFeatures' => $allowAdFeatures, 'debug'			  => 'false', ); } $tmanalytics .= "gtag('consent', 'default', {'analytics_storage': '" . $gdpr_analytics . "', 'ad_storage': '" . $gdpr_marketing . "' });";

$tmanalytics .= "</script>" . "\n";

$setting_tags[] = array( 'tracking' => 'multi', );

if (isset($tagmanager['ua_status']) && $tagmanager['ua_status']) { if (isset($tagmanager['gid'])) { $setting_tags[] = array( 'gid'		=> $tagmanager['gid'], 'ua_status' => $tagmanager['ua_status'] ); } }

if (isset($tagmanager['ga4_status']) && $tagmanager['ga4_status']) { if (isset($tagmanager['ga4_mid'])) { $setting_tags[] = array( 'ga4_mid'		=> $tagmanager['ga4_mid'], 'ga4_status'	=> $tagmanager['ga4_status'], ); } }

if (isset($user_logged) && $tagmanager['admin'] =='1') { $setting_tags[] = array( 'event'	=> 'adminVisit', 'admin'	=> '1' ); }

if (isset($tagmanager['custom_dimension']) && $tagmanager['custom_dimension']) { for ($i = 1; $i <= 8; $i++) { $case_chercker = ''; if (isset($tagmanager['custom_dimension' . $i . '_text']) && $tagmanager['custom_dimension' . $i . '_text'] != 'disable') { $case_chercker = $tagmanager['custom_dimension' . $i . '_text']; ${'dimension_value' . $i} = false; switch ($case_chercker) {

case "ecomm_prodid": if (isset($_data['datalayer']['ecomm_prodid'])) { $y = 0; $dimvalue = ''; $ecom_array = $this->gtm->check_array($_data['datalayer']['ecomm_prodid']); if ($ecom_array){ foreach ($_data['datalayer']['ecomm_prodid'] as $dim) { if ($y > 0) { $dimvalue .= ','; } $dimvalue .= (isset($dim) ? $dim : false); $y++; } } else { $dimvalue = $_data['datalayer']['ecomm_prodid']; } } else { $dimvalue = false; } ${'dimension_value' . $i} = $dimvalue; break; case "ecomm_pagetype": ${'dimension_value' . $i} =(isset($_data['datalayer']['ecomm_pagetype']) ? $_data['datalayer']['ecomm_pagetype'] : false); break; case "ecomm_totalvalue": ${'dimension_value' . $i} = (isset($_data['datalayer']['ecomm_totalvalue']) ? $_data['datalayer']['ecomm_totalvalue'] : false); break; case "dynx_itemid": ${'dimension_value' . $i} = (isset($_data['datalayer']['dynx_itemid']) ? $_data['datalayer']['dynx_itemid'] : false); break; case "dynx_itemid2": ${'dimension_value' . $i} = (isset($_data['datalayer']['dynx_itemid2']) ? $_data['datalayer']['dynx_itemid2'] : false); break; case "dynx_pagetype": ${'dimension_value' . $i} = (isset($_data['datalayer']['dynx_pagetype']) ? $_data['datalayer']['dynx_pagetype'] : false); break; case "dynx_totalvalue": ${'dimension_value' . $i} = (isset($_data['datalayer']['dynx_totalvalue']) ? $_data['datalayer']['dynx_totalvalue'] : false); break; case "user_id": ${'dimension_value' . $i} = (isset($tagmanager['userid']) ? $tagmanager['userid'] : false); break; case "disable": ${'dimension_value' . $i} = false; break; }

} } for ($i = 1; $i <= 8; $i++) { if (isset($tagmanager['custom_dimension' . $i]) && $tagmanager['custom_dimension' . $i] !='0' &&  isset(${'dimension_value' . $i}) && ${'dimension_value' . $i}) { $setting_tags[] = array( 'dimension_index' . $i	=> $tagmanager['custom_dimension' . $i], 'dimension_text' . $i	=> ${'dimension_value' . $i}, ); } } }

if ($tagmanager['conversion_id'] && $tagmanager['adword'] == '1') { $setting_tags[] = array( 'adwordEnable'			=> $tagmanager['adword'], 'adwordConversionID'	=> $tagmanager['conversion_id'], 'adwordConversionLabel'=> $tagmanager['conversion_label'], 'adwordCurrency'		=> $tagmanager['currency'] ); if ($tagmanager['conversion_id2'] && $tagmanager['adword2'] == '1') { $setting_tags[] = array( 'adword2Enable'			=> $tagmanager['adword2'], 'adwordConversionID2'		=> $tagmanager['conversion_id2'], 'adwordConversionLabel2'	=> $tagmanager['conversion_label2'], 'adwordConversionValue2'	=> $tagmanager['conversion_value2'], ); } } if (isset($tagmanager['remarketing']) && $tagmanager['remarketing'] == '1') { $setting_tags[] = array( 'RemarketingEnable'	=> '1' ); } if (isset($tagmanager['userid']) &&  $tagmanager['userid_status'] == '1' ) { $setting_tags[] = array( 'userid'	=> $tagmanager['userid'] ); } if (isset($tagmanager['useremail']) && !empty($tagmanager['useremail']) ) { $setting_tags[] = array( 'um'	=> $tagmanager['useremail'] ); } if (isset($tagmanager['email']) && !empty($tagmanager['email']) ) { $setting_tags[] = array( 'ue'	=> $tagmanager['email'] ); }

if (isset($tagmanager['pixelcode']) && !empty($tagmanager['pixelcode']) && $tagmanager['pixel'] == '1') {

$event_id = '0-' . $this->gtm->eventid();

$setting_tags[] = array( 'facebookPixelID'		=> $tagmanager['pixelcode'], 'facebookPixel'		=> $tagmanager['pixel'], 'pageview_event_id'	=> $event_id, 'external_id'          => $tagmanager['external_id'], 'pixel_em'	            => $tagmanager['em'], );

if (isset($tagmanager['fb_catalog_id']) && !empty($tagmanager['fb_catalog_id'])) { $setting_tags[] = array( 'product_catalog_id'	=> $tagmanager['fb_catalog_id'], ); }

} $setting_tags[] = array( 'currencyCode'	=> $tagmanager['currency'], 'user_agent'	=> $tagmanager['user_agent'], 'store_country'	=> (isset($tagmanager['store_country']) ? $tagmanager['store_country'] : '') , );

if (isset($tagmanager['bing_uetid']) && !empty($tagmanager['bing_uetid'])  && $tagmanager['bing_status'] == '1') { $setting_tags[] = array( 'bingEnable'	=> '1', 'bingid'		=> $tagmanager['bing_uetid'] ); }

if (isset($tagmanager['snap_pixel_id']) && !empty($tagmanager['snap_pixel_id']) && $tagmanager['snap_pixel_status'] == '1'){ $setting_tags[] = array( 'snappixelenable'	=> '1', 'snappixelid'		=> $tagmanager['snap_pixel_id'] ); }

if (isset($tagmanager['hotjar_siteid']) && !empty($tagmanager['hotjar_siteid']) && $tagmanager['hotjar_status'] == '1'){ $setting_tags[] = array( 'hotjarenable'	=> '1', 'hotjarid'		=> $tagmanager['hotjar_siteid'] ); }

if (isset($tagmanager['google_optimize']) && !empty($tagmanager['google_optimize']) && $tagmanager['google_optimize_status'] == '1') { $setting_tags[] = array( 'goptimizeenable'	=> '1', 'goptimize'			=> $tagmanager['google_optimize'] ); }

if (isset($tagmanager['skroutz_siteid']) && !empty($tagmanager['skroutz_siteid']) && $tagmanager['skroutz_status'] == '1') { $setting_tags[] = array( 'SkroutzEnable'	=> '1', 'skroutzID' 	=> $tagmanager['skroutz_siteid'] ); }

if (isset($tagmanager['yandex_code']) && !empty($tagmanager['yandex_code']) && $tagmanager['yandex_status'] == '1' ) { $setting_tags[] = array( 'YandexEnable'	=> '1', 'YandexCode'	=> $tagmanager['yandex_code'] ); }

if (isset($tagmanager['paypal_code']) && !empty($tagmanager['paypal_code']) && $tagmanager['paypal_status'] == '1' ) { $setting_tags[] = array( 'paypal_status'		=> '1', 'paypal_code'		=> $tagmanager['paypal_code'] ); }

if (isset($tagmanager['twitter_tag']) && !empty($tagmanager['twitter_tag']) && $tagmanager['twitter_status'] == '1' ) { $setting_tags[] = array( 'twitter_status'	=> '1', 'twitter_tag'		=> $tagmanager['twitter_tag'] ); }

if (isset($tagmanager['pinterest_tag']) && !empty($tagmanager['pinterest_tag']) && $tagmanager['pinterest_status'] == '1' ) { $setting_tags[] = array( 'pinterest_status'	=> '1', 'pinterest_tag' 	=> $tagmanager['pinterest_tag'] ); }

if (isset($tagmanager['glami_code']) && !empty($tagmanager['glami_code']) && $tagmanager['glami_status'] == '1' ) { $setting_tags[] = array( 'GlamiEnable'	=> '1', 'glami_code'	=> $tagmanager['glami_code'] ); }

if (isset($tagmanager['clarity_id']) && !empty($tagmanager['clarity_id']) && $tagmanager['clarity_status'] == '1' ) { $setting_tags[] = array( 'clarity_status'	=> '1', 'clarity_id'		=> $tagmanager['clarity_id'] ); }

if (isset($tagmanager['alt_currency'])) { $setting_tags[] = array( 'alt_currency'	=> $tagmanager['alt_currency'] ); }

if (isset($tagmanager['ver'])) { $setting_tags[] = array( 'ver'	=> $tagmanager['ver'] ); }

if (isset($route)) { $setting_tags[] = array( 'r'		=> $org_route );

}

$tag_initiate = array();

foreach ($setting_tags as $result){ foreach ($result as $key => $value){ $tag_initiate[$key] = $value; } }

if (isset($tagmanager['server']) && $tagmanager['server'] && isset($tagmanager['server_url']) && !empty($tagmanager['server_url'])) { $server_url = $tagmanager['server_url']; } else { $server_url = 'www.googletagmanager.com'; }

$tmanalytics .= '<script type="text/javascript" nitro-exclude="">'; $tmanalytics .= "dataLayer.push(" . json_encode($tag_initiate) . " );</script>"; include('inc_gtm_main.php');

if(isset($tracking_block) && !$tracking_block){

if (isset($tagmanager['luckyorange_status']) && !empty($tagmanager['luckyorange_siteid']) && $tagmanager['luckyorange_status'] == '1' ) { include('inc_lucky_main.php'); }

if (isset($tagmanager['tiktok_status']) && !empty($tagmanager['tiktok_code']) && $tagmanager['tiktok_status'] == '1' ) { include('inc_tiktok.php'); }

}

if(isset($marketing_block) && !$marketing_block){

if (isset($tagmanager['admitad_code']) && !empty($tagmanager['admitad_code']) && $tagmanager['admitad_status'] == '1' ) { include('inc_admitad_main.php'); }

if (isset($tagmanager['sendinblue_status']) && !empty($tagmanager['sendinblue_code']) && $tagmanager['sendinblue_status'] == '1' ) { include('inc_sendinblue.php'); }

if (isset($tagmanager['linkwise_status']) && !empty($tagmanager['linkwise_code']) && $tagmanager['linkwise_status'] == '1' ) { include('inc_linkwise.php'); }

}

if (isset($tagmanager['pixelcode']) && $tagmanager['pixel'] == '1' && $tagmanager['fb_api']){ $this->gtm->pixelView('pageview',$event_id); }

switch ($page_type) {

case "success":

if ($tagmanager['pixel'] && $tagmanager['fb_api']) { if (isset($_data['fbdata']) && $_data['fbdata']) { $pixel_post = $this->gtm->pixelSetup((isset($_data['tagmanager']) ? $_data['tagmanager'] : $tagmanager),'Purchase',$_data['fbdata']); } }

if (isset($tagmanager['sendinblue_status']) && $tagmanager['sendinblue_status']) { if (isset($_data['sendinblue'])) { $this->gtm->sendinbluePost($_data['sendinblue'], 'trackEvent'); } }

if (isset($_data['gadata'])) { $tmanalytics .= '<script type="text/javascript" nitro-exclude="">'; $tmanalytics .= "dataLayer.push(" . json_encode($_data['gadata']) . ");</script>"; }

if (isset($_data['aw_ec_data'])) { if (isset($tagmanager['adword_ec']) && $tagmanager['adword_ec']) { $tmanalytics .= '<script type="text/javascript" nitro-exclude="">'; $tmanalytics .= "dataLayer.push(" . json_encode($_data['aw_ec_data']) . ");</script>"; } }

if (isset($_data['pixel_customer_data'])) { if (isset($tagmanager['pixel']) && $tagmanager['pixel']) { $tmanalytics .= '<script type="text/javascript" nitro-exclude="">'; $tmanalytics .= "dataLayer.push(" . json_encode($_data['pixel_customer_data']) . ");</script>"; } }


if (isset($_data['datalayer'])) { $tmanalytics .= '<script type="text/javascript" nitro-exclude="">'; $tmanalytics .= "dataLayer.push(" . json_encode($_data['datalayer']) . ");</script>"; } if ($tagmanager['conversion_id2'] && $tagmanager['adword'] == '1' && $tagmanager['adword2'] == '1' && $tagmanager['conversion_route2'] == 'purchase') { $tmanalytics .= '<script type="text/javascript" nitro-exclude="">'; $tmanalytics .= "dataLayer.push({'event'	: 'conversion2', 'adwordConversionValue2' : '" . $_data['revenue'] ."'});</script>"; }

$tmanalytics .= '<script type="text/javascript" nitro-exclude="">'; $tmanalytics .= "var initIntervalhit = setInterval(function(){ if(window.jQuery) { clearInterval(initIntervalhit); inithit(); } }, 5); function inithit(){" . "$.ajax({ url: 'index.php?route=extension/analytics/tagmanager/hitorder&oid=" . $_data['order_id'] . "&v=" . $tagmanager['vs'] ."'});" ."}</script>"."\n";

if(isset($tracking_block) && !$tracking_block){

if (!empty($tagmanager['skroutz_status']) && !empty($tagmanager['skroutz_siteid']) && isset($_data['skroutz_items'])) { $tmanalytics .= '<script type="text/javascript" nitro-exclude="">'; if (isset($_data['skroutz_items'])){ foreach ($_data['skroutz_items'] as $item) { $tmanalytics .= "whenAvailable(\"skroutz_analytics\", function(t) { skroutz_analytics('ecommerce', 'addItem', " . json_encode($item) . ");});"; } } $tmanalytics .= '</script>'; } }

if(isset($marketing_block) && !$marketing_block){

if (isset($tagmanager['performant_code']) && isset($tagmanager['performant_confirm']) && $tagmanager['performant_status']) { include('inc_2performant.php'); } if (isset($tagmanager['affgateway_code']) && $tagmanager['affgateway_status']) {

}

if (isset($tagmanager['admitad_code']) && !empty($tagmanager['admitad_code']) && $tagmanager['admitad_status'] == '1' ) { include('inc_admitad_success.php'); }

if (isset($tagmanager['admitad_retag_code5']) && !empty($tagmanager['admitad_retag_code5']) && $tagmanager['admitad_retag_status']) { include('inc_retag_success.php'); } if (isset($tagmanager['merchant_id']) && !empty($tagmanager['merchant_id']) && $tagmanager['greview'] == '1') { include('inc_google_review_success.php'); } }

break;

case "product":

$tmanalytics .= '<script type="text/javascript" nitro-exclude="">';

if (isset($_data['google_ec'])){ $tmanalytics .= "dataLayer.push(" . json_encode($_data['google_ec']) . ");"; }

if (isset($_data['datalayer'])){ $tmanalytics .= "dataLayer.push(" . json_encode($_data['datalayer']) . ");"; } /* if (isset($_data['related'])){ $tmanalytics .= "setTimeout(function() {dataLayer.push(" . json_encode($_data['related']) . ");}, delayInMilliseconds);"; } */ $tmanalytics .= '</script>';

if (isset($tagmanager['admitad_retag_code3']) && !empty($tagmanager['admitad_retag_code3']) && $tagmanager['admitad_retag_status']) { if(isset($marketing_block) && !$marketing_block){ include('inc_retag_product.php'); } }

if ($tagmanager['pixel'] && $tagmanager['fb_api'] && $_data['fb_data']) { $pixel_post = $this->gtm->pixelSetup($tagmanager,'ViewContent',$_data['fb_data']); }


break;

case "listing":

$tmanalytics .= '<script type="text/javascript" nitro-exclude="">';

if (!isset($_data['ttheme'])) { if (isset($_data['google_ec'])){ $count = 0; foreach ($_data['google_ec'] as $gdata){ if ($count > 0) { $tmanalytics .= "setTimeout(function() {dataLayer.push(" . json_encode($gdata) . ");}, delayInMilliseconds);"; } else { $tmanalytics .= "dataLayer.push(" . json_encode($gdata) . ");"; } $count++; } } if (isset($_data['datalayer'])){ $tmanalytics .= "dataLayer.push(" . json_encode($_data['datalayer']) . ");"; } }

$tmanalytics .= '</script>';

if (isset($tagmanager['admitad_retag_code2']) && !empty($tagmanager['admitad_retag_code2']) && $tagmanager['admitad_retag_status']) { if(isset($marketing_block) && !$marketing_block){ include('inc_retag_category.php'); } }

if ($tagmanager['pixel'] && $tagmanager['fb_api'] && $_data['fb_data']) { $pixel_post = $this->gtm->pixelSetup($tagmanager,$_data['fb_event'],$_data['fb_data']); }

break;

case "checkout":

$tmanalytics .= '<script type="text/javascript" nitro-exclude="">';

if (isset($_data['gadata'])){ $tmanalytics .= "dataLayer.push(" . json_encode($_data['gadata']) . ");"; } if (isset($_data['datalayer'])){ $tmanalytics .= "dataLayer.push(" . json_encode($_data['datalayer']) . ");"; }

$tmanalytics .= '</script>';

if ($tagmanager['pixel'] && $tagmanager['fb_api'] && $_data['fb_data']) { $pixel_post = $this->gtm->pixelSetup($tagmanager,'InitiateCheckout',$_data['fb_data']); }

if (isset($tagmanager['sendinblue_status']) && $tagmanager['sendinblue_status'] && $_data['sendinblue']) { $this->gtm->sendinbluePost($_data['sendinblue'], 'trackEvent'); }

break;

case "cart":

if (isset($this->session->data['addtocart'])) { $tmanalytics .= (!empty($this->session->data['addtocart']) ? $this->session->data['addtocart'] : ''); unset($this->session->data['addtocart']); if (isset($_data['datalayer'])){ $tmanalytics .= '<script type="text/javascript" nitro-exclude="">'; $tmanalytics .= "setTimeout(function() {dataLayer.push(" . json_encode($_data['datalayer']) . ");}, delayInMilliseconds);"; $tmanalytics .= '</script>'; } } else { if (isset($_data['datalayer'])){ $tmanalytics .= '<script type="text/javascript" nitro-exclude="">'; $tmanalytics .= "dataLayer.push(" . json_encode($_data['datalayer']) . ");"; $tmanalytics .= '</script>'; } }

if (isset($tagmanager['admitad_retag_code4']) && !empty($tagmanager['admitad_retag_code4']) && $tagmanager['admitad_retag_status']) { if(isset($marketing_block) && !$marketing_block){ include('inc_retag_cart.php'); } }

if ($tagmanager['pixel'] && $tagmanager['fb_api'] && $_data['fb_data']) { $pixel_post = $this->gtm->pixelSetup($tagmanager,'InitiateCheckout',$_data['fb_data']); }

if (isset($tagmanager['sendinblue_status']) && $tagmanager['sendinblue_status'] && $_data['sendinblue']) { $this->gtm->sendinbluePost($_data['sendinblue'], 'trackEvent'); }

break;

case "confirm":

$tmanalytics .= '<script type="text/javascript" nitro-exclude="">';

if (isset($_data['gadata'])){ $tmanalytics .= "dataLayer.push(" . json_encode($_data['gadata']) . ");"; }

if (isset($_data['datalayer'])){ $tmanalytics .= "dataLayer.push(" . json_encode($_data['datalayer']) . ");"; }

$tmanalytics .= '</script>';

break;

case "contact":

$tmanalytics .= '<script type="text/javascript" nitro-exclude="">';

$tmanalytics .= "dataLayer.push({'event' : 'contact', 'eventAction' : 'contact','eventLabel': 'contact'});";

$tmanalytics .= '</script>';

if ($tagmanager['conversion_id2'] && $tagmanager['adword'] == '1' && $tagmanager['adword2'] == '1' && $tagmanager['conversion_route2'] == 'contact') { $tmanalytics .= '<script type="text/javascript" nitro-exclude="">'; $tmanalytics .= "dataLayer.push({'event'	: 'conversion2', 'adwordConversionValue2' : '" . $tagmanager['conversion_value2'] ."'});</script>"; }

if ($tagmanager['fb_api']){ $this->gtm->pixelView('contact',false); }

break;

case "signup":

if ($tagmanager['conversion_id2'] && $tagmanager['adword'] == '1' && $tagmanager['adword2'] == '1' && $tagmanager['conversion_route2'] == 'signup') { $tmanalytics .= '<script type="text/javascript" nitro-exclude="">'; $tmanalytics .= "dataLayer.push({'event'	: 'conversion2', 'adwordConversionValue2' : '" . $tagmanager['conversion_value2'] ."'});</script>"; }

if ($tagmanager['fb_api']){ $this->gtm->pixelView('signup',false); }

break;

case "home":

$tmanalytics .= '<script type="text/javascript" nitro-exclude="">';

$tmanalytics .= "dataLayer.push({'event' : 'home', 'eventAction' : 'home','eventLabel': 'Home Page'});";

$tmanalytics .= '</script>';

if (isset($tagmanager['admitad_retag_code1']) && !empty($tagmanager['admitad_retag_code1']) && $tagmanager['admitad_retag_status']) { if(isset($marketing_block) && !$marketing_block){ include('inc_retag_home.php'); } }

break; }

$tmanalytics .= "<!-- End Google Tag Manager -->" ."\n"; }

if ($j3popup == 'quickview') { if ($page_type == 'product' && isset($_data['google_ec'])) { $tmanalytics .= "<!-- End Google Tag Manager -->" . "\n" . "<script>"; if (isset($_data['datalayer'])){ $tmanalytics .= "parent.dataLayer.push(" . json_encode($_data['datalayer']) . ");"; } $tmanalytics .= "</script>" . "\n" . "<!-- Google Tag Manager Extension -->"; } }

if(substr(VERSION,0,1)=='1' ) { $this->data['tmanalytics'] = $tmanalytics; } else { $data['tmanalytics'] = $tmanalytics; }

if (isset($this->data)) { $this->data['tmanalytics'] = $tmanalytics; $this->data['tagmanager'] = $tagmanager; $this->data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home'); } }


if (isset($this->request->get['debug'])) { if ($this->request->get['debug'] == 'log') {

} if ($this->request->get['debug'] == 'show') { echo '<pre>'; if(substr(VERSION,0,1)=='1' ) { print_r($this->data); } else { print_r($data); }

die; } if ($this->request->get['debug'] == 'twig') { if(substr(VERSION,0,1)=='1' ) { $this->data['twig_debug'] = true; } else { $data['twig_debug'] = true; }

} } ?>