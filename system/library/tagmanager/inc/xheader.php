<?php /* v:5.1 */ if (isset($this->session->data['tagmanager'])) { unset($this->session->data['tagmanager']); } $this->load->model('extension/module/tagmanager'); $tagmanager = $this->model_extension_module_tagmanager->getTagmanger();

if (isset($tagmanager['code']) && $tagmanager['status']=='1') {

$tmanalytics = ''; $eu_css = ''; $eu_js = ''; $cc_analytics = 0; $cc_marketing = 0; $cc_enabled = 0; $cc_data = array(); $tm_delay = 500;

if (isset($GLOBALS['tm'])) { $tm = $GLOBALS['tm']; } unset($GLOBALS['tm']); if (isset($GLOBALS['tm_m'])) { $tm_m = $GLOBALS['tm_m']; } unset($GLOBALS['tm_m']);

$this->load->model('account/customer'); $this->session->data['userid'] = $this->customer->getId(); $oc1 = false;

if (defined('JOURNAL3_ACTIVE')) { $this->data['ttheme'] = 'journal3'; }



if(substr(VERSION,0,1)=='3') {

$this->user = new Cart\User($this->registry); $this->data['tagmanager'] = $tagmanager; $this->data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home'); $user_logged = $this->user->isLogged();



} elseif (substr(VERSION,0,3)=='2.3' || substr(VERSION,0,3)=='2.2') {

$this->user = new Cart\User($this->registry); $this->data['tagmanager'] = $tagmanager; $this->data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home'); $user_logged = $this->user->isLogged();

} elseif (substr(VERSION,0,3)=='2.1' ) {

$this->user = new User($this->registry); $user_logged = $this->user->isLogged(); $this->data['tagmanager'] = $tagmanager; $this->data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home');

} elseif (substr(VERSION,0,3)=='2.0') {

$this->load->library('user'); $this->user = new User($this->registry); $user_logged = $this->user->isLogged(); $this->data['tagmanager'] = $tagmanager; $this->data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home');

} elseif (substr(VERSION,0,1)=='1' ) {

$oc1 = true; $this->load->library('user'); $this->user = new User($this->registry); $user_logged = $this->user->isLogged(); $this->data['tagmanager'] = $tagmanager; $this->data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home');

} else {

}

$page_type = (isset($tm['type']) ? $tm['type'] : ''); $j3popup = (isset($this->request->get['popup']) ? $this->request->get['popup'] : '') ; $tmdata = array(); $route = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home'); $org_route = $route;

if (in_array($route, $tagmanager['route_success'])) { $route = 'checkout/success'; }

if (in_array($route, $tagmanager['route_confirm'])) { $route = 'checkout/confirm'; }

if (in_array($route, $tagmanager['route_checkout'])) { $route = 'checkout/checkout'; }

if ($route == 'product/quickview' || $route == 'extension/module/pavquickview/show' || $route == 'journal2/quickview' ) { $j3popup ='quickview'; }

if ($page_type == 'product'){ $tmdata = $this->model_extension_module_tagmanager->prepareProduct($tm); }

if ($page_type == 'listing'){ $tmdata = $this->model_extension_module_tagmanager->prepareProducts($tm); }

switch ($route) { case "checkout/cart": if ($this->cart->hasProducts()) { $tmdata = $this->model_extension_module_tagmanager->prepareCart(); $page_type = 'cart'; } break;

case "checkout/checkout": $prepare = array( 'page' => 'checkout', 'step' => '1', 'mode' => 'checkout' );

$this->session->data['steps'] = 1; $this->session->data['reload_check'] = array();

if ($this->cart->hasProducts()) { $tmdata = $this->model_extension_module_tagmanager->prepareCheckout($prepare); $page_type = 'checkout'; } break;

case "checkout/confirm": if (isset($this->session->data['steps'])) { $this->session->data['steps'] ++;

} else { $this->session->data['steps'] = 2; }

$this->session->data['reload_check'] = array();

$prepare = array( 'page' => 'confirm', 'step' => $this->session->data['steps'], 'mode' => 'confirm' ); if ($this->cart->hasProducts()) { $tmdata = $this->model_extension_module_tagmanager->prepareConfirm($prepare); $page_type = 'confirm'; } break;

case "checkout/success": if (isset($this->session->data['tm_order_id']) && !empty($this->session->data['tm_order_id'])) { $order_id = $this->session->data['tm_order_id']; $order_data = $this->model_extension_module_tagmanager->prepareOrder($order_id); $order_status_id = $this->model_extension_module_tagmanager->OrderStatusCheck($order_id);

if ($order_status_id == '0') {

}

if (isset($order_data['hit']) && $order_data['hit'] == '0') { $tmdata = $order_data; $page_type = 'success'; if(substr(VERSION,0,1)=='1' ) { $this->data['order_data'] = $order_data; } else { $this->data['order_data'] = $order_data; } } $this->log->write('Tagmanager Debug Log: Order Success Called [ Order: ' . $order_id . ' ] Result: success order'); } break;

}

if (isset($tmdata)) { if (isset($tmdata['tmerror']) && $tmdata['tmerror'] =='false') { $_data = $tmdata; } }

if (empty($j3popup)) {

$tmanalytics .= '<!-- Google Tag Manager -->';

if ($tagmanager['eu_cookie'] == '1') { $tmanalytics .= '<script type="text/javascript" src="catalog/view/javascript/jquery/cookieconsent/jquery.cookieconsent.min.js" nitro-exclude="" defer></script>'; $eu_css .= "<style>"; if (empty($tagmanager['cookie_position'])) { $tagmanager['cookie_position'] = 'left'; } switch ($tagmanager['cookie_position']) { case "top": $eu_css .= "#gdpr-cookie-message {position: fixed;left: 0px;top: 0px;width: 100%;background-color: " . (isset($tagmanager['cookie_bg_popup']) && !empty($tagmanager['cookie_bg_popup']) ? $tagmanager['cookie_bg_popup'] : '#3B3646'). ";padding: 20px;z-index:9999;}#gdpr-cookie-message p:last-child {margin-bottom: 0;text-align: right;padding-right: 50px;margin-top: -50px;}"; break; case "bottom": $eu_css .= "#gdpr-cookie-message {position: fixed;left: 0px;bottom: 0px;width: 100%;background-color: " . (isset($tagmanager['cookie_bg_popup']) && !empty($tagmanager['cookie_bg_popup']) ? $tagmanager['cookie_bg_popup'] : '#3B3646'). ";padding: 20px;z-index:9999;}#gdpr-cookie-message p:last-child {margin-bottom: 0;text-align: right;padding-right: 50px;margin-top: -50px;}"; break; case "right": $eu_css .= "#gdpr-cookie-message {position: fixed;left: 30px;bottom: 30px;max-width: 375px;background-color: " . (isset($tagmanager['cookie_bg_popup']) && !empty($tagmanager['cookie_bg_popup']) ? $tagmanager['cookie_bg_popup'] : '#3B3646'). ";padding: 20px;border-radius: 5px;box-shadow: 0 6px 6px rgba(0,0,0,0.25);margin-left: 30px;z-index:9999;}#gdpr-cookie-message p:last-child {margin-bottom: 0;text-align: right;}"; break; case "left": $eu_css .= "#gdpr-cookie-message {position: fixed;right: 30px;bottom: 30px;max-width: 375px;background-color: " . (isset($tagmanager['cookie_bg_popup']) && !empty($tagmanager['cookie_bg_popup']) ? $tagmanager['cookie_bg_popup'] : '#3B3646'). ";padding: 20px;border-radius: 5px;box-shadow: 0 6px 6px rgba(0,0,0,0.25);margin-left: 30px;z-index:9999;}#gdpr-cookie-message p:last-child {margin-bottom: 0;text-align: right;}"; break; } $eu_css .= "#gdpr-cookie-message h4 {color: ". (isset($tagmanager['cookie_heading_color']) && !empty($tagmanager['cookie_heading_color']) ? $tagmanager['cookie_heading_color'] : '#EE4B5A') . ";font-size: 18px;font-weight: 500;margin-bottom: 10px;}#gdpr-cookie-message h5 {color: ". (isset($tagmanager['cookie_heading_color']) && !empty($tagmanager['cookie_heading_color']) ? $tagmanager['cookie_heading_color'] : '#EE4B5A') . ";font-size: 15px;font-weight: 500;margin-bottom: 10px;} #gdpr-cookie-message p, #gdpr-cookie-message ul {color: " . (isset($tagmanager['cookie_text_popup']) && !empty($tagmanager['cookie_text_popup']) ? $tagmanager['cookie_text_popup'] : 'white') . ";font-size: 15px;line-height: 1.5em;} #gdpr-cookie-message li {width: 49%;display: inline-block;} #gdpr-cookie-message a {color: #EE4B5A;text-decoration: none;font-size: 15px;padding-bottom: 2px;border-bottom: 1px dotted rgba(255,255,255,0.75);transition: all 0.3s ease-in;} #gdpr-cookie-message a:hover {color: white;border-bottom-color: #EE4B5A;transition: all 0.3s ease-in;} #gdpr-cookie-message button,button#ihavecookiesBtn {border: none;background: " . (isset($tagmanager['cookie_bg_button']) && !empty($tagmanager['cookie_bg_button']) ? $tagmanager['cookie_bg_button'] : '#EE4B5A') .";color: " . (isset($tagmanager['cookie_text_button']) && !empty($tagmanager['cookie_text_button']) ? $tagmanager['cookie_text_button'] : 'white') .";font-size: 15px;padding: 7px;border-radius: 3px;margin-left: 15px;cursor: pointer; transition: all 0.3s ease-in;}	#gdpr-cookie-message button:hover {background: " . (isset($tagmanager['cookie_text_button']) && !empty($tagmanager['cookie_text_button']) ? $tagmanager['cookie_text_button'] : 'white') .";color: " . (isset($tagmanager['cookie_bg_button']) && !empty($tagmanager['cookie_bg_button']) ? $tagmanager['cookie_bg_button'] : '#EE4B5A') .";transition: all 0.3s ease-in;} button#gdpr-cookie-advanced {background: " . (isset($tagmanager['cookie_text_button']) && !empty($tagmanager['cookie_text_button']) ? $tagmanager['cookie_text_button'] : 'white') .";color: " . (isset($tagmanager['cookie_bg_button']) && !empty($tagmanager['cookie_bg_button']) ? $tagmanager['cookie_bg_button'] : '#EE4B5A') .";} #gdpr-cookie-message button:disabled {opacity: 0.3;}	#gdpr-cookie-message input[type=\"checkbox\"] {float: none;margin-top: 0;margin-right: 5px;}";

if (empty($tagmanager['cookie_badge_position'])) { $tagmanager['cookie_badge_position'] = 'bottom left'; }

switch ($tagmanager['cookie_badge_position']) { case "bottom left": $eu_css .= "div.cookie_button {width: 0;height: 0;position: fixed;z-index: 999;}div.cookie_button:hover {cursor: pointer;}div.cookie_button img {height: 25px;width: 25px;position: fixed;bottom: 10px;left: 10px;}div.cookie_button {border-bottom: 75px solid " . (isset($tagmanager['cookie_badge_color']) && !empty($tagmanager['cookie_badge_color']) ? $tagmanager['cookie_badge_color'] : '#3B3646'). ";border-right: 75px solid transparent;left: 0px; bottom: 0px;}"; break; case "bottom right": $eu_css .= "div.cookie_button {width: 0;height: 0;position: fixed;z-index: 999;}div.cookie_button:hover {cursor: pointer;}div.cookie_button img {height: 25px;width: 25px;position: fixed;bottom: 10px;right: 10px;}div.cookie_button {border-bottom: 75px solid " . (isset($tagmanager['cookie_badge_color']) && !empty($tagmanager['cookie_badge_color']) ? $tagmanager['cookie_badge_color'] : '#3B3646'). ";border-left: 75px solid transparent;right: 0px; bottom: 0px;}"; break; }

$eu_css .= "</style>" ."\n";

$eu_js .= "var options = {title: '&#x1F36A; " . (!empty($tagmanager['cookie_title']) ? $tagmanager['cookie_title'] : 'Cookies') . "',message: '" . (!empty($tagmanager['cookie_text']) ? $tagmanager['cookie_text'] : 'We use cookies to give you the best online experience. By using our website you agree to our use of cookies in accordance with our Cookie Policy') . "',delay: 600,expires: 30,link: '" . $tagmanager['cookie_link'] . "',onAccept: function(){ dataLayer.push({'event': 'site','cc_site' : '1'}); if ($.fn.gdprcookie.preference('analytics') === true) { dataLayer.push({'event': 'analytics','cc_analytics' : '1'});var cc_analytics=1;} if ($.fn.gdprcookie.preference('marketing') === true) {dataLayer.push({'event': 'marketing','cc_marketing' : '1'});var cc_marketing=1;} },uncheckBoxes: false,acceptBtnLabel: '" . (!empty($tagmanager['cookie_button1']) ? $tagmanager['cookie_button1'] : 'Accept Cookies') . "',advancedBtnLabel: '" . (!empty($tagmanager['cookie_button1']) ? $tagmanager['cookie_button2'] : 'Customise Cookies') ."', moreInfoLabel: '" . (!empty($tagmanager['cookie_button3']) ? $tagmanager['cookie_button3'] : 'More information') ."',cookieTypesTitle: '" . (!empty($tagmanager['cookie_text2']) ? $tagmanager['cookie_text2'] : 'Select which cookies you want to accept') ."',fixedCookieTypeLabel: 'Essential',fixedCookieTypeDesc: 'These are essential for the website to work correctly.',cookieTypes: [{type: 'Analytics',value: 'analytics',description: 'Cookies related to site visits, browser types, etc.',status: ' '},{type: 'Marketing',value: 'marketing',description: 'Cookies related to marketing, e.g. newsletters, social media, etc',status: 'checked'}],}"; $eu_js .= "\n"."$(document).ready(function() { $('body').gdprcookie(options); if ($.fn.gdprcookie.preference('analytics') === true) { dataLayer.push({'event': 'analytics','cc_analytics' : '1'});var cc_analytics=1; } if ($.fn.gdprcookie.preference('marketing') === true) { dataLayer.push({'event': 'marketing','cc_marketing' : '1'});var cc_marketing=1; } $('#cookieconsent').on('click', function(){ $('body').gdprcookie(options, 'reinit'); }); });" . "\n" ; }

$tmanalytics .= $eu_css . "\n";

$tmanalytics .= '<script type="text/javascript" nitro-exclude=""> var dataLayer = window.dataLayer = window.dataLayer || [];var delayInMilliseconds = ' . $tm_delay . '; '. 'function whenAvailable(name, callback) {var interval = 10; window.setTimeout(function() {if (window[name]) {callback(window[name]);} else {window.setTimeout(arguments.callee, interval);}}, interval);}' . "\n";

$tmanalytics .= $eu_js;

$cc_accepted = (isset($_COOKIE["cookieControl"]) ? $_COOKIE["cookieControl"] : false);

if (isset($_COOKIE["cookieControlPrefs"])) { $cc_data = (array) json_decode($_COOKIE["cookieControlPrefs"]); foreach ($cc_data as $cc_option) { if ($cc_option == 'analytics') { $cc_analytics = 1; } if ($cc_option == 'marketing') { $cc_marketing = 1; } }					}

if ($tagmanager['eu_cookie_enforce'] == '1') { $cc_enabled = 1; }

$tmanalytics .= "dataLayer.push({ 'cc_enabled' : '" . $cc_enabled ."','cc_accepted' : '" . $cc_accepted ."','cc_analytics' : '" . $cc_analytics ."','cc_marketing' : '". $cc_marketing . "',"; if (isset($user_logged) && $tagmanager['admin'] =='1') { $tmanalytics .= "'event': 'adminVisit','admin' : '1',"; }	if ($tagmanager['conversion_id'] && $tagmanager['adword'] == '1') { $tmanalytics .= "'adwordEnable' : '" . $tagmanager['adword'] ."','adwordConversionID' : '" . $tagmanager['conversion_id'] ."',"; if (!empty($tagmanager['conversion_label'])) { $tmanalytics .= "'adwordConversionLabel' : '" . $tagmanager['conversion_label'] ."','adwordCurrency':'" . $tagmanager['currency'] ."',"; } } if (isset($tagmanager['remarketing']) && $tagmanager['remarketing'] == '1') { $tmanalytics .= "'RemarketingEnable' : '1',"; }

if (isset($tagmanager['userid']) &&  $tagmanager['userid_status'] == '1' ) { $tmanalytics .= "'userid' : '" . $tagmanager['userid'] . "',"; }	

if (isset($tagmanager['pixelcode']) && !empty($tagmanager['pixelcode']) && $tagmanager['pixel'] == '1') { $tmanalytics .= "'facebookPixelID' : '" . $tagmanager['pixelcode'] . "','facebookPixel' : '" . $tagmanager['pixel'] . "',"; if (isset($tagmanager['fb_catalog_id']) && !empty($tagmanager['fb_catalog_id'])) { $tmanalytics .= "'product_catalog_id' : '" . $tagmanager['fb_catalog_id'] . "',"; } } $tmanalytics .= "'currencyCode': '" . $tagmanager['currency'] ."'";

if (isset($tagmanager['bing_uetid']) && !empty($tagmanager['bing_uetid'])  && $tagmanager['bing_status'] == '1') { $tmanalytics .= ",'bingEnable' : '1','bingid' : '" . $tagmanager['bing_uetid'] . "'"; }

if (isset($tagmanager['hotjar_siteid']) && !empty($tagmanager['hotjar_siteid']) && $tagmanager['hotjar_status'] == '1'){ $tmanalytics .= ",'hotjarenable' : '1','hotjarid' : '" . $tagmanager['hotjar_siteid'] . "'"; }

if (isset($tagmanager['goptimize']) && !empty($tagmanager['goptimize']) && $tagmanager['goptimize_status'] == '1') { $tmanalytics .= ",'goptimizeenable' : '1','goptimize' : '" . $tagmanager['goptimize'] ."'"; }

if (isset($tagmanager['gid'])) { $tmanalytics .= ",'gid' : '" . $tagmanager['gid'] . "'"; }	

if (isset($tagmanager['skroutz_siteid']) && !empty($tagmanager['skroutz_siteid']) && $tagmanager['skroutz_status'] == '1') { $tmanalytics .= ",'SkroutzEnable' : '1', 'skroutzID' : '" . $tagmanager['skroutz_siteid'] . "'"; }

if (isset($tagmanager['yandex_code']) && !empty($tagmanager['yandex_code']) && $tagmanager['yandex_status'] == '1' ) { $tmanalytics .= ",'YandexEnable' : '1', 'YandexCode' : '" . $tagmanager['yandex_code'] . "'"; }

if (isset($tagmanager['zenchat_code']) && !empty($tagmanager['zenchat_code']) && $tagmanager['zenchat_status'] == '1' ) { $tmanalytics .= ",'zenchatStatus' : '1','zenchatCode' : '" . $tagmanager['zenchat_code'] ."'"; }

if (isset($tagmanager['alt_currency'])) { $tmanalytics .= ",'alt_currency' : '" . $tagmanager['alt_currency'] . "'"; }

if (isset($tagmanager['ver'])) { $tmanalytics .= ",'ver' : '" . $tagmanager['ver'] . "'"; }

if (isset($route)) { $tmanalytics .= ",'r' : '" . $org_route . "'"; }

$tmanalytics .= " });</script>";

$tmanalytics .= "<script nitro-exclude=\"\">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src= 'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f); })(window,document,'script','dataLayer','" . $tagmanager['code'] ."');</script>";

$tmanalytics .= '<script type="text/javascript" nitro-exclude="">';

if (isset($_data['gadata']) && $page_type == 'success'){ $tmanalytics .= "dataLayer.push(" . json_encode($_data['gadata']) . ");"; if ($tagmanager['pixel'] == '1' && isset($_data['fbpixel'])) { $tmanalytics .= "dataLayer.push(" . json_encode($_data['fbpixel']) . ");"; } if (!empty($tagmanager['skroutz_status']) && !empty($tagmanager['skroutz_siteid']) && isset($_data['skroutz_order'])) { $tmanalytics .= "dataLayer.push({'event':'skroutz','eventLabel':'skroutz','eventAction':'addOrder', 'skroutzOrder': " . json_encode($_data['skroutz_order']) . "});"; if (isset($_data['skroutz_items'])){ foreach ($_data['skroutz_items'] as $item) { $tmanalytics .= "whenAvailable(\"skroutz_analytics\", function(t) { skroutz_analytics('ecommerce', 'addItem', " . json_encode($item) . ");});"; } } } if (!empty($tagmanager['conversion_id']) && $tagmanager['adword'] == '1') { $tmanalytics .= "dataLayer.push(" . json_encode($_data['adword']) . ");"; if ($tagmanager['remarketing'] == '1' && $tagmanager['adword'] == '1' ) { $tmanalytics .= "setTimeout(function() {dataLayer.push(" . json_encode($_data['remarketing']) . ");}, delayInMilliseconds);"; } } $tmanalytics .= "window.onload = function () {" . "$.ajax({ url: 'index.php?route=extension/module/tagmanager/hitorder&oid=" . $_data['order_id'] . "&v=" . $tagmanager['vs'] ."'});" ."}"; }

if ($page_type == 'product' && isset($_data['google_ec'])) { if (isset($_data['google_ec'])){ $tmanalytics .= "dataLayer.push(" . json_encode($_data['google_ec']) . ");";

} if ($tagmanager['pixel'] == '1') { $tmanalytics .= "dataLayer.push(" . json_encode($_data['fbpixel']) . ");"; } if (isset($_data['related'])){ $tmanalytics .= "setTimeout(function() {dataLayer.push(" . json_encode($_data['related']) . ");}, delayInMilliseconds);"; } }

if ($page_type == 'listing' && isset($_data['google_ec'])  && !isset($_data['ttheme'])) { if (isset($_data['google_ec'])){ $count = 0; foreach ($_data['google_ec'] as $gdata){ if ($count > 0) { $tmanalytics .= "setTimeout(function() {dataLayer.push(" . json_encode($gdata) . ");}, delayInMilliseconds);"; } else { $tmanalytics .= "dataLayer.push(" . json_encode($gdata) . ");";	} $count++; } } if ($tagmanager['pixel'] == '1') { $tmanalytics .= "dataLayer.push(" . json_encode($_data['fbpixel']) . ");"; } if ($tagmanager['remarketing'] == '1' && $tagmanager['adword'] == '1' && isset($_data['google_ads'])) { $tmanalytics .= "setTimeout(function() {dataLayer.push(" . json_encode($_data['google_ads']) . ");}, delayInMilliseconds);"; } }

if ($org_route == 'checkout/cart' && $tagmanager['remarketing'] == '1' && $tagmanager['adword'] == '1' && isset($_data['google_ads'])){ $tmanalytics .= "dataLayer.push(" . json_encode($_data['google_ads']) . ");"; }

if ($page_type == 'checkout' && isset($_data['gadata'])){ if (isset($_data['gadata'])){ $tmanalytics .= "dataLayer.push(" . json_encode($_data['gadata']) . ");"; } if ($tagmanager['pixel'] == '1') { $tmanalytics .= "dataLayer.push(" . json_encode($_data['fbpixel']) . ");"; } if ($tagmanager['remarketing'] == '1' && $tagmanager['adword'] == '1' && isset($_data['remarketing'])) { $tmanalytics .= "setTimeout(function() {dataLayer.push(" . json_encode($_data['remarketing']) . ");}, delayInMilliseconds);"; } }

if ($page_type == 'confirm' && isset($_data['gadata'])){ if (isset($_data['gadata'])){ $tmanalytics .= "dataLayer.push(" . json_encode($_data['gadata']) . ");"; } if ($tagmanager['pixel'] == '1') { $tmanalytics .= "dataLayer.push(" . json_encode($_data['fbpixel']) . ");"; } if ($tagmanager['remarketing'] == '1' && $tagmanager['adword'] == '1' && isset($_data['remarketing'])) { $tmanalytics .= "setTimeout(function() {dataLayer.push(" . json_encode($_data['remarketing']) . ");}, delayInMilliseconds);"; } }

if (isset($tm_m) && $page_type != 'confirm' && $page_type != 'checkout' && $page_type != 'success'){ $google_ecm = $this->model_extension_module_tagmanager->prepareModuleProducts($tm_m); $count = 0; foreach ($google_ecm as $ecm) { if ($count > 0 ) { $tmanalytics .= "setTimeout(function() { dataLayer.push(" . json_encode($ecm) . "); }, delayInMilliseconds);"; } else { $tmanalytics .= "dataLayer.push(" . json_encode($ecm) . ");"; }		

$count++;					 } }

if ($org_route == 'common/home' && $tagmanager['remarketing'] == '1' && $tagmanager['adword'] == '1'){ $tmanalytics .= "setTimeout(function() {dataLayer.push({'event' : 'remarketing', 'eventAction' : 'remarketing','eventLabel': 'home'});}, delayInMilliseconds);"; }

if ($org_route == 'information/contact'){ $tmanalytics .= "dataLayer.push({'event' : 'contact', 'eventAction' : 'contact','eventLabel': 'contact'});"; }

$tmanalytics .= "</script>" . "\n" . "<!-- End Google Tag Manager -->"; }



if ($j3popup == 'quickview') { if ($page_type == 'product' && isset($_data['google_ec'])) { $tmanalytics .= "<!-- End Google Tag Manager -->" . "\n" . "<script>"; if (isset($_data['google_ec'])){ $tmanalytics .= "parent.dataLayer.push(" . json_encode($_data['google_ec']) . ");"; } if ($tagmanager['pixel'] == '1') { $tmanalytics .= "parent.dataLayer.push(" . json_encode($_data['fbpixel']) . ");"; } if ($tagmanager['remarketing'] == '1' && $tagmanager['adword'] == '1' && isset($_data['google_ads'])) { $tmanalytics .= "setTimeout(function() {parent.dataLayer.push(" . json_encode($_data['google_ads']) . ");}, delayInMilliseconds);"; } $tmanalytics .= "</script>" . "\n" . "<!-- End Google Tag Manager -->";	} }

if(substr(VERSION,0,1)=='1' ) { $this->data['tmanalytics'] = $tmanalytics; } else { $this->data['tmanalytics'] = $tmanalytics; } }



if (isset($this->request->get['debug'])) { if ($this->request->get['debug'] == 'log') {

} if ($this->request->get['debug'] == 'show') { echo '<pre>'; if(substr(VERSION,0,1)=='1' ) { print_r($this->data); } else { print_r($this->data); }

die; } if ($this->request->get['debug'] == 'twig') { if(substr(VERSION,0,1)=='1' ) { $this->data['twig_debug'] = true; } else { $this->data['twig_debug'] = true; }

} } ?>