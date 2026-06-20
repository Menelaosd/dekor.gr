<?php
/******************************************************
 * ADMIN
 * @package Google Tag Manager for OC1.5x, OC2x,3x
 * @version 9.6
 * @author Muhammad Akram
 * @link https://aits.xyz
 * @copyright Copyright (C)2021 aits.xyz All rights reserved.
 * @email:info@aits.pk. 
 * $date: 22 MAR 2022
*******************************************************/

class ModelExtensionModuleTagmanager extends Model {

    public function getTagmanger() {
		
		$tagmanager = array();
		$PREFIX = '';
		
		$cid = (isset($_COOKIE['_ga']) ? $_COOKIE['_ga'] : '');

		if(substr(VERSION,0,1)=='3' ) {
			$PREFIX = 'analytics_';
		}
		$vs = $this->getNewURL();
		$vs = base64_encode($vs);
		
		$tagmanager = array (
			'vs'					=> $vs
			);

		return $tagmanager;
		
	}
	
	public function getlang() {
		
		$languageVariables = array(
		    'heading_title','primary','entry_server','entry_server_url','text_edit','text_enabled','text_disabled','text_signup',
		    'text_about','text_about_cookie','text_version','heading_container','text_container','text_order','entry_primary',
			'entry_status','entry_ampcode','entry_ampstatus','entry_admin','entry_cache','entry_debug','entry_customer_data',
			'entry_gid','entry_ua_status','entry_ga4_status','entry_ga4_mid','entry_ga4_api','entry_mp','entry_custom_dimension1',
			'entry_custom_dimension2','entry_custom_dimension3','entry_custom_dimension4','entry_custom_dimension5',
			'entry_custom_dimension6','entry_custom_dimension7','entry_custom_dimension8','entry_custom_dimension9',
			'entry_custom_dimension','entry_google_optimize','entry_google_optimize_status','entry_greview','entry_greview_badge',
			'entry_merchant_id','entry_custom','entry_remarketing',	'entry_userid_status','entry_product','entry_ptitle',
			'entry_id_prefix','entry_id_suffix','entry_customcode',	'entry_adword','entry_adword2','entry_conversion_id',
			'entry_conversion_label','entry_adword_ec','entry_conversion_id2','entry_conversion_label2','entry_conversion_route2',
			'entry_conversion_value2','entry_aw_optional','entry_aw_merchant_id','entry_aw_feed_country','entry_aw_feed_language',
			'entry_dynx_itemid','entry_dynx_itemid2','entry_dynx_pagetype','entry_dynx_totalvalue','entry_ecomm_pagetype',
			'entry_ecomm_prodid','entry_ecomm_totalvalue','entry_pixel','entry_pixelcode','entry_fb_api','entry_fb_token',
			'entry_alt_currency','entry_alt_currency_status','entry_alt_currency_val','entry_fb_catalog_id','entry_twitter_status',
			'entry_twitter_tag','entry_pinterest_status','entry_pinterest_tag','entry_glami_status','entry_glami_code',
			'entry_hotjar_status','entry_hotjar_siteid','entry_luckyorange_status',	'entry_luckyorange_siteid',
			'entry_tiktok_status','entry_tiktok_code','entry_clarity_status','entry_clarity_siteid','entry_bing_status',
			'entry_bing_uetid',	'entry_skroutz_status',	'entry_skroutz_siteid',	'entry_skroutz_manual_tax',	'entry_skroutz_manual_tax_value',
			'entry_skroutz_payment_fee','entry_yandex_status','entry_yandex_code','entry_admitad_status','entry_admitad_code',
			'entry_admitad_category','entry_admitad_additional_type','entry_admitad_invoice_broker','entry_admitad_invoice_category',
			'entry_admitad_retag_status','entry_admitad_retag_code','entry_admitad_retag_code1','entry_admitad_retag_code2',
			'entry_admitad_retag_code3','entry_admitad_retag_code4','entry_admitad_retag_code5','entry_linkwise_status',
			'entry_linkwise_code','entry_linkwise_decimal','entry_freshchat_status','entry_freshchat_code','entry_freshchat_host',
			'entry_snap_pixel_status','entry_snap_pixel_id','entry_yandex_code','entry_performant_status','entry_performant_code',
			'entry_performant_confirm','entry_affgateway_status','entry_affgateway_code','entry_sendinblue_status',
			'entry_sendinblue_code','entry_eu_cookie','entry_eu_cookie_enforce','entry_cookie_position','entry_cookie_title',
			'entry_cookie_text','entry_cookie_text2','entry_cookie_link','entry_cookie_button1','entry_cookie_button2',
			'entry_cookie_button3','entry_cookie_bg_popup','entry_cookie_text_popup','entry_cookie_bg_button','entry_cookie_text_button',
			'entry_cookie_heading_color','entry_cookie_badge','entry_cookie_badge_position','entry_cookie_badge_color','entry_zopim_status',
			'entry_zopim_code','entry_zenchat_status','entry_zenchat_code','entry_zopimchat_status','entry_zopimchat_code',
			'entry_hubspot_status','entry_hubspot_code','entry_smartsupp_status','entry_smartsupp_code','entry_paypal_status',
			'entry_paypal_code','entry_route_checkout','entry_route_success','column_oid','column_status','column_action','help_ua',
			'help_server','help_server_url','help_ga4','help_ga4_api','help_gid','help_secondary','help_admin','help_conversion_id',
			'help_userid','help_conversion_label','help_conversion_value2','help_remarketing','help_product','help_ptitle',
			'help_mp','help_cache','help_ac','help_ac_value','help_custom',	'help_route','help_route_checkout',	'help_route_success',
			'help_id_prefix','help_id_suffix','help_customcode','help_custom_dim','help_aw','help_aw_ec','help_aw_secondary',
			'help_aw_optional','help_aw_merchant','help_aw_country','help_aw_language','help_aw_route',	'help_cenforce','help_ctitle',
			'help_ctext','help_clink','help_debug','tab_tab1','tab_tab2','tab_tab3','tab_tab4','tab_tab5','tab_tab6','tab_tab7',
			'tab_tab8','button_save','button_cancel','button_send','button_refund','error_permission','error_primary','error_secondary',
			'error_analytics','error_warrning','error_ga4','entry_debug_api','help_debug_api','entry_performant_tax','entry_performant_tax_value',
			'entry_performant_currency','help_customer_data','entry_pixel_test_code','help_pixel_test_code','entry_ajax','help_ajax','entry_pagecache','help_pagecache',
			'entry_cookie_position_mobile',
		);
		foreach ($languageVariables as $languageVariable) {
    		$data[$languageVariable] = $this->language->get($languageVariable);
		}
		return $data;
	}

	public function getTransactions($filter_data, $store_id) {
		
		$sql  = "SELECT o.id, o.order_id, o.cid, o.uid, o.ip, o.geoid, o.hit, o.ul,o.tid,o.currency_code,o.currency_id, os.order_status_id FROM `" . DB_PREFIX . "analytics_tracking` AS o LEFT JOIN `" . DB_PREFIX ."order` AS os ON o.order_id = os.order_id";
		$sql .= " WHERE os.order_status_id > 0 AND os.store_id = '" . $store_id . "'";
		$sql .= " ORDER BY id DESC";
		if (isset($filter_data['start']) || isset($filter_data['limit'])) {
			if ($filter_data['start'] < 0) {
				$filter_data['start'] = 0;
			}

			if ($filter_data['limit'] < 1) {
				$$filter_data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$filter_data['start'] . "," . (int)$filter_data['limit'];
		}
		$query = $this->db->query($sql);

		return $query->rows;
	}
	
	public function getTotalTransactions($data = array(),$store_id=0) {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order`";

		$sql .= " WHERE order_status_id > '0' AND store_id = '" . $store_id . "'";

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getSettingValue($key, $store_id = 0) {
		$ver = substr(VERSION,0,1);
		$sub_ver = substr(VERSION,0,3);
		
		if ($sub_ver == '2.0') {
			$this->load->model('setting/setting');
			$data = $this->model_setting_setting->getSetting($key,$store_id);
			if (isset($data[$key])) {
				$settings = json_encode($data[$key]);
			} else {
				$settings = false;
			}
			return $settings;
			
		} 
		
		$query = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");
			
		if ($query->num_rows) {
			return $query->row['value'];
		} else {
			return null;	
		}
	}
	
	public function getNewURL() {
		$url = false;
		$temp = $this->request->server['SERVER_NAME'];
		$explode = explode(".", $temp);
		$counter = $this->check_array($explode);
		if ($counter) {
			$i = count($explode);
			if ($i == 2) {
				$url = $explode[0] . '.' . $explode[1];
			} elseif ($i == 3) {
				if (strtolower($explode[0]) != 'www' ) {
					$url = $explode[0] . '.' . $explode[1] . '.' . $explode[2];
				} else {
					$url = $explode[1] . '.' . $explode[2];
				}
			} elseif ($i == 4) {
				$url = $explode[1] . '.' . $explode[2] . '.' . $explode[3];
			}
		}
		return $url;
	}
	
	public function check_array($var) {
        return is_array($var)
          || $var instanceof \Countable
          || $var instanceof \SimpleXMLElement
          || $var instanceof \ResourceBundle;
    }
    
    public function upgrade() {
    	
    	$data = array();
    	
    	if(substr(VERSION,0,1)=='3' ) {
			$PREFIX = 'analytics_';
			$ver = '3';
		} else {
			$ver = '2';
			$PREFIX = '';
		}
		
		if (!isset($this->request->get['store_id'])) {
			$store_id = 0;
		} else {
			$store_id = $this->request->get['store_id'];
		}
		
	    $postKeyArray = array($PREFIX . 'tagmanager_mp', $PREFIX . 'tagmanager_route_confirm',$PREFIX . 'tagmanager_status',$PREFIX . 'tagmanager_primary',$PREFIX . 'tagmanager_code', $PREFIX . 'tagmanager_server', $PREFIX . 'tagmanager_server_url',$PREFIX . 'tagmanager_admin',$PREFIX . 'tagmanager_cache',$PREFIX . 'tagmanager_debug',$PREFIX . 'tagmanager_debug_api',$PREFIX . 'tagmanager_customer_data',$PREFIX . 'tagmanager_ptitle',$PREFIX . 'tagmanager_product', $PREFIX . 'tagmanager_id_prefix',$PREFIX . 'tagmanager_id_suffix',$PREFIX . 'tagmanager_route_checkout',$PREFIX . 'tagmanager_route_success',$PREFIX . 'tagmanager_customcode',$PREFIX . 'tagmanager_ua_status',$PREFIX . 'tagmanager_gid',$PREFIX . 'tagmanager_userid_status',$PREFIX . 'tagmanager_custom_dimension',$PREFIX . 'tagmanager_custom_dimension1',$PREFIX . 'tagmanager_custom_dimension2',$PREFIX . 'tagmanager_custom_dimension3',$PREFIX . 'tagmanager_custom_dimension4',$PREFIX . 'tagmanager_custom_dimension5',$PREFIX . 'tagmanager_custom_dimension6',$PREFIX . 'tagmanager_custom_dimension7',$PREFIX . 'tagmanager_custom_dimension8',$PREFIX . 'tagmanager_custom_dimension1_text',$PREFIX . 'tagmanager_custom_dimension2_text',$PREFIX . 'tagmanager_custom_dimension3_text',$PREFIX . 'tagmanager_custom_dimension4_text',$PREFIX . 'tagmanager_custom_dimension5_text',$PREFIX . 'tagmanager_custom_dimension6_text',$PREFIX . 'tagmanager_custom_dimension7_text',$PREFIX . 'tagmanager_custom_dimension8_text',$PREFIX . 'tagmanager_ga4_status',$PREFIX . 'tagmanager_ga4_mid',$PREFIX . 'tagmanager_ga4_api',$PREFIX . 'tagmanager_adword',$PREFIX . 'tagmanager_conversion_id',$PREFIX . 'tagmanager_conversion_label',$PREFIX . 'tagmanager_adword_ec',$PREFIX . 'tagmanager_aw_optional',$PREFIX . 'tagmanager_aw_merchant_id',$PREFIX . 'tagmanager_aw_feed_country',$PREFIX . 'tagmanager_aw_feed_language',$PREFIX . 'tagmanager_adword2',$PREFIX . 'tagmanager_conversion_id2',$PREFIX . 'tagmanager_conversion_label2',$PREFIX . 'tagmanager_conversion_route2',$PREFIX . 'tagmanager_conversion_value2',$PREFIX . 'tagmanager_remarketing',$PREFIX . 'tagmanager_custom',$PREFIX . 'tagmanager_dynx_itemid',$PREFIX . 'tagmanager_dynx_itemid2',$PREFIX . 'tagmanager_dynx_pagetype',$PREFIX . 'tagmanager_dynx_totalvalue',$PREFIX . 'tagmanager_ecomm_pagetype',$PREFIX . 'tagmanager_ecomm_totalvalue',$PREFIX . 'tagmanager_ecomm_prodid',$PREFIX . 'tagmanager_google_optimize',$PREFIX . 'tagmanager_google_optimize_status',$PREFIX . 'tagmanager_greview',$PREFIX . 'tagmanager_greview_badge',$PREFIX . 'tagmanager_merchant_id',$PREFIX . 'tagmanager_pixel',$PREFIX . 'tagmanager_pixelcode',$PREFIX . 'tagmanager_fb_api',$PREFIX . 'tagmanager_fb_token',$PREFIX . 'tagmanager_fb_catalog_id',$PREFIX . 'tagmanager_alt_currency_status',$PREFIX . 'tagmanager_alt_currency',$PREFIX . 'tagmanager_snap_pixel_status',$PREFIX . 'tagmanager_snap_pixel_id',$PREFIX . 'tagmanager_twitter_status',$PREFIX . 'tagmanager_twitter_tag',$PREFIX . 'tagmanager_pinterest_status',$PREFIX . 'tagmanager_pinterest_tag',$PREFIX . 'tagmanager_paypal_status',$PREFIX . 'tagmanager_paypal_code',$PREFIX . 'tagmanager_yandex_status',$PREFIX . 'tagmanager_yandex_code',$PREFIX . 'tagmanager_zenchat_status',$PREFIX . 'tagmanager_zenchat_code',$PREFIX . 'tagmanager_freshchat_status',$PREFIX . 'tagmanager_freshchat_code',$PREFIX . 'tagmanager_freshchat_host',$PREFIX . 'tagmanager_zopimchat_status',$PREFIX . 'tagmanager_zopimchat_code',$PREFIX . 'tagmanager_hubspot_status',$PREFIX . 'tagmanager_hubspot_code',$PREFIX . 'tagmanager_smartsupp_status',$PREFIX . 'tagmanager_smartsupp_code',$PREFIX . 'tagmanager_admitad_status',$PREFIX . 'tagmanager_admitad_code', $PREFIX . 'tagmanager_admitad_category',$PREFIX . 'tagmanager_admitad_additional_type',$PREFIX . 'tagmanager_admitad_invoice_broker',$PREFIX . 'tagmanager_admitad_invoice_category',$PREFIX . 'tagmanager_admitad_retag_status',$PREFIX . 'tagmanager_admitad_retag_code1',$PREFIX . 'tagmanager_admitad_retag_code2',$PREFIX . 'tagmanager_admitad_retag_code3',$PREFIX . 'tagmanager_admitad_retag_code4',$PREFIX . 'tagmanager_admitad_retag_code5',$PREFIX . 'tagmanager_affgateway_status',$PREFIX . 'tagmanager_affgateway_code',$PREFIX . 'tagmanager_performant_status',$PREFIX . 'tagmanager_performant_code',$PREFIX . 'tagmanager_performant_confirm',$PREFIX . 'tagmanager_performant_tax',$PREFIX . 'tagmanager_performant_tax_value',$PREFIX . 'tagmanager_performant_currency',$PREFIX . 'tagmanager_performant_currency_status',$PREFIX . 'tagmanager_hotjar_status',$PREFIX . 'tagmanager_hotjar_siteid',$PREFIX . 'tagmanager_luckyorange_status',$PREFIX . 'tagmanager_luckyorange_siteid',$PREFIX . 'tagmanager_clarity_status',$PREFIX . 'tagmanager_clarity_siteid',$PREFIX . 'tagmanager_bing_status',$PREFIX . 'tagmanager_bing_uetid',$PREFIX . 'tagmanager_skroutz_status',$PREFIX . 'tagmanager_skroutz_siteid',$PREFIX . 'tagmanager_skroutz_manual_tax',$PREFIX . 'tagmanager_skroutz_manual_tax_value',$PREFIX . 'tagmanager_glami_status',$PREFIX . 'tagmanager_glami_code',$PREFIX . 'tagmanager_tiktok_status',$PREFIX . 'tagmanager_tiktok_code',$PREFIX . 'tagmanager_sendinblue_status',$PREFIX . 'tagmanager_sendinblue_code',$PREFIX . 'tagmanager_linkwise_status',$PREFIX . 'tagmanager_linkwise_code',$PREFIX . 'tagmanager_linkwise_decimal',$PREFIX . 'tagmanager_eu_cookie',$PREFIX . 'tagmanager_eu_cookie_enforce',$PREFIX . 'tagmanager_cookie_position',$PREFIX . 'tagmanager_cookie_bg_popup',$PREFIX . 'tagmanager_cookie_text_popup',$PREFIX . 'tagmanager_cookie_bg_button',$PREFIX . 'tagmanager_cookie_text_button',$PREFIX . 'tagmanager_cookie_heading_color',$PREFIX . 'tagmanager_cookie_badge',$PREFIX . 'tagmanager_cookie_badge_position',$PREFIX . 'tagmanager_cookie_badge_color',$PREFIX . 'tagmanager_ampcode',$PREFIX . 'tagmanager_ampstatus' );
	
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();

        foreach ($languages as $lang) {
        	$cookie_lang = array(
	        	$PREFIX . 'tagmanager_cookie_title_'.$lang['language_id'],
				$PREFIX . 'tagmanager_cookie_text_'.$lang['language_id'],
				$PREFIX . 'tagmanager_cookie_text2_'.$lang['language_id'],
				$PREFIX . 'tagmanager_cookie_link_'.$lang['language_id'],
				$PREFIX . 'tagmanager_cookie_button1_'.$lang['language_id'],
				$PREFIX . 'tagmanager_cookie_button2_'.$lang['language_id'],
				$PREFIX . 'tagmanager_cookie_button3_'.$lang['language_id'],
			);
			$postKeyArray = array_merge($postKeyArray, $cookie_lang);
        }
        
        $postKeyArray = array_merge($postKeyArray, $cookie_lang);
        
        foreach($postKeyArray as $key) {
            $value = str_replace($PREFIX."tagmanager_","",$key);
            if (isset($this->request->post[$PREFIX . 'tagmanager_status'])) {
                $data[$value] = $this->request->post[$key];
            } else {
                $data[$value] = $this->getSettingValue($key,$store_id);
            }
        }
        if (!isset($data['code'])) {
            $data['code'] = (isset($data['primary']) && !empty($data['primary']) ? $data['primary'] : ''); 
        }
        if (isset($data['code']) && empty($data['code'])) {
        	$data['code'] = (isset($data['primary']) && !empty($data['primary']) ? $data['primary'] : '');
        }
        $data['pmap'] = $data['product'];
        $data['clarity_id'] = $data['clarity_siteid'];
        
        
        return $data;
    }
    
}
?>