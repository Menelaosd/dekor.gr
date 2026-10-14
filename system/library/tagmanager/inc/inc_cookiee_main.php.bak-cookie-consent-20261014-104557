<?php
/* v:9.6 06042022*/
$eu_cookie = array();
$lang = (int)$this->config->get('config_language_id');
if (!isset($lang) || empty($lang)) {
	$lang = 1;
}
if ($tm_cdn) {
	$tmanalytics .= '<script type="text/javascript" src="https://' . $cdn_url . '/jquery.cookieconsent2.min.js" nitro-exclude="" defer></script>';
} else {
	$tmanalytics .= '<script type="text/javascript" src="catalog/view/javascript/tagmanager/jquery.cookieconsent.min.js" nitro-exclude="" defer></script>';
}

$eu_cookie = array(
	'title'					=> false,
	'icon'					=> false,
   	'cookie_title'			=> $tagmanager['cookie_title_'.$lang],
	'cookie_text'			=> $tagmanager['cookie_text_'.$lang],
	'cookie_text2'			=> $tagmanager['cookie_text2_'.$lang],
	'cookie_link'			=> $tagmanager['cookie_link_'.$lang],
	'cookie_button1'		=> $tagmanager['cookie_button1_'.$lang],
	'cookie_button2'		=> $tagmanager['cookie_button2_'.$lang],
	'cookie_button3'		=> $tagmanager['cookie_button3_'.$lang],
	'cookie_bg_popup'   	=> (isset($tagmanager['cookie_bg_popup']) && !empty($tagmanager['cookie_bg_popup']) ? $tagmanager['cookie_bg_popup'] : '#3B3646'),
	'cookie_heading_color'	=> (isset($tagmanager['cookie_heading_color']) && !empty($tagmanager['cookie_heading_color']) ? $tagmanager['cookie_heading_color'] : '#EE4B5A'),
	'cookie_text_popup'		=> (isset($tagmanager['cookie_text_popup']) && !empty($tagmanager['cookie_text_popup']) ? $tagmanager['cookie_text_popup'] : 'white'),
	'cookie_bg_button'		=> (isset($tagmanager['cookie_bg_button']) && !empty($tagmanager['cookie_bg_button']) ? $tagmanager['cookie_bg_button'] : '#EE4B5A'),
	'cookie_badge_color'	=> (isset($tagmanager['cookie_badge_color']) && !empty($tagmanager['cookie_badge_color']) ? $tagmanager['cookie_badge_color'] : '#3B3646'),
	'cookie_text_button'	=> (isset($tagmanager['cookie_text_button']) && !empty($tagmanager['cookie_text_button']) ? $tagmanager['cookie_text_button'] : 'white')
);

if (isset($eu_cookie['cookie_title']) && !empty($eu_cookie['cookie_title'])) {
	$eu_cookie['title'] = true;
	$eu_cookie['icon'] = true;
}

if (empty($tagmanager['cookie_position'])) { 
	$tagmanager['cookie_position'] = 'Bottom Right'; 
}
if (empty($tagmanager['cookie_badge_position'])) { 
	$tagmanager['cookie_badge_position'] = 'bottom left'; 
}

$eu_css .= "<style>";
			
switch ($tagmanager['cookie_position']) {
	case "Top Bar":
		$eu_css .= "
		#gdpr-cookie-message {position: fixed;left: 0px;top: 0px;width: 100%;background-color: " . $eu_cookie['cookie_bg_popup'] . ";padding: 20px;z-index:9999;border-bottom: 1px solid #f1f1f0;}
		#gdpr-cookie-message p, #gdpr-cookie-message ul {padding-right:30px;}
		#gdpr-cookie-message p:last-child {margin-bottom: 0;text-align: right;padding-right: 20px;}
		.gdprcookiemessage {max-width: 75%; float: left;} .gdprcookiebuttons{float: right; padding-right: 2em;}
		#gdpr-cookie-types {clear:both;width:50%;padding-top:10px}
		#gdpr-cookie-message li {width: 30%;display: inline-block;}
		";
		
		break;
	case "Bottom Bar":
		$eu_css .= "#gdpr-cookie-message {position: fixed;left: 0px;bottom: 0px;width: 100%;background-color: " . $eu_cookie['cookie_bg_popup'] . ";padding: 20px;z-index:999999999999999999;border-top: 1px solid #f1f1f0;}
		#gdpr-cookie-message p, #gdpr-cookie-message ul {padding-right:30px;}
		#gdpr-cookie-message p:last-child {margin-bottom: 0;text-align: right;padding-right: 50px;}
		.gdprcookiemessage {max-width: 75%; float: left;} .gdprcookiebuttons{float: right; padding-right: 2em;}
		#gdpr-cookie-types {clear:both;width:50%;padding-top:10px}
		#gdpr-cookie-message li {width: 30%;display: inline-block;}
		";
		
		break;
	case "Bottom Left":
		$eu_css .= "#gdpr-cookie-message {position: fixed;left: 30px;bottom: 30px;max-width: 375px;background-color: " . $eu_cookie['cookie_bg_popup'] . ";padding: 20px;border-radius: 5px;box-shadow: 0 6px 6px rgba(0,0,0,0.25);margin-left: 30px;z-index:999999999999999999;}
		#gdpr-cookie-message p:last-child {margin-bottom: 0;text-align: right;}
		#gdpr-cookie-message li {width: 49%;display: inline-block;}";
		break;
	case "Bottom Right":
		$eu_css .= "#gdpr-cookie-message {position: fixed;right: 30px;bottom: 30px;max-width: 375px;background-color: " . $eu_cookie['cookie_bg_popup'] . ";padding: 20px;border-radius: 5px;box-shadow: 0 6px 6px rgba(0,0,0,0.25);margin-left: 30px;z-index:999999999999999999;}
		#gdpr-cookie-message p:last-child {margin-bottom: 0;text-align: right;}
		#gdpr-cookie-message li {width: 49%;display: inline-block;}";
		break;
}

if (isset($tagmanager['cookie_position_mobile'])) {
	switch ($tagmanager['cookie_position_mobile']) {
		case "Top Bar":
			$eu_css .= "@media screen and (max-width : 767px) {#gdpr-cookie-message {position: fixed;left: 0px;top: 0px;width: 100%;height:40vw;}}";
			break;
		case "Bottom Bar":
			$eu_css .= "@media screen and (max-width : 767px) {#gdpr-cookie-message {position: fixed;left: 0px;bottom: 30px;width: 100%;height:40vw;}}";
			break;
		case "Bottom Left":
			$eu_css .= "@media screen and (max-width : 767px) {#gdpr-cookie-message {position: fixed;left: 0px;bottom: 30px;width: 100%;}}";
			break;
		case "Bottom Right":
			$eu_css .= "@media screen and (max-width : 767px) {#gdpr-cookie-message {position: fixed;left: 0px;bottom: 30px;width: 100%;}}";
			break;
	}	
}
	
$eu_css .= "#gdpr-cookie-message h4 {" . (!$eu_cookie['title'] ? 'display:none;' : '') ."color: ". $eu_cookie['cookie_heading_color'] . ";font-size: 18px;font-weight: 500;margin-bottom: 10px;}#gdpr-cookie-message h5 {color: ". $eu_cookie['cookie_heading_color'] . ";font-size: 15px;font-weight: 500;margin-bottom: 10px;}
		#gdpr-cookie-message , #gdpr-cookie-message p, #gdpr-cookie-message ul {color: " . $eu_cookie['cookie_text_popup'] . ";font-size: 15px;line-height: 1.5em;} 
		#gdpr-cookie-message a {color: " . $eu_cookie['cookie_text_popup'] . ";text-decoration: none;font-size: 15px;padding-bottom: 2px;border-bottom: 1px dotted rgba(255,255,255,0.75);transition: all 0.3s ease-in;} 
		#gdpr-cookie-message a:hover {color: " . $eu_cookie['cookie_text_popup'] . ";border-bottom-color: #EE4B5A;transition: all 0.3s ease-in;} 
		#gdpr-cookie-message button,button#ihavecookiesBtn {border: none;background: " . $eu_cookie['cookie_bg_button'] .";color: " . $eu_cookie['cookie_text_button'] .";font-size: 15px;padding: 7px;border-radius: 3px;margin-left: 15px;cursor: pointer; transition: all 0.3s ease-in;}	
		#gdpr-cookie-message button:hover {background: " . $eu_cookie['cookie_text_button'] ."; color: " . $eu_cookie['cookie_bg_button'] .";transition: all 0.3s ease-in;} 
		button#gdpr-cookie-advanced {background: " . $eu_cookie['cookie_text_button'] .";border:1px solid ". $eu_cookie['cookie_bg_button'] .";color: " . $eu_cookie['cookie_bg_button'] .";} #gdpr-cookie-message button:disabled {opacity: 0.3;}	#gdpr-cookie-message input[type=\"checkbox\"] {float: none;margin-top: 0;margin-right: 5px;}";
		
	
switch ($tagmanager['cookie_badge_position']) {
	case "bottom left":
		$eu_css .= "div.cookie_button {width: 0;height: 0;position: fixed;z-index: 999;}div.cookie_button:hover {cursor: pointer;}div.cookie_button img {height: 25px;width: 25px;position: fixed;bottom: 10px;left: 10px;}div.cookie_button {border-bottom: 75px solid " . $eu_cookie['cookie_badge_color'] . ";border-right: 75px solid transparent;left: 0px; bottom: 0px;}";
		break;
	case "bottom right":
		$eu_css .= "div.cookie_button {width: 0;height: 0;position: fixed;z-index: 999;}div.cookie_button:hover {cursor: pointer;}div.cookie_button img {height: 25px;width: 25px;position: fixed;bottom: 10px;right: 10px;}div.cookie_button {border-bottom: 75px solid " . $eu_cookie['cookie_badge_color'] . ";border-left: 75px solid transparent;right: 0px; bottom: 0px;}";
		break;
}
		
$eu_css .= "</style>" ."\n";

$eu_js .= "var options = {
cookieTypes: [
            {
                type: 'Site Preferences',
                value: 'preferences',
                description: 'These are cookies that are related to your site preferences, e.g. remembering your username, site colours, etc.'
            },
            {
                type: 'Analytics',
                value: 'analytics',
                description: 'Cookies related to site visits, browser types, etc.'
            },
            {
                type: 'Marketing',
                value: 'marketing',
                description: 'Cookies related to marketing, e.g. newsletters, social media, etc'
            }
        ],
title: '&#x1F36A; " . str_replace('"', "", json_encode($eu_cookie['cookie_title'])) . "',
message: '" . str_replace('"', "", json_encode($eu_cookie['cookie_text'])) . "',
delay: 600,
expires: 30,
link: '" . $eu_cookie['cookie_link'] . "',onAccept: function(){
	dataLayer.push({'event': 'site','cc_site' : '1'});
	if ($.fn.gdprcookie.preference('analytics') === true) { gtag('consent', 'update', {'analytics_storage': 'granted'});var cc_analytics=1;}
	if ($.fn.gdprcookie.preference('marketing') === true) {gtag('consent', 'update', {'ad_storage': 'granted'});var cc_marketing=1;}
	},uncheckBoxes: false,acceptBtnLabel: '" . $eu_cookie['cookie_button1'] . "',advancedBtnLabel: '" . $eu_cookie['cookie_button2'] ."', moreInfoLabel: '" . $eu_cookie['cookie_button3'] ."',cookieTypesTitle: '" . $eu_cookie['cookie_text2'] ."',fixedCookieTypeLabel: 'Essential',fixedCookieTypeDesc: 'These are essential for the website to work correctly.',cookieTypes: [{type: 'Analytics',value: 'analytics',description: 'Cookies related to site visits, browser types, etc.',status: ' '},{type: 'Marketing',value: 'marketing',description: 'Cookies related to marketing, e.g. newsletters, social media, etc',status: 'checked'}],}";
$eu_js .= "\n" ;

$tmanalytics .= '<script type="text/javascript" nitro-exclude="">';
$tmanalytics .= $eu_js;
$tmanalytics .= '</script>';
if (isset($tagmanager['cookie_badge']) && $tagmanager['cookie_badge']=='1') {
	if ($tm_cdn) {
	$tmanalytics .= "<div onclick=\" $('body').gdprcookie(options, 'reinit');\" class=\"cookie_button\"><img src=\"https://" . $cdn_url . "/cookie.png\" alt=\"Cookies Settings\"></div>";
	} else {
	$tmanalytics .= "<div onclick=\" $('body').gdprcookie(options, 'reinit');\" class=\"cookie_button\"><img src=\"/catalog/view/javascript/tagmanager/cookie.png\" alt=\"Cookies Settings\"></div>";
	}
}
$tmanalytics .= $eu_css . "\n";

?>