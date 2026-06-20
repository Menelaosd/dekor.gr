<?php /* v:9.6 06042022*/ $tmanalytics = ''; if ($this->gtm->isActive()) {

$tagmanager = $this->gtm->settings; $cdn_url = ''; $tm_cdn = false; $eu_css = ''; $eu_js = '';

$tmanalytics = '<!-- Google Tag Manager Extension-->';

$gdpr_analytics = 'granted'; $gdpr_marketing = 'granted'; $allowAdFeatures = 'false'; $cc_analytics = 0; $cc_marketing = 0; $tracking_block = false; $marketing_block = false;

if ($tagmanager['eu_cookie']) { include( DIR_SYSTEM .'library/tagmanager/inc/inc_cookiee_main.php'); if ($tagmanager['eu_cookie_enforce']) { $cc_enabled = 1; $tracking_block = true; $marketing_block = true; $gdpr_analytics = 'denied'; $gdpr_marketing = 'denied'; $allowAdFeatures = 'false'; } $cc_accepted = (isset($_COOKIE["cookieControl"]) ? $_COOKIE["cookieControl"] : false);

if (isset($_COOKIE["cookieControlPrefs"])) { $cc_data = (array) json_decode($_COOKIE["cookieControlPrefs"]); foreach ($cc_data as $cc_option) { if ($cc_option == 'analytics') { $cc_analytics = 1; $tracking_block = false; } if ($cc_option == 'marketing') { $cc_marketing = 1; $marketing_block = false; } } } }


$j3popup = (isset($this->request->get['popup']) ? $this->request->get['popup'] : '') ; if(substr(VERSION,0,1)=='1' ) { $this->data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home'); if ($this->data['route'] == 'journal2/quickview') { $j3popup = 'quickview'; } }

include('event_scripts.php');

if (isset($tagmanager['freshchat_code']) && !empty($tagmanager['freshchat_code']) && $tagmanager['freshchat_status'] == '1' ) { include('inc_freshchat_main.php'); }

if (isset($tagmanager['hubspot_code']) && !empty($tagmanager['hubspot_code']) && $tagmanager['hubspot_status'] == '1' ) { include('inc_hubspot_main.php'); }

if (isset($tagmanager['smartsupp_code']) && !empty($tagmanager['smartsupp_code']) && $tagmanager['smartsupp_status'] == '1' ) { include('inc_smartsupp_main.php'); }

if (isset($tagmanager['zenchat_code']) && !empty($tagmanager['zenchat_code']) && $tagmanager['zenchat_status'] == '1' ) { include('inc_zenchat_main.php'); }

if (isset($tagmanager['zopimchat_code']) && !empty($tagmanager['zopimchat_code']) && $tagmanager['zopimchat_status'] == '1' ) { include('inc_zopimchat_main.php'); }

if (isset($tagmanager['merchant_id']) && !empty($tagmanager['merchant_id']) && $tagmanager['greview'] == '1' && $tagmanager['greview_badge'] == '1') { if(isset($marketing_block) && !$marketing_block){ include('inc_google_review_badge.php'); } }

if(substr(VERSION,0,1)=='1' ) { $this->data['tagmanager'] = $tagmanager; $this->data['j3popup'] = $j3popup; $this->data['tmanalytics'] = $tmanalytics; } else { $data['tagmanager'] = $tagmanager; $data['j3popup'] = $j3popup; $data['tmanalytics'] = $tmanalytics; } if (isset($this->data)) { $this->data['tmanalytics'] = $tmanalytics; $this->data['tagmanager'] = $tagmanager; $this->data['j3popup'] = $j3popup; }

} ?>