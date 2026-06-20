<?php
class ModelExtensionModuleiSenseLabsSeo extends Model {  
    
    public function initDb($store_id = '0') {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_module_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `store_id` int(11) NOT NULL DEFAULT '0',
            `key` varchar(255) NOT NULL,
            `value` text NOT NULL,
            PRIMARY KEY (`id`)
        ) CHARACTER SET utf8 COLLATE utf8_general_ci");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_autolinks` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `keyword` varchar(100) NOT NULL,
            `url` varchar(255) NOT NULL,
            `store_id` int(11) NOT NULL DEFAULT '0',
            `date_added` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `keyword` (`keyword`),
            KEY `url` (`url`)
        ) CHARACTER SET utf8 COLLATE utf8_general_ci");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_analysis` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `crawler` varchar(100) NOT NULL,
            `url` varchar(255) NOT NULL,
            `store_id` int(11) NOT NULL DEFAULT '0',
            `date_added` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `crawler` (`crawler`),
            KEY `url` (`url`)
        ) CHARACTER SET utf8 COLLATE utf8_general_ci");        
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_custom_urls` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `query` varchar(255) NOT NULL,
            `keyword` varchar(255) NOT NULL,
            `store_id` int(11) NOT NULL DEFAULT '0',
            `language_id` int(11) NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`),
            KEY `query` (`query`),
            KEY `keyword` (`keyword`)
        ) CHARACTER SET utf8 COLLATE utf8_general_ci");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_404_pages` (
            `page_id` int(11) NOT NULL AUTO_INCREMENT,
            `visits` int(11) NOT NULL DEFAULT '1',
            `route` varchar(255) NOT NULL,
            `first_visited` datetime NOT NULL NOT NULL,
            `last_visited` datetime NOT NULL NOT NULL,
            `store_id` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`page_id`)
        ) CHARACTER SET utf8 COLLATE utf8_general_ci");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_404_redirects` (
            `redirect_id` int(11) NOT NULL AUTO_INCREMENT,
            `route_from` varchar(255) NOT NULL,
            `route_to` varchar(255) NOT NULL,
            `date_added` datetime NOT NULL NOT NULL,
            `date_modified` datetime NOT NULL NOT NULL,
            `store_id` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`redirect_id`)
        ) CHARACTER SET utf8 COLLATE utf8_general_ci");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_manufacturer_description` (
            `manufacturer_id` int(11) NOT NULL AUTO_INCREMENT,
            `language_id` int(11) NOT NULL DEFAULT '1',
            `meta_title` varchar(255) NOT NULL,
            `meta_description` varchar(255) NOT NULL,
            `meta_keyword` varchar(255) NOT NULL,
            PRIMARY KEY (`manufacturer_id`, `language_id`)
        ) CHARACTER SET utf8 COLLATE utf8_general_ci");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_product_description` (
            `product_id` int(11) NOT NULL AUTO_INCREMENT,
            `language_id` int(11) NOT NULL DEFAULT '1',
            `h1` varchar(255) NOT NULL,
            `h2` varchar(255) NOT NULL,
            PRIMARY KEY (`product_id`, `language_id`)
        ) CHARACTER SET utf8 COLLATE utf8_general_ci");
        
        $check_update_multi_store = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "seo_custom_urls` LIKE 'store_id'");
		if (!$check_update_multi_store->rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "seo_custom_urls` ADD `store_id` int(10)  NOT NULL DEFAULT '0'");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "seo_autolinks` ADD `store_id` int(10)  NOT NULL DEFAULT '0'");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "seo_analysis` ADD `store_id` int(10)  NOT NULL DEFAULT '0'");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "seo_404_pages` ADD `store_id` int(10)  NOT NULL DEFAULT '0'");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "seo_404_redirects` ADD `store_id` int(10)  NOT NULL DEFAULT '0'");
		}
		
        $check_if_manufacturer_table_is_filled = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_manufacturer_description`")->num_rows;
        if ($check_if_manufacturer_table_is_filled == 0) {
            $ids = $this->db->query("SELECT m.manufacturer_id FROM `" . DB_PREFIX . "manufacturer` m")->rows;
            $this->load->model('localisation/language');
            $languages = $this->model_localisation_language->getLanguages();

            foreach ($ids as $manufacturer_id) {
                foreach ($languages as $language) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_manufacturer_description` SET `manufacturer_id` = '" . $manufacturer_id['manufacturer_id'] . "', `language_id` = '" . $language['language_id'] . "'");
                }
            }
        }
        
        $check_if_table_is_filled = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `store_id` = '" . (int) $store_id . "'")->num_rows;
        
        if ($check_if_table_is_filled == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 
                (`store_id`, `key`, `value`) VALUES
                (" . (int) $store_id . ", 'product_url_string', '[product]-[model]'),
                (" . (int) $store_id . ", 'category_url_string', '[category]'),
                (" . (int) $store_id . ", 'manufacturer_url_string', '[manufacturer]'),
                (" . (int) $store_id . ", 'information_url_string', '[information]'),
                (" . (int) $store_id . ", 'product_description_string', '[description]'),
                (" . (int) $store_id . ", 'product_title_string', '[product] - [site_name] '),
                (" . (int) $store_id . ", 'category_title_string', '[category] - [site_name]'),
                (" . (int) $store_id . ", 'information_title_string', '[information] - [site_name]'),
                (" . (int) $store_id . ", 'category_description_string', '[category] - [description]'),
                (" . (int) $store_id . ", 'information_description_string', '[information] - [site_name]'),
                (" . (int) $store_id . ", 'meta_description_word_limit', '10'),
                (" . (int) $store_id . ", 'product_keyword_string', '[product], [model]'),
                (" . (int) $store_id . ", 'category_keyword_string', '[category], [site_name]'),
                (" . (int) $store_id . ", 'information_keyword_string', '[information], [site_name]'),
                (" . (int) $store_id . ", 'seo_image_titles_date', ''),
                (" . (int) $store_id . ", 'images_filename_string', '[product][model]'),
                (" . (int) $store_id . ", 'images_filename_product', '0'),
                (" . (int) $store_id . ", 'images_filename_product_additional', '0'),
                (" . (int) $store_id . ", 'richsnippets_product_data', '0'),
                (" . (int) $store_id . ", 'seo_image_last_activity', ''),
                (" . (int) $store_id . ", 'richsnippets_product_breadcrumbs', '0'),
                (" . (int) $store_id . ", 'seo_score', '0'),
                (" . (int) $store_id . ", 'facebook_open_graph', '0'),
                (" . (int) $store_id . ", 'twitter_card', '0'),
                (" . (int) $store_id . ", 'google_publisher', '0'),
                (" . (int) $store_id . ", 'google_publisher_id', '+iSenseLabs'),
                (" . (int) $store_id . ", 'twitter_card_username', '@test'),
                (" . (int) $store_id . ", 'twitter_card_product_data', '0'),
                (" . (int) $store_id . ", 'facebook_open_graph_product_data', '0'),
                (" . (int) $store_id . ", 'facebook_open_graph_app_id', '966242223397117'),
                (" . (int) $store_id . ", 'seo_score_last_checked', ''),
                (" . (int) $store_id . ", 'hreflang_products', '0'),
                (" . (int) $store_id . ", 'hreflang_categories', '0'),
                (" . (int) $store_id . ", 'hreflang_manufacturers', '0'),
                (" . (int) $store_id . ", 'hreflang_informations', '0'),
                (" . (int) $store_id . ", 'richsnippets_company_info', '0'),
                (" . (int) $store_id . ", 'feed_product_limit', '100'),
                (" . (int) $store_id . ", 'feed_category_product', '0'),
                (" . (int) $store_id . ", 'feed_manufacturer_product', '0')"); 
        }
        
        $check_if_manufacturer_info_is_filled = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='manufacturer_title_string' AND `store_id` = '" . (int) $store_id . "'")->num_rows;
        
        if ($check_if_manufacturer_info_is_filled == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 
                (`store_id`, `key`, `value`) VALUES
                (" . (int) $store_id . ", 'manufacturer_title_string', '[manufacturer] - [site_name]'),
                (" . (int) $store_id . ", 'manufacturer_description_string', '[manufacturer] - [site_name]'),
                (" . (int) $store_id . ", 'manufacturer_keyword_string', '[manufacturer], [site_name]')");         
        }
        
        $check_if_sea_field_exists = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='search_engine_analytics_enable' AND `store_id` = '" . (int) $store_id . "'")->num_rows;
        
        if ($check_if_sea_field_exists == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 
                (`store_id`, `key`, `value`) VALUES
                (" . (int) $store_id . ", 'search_engine_analytics_enable', '1')");         
        }
        
        $check_if_404_gathering_is_enabled = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='404_pages_gathering' AND `store_id` = '" . (int) $store_id . "'")->num_rows;
        
        if ($check_if_404_gathering_is_enabled == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 
                (`store_id`, `key`, `value`) VALUES
                (" . (int) $store_id . ", '404_pages_gathering', '1')");         
        }        
        
        $check_if_canonical_settings_are_filled = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='canonical_products' AND `store_id` = '" . (int) $store_id . "'")->num_rows;
        
        if ($check_if_canonical_settings_are_filled == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 
                (`store_id`, `key`, `value`) VALUES
                (" . (int) $store_id . ", 'canonical_products', '1'),
                (" . (int) $store_id . ", 'canonical_information_pages', '1'),
                (" . (int) $store_id . ", 'canonical_home_page', '1'),
                (" . (int) $store_id . ", 'canonical_special_page', '1'),
                (" . (int) $store_id . ", 'canonical_manufacturers', '1'),
                (" . (int) $store_id . ", 'canonical_categories', '1')");         
        }
        		
		$check_if_unify_field_exists = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='unify_urls' AND `store_id` = '" . (int) $store_id . "'")->num_rows;

        if ($check_if_unify_field_exists == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 
                (`store_id`, `key`, `value`) VALUES
                (" . (int) $store_id . ", 'unify_urls', '0'),
                (" . (int) $store_id . ", 'breadcrumb_products', '0'),
                (" . (int) $store_id . ", 'breadcrumb_categories', '0')");         
        }

        $check_if_auto_generate_exists = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='url_product_autogenerate' AND `store_id` = '" . (int) $store_id . "'")->num_rows;

        if ($check_if_auto_generate_exists == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 
                (`store_id`, `key`, `value`) VALUES
                (" . (int) $store_id . ", 'url_product_autogenerate', '0'),
                (" . (int) $store_id . ", 'url_category_autogenerate', '0'),
                (" . (int) $store_id . ", 'url_manufacturer_autogenerate', '0'),
                (" . (int) $store_id . ", 'url_information_autogenerate', '0') ");         
        }

        $check_if_subfolders_exists = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='subfolder_prefixes' AND `store_id` = '" . (int) $store_id . "'")->num_rows;

        if ($check_if_subfolders_exists == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 
                (`store_id`, `key`, `value`) VALUES
                (" . (int) $store_id . ", 'subfolder_prefixes', '0')");         
        }

		$check_if_richsnippet_category_breadcrumbs_exists = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='richsnippets_category_breadcrumbs' AND `store_id` = '" . (int) $store_id . "'")->num_rows;
        
        if ($check_if_richsnippet_category_breadcrumbs_exists == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 
                (`store_id`, `key`, `value`) VALUES
                (" . (int) $store_id . ", 'richsnippets_category_breadcrumbs', '0')");         
        }

        // Clone custom url if alias for default language is empty
        $check_custom_url_multi_language = $this->db->query("SELECT COUNT(*) as `count`  FROM `" . DB_PREFIX . "seo_custom_urls` WHERE `store_id` = '" . (int)$store_id . "'");
        $test_custom_url_multi_language = $this->getCustomUrls(1, 1000, $store_id);

        if ($check_custom_url_multi_language->row['count'] && empty($test_custom_url_multi_language)) {
            $custom_url_multi_language_group = $this->db->query("select * FROM `" . DB_PREFIX . "seo_custom_urls` WHERE `store_id` = '" . (int)$store_id . "' GROUP BY language_id ORDER BY language_id ASC");
            $lang_id = $this->config->get('config_language_id');
            foreach ($custom_url_multi_language_group->rows as $custom_urls) {
                if (!empty($custom_urls['language_id'])) {
                    $lang_id = $custom_urls['language_id'];
                    break;
                }
            }

            $custom_url_multi_language_clones = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_custom_urls` WHERE `language_id` = '" . (int)$lang_id . "'");
            foreach ($custom_url_multi_language_clones->rows as $custom_urls) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_custom_urls` SET `keyword` = '" . $this->db->escape($custom_urls['keyword']) . "', `query` = '" . $this->db->escape($custom_urls['query']). "', `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$this->config->get('config_language_id') . "'");
            }
        }

        $check_if_sitemap_feed_exists = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='feed_product_limit' AND `store_id` = '" . (int) $store_id . "'")->num_rows;
        
        if ($check_if_sitemap_feed_exists == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 
                (`store_id`, `key`, `value`) VALUES
                (" . (int) $store_id . ", 'feed_product_limit', '100'),
                (" . (int) $store_id . ", 'feed_category_product', '0'),
                (" . (int) $store_id . ", 'feed_manufacturer_product', '0') ");
        }

        $check_if_product_heading_tags_info_filled = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='h1_heading_tags_string' AND `store_id` = '" . (int) $store_id . "'")->num_rows;
     
        if ($check_if_product_heading_tags_info_filled == 0) {

            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 

                (`store_id`, `key`, `value`) VALUES

                (" . (int) $store_id . ", 'h1_heading_tags_string', '[product]'),

                (" . (int) $store_id . ", 'h2_heading_tags_string', '[product]')");         

        }

        $check_if_cyrillic_urls_exist = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='cyrillic_urls' AND `store_id` = '" . (int) $store_id . "'")->num_rows;
 
        if ($check_if_cyrillic_urls_exist == 0) {

            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 

                (`store_id`, `key`, `value`) VALUES

                (" . (int) $store_id . ", 'cyrillic_urls', '0')");  

        }

        $check_if_redirect_to_seo_links_exist = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='redirect_to_seo_links' AND `store_id` = '" . (int) $store_id . "'")->num_rows;
 
        if ($check_if_redirect_to_seo_links_exist == 0) {

            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 

                (`store_id`, `key`, `value`) VALUES

                (" . (int) $store_id . ", 'redirect_to_seo_links', '0')");  

        }

        $check_if_include_default_lang_prefix_exist = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='default_lang_prefix' AND `store_id` = '" . (int) $store_id . "'")->num_rows;
 
        if ($check_if_include_default_lang_prefix_exist == 0) {

            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 

                (`store_id`, `key`, `value`) VALUES

                (" . (int) $store_id . ", 'default_lang_prefix', '0')");  

        }

        $check_if_include_redirect_active_lang_prefix_exist = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='redirect_active_lang_prefix' AND `store_id` = '" . (int) $store_id . "'")->num_rows;
 
        if ($check_if_include_redirect_active_lang_prefix_exist == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 
                (`store_id`, `key`, `value`) VALUES
                (" . (int) $store_id . ", 'redirect_active_lang_prefix', '0')");  
        }

        $check_if_subfolder_prefixes_alias_exist = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='subfolder_prefixes_alias' AND `store_id` = '" . (int) $store_id . "'")->num_rows;

        if ($check_if_subfolder_prefixes_alias_exist == 0) {
            $this->load->model('localisation/language');
            $languages = $this->model_localisation_language->getLanguages();

            $subfolder_prefixes_alias = array();
            foreach ($languages as $lang) {
                $subfolder_prefixes_alias[$lang['code']] = $lang['code'];
            }

            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` 
                (`store_id`, `key`, `value`) VALUES
                (" . (int) $store_id . ", 'subfolder_prefixes_alias', '" . json_encode($subfolder_prefixes_alias) . "')");  
        }

        $check_if_images_filename_product_exist = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key`='images_filename_product' AND `store_id` = '" . (int) $store_id . "'")->num_rows;
 
        if ($check_if_images_filename_product_exist == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` (`store_id`, `key`, `value`) VALUES (" . (int) $store_id . ", 'images_filename_product', '0')");  
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_module_settings` (`store_id`, `key`, `value`) VALUES (" . (int) $store_id . ", 'images_filename_product_additional', '0')");  
        }
    }

    public function getSetting($key = '', $store_id = null) {
        if (is_null($store_id)) {
            if (!isset($this->request->get['store_id'])) {
                $this->request->get['store_id'] = 0;
            }
            $store_id = (int)$this->request->get['store_id'];
        }

        $result = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "seo_module_settings` WHERE `key` = '" . $this->db->escape($key) . "' AND `store_id` = '" . (int)$store_id . "' LIMIT 1");
        
        if ($result->num_rows > 0) { 
            return $result->row['value'];
        } else {
            return false;
        }
    }
    
    public function saveSetting($key, $value, $store_id = null) {
        if (is_null($store_id)) {
            if (!isset($this->request->get['store_id'])) {
                $this->request->get['store_id'] = 0;
            }
            $store_id = (int)$this->request->get['store_id'];
        }

        $this->db->query("UPDATE `" . DB_PREFIX . "seo_module_settings` SET `value`='" . $this->db->escape($value) . "' WHERE `key`='" . $key . "' AND `store_id` = '" . (int)$store_id . "' LIMIT 1");
        
        return true;
    }
    
    public function enableSEOUrls($store_id = 0) {
        $this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '1' WHERE `code` = 'config' AND `key` = 'config_seo_url' AND `store_id` = '" . (int) $this->db->escape($store_id) . "' LIMIT 1");
        
        return true;
    }
    
    public function getKeywords($item_id = 0, $type = 'product', $store_id = '0') {
        $keywords = array();
        
        switch($type) {
            case "category":
                $results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'category_id=" . (int)$this->db->escape($item_id) . "' AND `store_id` = '" . (int) $this->db->escape($store_id) . "'");
                break;
            case "product":
                $results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'product_id=" . (int)$this->db->escape($item_id) . "' AND `store_id` = '" . (int) $this->db->escape($store_id) . "'");
                break;
            case "manufacturer":
                $results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'manufacturer_id=" . (int)$this->db->escape($item_id) . "' AND `store_id` = '" . (int) $this->db->escape($store_id) . "'");
                break;
            case "information":
                $results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'information_id=" . (int)$item_id . "' AND `store_id` = '" . (int) $this->db->escape($store_id) . "'");
                break;
            default:
                return false;
                break;
        }
        
        foreach ($results->rows as $result) {
            $keywords[$result['language_id']] = $result['keyword'];					
        }
        
        return $keywords;
    }
    
    public function checkSEOUrlsCount($store_id = '0') {
        $this->load->model('localisation/language');
		$languages                                  = $this->model_localisation_language->getLanguages();
        $total_products                             = array();
        $total_products_with_seo_titles             = array();
        $total_categories                           = array();
        $total_categories_with_seo_titles           = array();
        $total_manufacturers                        = array();
        $total_manufacturers_with_seo_titles        = array();
        $total_informations                         = array();
        $total_informations_with_seo_titles         = array();
        $total_products_difference                  = array();
        $total_categories_difference                = array();
        $total_manufacturers_difference             = array();
        $total_informations_difference              = array();
        $have_minus_count = false;
        
        foreach ($languages as $language) { 
            $total_products[$language['language_id']]       = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p2s.store_id = " . (int)$store_id)->row['count'];
            $total_products_with_seo_titles[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "seo_url` s LEFT JOIN  `" . DB_PREFIX . "product_to_store` p2s ON (p2s.product_id = SUBSTRING_INDEX(s.query,'=',-1)) WHERE s.`query` LIKE '%product_id=%' AND s.`language_id` = " . (int)$language['language_id'] . " AND s.`store_id` = " . (int)$store_id . " AND p2s.store_id = " . (int)$store_id)->row['count'];
            $total_products_difference[$language['language_id']] = $total_products[$language['language_id']]-$total_products_with_seo_titles[$language['language_id']];
            if ($total_products_difference[$language['language_id']] < 0) { $have_minus_count = true; }

            $total_categories[$language['language_id']]     = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.category_id = c2s.category_id) WHERE c2s.store_id = " . (int)$store_id)->row['count'];
            $total_categories_with_seo_titles[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "seo_url` s LEFT JOIN  `" . DB_PREFIX . "category_to_store` c2s ON (c2s.category_id = SUBSTRING_INDEX(s.query,'=',-1)) WHERE s.`query` LIKE '%category_id=%' AND s.`language_id` = " . (int)$language['language_id'] . " AND s.`store_id` = '" . (int)$store_id . "' AND c2s.store_id = " . (int)$store_id)->row['count'];
            $total_categories_difference[$language['language_id']] = $total_categories[$language['language_id']]-$total_categories_with_seo_titles[$language['language_id']];
            if ($total_categories_difference[$language['language_id']] < 0) { $have_minus_count = true; }

            $total_manufacturers[$language['language_id']]  = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "manufacturer` m LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = " . (int)$store_id)->row['count'];
            $total_manufacturers_with_seo_titles[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "seo_url` s LEFT JOIN  `" . DB_PREFIX . "manufacturer_to_store` m2s ON (m2s.manufacturer_id = SUBSTRING_INDEX(s.query,'=',-1)) WHERE s.`query` LIKE '%manufacturer_id=%' AND s.`language_id` = " . $language['language_id'] . " AND s.`store_id` = '" . (int)$store_id . "' AND m2s.store_id = " . (int)$store_id)->row['count'];
            $total_manufacturers_difference[$language['language_id']] = $total_manufacturers[$language['language_id']]-$total_manufacturers_with_seo_titles[$language['language_id']];
            if ($total_manufacturers_difference[$language['language_id']] < 0) { $have_minus_count = true; }

            $total_informations[$language['language_id']]   = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "information` i LEFT JOIN `" . DB_PREFIX . "information_to_store` i2s ON (i.information_id = i2s.information_id) WHERE i2s.store_id = " . (int)$store_id)->row['count'];
            $total_informations_with_seo_titles[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "seo_url` s LEFT JOIN  `" . DB_PREFIX . "information_to_store` i2s ON (i2s.information_id = SUBSTRING_INDEX(s.query,'=',-1)) WHERE s.`query` LIKE '%information_id=%' AND s.`language_id` = " . $language['language_id'] . " AND s.`store_id` = '" . (int)$store_id . "' AND i2s.store_id = " . (int)$store_id)->row['count'];
            $total_informations_difference[$language['language_id']] = $total_informations[$language['language_id']]-$total_informations_with_seo_titles[$language['language_id']];
            if ($total_informations_difference[$language['language_id']] < 0) { $have_minus_count = true; }
        }
        
        return array(
            'total_products'                => $total_products,
            'total_products_meta'           => $total_products_with_seo_titles,
            'total_categories'              => $total_categories,
            'total_categories_meta'         => $total_categories_with_seo_titles,
            'total_manufacturers'           => $total_manufacturers,
            'total_manufacturers_meta'      => $total_manufacturers_with_seo_titles,
            'total_informations'            => $total_informations,
            'total_informations_meta'       => $total_informations_with_seo_titles,
            'total_products_difference'     => $total_products_difference,
            'total_categories_difference'   => $total_categories_difference,
            'total_manufacturers_difference'=> $total_manufacturers_difference,
            'total_informations_difference' => $total_informations_difference,
            'have_minus_count'              => $have_minus_count
        );
    }
    
    public function createSEOUrls($type = '', $language_id = '1', $store_id = '0') {
        $ids = array();
        $pattern = '';
        
        switch($type) {
            case "categories":
                $ids = $this->db->query("SELECT c.category_id FROM `" . DB_PREFIX . "category` c WHERE CONCAT('category_id=',c.category_id) NOT IN (SELECT `query` FROM `" . DB_PREFIX . "seo_url` WHERE `query` = CONCAT('category_id=',c.category_id) AND `language_id`=" . $language_id . " AND `store_id` = '" . (int) $this->db->escape($store_id) . "')")->rows;
                
                $result = $this->SeoCategoryURLs($ids, $language_id, $store_id);
                return $result;
            case "categories_all":
                $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` LIKE '%category_id=%' AND `language_id`=" . $language_id . " AND `store_id` = '" . (int) $this->db->escape($store_id) . "'");
                
                $ids = $this->db->query("SELECT c.category_id FROM `" . DB_PREFIX . "category` c")->rows;
                
                $result = $this->SeoCategoryURLs($ids, $language_id, $store_id);
                return $result;
            case "products":
                $ids = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product` p WHERE CONCAT('product_id=',p.product_id) NOT IN (SELECT `query` FROM `" . DB_PREFIX . "seo_url` WHERE `query` = CONCAT('product_id=',p.product_id) AND `language_id`=" . $language_id . " AND `store_id` = '" . (int) $this->db->escape($store_id) . "')")->rows;
                
                $result = $this->SeoProductURLs($ids, $language_id, $store_id);
                return $result;
            case "products_all":
                $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` LIKE '%product_id=%' AND `language_id`=" . $language_id . " AND `store_id` = '" . (int) $this->db->escape($store_id) . "'");
                
                $ids = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product` p")->rows;
                
                $result = $this->SeoProductURLs($ids, $language_id, $store_id);
                return $result;
            case "manufacturers":
                $ids = $this->db->query("SELECT m.manufacturer_id FROM `" . DB_PREFIX . "manufacturer` m WHERE CONCAT('manufacturer_id=',m.manufacturer_id) NOT IN (SELECT `query` FROM `" . DB_PREFIX . "seo_url` WHERE `query` = CONCAT('manufacturer_id=',m.manufacturer_id) AND `language_id`=" . $language_id . " AND `store_id` = '" . (int) $this->db->escape($store_id) . "')")->rows;
                
                $result = $this->SeoManufacturerURLs($ids, $language_id, $store_id);
                return $result;
            case "manufacturers_all":
                $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` LIKE '%manufacturer_id=%' AND `language_id`=" . $language_id . " AND `store_id` = '" . (int) $this->db->escape($store_id) . "'");
                
                $ids = $this->db->query("SELECT m.manufacturer_id FROM `" . DB_PREFIX . "manufacturer` m")->rows;
                
                $result = $this->SeoManufacturerURLs($ids, $language_id, $store_id);
                return $result;
            case "informations":
                $ids = $this->db->query("SELECT i.information_id FROM `" . DB_PREFIX . "information` i WHERE CONCAT('information_id=',i.information_id) NOT IN (SELECT `query` FROM `" . DB_PREFIX . "seo_url` WHERE `query` = CONCAT('information_id=',i.information_id) AND `language_id`=" . $language_id . " AND `store_id` = '" . (int) $this->db->escape($store_id) . "')")->rows;
               
                $result = $this->SeoInformationURLs($ids, $language_id, $store_id);
                return $result;
            case "informations_all":
                $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` LIKE '%information_id=%' AND `language_id`=" . $language_id . " AND `store_id` = '" . (int) $this->db->escape($store_id) . "'");

                $ids = $this->db->query("SELECT i.information_id FROM `" . DB_PREFIX . "information` i")->rows;
               
                $result = $this->SeoInformationURLs($ids, $language_id, $store_id);
                return $result;
            default:
                return true;
                break;
        }
        
    }

    public function fixMinusSEOUrls($type = '', $language_id = '1') {
        $ids = array();
        $items = array();

        switch ($type) {
            case 'product':
                $items = $this->db->query("SELECT s.seo_url_id FROM `" . DB_PREFIX . "seo_url` s WHERE s.query LIKE '%product_id=%' and SUBSTRING_INDEX(s.query,'=',-1) NOT IN (SELECT product_id FROM `" . DB_PREFIX . "product`) AND s.language_id = " . (int)$language_id)->rows;
                break;
            case 'category':
                $items = $this->db->query("SELECT s.seo_url_id FROM `" . DB_PREFIX . "seo_url` s WHERE s.query LIKE '%category_id=%' and SUBSTRING_INDEX(s.query,'=',-1) NOT IN (SELECT category_id FROM `" . DB_PREFIX . "category`) AND s.language_id = " . (int)$language_id)->rows;
                break;
            case 'manufacturer':
                $items = $this->db->query("SELECT s.seo_url_id FROM `" . DB_PREFIX . "seo_url` s WHERE s.query LIKE '%manufacturer_id=%' and SUBSTRING_INDEX(s.query,'=',-1) NOT IN (SELECT manufacturer_id FROM `" . DB_PREFIX . "manufacturer`) AND s.language_id = " . (int)$language_id)->rows;
                break;
            case 'information':
                $items = $this->db->query("SELECT s.seo_url_id FROM `" . DB_PREFIX . "seo_url` s WHERE s.query LIKE '%information_id=%' and SUBSTRING_INDEX(s.query,'=',-1) NOT IN (SELECT information_id FROM `" . DB_PREFIX . "information`) AND s.language_id = " . (int)$language_id)->rows;
                break;
        }

        foreach ($items as $item) {
            $ids[] = $item['seo_url_id'];
            $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `seo_url_id` = " . (int)$item['seo_url_id'] . " AND `language_id` = " . (int)$language_id);
        }

        return $ids;
    }
    
    public function SeoProductURLs($product_ids = array(), $language_id, $store_id = '0') {
        $pattern    = $this->getSetting('product_url_string', $store_id);
        $search     = array('[product]', '[model]', '[sku]', '[upc]', '[random]', '[lang]', '[manufacturer]', '[id]');
        $counter    = 0;
        $items      = array();
        
        $language_code = $this->db->query("SELECT `code` FROM `" . DB_PREFIX . "language` WHERE `language_id` = '".$language_id."'")->row['code'];

        foreach ($product_ids as $id) {
            $product_info = $this->db->query("SELECT p.*, pd.*, m.name as manufacturer FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd on (p.product_id=pd.product_id AND pd.language_id=" . $language_id . ") LEFT JOIN `" . DB_PREFIX . "manufacturer` m on (p.manufacturer_id=m.manufacturer_id) WHERE p.product_id=" . $id['product_id'] . " LIMIT 1")->row;

            $replace = array($product_info['name'], $product_info['model'], $product_info['sku'], $product_info['upc'], rand(), $language_code, $product_info['manufacturer'], $product_info['product_id']);
            
            if (strpos($pattern, '[attributes]') !== false) {
                $attributes = $this->db->query("SELECT `attribute_id`, `text` FROM `" . DB_PREFIX . "product_attribute` WHERE `product_id` = " . (int)$product_info['product_id'] . " AND `language_id` = " . (int)$language_id);

                if ($attributes->num_rows) {
                    $attribute_values = array();
                    foreach ($attributes->rows as $attribute) {
                        $attributes_values[] = $attribute['text'];
                    }

                    $search[] = '[attributes]';
                    $replace[] = implode('-', $attributes_values);
                }
            }

            $string = str_replace($search, $replace, $pattern);
            $useCyrillicUrl = preg_match('/[\p{Cyrillic}]/u', $string) && $this->getSetting('cyrillic_urls', $store_id) ? true : false;
            $generated_slug = $this->formatSEOUrl($string, $useCyrillicUrl);

            $slug_exists = $this->db->query("SELECT `query` FROM `" . DB_PREFIX . "seo_url` WHERE `keyword` = '" . $generated_slug . "' AND `store_id` = '" . (int) $this->db->escape($store_id) . "'")->num_rows;
            if ($slug_exists > 0) {
				$generated_slug = $generated_slug.'-'.$language_id.'-'.$id['product_id'];
            }

            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` (query, keyword, language_id, store_id) VALUES ('product_id=" . $id['product_id'] . "', '" . $this->db->escape($generated_slug) . "', " . $language_id . ", " . $store_id . ")");

            $counter++;
            $items[$product_info['product_id']] = array('name' => $product_info['name'], 'result' => $generated_slug);
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    public function SeoCategoryURLs($category_ids = array(), $language_id, $store_id = '0') {
        $pattern    = $this->getSetting('category_url_string', $store_id);
        $search     = array('[category]', '[random]', '[lang]');
        $counter    = 0;
        $items      = array();
        
        $language_code = $this->db->query("SELECT `code` FROM `" . DB_PREFIX . "language` WHERE `language_id` = '".$language_id."'")->row['code'];
        
        foreach ($category_ids as $id) {
            $category_info = $this->db->query("SELECT cd.* FROM `" . DB_PREFIX . "category_description` cd WHERE cd.category_id=" . $id['category_id'] . " AND cd.language_id=" . $language_id . " LIMIT 1")->row;

            $replace = array($category_info['name'], rand(), $language_code);

            $string = str_replace($search, $replace, $pattern);
            $useCyrillicUrl = preg_match('/[\p{Cyrillic}]/u', $string) && $this->getSetting('cyrillic_urls', $store_id) ? true : false;
            $generated_slug = $this->formatSEOUrl($string, $useCyrillicUrl);

            $slug_exists = $this->db->query("SELECT `query` FROM `" . DB_PREFIX . "seo_url` WHERE `keyword` = '" . $generated_slug . "' AND `store_id` = '" . (int) $this->db->escape($store_id) . "'")->num_rows;
            if ($slug_exists > 0) {
				$generated_slug = $generated_slug.'-'.$language_id.'-'.$id['category_id'];
            }

            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` (query, keyword, language_id, store_id) VALUES ('category_id=" . $id['category_id'] . "', '" . $this->db->escape($generated_slug) . "', " . $language_id . ", " . $store_id . ")");

            $counter++;
            $items[$category_info['category_id']] = array('name' => $category_info['name'], 'result' => $generated_slug);
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    public function SeoManufacturerURLs($manufacturer_ids = array(), $language_id, $store_id = '0') {
        $pattern    = $this->getSetting('manufacturer_url_string', $store_id);
        $search     = array('[manufacturer]', '[random]', '[lang]');
        $counter    = 0;
        $items      = array();
        
        $language_code = $this->db->query("SELECT `code` FROM `" . DB_PREFIX . "language` WHERE `language_id` = '".$language_id."'")->row['code'];
        
        foreach ($manufacturer_ids as $id) {
            $manufacturer_info = $this->db->query("SELECT m.* FROM `" . DB_PREFIX . "manufacturer` m WHERE m.manufacturer_id=" . $id['manufacturer_id'] . " LIMIT 1")->row;

            $replace = array($manufacturer_info['name'], rand(), $language_code);

            $string = str_replace($search, $replace, $pattern);
            $useCyrillicUrl = preg_match('/[\p{Cyrillic}]/u', $string) && $this->getSetting('cyrillic_urls', $store_id) ? true : false;
            $generated_slug = $this->formatSEOUrl($string, $useCyrillicUrl);

            $slug_exists = $this->db->query("SELECT `query` FROM `" . DB_PREFIX . "seo_url` WHERE `keyword` = '" . $generated_slug . "' AND `store_id` = '" . (int) $this->db->escape($store_id) . "'")->num_rows;
            if ($slug_exists > 0) {
				$generated_slug = $generated_slug.'-'.$language_id.'-'.$id['manufacturer_id'];
            }

            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` (query, keyword, language_id, store_id) VALUES ('manufacturer_id=" . $id['manufacturer_id'] . "', '" . $this->db->escape($generated_slug) . "', " . $language_id . ", " . $store_id . ")");

            $counter++;
            $items[$manufacturer_info['manufacturer_id']] = array('name' => $manufacturer_info['name'], 'result' => $generated_slug);
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    public function SeoInformationURLs($information_ids = array(), $language_id, $store_id = '0') {
        $pattern    = $this->getSetting('information_url_string', $store_id);
        $search     = array('[information]', '[random]', '[lang]');
        $counter    = 0;
        $items      = array();
        
        $language_code = $this->db->query("SELECT `code` FROM `" . DB_PREFIX . "language` WHERE `language_id` = '".$language_id."'")->row['code'];
        
        foreach ($information_ids as $id) {
            $information_info = $this->db->query("SELECT id.* FROM `" . DB_PREFIX . "information_description` id WHERE id.information_id=" . $id['information_id'] . " AND id.language_id=" . $language_id . " LIMIT 1")->row;
            
            if (!empty($information_info) && !empty($information_info['title'])) {
                $replace = array($information_info['title'], rand(), $language_code);

                $string = str_replace($search, $replace, $pattern);
                $useCyrillicUrl = preg_match('/[\p{Cyrillic}]/u', $string) && $this->getSetting('cyrillic_urls', $store_id) ? true : false;
                $generated_slug = $this->formatSEOUrl($string, $useCyrillicUrl);

                $slug_exists = $this->db->query("SELECT `query` FROM `" . DB_PREFIX . "seo_url` WHERE `keyword` = '" . $generated_slug . "' AND `store_id` = '" . (int) $this->db->escape($store_id) . "'")->num_rows;
                if ($slug_exists > 0) {
					$generated_slug = $generated_slug.'-'.$language_id.'-'.$id['information_id'];
                }

                $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` (query, keyword, language_id, store_id) VALUES ('information_id=" . $id['information_id'] . "', '" . $this->db->escape($generated_slug) . "', " . $language_id . ", " . $store_id . ")");

                $counter++;
                $items[$information_info['information_id']] = array('name' => $information_info['title'], 'result' => $generated_slug);
            }
        }
        
        return array('counter' => $counter, 'items' => $items);
    }

    public function createProductHeadingTags($type = '', $language_id = '1', $store_id = '0') {

        $ids = array();

        $pattern = '';

        

        switch($type) {

            case "product_h1_tags":

                $ids = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product` p WHERE p.product_id NOT IN (SELECT `product_id` FROM `" . DB_PREFIX . "seo_product_description` WHERE `h1` != '' AND `language_id`=" . $language_id . ")")->rows;

                $result = $this->SeoProductH1Tags($ids, $language_id, $store_id);

                return $result;

            case "product_h1_tags_all":

                $this->db->query("UPDATE `" . DB_PREFIX . "seo_product_description` SET `h1` = '' WHERE `language_id`=" . $language_id . "");
             
                $ids = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product` p")->rows;              

                $result = $this->SeoProductH1Tags($ids, $language_id, $store_id);

                return $result;

            case "product_h2_tags":

               $ids = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product` p WHERE p.product_id NOT IN (SELECT `product_id` FROM `" . DB_PREFIX . "seo_product_description` WHERE `h2` != '' AND `language_id`=" . $language_id . ")")->rows;
            

                $result = $this->SeoProductH2Tags($ids, $language_id, $store_id);

                return $result;

            case "product_h2_tags_all":

                $this->db->query("UPDATE `" . DB_PREFIX . "seo_product_description` SET `h2` = '' WHERE `language_id`=" . $language_id . "");
                
                $ids = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product` p")->rows;
        
                $result = $this->SeoProductH2Tags($ids, $language_id, $store_id);

                return $result;

            default:

                return true;

                break;

        }      

    }

    public function SeoProductH1Tags($product_ids = array(), $language_id, $store_id = '0') {

        $pattern    = $this->getSetting('h1_heading_tags_string', $store_id);

        $search     = array('[product]', '[model]', '[sku]', '[upc]', '[random]', '[lang]', '[manufacturer]', '[id]');

        $counter    = 0;

        $items      = array();
     

        $language_code = $this->db->query("SELECT `code` FROM `" . DB_PREFIX . "language` WHERE `language_id` = '".$language_id."'")->row['code'];


        foreach ($product_ids as $id) {
           
            $product_info = $this->db->query("SELECT p.*, pd.*, m.name as manufacturer FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd on (p.product_id=pd.product_id AND pd.language_id=" . $language_id . ") LEFT JOIN `" . DB_PREFIX . "manufacturer` m on (p.manufacturer_id=m.manufacturer_id) WHERE p.product_id=" .$id['product_id'] . " LIMIT 1")->row;


            $replace = array($product_info['name'], $product_info['model'], $product_info['sku'], $product_info['upc'], rand(), $language_code, $product_info['manufacturer'], $product_info['product_id']);

            $string = str_replace($search, $replace, $pattern);
         
            $generated_slug = $this->db->escape($string);
            

            // $slug_exists = $this->db->query("SELECT `h1` FROM `" . DB_PREFIX . "seo_product_description` WHERE `h1` = '" . $generated_slug . "'")->num_rows;

            // if ($slug_exists > 0) {

            //     $generated_slug = $generated_slug.'-'.$language_id.'-'.$id['product_id'];

            // }

            $product_id_exist = $this->db->query("SELECT `product_id` FROM `" . DB_PREFIX . "seo_product_description` WHERE `product_id` = '" . $id['product_id'] ."' AND `language_id` = '" . $language_id . "'")->num_rows;

            if ($product_id_exist > 0){
                $this->db->query("UPDATE `" . DB_PREFIX . "seo_product_description` SET `h1` = '" . $this->db->escape($generated_slug) . "' WHERE `product_id` = '" . $id['product_id'] . "' AND `language_id` = '" . $language_id . "'");

            } else {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_product_description` (product_id,h1,language_id) VALUES ('". $id['product_id'] . "', '" . $this->db->escape($generated_slug) . "', " . $language_id . ")");
            }

            $counter++;

            $items[$product_info['product_id']] = array('name' => $product_info['name'], 'result' => $generated_slug);

        }      

        return array('counter' => $counter, 'items' => $items);

    }

    public function SeoProductH2Tags($product_ids = array(), $language_id, $store_id = '0') {

        $pattern    = $this->getSetting('h2_heading_tags_string', $store_id);

        $search     = array('[product]', '[model]', '[sku]', '[upc]', '[random]', '[lang]', '[manufacturer]', '[id]');

        $counter    = 0;

        $items      = array();

        $language_code = $this->db->query("SELECT `code` FROM `" . DB_PREFIX . "language` WHERE `language_id` = '".$language_id."'")->row['code'];

        foreach ($product_ids as $id) {
           
            $product_info = $this->db->query("SELECT p.*, pd.*, m.name as manufacturer FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd on (p.product_id=pd.product_id AND pd.language_id=" . $language_id . ") LEFT JOIN `" . DB_PREFIX . "manufacturer` m on (p.manufacturer_id=m.manufacturer_id) WHERE p.product_id=" .$id['product_id'] . " LIMIT 1")->row;


            $replace = array($product_info['name'], $product_info['model'], $product_info['sku'], $product_info['upc'], rand(), $language_code, $product_info['manufacturer'], $product_info['product_id']);

            $string = str_replace($search, $replace, $pattern);
  
            $generated_slug = $this->db->escape($string);
            

            // $slug_exists = $this->db->query("SELECT `h2` FROM `" . DB_PREFIX . "seo_product_description` WHERE `h2` = '" . $generated_slug . "'")->num_rows;

            // if ($slug_exists > 0) {

            //     $generated_slug = $generated_slug.'-'.$language_id.'-'.$id['product_id'];

            // }

            $product_id_exist = $this->db->query("SELECT `product_id` FROM `" . DB_PREFIX . "seo_product_description` WHERE `product_id` = '" . $id['product_id'] ."' AND `language_id` = '" . $language_id . "'")->num_rows;
           
            if ($product_id_exist > 0){
                $this->db->query("UPDATE `" . DB_PREFIX . "seo_product_description` SET `h2` = '" . $this->db->escape($generated_slug) . "' WHERE `product_id` = '" . $id['product_id'] . "' AND `language_id` = '" . $language_id . "'");

            } else {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_product_description` (product_id,h2,language_id) VALUES ('". $id['product_id'] . "', '" . $this->db->escape($generated_slug) . "', " . $language_id . ")");
            }

            $counter++;

            $items[$product_info['product_id']] = array('name' => $product_info['name'], 'result' => $generated_slug);

        }     

        return array('counter' => $counter, 'items' => $items);

    }
    
    public function checkSEOTitlesCount($store_id = '0') {
        $this->load->model('localisation/language');
		$languages                                  = $this->model_localisation_language->getLanguages();
        $total_products                             = array();
        $total_products_with_meta_titles            = array();
        $total_categories                           = array();
        $total_categories_with_meta_titles          = array();
        $total_informations                         = array();
        $total_informations_with_meta_titles        = array();
        $total_manufacturers                        = array();
        $total_manufacturers_with_meta_titles       = array();
        $total_products_difference                  = array();
        $total_categories_difference                = array();
        $total_informations_difference              = array();
        $total_manufacturers_difference             = array();
        
        foreach ($languages as $language) { 
            $total_products[$language['language_id']]       = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p2s.store_id = " . (int)$store_id)->row['count'];
            $total_products_with_meta_titles[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "product_description` pd LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (pd.product_id = p2s.product_id) WHERE pd.`meta_title` != '' AND pd.`language_id` = " . $language['language_id'] . " AND p2s.store_id = " . (int)$store_id)->row['count'];
            $total_products_difference[$language['language_id']] = $total_products[$language['language_id']] - $total_products_with_meta_titles[$language['language_id']];

            $total_categories[$language['language_id']]     = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.category_id = c2s.category_id) WHERE c2s.store_id = " . (int)$store_id)->row['count'];
            $total_categories_with_meta_titles[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "category_description` cd LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (cd.category_id = c2s.category_id) WHERE cd.`meta_title` != '' AND cd.`language_id` = " . $language['language_id'] . " AND c2s.store_id = " . (int)$store_id)->row['count'];
            $total_categories_difference[$language['language_id']] = $total_categories[$language['language_id']]-$total_categories_with_meta_titles[$language['language_id']];

            $total_informations[$language['language_id']]   = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "information` i LEFT JOIN `" . DB_PREFIX . "information_to_store` i2s ON (i.information_id = i2s.information_id) WHERE i2s.store_id = " . (int)$store_id)->row['count'];
            $total_informations_with_meta_titles[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "information_description` id LEFT JOIN `" . DB_PREFIX . "information_to_store` i2s ON (id.information_id = i2s.information_id) WHERE id.`meta_title` != '' AND id.`language_id` = " . $language['language_id'] . " AND i2s.store_id = " . (int)$store_id)->row['count'];
            $total_informations_difference[$language['language_id']] = $total_informations[$language['language_id']]-$total_informations_with_meta_titles[$language['language_id']];
            
            $total_manufacturers[$language['language_id']]   = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "manufacturer` m LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = " . (int)$store_id)->row['count'];
            $total_manufacturers_with_meta_titles[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "seo_manufacturer_description` md LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` m2s ON (md.manufacturer_id = m2s.manufacturer_id) WHERE md.`meta_title` != '' AND md.`language_id` = " . $language['language_id'] . " AND m2s.store_id = " . (int)$store_id)->row['count'];
            $total_manufacturers_difference[$language['language_id']] = $total_manufacturers[$language['language_id']]-$total_manufacturers_with_meta_titles[$language['language_id']];
        }
        
        return array(
            'total_products'                => $total_products,
            'total_products_meta'           => $total_products_with_meta_titles,
            'total_categories'              => $total_categories,
            'total_categories_meta'         => $total_categories_with_meta_titles,
            'total_informations'            => $total_informations,
            'total_informations_meta'       => $total_informations_with_meta_titles,
            'total_manufacturers'           => $total_manufacturers,
            'total_manufacturers_meta'      => $total_manufacturers_with_meta_titles,
            'total_products_difference'     => $total_products_difference,
            'total_categories_difference'   => $total_categories_difference,
            'total_informations_difference' => $total_informations_difference,
            'total_manufacturers_difference' => $total_manufacturers_difference
        );
    }
    
    public function createSEOTitles($type = '', $language_id = '1') {
        $ids = array();
        $pattern = '';
        
        switch($type) {
            case "categories":
                $ids = $this->db->query("SELECT c.category_id FROM `" . DB_PREFIX . "category_description` c WHERE c.meta_title='' AND `language_id`=" . $language_id . "")->rows;

                $result = $this->SeoCategoryTitles($ids, $language_id);
                return $result;
            case "categories_all":
                $ids = $this->db->query("SELECT c.category_id FROM `" . DB_PREFIX . "category_description` c WHERE `language_id`=" . $language_id . "")->rows;

                $result = $this->SeoCategoryTitles($ids, $language_id);
                return $result;
            case "products":
                $ids = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product_description` p WHERE p.meta_title='' AND `language_id`=" . $language_id . "")->rows;
                
                $result = $this->SeoProductTitles($ids, $language_id);
                return $result;
            case "products_all":
                $ids = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product_description` p WHERE `language_id`=" . $language_id . "")->rows;
                
                $result = $this->SeoProductTitles($ids, $language_id);
                return $result;
            case "informations":
                $ids = $this->db->query("SELECT i.information_id FROM `" . DB_PREFIX . "information_description` i WHERE i.meta_title='' AND `language_id`=" . $language_id . "")->rows;
               
                $result = $this->SeoInformationTitles($ids, $language_id);
                return $result;
            case "informations_all":
                $ids = $this->db->query("SELECT i.information_id FROM `" . DB_PREFIX . "information_description` i WHERE `language_id`=" . $language_id . "")->rows;
               
                $result = $this->SeoInformationTitles($ids, $language_id);
                return $result;
            case "manufacturers":
                $ids = $this->db->query("SELECT m.manufacturer_id FROM `" . DB_PREFIX . "manufacturer` m WHERE m.manufacturer_id NOT IN (SELECT manufacturer_id FROM `" . DB_PREFIX . "seo_manufacturer_description` smd WHERE m.manufacturer_id=manufacturer_id AND meta_title!='' AND `language_id`=" . $language_id . ")")->rows;

                $result = $this->SeoManufacturerTitles($ids, $language_id);
                return $result;
            case "manufacturers_all":
                $this->db->query("UPDATE `" . DB_PREFIX . "seo_manufacturer_description` SET `meta_title` = '' WHERE `language_id` = '" . $language_id . "'");
                
                $ids = $this->db->query("SELECT m.manufacturer_id FROM `" . DB_PREFIX . "manufacturer` m")->rows;
               
                $result = $this->SeoManufacturerTitles($ids, $language_id);
                return $result;
            default:
                return true;
        }
    }
    
    private function SeoProductTitles($product_ids = array(), $language_id) {
        $pattern    = $this->getSetting('product_title_string');
        $search     = array('[product]', '[model]', '[sku]', '[upc]', '[site_name]');
        $counter    = 0;
        $site_name  = '';
        $items      = array();

        // Multi-language Title format - backward compatible
        $pattern = json_decode($pattern, true);
        if (is_array($pattern) && isset($pattern[$language_id])) {
            $pattern = $pattern[$language_id];
        }
        
        foreach ($product_ids as $id) {
            $product_info = $this->db->query("SELECT p.*, pd.* FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd on (p.product_id=pd.product_id AND pd.language_id=" . $language_id . ") WHERE p.product_id=" . $id['product_id'] . " LIMIT 1")->row;
            
            if (!empty($product_info['name'])) {
                $site_name = $this->getSiteName($language_id);

                $replace = array($product_info['name'], $product_info['model'], $product_info['sku'], $product_info['upc'], $site_name);

                $string = str_replace($search, $replace, $pattern);

                $this->db->query("UPDATE `" . DB_PREFIX . "product_description` SET `meta_title`='" . $this->db->escape($string) . "' WHERE `product_id`=" . $id['product_id'] . " AND `language_id` = '" . $language_id . "'");

                $counter++;
                $items[$product_info['product_id']] = array('name' => $product_info['name'], 'result' => $string);
            }
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    private function SeoCategoryTitles($category_ids = array(), $language_id) {
        $pattern    = $this->getSetting('category_title_string');
        $search     = array('[category]','[site_name]');
        $counter    = 0;
        $site_name  = '';
        $items      = array();

        // Multi-language Title format - backward compatible
        $pattern = json_decode($pattern, true);
        if (is_array($pattern) && isset($pattern[$language_id])) {
            $pattern = $pattern[$language_id];
        }
        
        foreach ($category_ids as $id) {
            $category_info = $this->db->query("SELECT cd.* FROM `" . DB_PREFIX . "category_description` cd WHERE cd.category_id=" . $id['category_id'] . " AND cd.language_id=" . $language_id . " LIMIT 1")->row;
            
            if (!empty($category_info['name'])) {
                $site_name = $this->getSiteName($language_id);

                $replace = array($category_info['name'], $site_name);

                $string = str_replace($search, $replace, $pattern);

                $this->db->query("UPDATE `" . DB_PREFIX . "category_description` SET `meta_title`='" . $this->db->escape($string) . "' WHERE `category_id`=" . $id['category_id'] . " AND `language_id` = '" . $language_id . "'");

                $counter++;
                $items[$category_info['category_id']] = array('name' => $category_info['name'], 'result' => $string);
            }
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    private function SeoInformationTitles($information_ids = array(), $language_id) {
        $pattern    = $this->getSetting('information_title_string');
        $search     = array('[information]','[site_name]');
        $counter    = 0;
        $site_name  = '';
        $items      = array();

        // Multi-language Title format - backward compatible
        $pattern = json_decode($pattern, true);
        if (is_array($pattern) && isset($pattern[$language_id])) {
            $pattern = $pattern[$language_id];
        }

        foreach ($information_ids as $id) {
            $information_info = $this->db->query("SELECT i.* FROM `" . DB_PREFIX . "information_description` i WHERE i.information_id=" . $id['information_id'] . " AND i.language_id=" . $language_id . " LIMIT 1")->row;
            
            if (!empty($information_info['title'])) {
                $site_name = $this->getSiteName($language_id);

                $replace = array($information_info['title'], $site_name);

                $string = str_replace($search, $replace, $pattern);

                $this->db->query("UPDATE `" . DB_PREFIX . "information_description` SET `meta_title`='" . $this->db->escape($string) . "' WHERE `information_id`=" . $id['information_id'] . " AND `language_id` = '" . $language_id . "'");

                $counter++;
                $items[$information_info['information_id']] = array('name' => $information_info['title'], 'result' => $string);
            }
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    private function SeoManufacturerTitles($manufacturer_ids = array(), $language_id) {
        $pattern    = $this->getSetting('manufacturer_title_string');
        $search     = array('[manufacturer]','[site_name]');
        $counter    = 0;
        $site_name  = '';
        $items      = array();

        // Multi-language Title format - backward compatible
        $pattern = json_decode($pattern, true);
        if (is_array($pattern) && isset($pattern[$language_id])) {
            $pattern = $pattern[$language_id];
        }

        foreach ($manufacturer_ids as $id) {
            $manufacturer_info = $this->db->query("SELECT m.* FROM `" . DB_PREFIX . "manufacturer` m WHERE m.manufacturer_id=" . $id['manufacturer_id'] . " LIMIT 1")->row;
            $manufacturer_description = $this->db->query("SELECT m.* FROM `" . DB_PREFIX . "seo_manufacturer_description` m WHERE m.manufacturer_id=" . $id['manufacturer_id'] . " AND `language_id` = '" . $language_id . "' LIMIT 1");
            
            if (!empty($manufacturer_info['name'])) {
                $site_name = $this->getSiteName($language_id);

                $replace = array($manufacturer_info['name'], $site_name);

                $string = str_replace($search, $replace, $pattern);
                
                if ($manufacturer_description->num_rows == 0) {
                    $this->db->query("INSERT `" . DB_PREFIX . "seo_manufacturer_description` SET `meta_title`='" . $this->db->escape($string) . "', `manufacturer_id`='" . $id['manufacturer_id'] . "', `language_id` = '" . $language_id . "'");
                } else {
                    $this->db->query("UPDATE `" . DB_PREFIX . "seo_manufacturer_description` SET `meta_title`='" . $this->db->escape($string) . "' WHERE `manufacturer_id`='" . $id['manufacturer_id'] . "' AND `language_id` = '" . $language_id . "'");
                }
                
                $counter++;
                $items[$manufacturer_info['manufacturer_id']] = array('name' => $manufacturer_info['name'], 'result' => $string);
            }
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    public function checkSEODescriptionsCount($store_id = '0') {
        $this->load->model('localisation/language');
		$languages                                  = $this->model_localisation_language->getLanguages();
        $total_products                             = array();
        $total_products_with_meta_descriptions      = array();
        $total_categories                           = array();
        $total_categories_with_meta_descriptions    = array();
        $total_informations                         = array();
        $total_informations_with_meta_descriptions  = array();
        $total_manufacturers                        = array();
        $total_manufacturers_with_meta_descriptions = array();
        $total_products_difference                  = array();
        $total_categories_difference                = array();
        $total_informations_difference              = array();
        $total_manufacturers_difference             = array();
        
        foreach ($languages as $language) { 
            $total_products[$language['language_id']]       = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p2s.store_id = " . (int)$store_id)->row['count'];
            $total_products_with_meta_descriptions[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "product_description` pd LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (pd.product_id = p2s.product_id) WHERE pd.`meta_description` != '' AND pd.`language_id` = " . $language['language_id'] . " AND p2s.store_id = " . (int)$store_id)->row['count'];
            $total_products_difference[$language['language_id']] = $total_products[$language['language_id']]-$total_products_with_meta_descriptions[$language['language_id']];

            $total_categories[$language['language_id']]     = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.category_id = c2s.category_id) WHERE c2s.store_id = " . (int)$store_id)->row['count'];
            $total_categories_with_meta_descriptions[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "category_description` cd LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (cd.category_id = c2s.category_id) WHERE cd.`meta_description` != '' AND cd.`language_id` = " . $language['language_id'] . " AND c2s.store_id = " . (int)$store_id)->row['count'];
            $total_categories_difference[$language['language_id']] = $total_categories[$language['language_id']]-$total_categories_with_meta_descriptions[$language['language_id']];

            $total_informations[$language['language_id']]   = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "information` i LEFT JOIN `" . DB_PREFIX . "information_to_store` i2s ON (i.information_id = i2s.information_id) WHERE i2s.store_id = " . (int)$store_id)->row['count'];
            $total_informations_with_meta_descriptions[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "information_description` id LEFT JOIN `" . DB_PREFIX . "information_to_store` i2s ON (id.information_id = i2s.information_id) WHERE id.`meta_description` != '' AND id.`language_id` = " . $language['language_id'] . " AND i2s.store_id = " . (int)$store_id)->row['count'];
            $total_informations_difference[$language['language_id']] = $total_informations[$language['language_id']]-$total_informations_with_meta_descriptions[$language['language_id']];
            
            $total_manufacturers[$language['language_id']]   = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "manufacturer` m LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = " . (int)$store_id)->row['count'];
            $total_manufacturers_with_meta_descriptions[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "seo_manufacturer_description` md LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` m2s ON (md.manufacturer_id = m2s.manufacturer_id) WHERE md.`meta_description` != '' AND md.`language_id` = " . $language['language_id'] . " AND m2s.store_id = " . (int)$store_id)->row['count'];
            $total_manufacturers_difference[$language['language_id']] = $total_manufacturers[$language['language_id']]-$total_manufacturers_with_meta_descriptions[$language['language_id']];
        }
        
        return array(
            'total_products'                => $total_products,
            'total_products_meta'           => $total_products_with_meta_descriptions,
            'total_categories'              => $total_categories,
            'total_categories_meta'         => $total_categories_with_meta_descriptions,
            'total_informations'            => $total_informations,
            'total_informations_meta'       => $total_informations_with_meta_descriptions,
            'total_manufacturers'           => $total_manufacturers,
            'total_manufacturers_meta'      => $total_manufacturers_with_meta_descriptions,
            'total_products_difference'     => $total_products_difference,
            'total_categories_difference'   => $total_categories_difference,
            'total_informations_difference' => $total_informations_difference,
            'total_manufacturers_difference' => $total_manufacturers_difference
        );
    }
    
    public function createSEODescriptions($type = '', $language_id = '1') {
        $ids = array();
        $pattern = '';
        
        switch($type) {
            case "categories":
                $ids = $this->db->query("SELECT c.category_id FROM `" . DB_PREFIX . "category_description` c WHERE c.meta_description='' AND `language_id`=" . $language_id . "")->rows;

                $result = $this->SeoCategoryDescriptions($ids, $language_id);
                return $result;
            case "categories_all":
                $ids = $this->db->query("SELECT c.category_id FROM `" . DB_PREFIX . "category_description` c WHERE `language_id`=" . $language_id . "")->rows;

                $result = $this->SeoCategoryDescriptions($ids, $language_id);
                return $result;
            case "products":
                $ids = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product_description` p WHERE p.meta_description='' AND `language_id`=" . $language_id . "")->rows;
                
                $result = $this->SeoProductDescriptions($ids, $language_id);
                return $result;
            case "products_all":
                $ids = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product_description` p WHERE `language_id`=" . $language_id . "")->rows;
                
                $result = $this->SeoProductDescriptions($ids, $language_id);
                return $result;
            case "informations":
                $ids = $this->db->query("SELECT i.information_id FROM `" . DB_PREFIX . "information_description` i WHERE i.meta_description='' AND `language_id`=" . $language_id . "")->rows;
               
                $result = $this->SeoInformationDescriptions($ids, $language_id);
                return $result;
            case "informations_all":
                $ids = $this->db->query("SELECT i.information_id FROM `" . DB_PREFIX . "information_description` i WHERE `language_id`=" . $language_id . "")->rows;
               
                $result = $this->SeoInformationDescriptions($ids, $language_id);
                return $result;
            case "manufacturers":
                $ids = $this->db->query("SELECT m.manufacturer_id FROM `" . DB_PREFIX . "manufacturer` m WHERE m.manufacturer_id NOT IN (SELECT manufacturer_id FROM `" . DB_PREFIX . "seo_manufacturer_description` smd WHERE m.manufacturer_id=manufacturer_id AND meta_description!='' AND `language_id`=" . $language_id . ")")->rows;

                $result = $this->SeoManufacturerDescriptions($ids, $language_id);
                return $result;
            case "manufacturers_all":
                $this->db->query("UPDATE `" . DB_PREFIX . "seo_manufacturer_description` SET `meta_description` = '' WHERE `language_id` = '" . $language_id . "'");
                
                $ids = $this->db->query("SELECT m.manufacturer_id FROM `" . DB_PREFIX . "manufacturer` m")->rows;
               
                $result = $this->SeoManufacturerDescriptions($ids, $language_id);
                return $result;
            default:
                return true;
        }
    }
    
    private function SeoProductDescriptions($product_ids = array(), $language_id) {
        $pattern    = $this->getSetting('product_description_string');
        $wordLimit  = $this->getSetting('meta_description_word_limit');
        $search     = array('[product]', '[description]', '[model]', '[sku]', '[upc]', '[site_name]');
        $counter    = 0;
        $site_name  = '';
        $items      = array();
        
        foreach ($product_ids as $id) {
            $product_info = $this->db->query("SELECT p.*, pd.* FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd on (p.product_id=pd.product_id AND pd.language_id=" . $language_id . ") WHERE p.product_id=" . $id['product_id'] . " LIMIT 1")->row;
            
            if (!empty($product_info)) {
                $site_name = $this->getSiteName($language_id);

                if (empty($product_info['description'])) continue;
                $description = $this->formatMetaDescription($product_info['description'], $wordLimit);

                $replace = array($product_info['name'], $description, $product_info['model'], $product_info['sku'], $product_info['upc'], $site_name);

                $string = str_replace($search, $replace, $pattern);

                $this->db->query("UPDATE `" . DB_PREFIX . "product_description` SET `meta_description`='" . $this->db->escape($string) . "' WHERE `product_id`=" . $id['product_id'] . " AND `language_id` = '" . $language_id . "'");

                $counter++;
                $items[$product_info['product_id']] = array('name' => $product_info['name'], 'result' => $string);
            }
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    private function SeoCategoryDescriptions($category_ids = array(), $language_id) {
        $pattern    = $this->getSetting('category_description_string');
        $wordLimit  = $this->getSetting('meta_description_word_limit');
        $search     = array('[category]','[description]','[site_name]');
        $counter    = 0;
        $site_name  = '';
        $items      = array();

        foreach ($category_ids as $id) {
            $category_info = $this->db->query("SELECT cd.* FROM `" . DB_PREFIX . "category_description` cd WHERE cd.category_id=" . $id['category_id'] . " AND cd.language_id=" . $language_id . " LIMIT 1")->row;
            
            if (!empty($category_info)) {
                $site_name = $this->getSiteName($language_id);

                if (empty($category_info['description'])) {
                    $description = $this->formatMetaDescription($category_info['name'], $wordLimit);
                } else {
                    $description = $this->formatMetaDescription($category_info['description'], $wordLimit);
                }

                $replace = array($category_info['name'], $description, $site_name);

                $string = str_replace($search, $replace, $pattern);

                $this->db->query("UPDATE `" . DB_PREFIX . "category_description` SET `meta_description`='" . $this->db->escape($string) . "' WHERE `category_id`=" . $id['category_id'] . " AND `language_id` = '" . $language_id . "'");

                $counter++;
                $items[$category_info['category_id']] = array('name' => $category_info['name'], 'result' => $string);
            }
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    private function SeoInformationDescriptions($information_ids = array(), $language_id) {
        $pattern    = $this->getSetting('information_description_string');
        $wordLimit  = $this->getSetting('meta_description_word_limit');
        $search     = array('[information]','[description]','[site_name]');
        $counter    = 0;
        $site_name  = '';
        $items      = array();

        foreach ($information_ids as $id) {
            $information_info = $this->db->query("SELECT i.* FROM `" . DB_PREFIX . "information_description` i WHERE i.information_id=" . $id['information_id'] . " AND i.language_id=" . $language_id . " LIMIT 1")->row;
            
            if (!empty($information_info)) {
                $site_name = $this->getSiteName($language_id);

                if (empty($information_info['description'])) continue;
                $description = $this->formatMetaDescription($information_info['description'], $wordLimit);

                $replace = array($information_info['title'], $description, $site_name);

                $string = str_replace($search, $replace, $pattern);

                $this->db->query("UPDATE `" . DB_PREFIX . "information_description` SET `meta_description`='" . $this->db->escape($string) . "' WHERE `information_id`=" . $id['information_id'] . " AND `language_id` = '" . $language_id . "'");

                $counter++;
                $items[$information_info['information_id']] = array('name' => $information_info['title'], 'result' => $string);
            }
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    private function SeoManufacturerDescriptions($manufacturer_ids = array(), $language_id) {
        $pattern    = $this->getSetting('manufacturer_description_string');
        $wordLimit  = $this->getSetting('meta_description_word_limit');
        $search     = array('[manufacturer]','[site_name]');
        $counter    = 0;
        $site_name  = '';
        $items      = array();
        
        foreach ($manufacturer_ids as $id) {
            $manufacturer_info = $this->db->query("SELECT m.* FROM `" . DB_PREFIX . "manufacturer` m WHERE m.manufacturer_id=" . $id['manufacturer_id'] . " LIMIT 1")->row;
            $manufacturer_description = $this->db->query("SELECT m.* FROM `" . DB_PREFIX . "seo_manufacturer_description` m WHERE m.manufacturer_id=" . $id['manufacturer_id'] . " AND `language_id` = '" . $language_id . "' LIMIT 1");
            
            if (!empty($manufacturer_info['name'])) {
                $site_name = $this->getSiteName($language_id);

                $replace = array($manufacturer_info['name'], $site_name);

                $string = str_replace($search, $replace, $pattern);
                
                if ($manufacturer_description->num_rows == 0) {
                    $this->db->query("INSERT `" . DB_PREFIX . "seo_manufacturer_description` SET `meta_description`='" . $this->db->escape($string) . "', `manufacturer_id`='" . $id['manufacturer_id'] . "', `language_id` = '" . $language_id . "'");
                } else {
                    $this->db->query("UPDATE `" . DB_PREFIX . "seo_manufacturer_description` SET `meta_description`='" . $this->db->escape($string) . "' WHERE `manufacturer_id`='" . $id['manufacturer_id'] . "' AND `language_id` = '" . $language_id . "'");
                }
                
                $counter++;
                $items[$manufacturer_info['manufacturer_id']] = array('name' => $manufacturer_info['name'], 'result' => $string);
            }
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    public function checkSEOKeywordsCount($store_id = '0') {
        $this->load->model('localisation/language');
		$languages                                  = $this->model_localisation_language->getLanguages();
        $total_products                             = array();
        $total_products_with_meta_keywords          = array();
        $total_categories                           = array();
        $total_categories_with_meta_keywords        = array();
        $total_informations                         = array();
        $total_informations_with_meta_keywords      = array();
        $total_manufacturers                        = array();
        $total_manufacturers_with_meta_keywords     = array();
        $total_products_difference                  = array();
        $total_categories_difference                = array();
        $total_informations_difference              = array();
        $total_manufacturers_difference             = array();
        
        foreach ($languages as $language) { 
            $total_products[$language['language_id']]       = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p2s.store_id = " . (int)$store_id)->row['count'];
            $total_products_with_meta_keywords[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "product_description` pd LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (pd.product_id = p2s.product_id) WHERE pd.`meta_keyword` != '' AND pd.`language_id` = " . $language['language_id'] . " AND p2s.store_id = " . (int)$store_id)->row['count'];
            $total_products_difference[$language['language_id']] = $total_products[$language['language_id']]-$total_products_with_meta_keywords[$language['language_id']];

            $total_categories[$language['language_id']]     = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.category_id = c2s.category_id) WHERE c2s.store_id = " . (int)$store_id)->row['count'];
            $total_categories_with_meta_keywords[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "category_description` cd LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (cd.category_id = c2s.category_id) WHERE cd.`meta_keyword` != '' AND cd.`language_id` = " . $language['language_id'] . " AND c2s.store_id = " . (int)$store_id)->row['count'];
            $total_categories_difference[$language['language_id']] = $total_categories[$language['language_id']]-$total_categories_with_meta_keywords[$language['language_id']];

            $total_informations[$language['language_id']]   = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "information` i LEFT JOIN `" . DB_PREFIX . "information_to_store` i2s ON (i.information_id = i2s.information_id) WHERE i2s.store_id = " . (int)$store_id)->row['count'];
            $total_informations_with_meta_keywords[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "information_description` id LEFT JOIN `" . DB_PREFIX . "information_to_store` i2s ON (id.information_id = i2s.information_id) WHERE id.`meta_keyword` != '' AND id.`language_id` = " . $language['language_id'] . " AND i2s.store_id = " . (int)$store_id)->row['count'];
            $total_informations_difference[$language['language_id']] = $total_informations[$language['language_id']]-$total_informations_with_meta_keywords[$language['language_id']];
            
            $total_manufacturers[$language['language_id']]   = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "manufacturer` m LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = " . (int)$store_id)->row['count'];
            $total_manufacturers_with_meta_keywords[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "seo_manufacturer_description` md LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` m2s ON (md.manufacturer_id = m2s.manufacturer_id) WHERE md.`meta_keyword` != '' AND md.`language_id` = " . $language['language_id'] . " AND m2s.store_id = " . (int)$store_id)->row['count'];
            $total_manufacturers_difference[$language['language_id']] = $total_manufacturers[$language['language_id']]-$total_manufacturers_with_meta_keywords[$language['language_id']];
        }
        
        return array(
            'total_products'                => $total_products,
            'total_products_meta'           => $total_products_with_meta_keywords,
            'total_categories'              => $total_categories,
            'total_categories_meta'         => $total_categories_with_meta_keywords,
            'total_informations'            => $total_informations,
            'total_informations_meta'       => $total_informations_with_meta_keywords,
            'total_manufacturers'           => $total_manufacturers,
            'total_manufacturers_meta'      => $total_manufacturers_with_meta_keywords,
            'total_products_difference'     => $total_products_difference,
            'total_categories_difference'   => $total_categories_difference,
            'total_informations_difference' => $total_informations_difference,
            'total_manufacturers_difference' => $total_manufacturers_difference
        );
    }
    
    public function checkProductHeadingTagsCount($store_id = '0') {

        $this->load->model('localisation/language');

        $languages                                  = $this->model_localisation_language->getLanguages();

        $total_products                             = array();

        $total_products_with_h1_tags                = array();

        $total_products_with_h2_tags                = array();
        
        $total_h1_tags_difference                   = array();

        $total_h2_tags_difference                   = array();


        foreach ($languages as $language) { 

            $total_products[$language['language_id']]       = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p2s.store_id = " . (int)$store_id)->row['count'];

            $total_products_with_h1_tags[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "seo_product_description` spd LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (spd.product_id = p2s.product_id) WHERE spd.`h1` != '' AND spd.`language_id` = " . $language['language_id'] . " AND p2s.store_id = " . (int)$store_id)->row['count'];

            $total_h1_tags_difference[$language['language_id']] = $total_products[$language['language_id']]-$total_products_with_h1_tags[$language['language_id']];

            $total_products_with_h2_tags[$language['language_id']] = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "seo_product_description` spd LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (spd.product_id = p2s.product_id) WHERE spd.`h2` != '' AND spd.`language_id` = " . $language['language_id'] . " AND p2s.store_id = " . (int)$store_id)->row['count'];

            $total_h2_tags_difference[$language['language_id']] = $total_products[$language['language_id']]-$total_products_with_h2_tags[$language['language_id']];

        }

        

        return array(

            'total_products'                            => $total_products,

            'total_products_with_h1_tags'               => $total_products_with_h1_tags,

            'total_products_with_h2_tags'               => $total_products_with_h2_tags,

            'total_h1_tags_difference'                  => $total_h1_tags_difference,

            'total_h2_tags_difference'                  => $total_h2_tags_difference

        );

    }

    public function createSEOKeywords($type = '', $language_id = '1') {
        $ids = array();
        $pattern = '';
        
        switch($type) {
            case "categories":
                $ids = $this->db->query("SELECT c.category_id FROM `" . DB_PREFIX . "category_description` c WHERE c.meta_keyword='' AND `language_id`=" . $language_id . "")->rows;

                $result = $this->SeoCategoryKeywords($ids, $language_id);
                return $result;
            case "categories_all":
                $ids = $this->db->query("SELECT c.category_id FROM `" . DB_PREFIX . "category_description` c WHERE `language_id`=" . $language_id . "")->rows;

                $result = $this->SeoCategoryKeywords($ids, $language_id);
                return $result;
            case "products":
                $ids = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product_description` p WHERE p.meta_keyword='' AND `language_id`=" . $language_id . "")->rows;
                
                $result = $this->SeoProductKeywords($ids, $language_id);
                return $result;
            case "products_all":
                $ids = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product_description` p WHERE `language_id`=" . $language_id . "")->rows;
                
                $result = $this->SeoProductKeywords($ids, $language_id);
                return $result;
            case "informations":
                $ids = $this->db->query("SELECT i.information_id FROM `" . DB_PREFIX . "information_description` i WHERE i.meta_keyword='' AND `language_id`=" . $language_id . "")->rows;
               
                $result = $this->SeoInformationKeywords($ids, $language_id);
                return $result;
            case "informations_all":
                $ids = $this->db->query("SELECT i.information_id FROM `" . DB_PREFIX . "information_description` i WHERE `language_id`=" . $language_id . "")->rows;
               
                $result = $this->SeoInformationKeywords($ids, $language_id);
                return $result;
            case "manufacturers":
                $ids = $this->db->query("SELECT m.manufacturer_id FROM `" . DB_PREFIX . "manufacturer` m WHERE m.manufacturer_id NOT IN (SELECT manufacturer_id FROM `" . DB_PREFIX . "seo_manufacturer_description` smd WHERE m.manufacturer_id=manufacturer_id AND meta_keyword!='' AND `language_id`=" . $language_id . ")")->rows;

                $result = $this->SeoManufacturerKeywords($ids, $language_id);
                return $result;
            case "manufacturers_all":
                $this->db->query("UPDATE `" . DB_PREFIX . "seo_manufacturer_description` SET `meta_keyword` = '' WHERE `language_id` = '" . $language_id . "'");
                
                $ids = $this->db->query("SELECT m.manufacturer_id FROM `" . DB_PREFIX . "manufacturer` m")->rows;
               
                $result = $this->SeoManufacturerKeywords($ids, $language_id);
                return $result;
            default:
                return true;
        }
    }
    
    private function SeoProductKeywords($product_ids = array(), $language_id) {
        $pattern    = $this->getSetting('product_keyword_string');
        $search     = array('[product]', '[model]', '[sku]', '[upc]', '[site_name]');
        $counter    = 0;
        $site_name  = '';
        $items      = array();
        
        foreach ($product_ids as $id) {
            $product_info = $this->db->query("SELECT p.*, pd.* FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd on (p.product_id=pd.product_id AND pd.language_id=" . $language_id . ") WHERE p.product_id=" . $id['product_id'] . " LIMIT 1")->row;
            
            if (!empty($product_info)) {
                $site_name = $this->getSiteName($language_id);

                $replace = array($product_info['name'], $product_info['model'], $product_info['sku'], $product_info['upc'], $site_name);
                $string = str_replace($search, $replace, $pattern);

                $string = $this->formatMetaKeywords($string);

                $this->db->query("UPDATE `" . DB_PREFIX . "product_description` SET `meta_keyword`='" . $this->db->escape($string) . "' WHERE `product_id`=" . $id['product_id'] . " AND `language_id` = '" . $language_id . "'");

                $counter++;
                $items[$product_info['product_id']] = array('name' => $product_info['name'], 'result' => $string);
            }
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    private function SeoCategoryKeywords($category_ids = array(), $language_id) {
        $pattern    = $this->getSetting('category_keyword_string');
        $search     = array('[category]', '[site_name]');
        $counter    = 0;
        $site_name  = '';
        $items      = array();
        
        foreach ($category_ids as $id) {
            $category_info = $this->db->query("SELECT cd.* FROM `" . DB_PREFIX . "category_description` cd WHERE cd.category_id=" . $id['category_id'] . " AND cd.language_id=" . $language_id . " LIMIT 1")->row;
            
            if (!empty($category_info)) {
                $site_name = $this->getSiteName($language_id);

                $replace = array($category_info['name'], $site_name);
                $string = str_replace($search, $replace, $pattern);

                $string = $this->formatMetaKeywords($string);

                $this->db->query("UPDATE `" . DB_PREFIX . "category_description` SET `meta_keyword`='" . $this->db->escape($string) . "' WHERE `category_id`=" . $id['category_id'] . " AND `language_id` = '" . $language_id . "'");

                $counter++;
                $items[$category_info['category_id']] = array('name' => $category_info['name'], 'result' => $string);
            }
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    private function SeoInformationKeywords($information_ids = array(), $language_id) {
        $pattern    = $this->getSetting('information_keyword_string');
        $search     = array('[information]', '[site_name]');
        $counter    = 0;
        $site_name  = '';
        $items      = array();

        foreach ($information_ids as $id) {
            $information_info = $this->db->query("SELECT i.* FROM `" . DB_PREFIX . "information_description` i WHERE i.information_id=" . $id['information_id'] . " AND i.language_id=" . $language_id . " LIMIT 1")->row;
            
            if (!empty($information_info)) {
                $site_name = $this->getSiteName($language_id);

                $replace = array($information_info['title'], $site_name);
                $string = str_replace($search, $replace, $pattern);

                $string = $this->formatMetaKeywords($string);

                $this->db->query("UPDATE `" . DB_PREFIX . "information_description` SET `meta_keyword`='" . $this->db->escape($string) . "' WHERE `information_id`=" . $id['information_id'] . " AND `language_id` = '" . $language_id . "'");

                $counter++;
                $items[$information_info['information_id']] = array('name' => $information_info['title'], 'result' => $string);
            }
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    private function SeoManufacturerKeywords($manufacturer_ids = array(), $language_id) {
        $pattern    = $this->getSetting('manufacturer_keyword_string');
        $search     = array('[manufacturer]', '[site_name]');
        $counter    = 0;
        $site_name  = '';
        $items      = array();
        
        foreach ($manufacturer_ids as $id) {
            $manufacturer_info = $this->db->query("SELECT m.* FROM `" . DB_PREFIX . "manufacturer` m WHERE m.manufacturer_id=" . $id['manufacturer_id'] . " LIMIT 1")->row;
            $manufacturer_description = $this->db->query("SELECT m.* FROM `" . DB_PREFIX . "seo_manufacturer_description` m WHERE m.manufacturer_id=" . $id['manufacturer_id'] . " AND `language_id` = '" . $language_id . "' LIMIT 1");
            
            if (!empty($manufacturer_info['name'])) {
                $site_name = $this->getSiteName($language_id);

                $replace = array($manufacturer_info['name'], $site_name);

                $string = str_replace($search, $replace, $pattern);
                $string = $this->formatMetaKeywords($string);
                
                if ($manufacturer_description->num_rows == 0) {
                    $this->db->query("INSERT `" . DB_PREFIX . "seo_manufacturer_description` SET `meta_keyword`='" . $this->db->escape($string) . "', `manufacturer_id`='" . $id['manufacturer_id'] . "', `language_id` = '" . $language_id . "'");
                } else {
                    $this->db->query("UPDATE `" . DB_PREFIX . "seo_manufacturer_description` SET `meta_keyword`='" . $this->db->escape($string) . "' WHERE `manufacturer_id`='" . $id['manufacturer_id'] . "' AND `language_id` = '" . $language_id . "'");
                }
                
                $counter++;
                $items[$manufacturer_info['manufacturer_id']] = array('name' => $manufacturer_info['name'], 'result' => $string);
            }
        }
        
        return array('counter' => $counter, 'items' => $items);
    }
    
    public function generateImageNames($rename_additional_images = false, $product_id = 0) {
        $language_id        = $this->config->get('config_language_id');
        $pattern            = $this->getSetting('images_filename_string');
        $search             = array('[product]', '[model]', '[sku]', '[upc]');
        
        $sql_where          = $product_id ? ' WHERE p.product_id = ' . (int)$product_id : '';
        $product_info       = $this->db->query("SELECT p.*, pd.* FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd on (p.product_id=pd.product_id AND pd.language_id=" . $language_id . ")" . $sql_where)->rows;
        $counter            = 0;
        $products_no_image  = array();
        $image_dir          = DIR_IMAGE;
        $errors             = false;
        $already_renamed    = 0;
        
        foreach ($product_info as $product) {
            
            if (!empty($product['image'])) {
                $replace = array($product['name'], $product['model'], $product['sku'], $product['upc']);

                if (strpos($pattern, '[attributes]') !== false) {
                    $attributes = $this->db->query("SELECT `attribute_id`, `text` FROM `" . DB_PREFIX . "product_attribute` WHERE `product_id` = " . (int)$product['product_id'] . " AND `language_id` = " . (int)$language_id);

                    if ($attributes->num_rows) {
                        $attribute_values = array();
                        foreach ($attributes->rows as $attribute) {
                            $attributes_values[] = $attribute['text'];
                        }

                        $search[] = '[attributes]';
                        $replace[] = implode('-', $attributes_values);
                    }
                }

                $string = str_replace($search, $replace, $pattern);
            
                $old_image_name = $product['image'];
                $new_image_name = $this->formatSEOUrl($this->db->escape($string));
                
                if (strpos($product['image'], 'no_image') !== false) {
                    continue; // Fix that the no_image.png image dont get renamed
                }
                
                if (file_exists($image_dir.$old_image_name)) {
                    $ext = pathinfo($image_dir.$old_image_name, PATHINFO_EXTENSION);
                    $image_path = str_replace($image_dir, '', dirname($image_dir.$old_image_name));
                    $image_path = ltrim(str_replace(rtrim($image_dir,"/"), '', dirname($image_dir.$old_image_name)), '/');
                    
                    $new_image_name = $image_path.'/'.$new_image_name.'-'.$product['product_id'].'.'.$ext;

                    if ($old_image_name != $new_image_name) {
                        $action = false;
                        
                        if (rename($image_dir.$old_image_name, $image_dir.$new_image_name)) {
                            $action = true;
                            $counter++;
                            
                            if ($action) {
                                $this->db->query("UPDATE `" . DB_PREFIX . "product` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                $this->db->query("UPDATE `" . DB_PREFIX . "product_image` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                $this->db->query("UPDATE `" . DB_PREFIX . "banner_image` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                $this->db->query("UPDATE `" . DB_PREFIX . "category` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                $this->db->query("UPDATE `" . DB_PREFIX . "language` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                $this->db->query("UPDATE `" . DB_PREFIX . "manufacturer` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                $this->db->query("UPDATE `" . DB_PREFIX . "option_value` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                $this->db->query("UPDATE `" . DB_PREFIX . "voucher_theme` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                            }
                        } else {
                            $errors = true;
                        }
                    } else {
                        $already_renamed++;  
                    }
                    
                }
            } else {
                $products_no_image[] = $product['product_id'];
            }

            if ($rename_additional_images) {
                $additonal_images = $this->db->query("SELECT image FROM `" . DB_PREFIX . "product_image` WHERE product_id='" . $product['product_id'] . "'")->rows;
                $add_image_num = 1;

                foreach ($additonal_images as $add_image) {
                    if (!empty($add_image['image'])) {
                        $replace = array($product['name'], $product['model'], $product['sku'], $product['upc']);
                        $string = str_replace($search, $replace, $pattern);
                    
                        $old_image_name = $add_image['image'];
                        $new_image_name = $this->formatSEOUrl($this->db->escape($string));
                        
                        if (strpos($add_image['image'], 'no_image') !== false) {
                            continue; // Fix that the no_image.png image dont get renamed
                        }

                        if (file_exists($image_dir.$old_image_name)) {
                            $ext = pathinfo($image_dir.$old_image_name, PATHINFO_EXTENSION);
                            $image_path = str_replace($image_dir, '', dirname($image_dir.$old_image_name));
                            $image_path = ltrim(str_replace(rtrim($image_dir,"/"), '', dirname($image_dir.$old_image_name)), '/');
                            
                            $new_image_name = $image_path.'/'.$new_image_name.'-'.$product['product_id'].'-'.$add_image_num.'.'.$ext;
                            $add_image_num++;

                            if ($old_image_name != $new_image_name) {
                                $action = false;
                                
                                if (rename($image_dir.$old_image_name, $image_dir.$new_image_name)) {
                                    $action = true;
                                    $counter++;
                                    
                                    if ($action) {
                                        $this->db->query("UPDATE `" . DB_PREFIX . "product` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                        $this->db->query("UPDATE `" . DB_PREFIX . "product_image` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                        $this->db->query("UPDATE `" . DB_PREFIX . "banner_image` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                        $this->db->query("UPDATE `" . DB_PREFIX . "category` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                        $this->db->query("UPDATE `" . DB_PREFIX . "language` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                        $this->db->query("UPDATE `" . DB_PREFIX . "manufacturer` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                        $this->db->query("UPDATE `" . DB_PREFIX . "option_value` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                        $this->db->query("UPDATE `" . DB_PREFIX . "voucher_theme` SET `image` = '" . $this->db->escape($new_image_name) . "' WHERE `image` = '" . $this->db->escape($old_image_name) . "'");
                                    }
                                } else {
                                    $errors = true;
                                }
                            } else {
                                $already_renamed++;  
                            }
                            
                        }
                    }
                }
               
            }
        }
        
        if ($errors) {
            $errors = 'ERR_IRNP';
        }
        $result = array(
            'renamed_images'            => $counter,
            'already_renamed'           => $already_renamed,
            'products_with_no_image'    => array_filter($products_no_image),
            'errors'                    => $errors
        );
        
        return $result;
    }
    
    public function getSeoScore($save_data = true, $store_id = '0') {
        $score              = 0;
        $fixes              = array();
        $fixed              = array();
        $results            = array();
        $perfect_results    = 0;
        $bad_results        = 0;
        $improve_results    = 0;
        
        if ($this->config->get('config_seo_url') == 1) {
            $results[] = 100;
        } else {
            $results[] = 0;
            $fixes['config_seo_url'] = true;
        }
        
        $sets                       = array();
        $sets['seo_urls']           = $this->checkSEOUrlsCount($store_id);
        $sets['seo_titles']         = $this->checkSEOTitlesCount($store_id);
        $sets['seo_descriptions']   = $this->checkSEODescriptionsCount($store_id);
        $sets['seo_keywords']       = $this->checkSEOKeywordsCount($store_id);
        
        foreach ($sets as $index => $set) {
            if (isset($set['total_products_difference'])) {
                if (reset($set['total_products']) != 0) {
                    $percentage = ((reset($set['total_products']) - reset($set['total_products_difference'])) / (reset($set['total_products']))) * 100;
                } else {
                    $percentage = 100; 
                }
                
                $results[] = $percentage;
                if ($percentage != '100') {
                    $fixes['product_' . $index] = true; 
                } else {
                    $fixed['product_' . $index] = true; 
                }
            }
            
            if (isset($set['total_categories_difference'])) {
                if (reset($set['total_categories']) != 0) {
                    $percentage = ((reset($set['total_categories']) - reset($set['total_categories_difference'])) / (reset($set['total_categories']))) * 100;
                } else {
                    $percentage = 100;
                }
                
                $results[] = $percentage;
                if ($percentage != '100') {
                    $fixes['category_' . $index] = true; 
                } else {
                    $fixed['category_' . $index] = true; 
                }
            }
            
            if (isset($set['total_informations_difference'])) {
                if (reset($set['total_informations']) != 0) {
                    $percentage = ((reset($set['total_informations']) - reset($set['total_informations_difference'])) / (reset($set['total_informations']))) * 100;
                } else {
                    $percentage = 100;
                }
                
                $results[] = $percentage;
                if ($percentage != '100') {
                    $fixes['information_' . $index] = true; 
                } else {
                    $fixed['information_' . $index] = true; 
                }
            }
            
            if (isset($set['total_manufacturers_difference'])) {
                if (reset($set['total_manufacturers']) != 0) {
                    $percentage = ((reset($set['total_manufacturers']) - reset($set['total_manufacturers_difference'])) / (reset($set['total_manufacturers']))) * 100;
                } else {
                    $percentage = 100; 
                }
                
                $results[] = $percentage;
                if ($percentage != '100') {
                    $fixes['manufacturer_' . $index] = true; 
                } else {
                    $fixed['manufacturer_' . $index] = true;  
                }
            }
        }
        
        if ($this->getSetting('richsnippets_product_data', $store_id) == '1') {
            $results[] = 100;
            $fixed['richsnippets_product_data'] = true; 
        } else {
            $results[] = 0;
            $fixes['richsnippets_product_data'] = true; 
        }
        
        if ($this->getSetting('richsnippets_product_breadcrumbs', $store_id) == '1') {
            $results[] = 100;
            $fixed['richsnippets_product_breadcrumbs'] = true; 
        } else {
            $results[] = 0;
            $fixes['richsnippets_product_breadcrumbs'] = true; 
        }
        
        if ($this->getSetting('richsnippets_company_info', $store_id) == '1') {
            $results[] = 100;
            $fixed['richsnippets_company_info'] = true; 
        } else {
            $results[] = 0;
            $fixes['richsnippets_company_info'] = true; 
        }
        
        
        $this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
        
        if (count($languages)>1) {
            if ($this->getSetting('hreflang_products', $store_id) == '1') {
                $results[] = 100;
                $fixed['hreflang_products'] = true; 
            } else {
                $results[] = 0;
                $fixes['hreflang_products'] = true; 
            }
            
            if ($this->getSetting('hreflang_categories', $store_id) == '1') {
                $results[] = 100;
                $fixed['hreflang_categories'] = true; 
            } else {
                $results[] = 0;
                $fixes['hreflang_categories'] = true; 
            }

            if ($this->getSetting('hreflang_manufacturers', $store_id) == '1') {
                $results[] = 100;
                $fixed['hreflang_manufacturers'] = true; 
            } else {
                $results[] = 0;
                $fixes['hreflang_manufacturers'] = true; 
            }

            if ($this->getSetting('hreflang_informations', $store_id) == '1') {
                $results[] = 100;
                $fixed['hreflang_informations'] = true; 
            } else {
                $results[] = 0;
                $fixes['hreflang_informations'] = true; 
            }

        }
        
        /*
        if ($this->getSetting('seo_image_titles_date') != '') {
            $results[] = 100;
            $fixed['seo_images_rename'] = true; 
        } else {
            $results[] = 0;
            $fixes['seo_images_rename'] = true; 
        }
        */
        
        $htaccess_file_path = dirname(DIR_SYSTEM).'/.htaccess';
        if (file_exists($htaccess_file_path)) { 
            $results[] = 100;
            $fixed['htaccess_exists'] = true; 
        } else {
            $results[] = 0;
            $fixes['htaccess_exists'] = true; 
        }
        
        $robots_file_path = dirname(DIR_SYSTEM).'/robots.txt';
        if (file_exists($robots_file_path)) { 
            $results[] = 100;
            $fixed['robots_exists'] = true; 
        } else {
            $results[] = 0;
            $fixes['robots_exists'] = true; 
        }
          
        foreach ($results as $result) {
            if ($result == '100') $perfect_results++;
            if ($result >= '40' && $result < '100') $improve_results++;
            if ($result < '40') $bad_results++;
            $score += $result;
        }
        $score      = number_format(($score/count($results)),0);
        
        $total_results = count($results);

        if ($save_data) {
            $this->saveSetting('seo_score', $score, $store_id);
            $this->saveSetting('seo_score_last_checked', date('M d, Y - H:i:s'), $store_id);
        }    
        return array(
            'score' => $score,
            'fixes' => $fixes,
            'fixed' => $fixed,
            'total' => $total_results,
            'perfect_results' => number_format((($total_results - ($improve_results+$bad_results)) / ($total_results)) * 100, 0),
            'improve_results' => number_format((($total_results - ($perfect_results+$bad_results)) / ($total_results)) * 100, 0),
            'bad_results' => number_format((($total_results - ($improve_results+$perfect_results)) / ($total_results)) * 100, 0)
        );
        
    }
    
    private function getSiteName($language_id = 1) {
        $site_name = '';
        
        $site_name_array = $this->config->get('config_meta_title');
        
        if (is_array($site_name_array) && !empty($site_name_array[$language_id])) {
            $site_name = $site_name_array[$language_id];
        } else if (is_array($site_name_array) && empty($site_name_array[$language_id])) {
            $site_name = reset($site_name_array);
        } else if (!is_array($site_name_array) && !empty($site_name_array)) {
            $site_name = $site_name_array;  
        } else {
            $site_name = '';
        }
        
        return $site_name;
    }
    
    private function formatMetaKeywords($string) {
        $keyword    = '';
        $string     = trim(str_replace(',', ' ', $string));
        $string     = explode(' ', $string);
        
        foreach ($string as $index => $str) {
            $str = trim($str);
            if (empty($str) || $str == ' ') {
                unset($string[$index]);
            } else {
                $string[$index] = trim($str);
            }
        }
        
        $keyword    = implode(', ', $string);
        
        return trim($keyword);
    }
    
    private function formatMetaDescription($string, $wordCount = 10) {
        $string = strip_tags(html_entity_decode($string, ENT_QUOTES, "UTF-8"));
        $string = str_replace(array("\r", "\n"), array(" ", " "), $string);
        $string = trim(preg_replace('/\s+/', ' ', $string));
        
        return implode( 
            '', 
            array_slice( 
              preg_split(
                '/([\s,\.;\?\!]+)/', 
                $string, 
                $wordCount*2+1, 
                PREG_SPLIT_DELIM_CAPTURE
              ),
              0,
              $wordCount*2-1
            )
        );
    }
    
    /**
     * Unaccent the input string string. An example string like `ÀØėÿᾜὨζὅБю`
     * will be translated to `AOeyIOzoBY`. More complete than :
     *   strtr( (string)$str,
     *          "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
     *          "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn" );
     *
     * @param $str input string
     * @param $utf8 if null, function will detect input string encoding
     * @author http://www.evaisse.net/2008/php-translit-remove-accent-unaccent-21001
     * @return string input string without accent
     */
    private function remove_accents($str, $utf8=true) {
        $str = (string)$str;
        if( is_null($utf8) ) {
            if( !function_exists('mb_detect_encoding') ) {
                $utf8 = (strtolower( mb_detect_encoding($str) )=='utf-8');
            } else {
                $length = strlen($str);
                $utf8 = true;
                for ($i=0; $i < $length; $i++) {
                    $c = ord($str[$i]);
                    if ($c < 0x80) $n = 0; # 0bbbbbbb
                    elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
                    elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
                    elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
                    elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
                    elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
                    else return false; # Does not match any model
                    for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
                        if ((++$i == $length)
                            || ((ord($str[$i]) & 0xC0) != 0x80)) {
                            $utf8 = false;
                            break;
                        }

                    }
                }
            }

        }

        if(!$utf8)
            $str = utf8_encode($str);

        $transliteration = array(
        'Ĳ' => 'I', 'Ö' => 'O','Œ' => 'O','Ü' => 'U','ä' => 'a','æ' => 'a',
        'ĳ' => 'i','ö' => 'o','œ' => 'o','ü' => 'u','ß' => 's','ſ' => 's',
        'À' => 'A','Á' => 'A','Â' => 'A','Ã' => 'A','Ä' => 'A','Å' => 'A',
        'Æ' => 'A','Ā' => 'A','Ą' => 'A','Ă' => 'A','Ç' => 'C','Ć' => 'C',
        'Č' => 'C','Ĉ' => 'C','Ċ' => 'C','Ď' => 'D','Đ' => 'D','È' => 'E',
        'É' => 'E','Ê' => 'E','Ë' => 'E','Ē' => 'E','Ę' => 'E','Ě' => 'E',
        'Ĕ' => 'E','Ė' => 'E','Ĝ' => 'G','Ğ' => 'G','Ġ' => 'G','Ģ' => 'G',
        'Ĥ' => 'H','Ħ' => 'H','Ì' => 'I','Í' => 'I','Î' => 'I','Ï' => 'I',
        'Ī' => 'I','Ĩ' => 'I','Ĭ' => 'I','Į' => 'I','İ' => 'I','Ĵ' => 'J',
        'Ķ' => 'K','Ľ' => 'K','Ĺ' => 'K','Ļ' => 'K','Ŀ' => 'K','Ł' => 'L',
        'Ñ' => 'N','Ń' => 'N','Ň' => 'N','Ņ' => 'N','Ŋ' => 'N','Ò' => 'O',
        'Ó' => 'O','Ô' => 'O','Õ' => 'O','Ø' => 'O','Ō' => 'O','Ő' => 'O',
        'Ŏ' => 'O','Ŕ' => 'R','Ř' => 'R','Ŗ' => 'R','Ś' => 'S','Ş' => 'S',
        'Ŝ' => 'S','Ș' => 'S','Š' => 'S','Ť' => 'T','Ţ' => 'T','Ŧ' => 'T',
        'Ț' => 'T','Ù' => 'U','Ú' => 'U','Û' => 'U','Ū' => 'U','Ů' => 'U',
        'Ű' => 'U','Ŭ' => 'U','Ũ' => 'U','Ų' => 'U','Ŵ' => 'W','Ŷ' => 'Y',
        'Ÿ' => 'Y','Ý' => 'Y','Ź' => 'Z','Ż' => 'Z','Ž' => 'Z','à' => 'a',
        'á' => 'a','â' => 'a','ã' => 'a','ā' => 'a','ą' => 'a','ă' => 'a',
        'å' => 'a','ç' => 'c','ć' => 'c','č' => 'c','ĉ' => 'c','ċ' => 'c',
        'ď' => 'd','đ' => 'd','è' => 'e','é' => 'e','ê' => 'e','ë' => 'e',
        'ē' => 'e','ę' => 'e','ě' => 'e','ĕ' => 'e','ė' => 'e','ƒ' => 'f',
        'ĝ' => 'g','ğ' => 'g','ġ' => 'g','ģ' => 'g','ĥ' => 'h','ħ' => 'h',
        'ì' => 'i','í' => 'i','î' => 'i','ï' => 'i','ī' => 'i','ĩ' => 'i',
        'ĭ' => 'i','į' => 'i','ı' => 'i','ĵ' => 'j','ķ' => 'k','ĸ' => 'k',
        'ł' => 'l','ľ' => 'l','ĺ' => 'l','ļ' => 'l','ŀ' => 'l','ñ' => 'n',
        'ń' => 'n','ň' => 'n','ņ' => 'n','ŉ' => 'n','ŋ' => 'n','ò' => 'o',
        'ó' => 'o','ô' => 'o','õ' => 'o','ø' => 'o','ō' => 'o','ő' => 'o',
        'ŏ' => 'o','ŕ' => 'r','ř' => 'r','ŗ' => 'r','ś' => 's','š' => 's',
        'ť' => 't','ù' => 'u','ú' => 'u','û' => 'u','ū' => 'u','ů' => 'u',
        'ű' => 'u','ŭ' => 'u','ũ' => 'u','ų' => 'u','ŵ' => 'w','ÿ' => 'y',
        'ý' => 'y','ŷ' => 'y','ż' => 'z','ź' => 'z','ž' => 'z','Α' => 'A',
        'Ά' => 'A','Ἀ' => 'A','Ἁ' => 'A','Ἂ' => 'A','Ἃ' => 'A','Ἄ' => 'A',
        'Ἅ' => 'A','Ἆ' => 'A','Ἇ' => 'A','ᾈ' => 'A','ᾉ' => 'A','ᾊ' => 'A',
        'ᾋ' => 'A','ᾌ' => 'A','ᾍ' => 'A','ᾎ' => 'A','ᾏ' => 'A','Ᾰ' => 'A',
        'Ᾱ' => 'A','Ὰ' => 'A','ᾼ' => 'A','Β' => 'B','Γ' => 'G','Δ' => 'D',
        'Ε' => 'E','Έ' => 'E','Ἐ' => 'E','Ἑ' => 'E','Ἒ' => 'E','Ἓ' => 'E',
        'Ἔ' => 'E','Ἕ' => 'E','Ὲ' => 'E','Ζ' => 'Z','Η' => 'I','Ή' => 'I',
        'Ἠ' => 'I','Ἡ' => 'I','Ἢ' => 'I','Ἣ' => 'I','Ἤ' => 'I','Ἥ' => 'I',
        'Ἦ' => 'I','Ἧ' => 'I','ᾘ' => 'I','ᾙ' => 'I','ᾚ' => 'I','ᾛ' => 'I',
        'ᾜ' => 'I','ᾝ' => 'I','ᾞ' => 'I','ᾟ' => 'I','Ὴ' => 'I','ῌ' => 'I',
        'Θ' => 'T','Ι' => 'I','Ί' => 'I','Ϊ' => 'I','Ἰ' => 'I','Ἱ' => 'I',
        'Ἲ' => 'I','Ἳ' => 'I','Ἴ' => 'I','Ἵ' => 'I','Ἶ' => 'I','Ἷ' => 'I',
        'Ῐ' => 'I','Ῑ' => 'I','Ὶ' => 'I','Κ' => 'K','Λ' => 'L','Μ' => 'M',
        'Ν' => 'N','Ξ' => 'KS','Ο' => 'O','Ό' => 'O','Ὀ' => 'O','Ὁ' => 'O',
        'Ὂ' => 'O','Ὃ' => 'O','Ὄ' => 'O','Ὅ' => 'O','Ὸ' => 'O','Π' => 'P',
        'Ρ' => 'R','Ῥ' => 'R','Σ' => 'S','Τ' => 'T','Υ' => 'Y','Ύ' => 'Y',
        'Ϋ' => 'Y','Ὑ' => 'Y','Ὓ' => 'Y','Ὕ' => 'Y','Ὗ' => 'Y','Ῠ' => 'Y',
        'Ῡ' => 'Y','Ὺ' => 'Y','Φ' => 'F','Χ' => 'X','Ψ' => 'PS','Ω' => 'O',
        'Ώ' => 'O','Ὠ' => 'O','Ὡ' => 'O','Ὢ' => 'O','Ὣ' => 'O','Ὤ' => 'O',
        'Ὥ' => 'O','Ὦ' => 'O','Ὧ' => 'O','ᾨ' => 'O','ᾩ' => 'O','ᾪ' => 'O',
        'ᾫ' => 'O','ᾬ' => 'O','ᾭ' => 'O','ᾮ' => 'O','ᾯ' => 'O','Ὼ' => 'O',
        'ῼ' => 'O','α' => 'a','ά' => 'a','ἀ' => 'a','ἁ' => 'a','ἂ' => 'a',
        'ἃ' => 'a','ἄ' => 'a','ἅ' => 'a','ἆ' => 'a','ἇ' => 'a','ᾀ' => 'a',
        'ᾁ' => 'a','ᾂ' => 'a','ᾃ' => 'a','ᾄ' => 'a','ᾅ' => 'a','ᾆ' => 'a',
        'ᾇ' => 'a','ὰ' => 'a','ᾰ' => 'a','ᾱ' => 'a','ᾲ' => 'a','ᾳ' => 'a',
        'ᾴ' => 'a','ᾶ' => 'a','ᾷ' => 'a','β' => 'b','γ' => 'g','δ' => 'd',
        'ε' => 'e','έ' => 'e','ἐ' => 'e','ἑ' => 'e','ἒ' => 'e','ἓ' => 'e',
        'ἔ' => 'e','ἕ' => 'e','ὲ' => 'e','ζ' => 'z','η' => 'i','ή' => 'i',
        'ἠ' => 'i','ἡ' => 'i','ἢ' => 'i','ἣ' => 'i','ἤ' => 'i','ἥ' => 'i',
        'ἦ' => 'i','ἧ' => 'i','ᾐ' => 'i','ᾑ' => 'i','ᾒ' => 'i','ᾓ' => 'i',
        'ᾔ' => 'i','ᾕ' => 'i','ᾖ' => 'i','ᾗ' => 'i','ὴ' => 'i','ῂ' => 'i',
        'ῃ' => 'i','ῄ' => 'i','ῆ' => 'i','ῇ' => 'i','θ' => 't','ι' => 'i',
        'ί' => 'i','ϊ' => 'i','ΐ' => 'i','ἰ' => 'i','ἱ' => 'i','ἲ' => 'i',
        'ἳ' => 'i','ἴ' => 'i','ἵ' => 'i','ἶ' => 'i','ἷ' => 'i','ὶ' => 'i',
        'ῐ' => 'i','ῑ' => 'i','ῒ' => 'i','ῖ' => 'i','ῗ' => 'i','κ' => 'k',
        'λ' => 'l','μ' => 'm','ν' => 'n','ξ' => 'ks','ο' => 'o','ό' => 'o',
        'ὀ' => 'o','ὁ' => 'o','ὂ' => 'o','ὃ' => 'o','ὄ' => 'o','ὅ' => 'o',
        'ὸ' => 'o','π' => 'p','ρ' => 'r','ῤ' => 'r','ῥ' => 'r','σ' => 's',
        'ς' => 's','τ' => 't','υ' => 'y','ύ' => 'y','ϋ' => 'y','ΰ' => 'y',
        'ὐ' => 'y','ὑ' => 'y','ὒ' => 'y','ὓ' => 'y','ὔ' => 'y','ὕ' => 'y',
        'ὖ' => 'y','ὗ' => 'y','ὺ' => 'y','ῠ' => 'y','ῡ' => 'y','ῢ' => 'y',
        'ῦ' => 'y','ῧ' => 'y','φ' => 'f','χ' => 'x','ψ' => 'ps','ω' => 'o',
        'ώ' => 'o','ὠ' => 'o','ὡ' => 'o','ὢ' => 'o','ὣ' => 'o','ὤ' => 'o',
        'ὥ' => 'o','ὦ' => 'o','ὧ' => 'o','ᾠ' => 'o','ᾡ' => 'o','ᾢ' => 'o',
        'ᾣ' => 'o','ᾤ' => 'o','ᾥ' => 'o','ᾦ' => 'o','ᾧ' => 'o','ὼ' => 'o',
        'ῲ' => 'o','ῳ' => 'o','ῴ' => 'o','ῶ' => 'o','ῷ' => 'o','А' => 'A',
        'Б' => 'B','В' => 'V','Г' => 'G','Д' => 'D','Е' => 'E','Ё' => 'E',
        'Ж' => 'Z','З' => 'Z','И' => 'I','Й' => 'I','К' => 'K','Л' => 'L',
        'М' => 'M','Н' => 'N','О' => 'O','П' => 'P','Р' => 'R','С' => 'S',
        'Т' => 'T','У' => 'U','Ф' => 'F','Х' => 'K','Ц' => 'Ts','Ч' => 'Ch',
        'Ш' => 'S','Щ' => 'S','Ы' => 'Y','Э' => 'E','Ю' => 'Y','Я' => 'Ya',
        'ъ' => 'a','Ъ' => 'A','ѝ' => 'i','ь' => 'u',
        'а' => 'A','б' => 'B','в' => 'V','г' => 'G','д' => 'D','е' => 'E',
        'ё' => 'E','ж' => 'Z','з' => 'Z','и' => 'I','й' => 'I','к' => 'K',
        'л' => 'L','м' => 'M','н' => 'N','о' => 'O','п' => 'P','р' => 'R',
        'с' => 'S','т' => 'T','у' => 'U','ф' => 'F','х' => 'K','ц' => 'ts',
        'ч' => 'ch','ш' => 'S','щ' => 'S','ы' => 'Y','э' => 'E','ю' => 'Y',
        'я' => 'ya','ð' => 'd','Ð' => 'D','þ' => 't','Þ' => 'T','ა' => 'a',
        'ბ' => 'b','გ' => 'g','დ' => 'd','ე' => 'e','ვ' => 'v','ზ' => 'z',
        'თ' => 't','ი' => 'i','კ' => 'k','ლ' => 'l','მ' => 'm','ნ' => 'n',
        'ო' => 'o','პ' => 'p','ჟ' => 'z','რ' => 'r','ს' => 's','ტ' => 't',
        'უ' => 'u','ფ' => 'p','ქ' => 'k','ღ' => 'g','ყ' => 'q','შ' => 's',
        'ჩ' => 'c','ც' => 't','ძ' => 'd','წ' => 't','ჭ' => 'c','ხ' => 'k',
        'ჯ' => 'j','ჰ' => 'h', 'ấ' => 'a', 'ở' => 'o', 'ễ' => 'e', 'ậ' => 'a',
        'à' => 'a','á' => 'a','ả' => 'a','ã' => 'a','ạ' => 'a','ă' => 'a','ằ' => 'a','ắ' => 'a','ẳ' => 'a','ẵ' => 'a','ặ' => 'a','â' => 'a','ầ' => 'a','ấ' => 'a','ẩ' => 'a','ẫ' => 'a','ậ' => 'a','À' => 'a','Á' => 'a','Ả' => 'a','Ã' => 'a','Ạ' => 'a','Ă' => 'a','Ằ' => 'a','Ắ' => 'a','Ẳ' => 'a','Ẵ' => 'a','Ặ' => 'a','Â' => 'a','Ầ' => 'a','Ấ' => 'a','Ẩ' => 'a','Ẫ' => 'a','Ậ' => 'a','ą' => 'a',
        'ǽ' => 'ae','ǣ' => 'ae',
        'ɓ' => 'b',
        'ć' => 'c','ċ' => 'c','ĉ' => 'c','č' => 'c',
        'ď' => 'd','ḍ' => 'd','đ' => 'd','ɗ' => 'd','ð' => 'd',
        'è' => 'e','é' => 'e','ẻ' => 'e','ẽ' => 'e','ẹ' => 'e','ê' => 'e','ề' => 'e','ế' => 'e','ể' => 'e','ễ' => 'e','ệ' => 'e','È' => 'e','É' => 'e','Ẻ' => 'e','Ẽ' => 'e','Ẹ' => 'e','Ê' => 'e','Ề' => 'e','Ế' => 'e','Ể' => 'e','Ễ' => 'e','Ệ' => 'e','ę' => 'e','ǝ' => 'e','ə' => 'e','ɛ' => 'e',
        'ġ' => 'g','ĝ' => 'g','ǧ' => 'g','ğ' => 'g','ģ' => 'g','ɣ' => 'g',
        'ĥ' => 'h','ḥ' => 'h','ħ' => 'h',
        'ì' => 'i','í' => 'i','ỉ' => 'i','ĩ' => 'i','ị' => 'i','Ì' => 'i','Í' => 'i','Ỉ' => 'i','Ĩ' => 'i','Ị' => 'i','I' => 'i','ǐ' => 'i','ĭ' => 'i','ī' => 'i','į' => 'i','İ' => 'i',
        'ĳ' => 'ij',
        'ĵ' => 'j',
        'ķ' => 'k','ƙ' => 'k','ĸ' => 'k',
        'ĺ' => 'l','ļ' => 'l','ł' => 'l','ľ' => 'l','ŀ' => 'l',
        'ŉ' => 'n','ń' => 'n','̈' => 'n','ň' => 'n','ņ' => 'n','ŋ' => 'n',
        'ò' => 'o','ó' => 'o','ỏ' => 'o','õ' => 'o','ọ' => 'o','ô' => 'o','ồ' => 'o','ố' => 'o','ổ' => 'o','ỗ' => 'o','ộ' => 'o','ơ' => 'o','ờ' => 'o','ớ' => 'o','ở' => 'o','ỡ' => 'o','ợ' => 'o','Ò' => 'o','Ó' => 'o','Ỏ' => 'o','Õ' => 'o','Ọ' => 'o','Ô' => 'o','Ồ' => 'o','Ố' => 'o','Ổ' => 'o','Ỗ' => 'o','Ộ' => 'o','Ơ' => 'o','Ờ' => 'o','Ớ' => 'o','Ở' => 'o','Ỡ' => 'o','Ợ' => 'o','ǒ' => 'o','ŏ' => 'o','ō' => 'o','ő' => 'o','ǫ' => 'o','ǿ' => 'o',
        'ŕ' => 'r','ř' => 'r','ŗ' => 'r',
        'ſ' => 's','ś' => 's','ŝ' => 's','ş' => 's','ș' => 's','ṣ' => 's',
        'ť' => 't','ţ' => 't','ṭ' => 't','ŧ' => 't', 'ț' => 't',
        'ù' => 'u','ú' => 'u','ủ' => 'u','ũ' => 'u','ụ' => 'u','ư' => 'u','ừ' => 'u','ứ' => 'u','ử' => 'u','ữ' => 'u','ự' => 'u','Ù' => 'u','Ú' => 'u','Ủ' => 'u','Ũ' => 'u','Ụ' => 'u','Ư' => 'u','Ừ' => 'u','Ứ' => 'u','Ử' => 'u','Ữ' => 'u','Ự' => 'u','ǔ' => 'u','ŭ' => 'u','ū' => 'u','ű' => 'u','ů' => 'u','ų' => 'u',
        'ẃ' => 'w','ẁ' => 'w','ŵ' => 'w','ẅ' => 'w','ƿ' => 'w',
        'ỳ' => 'y','ý' => 'y','ỷ' => 'y','ỹ' => 'y','ỵ' => 'y','Y' => 'y','Ỳ' => 'y','Ý' => 'y','Ỷ' => 'y','Ỹ' => 'y','Ỵ' => 'y','ŷ' => 'y','ȳ' => 'y','ƴ' => 'y',
        'ź' => 'z','ż' => 'z','ž' => 'z','ẓ' => 'z'
        );
        
        $str = str_replace( array_keys( $transliteration ),
                            array_values( $transliteration ),
                            $str);
        return $str;
    }
    
    private function formatCyrillic($str, $utf8=true) {
        $str = (string)$str;

        if( is_null($utf8) ) {

            if( !function_exists('mb_detect_encoding') ) {

                $utf8 = (strtolower( mb_detect_encoding($str) )=='utf-8');

            } else {

                $length = strlen($str);

                $utf8 = true;

                for ($i=0; $i < $length; $i++) {

                    $c = ord($str[$i]);

                    if ($c < 0x80) $n = 0; # 0bbbbbbb

                    elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb

                    elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb

                    elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb

                    elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb

                    elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b

                    else return false; # Does not match any model

                    for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?

                        if ((++$i == $length)

                            || ((ord($str[$i]) & 0xC0) != 0x80)) {

                            $utf8 = false;

                            break;

                        }



                    }

                }

            }



        }



        if(!$utf8)

            $str = utf8_encode($str);

        $transliteration = array(
        'А' => 'а',

        'Б' => 'б','В' => 'в','Г' => 'г','Д' => 'д','Е' => 'е','Ё' => 'ё',

        'Ж' => 'з','З' => 'з','И' => 'и','Й' => 'и','К' => 'к','Л' => 'л',

        'М' => 'м','Н' => 'н','О' => 'о','П' => 'п','Р' => 'р','С' => 'с',

        'Т' => 'т','У' => 'у','Ф' => 'ф','Х' => 'х','Ц' => 'ц','Ч' => 'ч',

        'Ш' => 'ш','Щ' => 'щ','Ы' => 'ы','Э' => 'э','Ю' => 'ю','Я' => 'я',

        'ъ' => 'ъ','Ъ' => 'ъ','ѝ' => 'ѝ','ь' => 'ь',

        'а' => 'а','б' => 'б','в' => 'в','г' => 'г','д' => 'д','е' => 'е',

        'ё' => 'ё','ж' => 'ж','з' => 'з','и' => 'и','й' => 'й','к' => 'к',

        'л' => 'л','м' => 'м','н' => 'н','о' => 'о','п' => 'п','р' => 'р',

        'с' => 'с','т' => 'т','у' => 'у','ф' => 'ф','х' => 'х','ц' => 'ц',

        'ч' => 'ч','ш' => 'ш','щ' => 'щ','ы' => 'ы','э' => 'э','ю' => 'ю',

        'я' => 'я'
        );      

        $result = str_replace( array_keys( $transliteration ),

                            array_values( $transliteration ),

                            $str);

        if (function_exists('mb_strtolower')) {

                $result = mb_strtolower($result);

            } else {

                $result = strtolower($result);

            }

        $result = str_replace('&amp;', '-', $result);

        $result = str_replace('&quot;', '', $result);

        $result = str_replace('&', '-', $result);

        $result = str_replace('^', '', $result);

        $result = str_replace(' ','-', $result);

        if (function_exists('mb_substr')) {

            $result = trim(mb_substr($result, 0, 200));

        } else {

            $result = trim(substr($result, 0, 200));

        }
        $result = preg_replace('{(-)\1+}','$1', $result); 

        //$result = $this->sanitizeChar($result);
        $result = preg_replace('/[\>\<\+\?\&\"\'\`\/\\\:\;\,\.\~\!\@\^\*\|\$\#\%\=]/', '', $result);

        $result = str_replace('-html','.html', $result);

        return $result;
    }

    public function formatSEOUrl($string, $useCyrillicUrl = false) {

        if($useCyrillicUrl){
            $result = $this->formatCyrillic($string);
        } else {

            $result = $this->remove_accents($string);

            if (function_exists('mb_strtolower')) {

                $result = mb_strtolower($result);

            } else {

                $result = strtolower($result);

            }          

            $result = str_replace('&amp;', '-', $result);

            $result = str_replace('&quot;', '', $result);

            $result = str_replace('&', '-', $result);

            $result = str_replace('^', '', $result);


            if (preg_match("/\p{Han}+/u", $result)) {

                $result = str_replace(' ','-', $result);

            } else if (preg_match("/\p{Arabic}+/u", $result)) {

                $result = str_replace(' ','-', $result);

            } else {

                $result = preg_replace("/[^a-z0-9-]/", "-", $result);

            }

            $result = preg_replace('{(-)\1+}','$1', $result); 

            if (function_exists('mb_substr')) {

                $result = trim(mb_substr($result, 0, 200));

            } else {

                $result = trim(substr($result, 0, 200));

            }

            $result = $this->sanitizeChar($result);
            $result = str_replace('-html','.html', $result);

        }

        return $result;     

    }

    /**
     * Remove unwanted characters for filename and url alias
     * Example: [1]<>+=_`~ !–@#$;"\'\%   ^&*(\{)?}/2=\ -,./../*:|3_-.#
     * Result: 1-_-2-3
     */
    public function sanitizeChar($data, $glue = '-', $trim = '_-.')
    {
        return trim(preg_replace('/[\>\<\+\?\&\"\'\`\/\\\:\;\s\–\-\,\.\{\}\(\)\[\]\~\!\@\^\*\|\$\#\%\=\r\n\t]+/', $glue, $data), $trim);
    }
    
    public function getAutoLinks($page = 1, $limit = 10, $store_id = 0) {
        $query = "SELECT * FROM `" . DB_PREFIX . "seo_autolinks` WHERE `store_id` = '" . (int)$store_id . "' ";
        
        if ($page) {
			$start = ($page - 1) * $limit;
		}
        
        $query .= "ORDER BY `id` DESC LIMIT ".$start.", ".$limit;
        
        $query = $this->db->query($query);

        return $query->rows;
    }
    
    public function getTotalAutoLinks($store_id = 0) {
		$query = "SELECT COUNT(*) as `count`  FROM `" . DB_PREFIX . "seo_autolinks` WHERE `store_id` = '" . (int)$store_id . "' ";
		
        
		$query = $this->db->query($query);
        
		return $query->row['count']; 
	}
    
    public function addAutoLink($data = array(), $store_id = 0) {
        $query = $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_autolinks` SET `keyword` = '" . $this->db->escape($data['keyword']) . "', `url` = '" . $this->db->escape($data['url']). "', `store_id` = '" . (int)$store_id . "', `date_added` = NOW()");
        
        return true;
    }
    
    public function getAutoLink($id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_autolinks` WHERE `id`= '" . (int)$id . "' LIMIT 1");
        
        if ($query->num_rows > 0) {
            return $query->row;
        } else {
            return false;
        }
    }
    
    public function editAutoLink($data = array(), $id, $store_id = 0) {
        $query = $this->db->query("UPDATE `" . DB_PREFIX . "seo_autolinks` SET `keyword` = '" . $this->db->escape($data['keyword']) . "', `url` = '" . $this->db->escape($data['url']). "', `store_id` = '" . (int)$store_id . "' WHERE `id` = '".$id."'");
        
        return true;
    }
    
    public function deleteAutoLink($id) {
        $query = $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_autolinks` WHERE `id`= '" . (int)$id . "'");
        
        return true;
    }
    
    public function getCustomUrls($page = 1, $limit = 10, $store_id = 0) {
        $query = "SELECT * FROM `" . DB_PREFIX . "seo_custom_urls` WHERE `store_id` = '" . (int)$store_id . "' AND `language_id` = '" . $this->config->get('config_language_id') . "'";
        
        if ($page) {
			$start = ($page - 1) * $limit;
		}
        
        $query .= "ORDER BY `query` DESC LIMIT ".$start.", ".$limit;
        
        $query = $this->db->query($query);

        return $query->rows;
    }
    
    public function getTotalCustomUrls($store_id = 0) {
		$query = "SELECT COUNT(*) as `count`  FROM `" . DB_PREFIX . "seo_custom_urls` WHERE `store_id` = '" . (int)$store_id . "' AND `language_id` = '" . $this->config->get('config_language_id') . "'";
		
		$query = $this->db->query($query);
        
		return $query->row['count']; 
	}
    
    public function addCustomUrl($data = array(), $store_id = 0) {
        $this->deleteCustomUrl($data['query'], $store_id);

        foreach ($data['keyword'] as $language_id => $keyword) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_custom_urls` SET `keyword` = '" . $this->db->escape($keyword) . "', `query` = '" . $this->db->escape($data['query']). "', `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "'");
        }
        
        return true;
    }

    public function getCustomUrl($query, $store_id = 0) {
        $results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_custom_urls` WHERE `query`= '" . $this->db->escape($query) . "' AND `store_id` = '" . (int)$store_id . "'");
        
        if ($results->num_rows) {
            $data = array(
                'query'     => $query,
                'keyword'   => array(),
            );
            foreach ($results->rows as $item) {
                $data['keyword'][$item['language_id']] = $item['keyword'];
            }
            return $data;
        } else {
            return false;
        }
    }

    public function deleteCustomUrl($query, $store_id = 0) {
        $query = $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_custom_urls` WHERE `query`= '" . $this->db->escape($query) . "' AND `store_id` = '" . (int)$store_id . "'");
        
        return true;
    }
    
    public function clearSeoAnalysisResults($store_id = 0) {
        $query = $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_analysis` WHERE `store_id` = '" . (int)$store_id . "'");
        
        return true;
    }
    
    public function getCrawledUrls($page = 1, $crawler = "All", $url="", $date_start="", $date_end="", $limit = 15, $store_id = 0) {
        $query = "SELECT * FROM `" . DB_PREFIX . "seo_analysis` WHERE `store_id` = '" . (int)$store_id . "' ";
        
        if ($crawler != "All") {
            $query .= " AND `crawler` = '" . $this->db->escape($crawler) . "'";
        }
        
        if (!empty($url)) {
            $query .= " AND `url` LIKE '%" . $this->db->escape($url) . "%'";
        }
        
        if (!empty($date_start)) {
            $query .= " AND `date_added` >= '" . $this->db->escape($date_start) . " 00:00:00'";
        }
        
        if (!empty($date_end)) {
            $query .= " AND `date_added` <= '" . $this->db->escape($date_end) . " 23:59:59'";
        }
        
        if ($page) {
			$start = ($page - 1) * $limit;
		}
        
        $query .= "ORDER BY `id` DESC LIMIT ".$start.", ".$limit;
        
        $query = $this->db->query($query);

        return $query->rows;
    }
    
    public function getTotalCrawledUrls($crawler = "All", $url="", $date_start="", $date_end="", $store_id = 0) {
		$query = "SELECT COUNT(*) as `count`  FROM `" . DB_PREFIX . "seo_analysis` WHERE `store_id` = '" . (int)$store_id . "'";
        
        if ($crawler != "All") {
            $query .= " AND `crawler` = '" . $this->db->escape($crawler) . "'";
        }
        
        if (!empty($url)) {
            $query .= " AND `url` LIKE '%" . $this->db->escape($url) . "%'";
        }
        
        if (!empty($date_start)) {
            $query .= " AND `date_added` >= '" . $this->db->escape($date_start) . " 00:00:00'";
        }
        
        if (!empty($date_end)) {
            $query .= " AND `date_added` <= '" . $this->db->escape($date_end) . " 23:59:59'";
        }
		
		$query = $this->db->query($query);
        
		return $query->row['count']; 
	}
    
    public function getDetectedPages($page = 1, $limit = 10, $filter_route = "", $visits="", $date_start="", $date_end="", $store_id = 0) {
        $query = "SELECT * FROM `" . DB_PREFIX . "seo_404_pages` WHERE `store_id` = '" . (int)$store_id . "' ";
        
        if ($page) {
			$start = ($page - 1) * $limit;
		}
        
        if (!empty($visits)) {
            $query .= " AND `visits` = '" . $this->db->escape($visits) . "'";
        }
        
        if (!empty($filter_route)) {
            $query .= " AND `route` LIKE '%" . $this->db->escape($filter_route) . "%'";
        }
        
        if (!empty($date_start)) {
            $query .= " AND `first_visited` >= '" . $this->db->escape($date_start) . " 00:00:00'";
        }
        
        if (!empty($date_end)) {
            $query .= " AND `first_visited` <= '" . $this->db->escape($date_end) . " 23:59:59'";
        }
        
        $query .= "ORDER BY `page_id` DESC LIMIT ".$start.", ".$limit;
        
        $query = $this->db->query($query);

        return array_map(array($this, 'addSlashedRouteParam'), $query->rows);
    }
    
    private function addSlashedRouteParam($row) {
        $row['routeParam'] = addslashes($row['route']);

        return $row;
    }
    
    public function getTotalDetectedPages($filter_route = "", $visits="", $date_start="", $date_end="", $store_id = 0) {
		$query = "SELECT COUNT(*) as `count`  FROM `" . DB_PREFIX . "seo_404_pages`";
        
        $query .= " WHERE `store_id` = '" . (int)$store_id . "' ";
        
        if (!empty($visits)) {
            $query .= " AND `visits` = '" . $this->db->escape($visits) . "'";
        }
        
        if (!empty($filter_route)) {
            $query .= " AND `route` LIKE '%" . $this->db->escape($filter_route) . "%'";
        }
        
        if (!empty($date_start)) {
            $query .= " AND `first_visited` >= '" . $this->db->escape($date_start) . " 00:00:00'";
        }
        
        if (!empty($date_end)) {
            $query .= " AND `first_visited` <= '" . $this->db->escape($date_end) . " 23:59:59'";
        }
		
		$query = $this->db->query($query);
        
		return $query->row['count']; 
	}
    
    public function deleteDetectedPage($id, $store_id = 0) {
        $query = $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_404_pages` WHERE `page_id` = '" . (int)$id . "' AND `store_id` = '" . (int)$store_id . "'");
        
        return true;
    }
    
    public function clearMissingPagesResults($store_id = 0) {
        $query = $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_404_pages` WHERE `store_id` = '" . (int)$store_id . "'");
        
        return true;
    }
    
    public function addMissingPageRedirect($data = array(), $store_id = 0) {
        // Consistent char
        $data['route_from'] = htmlspecialchars_decode($data['route_from']);
        $data['route_to'] = htmlspecialchars_decode($data['route_to']);

        $check_if_exists = $this->db->query("SELECT page_id FROM `" . DB_PREFIX . "seo_404_pages` WHERE route = '" . $this->db->escape($data['route_from']) . "' AND `store_id` = '" . (int)$store_id . "' LIMIT 1");
        
        if ($check_if_exists->num_rows) {
            $result = $this->deleteDetectedPage($check_if_exists->row['page_id'], $store_id);
        }
        
        $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_404_redirects` SET route_from='" . $this->db->escape($data['route_from']) . "', route_to = '" . $this->db->escape($data['route_to']) . "', date_added=NOW(), date_modified=NOW(), `store_id` = '" . (int)$store_id . "'");

        return true;
    }
    
    public function getRedirects($page = 1, $limit = 10, $route_from = "", $route_to = "", $store_id = 0) {
        $query = "SELECT * FROM `" . DB_PREFIX . "seo_404_redirects` WHERE `store_id` = '" . (int)$store_id . "' ";
        
        if ($page) {
			$start = ($page - 1) * $limit;
		}
        
        if (!empty($route_from)) {
            $query .= " AND `route_from` LIKE '%" . $this->db->escape($route_from) . "%'";
        }
        
        if (!empty($route_to)) {
            $query .= " AND `route_to` LIKE '%" . $this->db->escape($route_to) . "%'";
        }
        
        $query .= "ORDER BY `redirect_id` DESC LIMIT ".$start.", ".$limit;
        
        $query = $this->db->query($query);

        return $query->rows;
    }
    
    public function getTotalRedirects($route_from = "", $route_to = "", $store_id = 0) {
		$query = "SELECT COUNT(*) as `count`  FROM `" . DB_PREFIX . "seo_404_redirects`";
        
        $query .= " WHERE `store_id` = '" . (int)$store_id . "' ";
        
        if (!empty($route_from)) {
            $query .= " AND `route_from` LIKE '%" . $this->db->escape($route_from) . "%'";
        }
        
        if (!empty($route_to)) {
            $query .= " AND `route_to` LIKE '%" . $this->db->escape($route_to) . "%'";
        }
        
		$query = $this->db->query($query);
        
		return $query->row['count']; 
	}
    
    public function deleteRedirect($id, $store_id = 0) {
        $query = $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_404_redirects` WHERE `redirect_id` = '" . (int)$id . "'AND `store_id` = '" . (int)$store_id . "'");
        
        return true;
    }
    
    public function getH1Tags($product_id = 0) {
        $tags = array();
        
        $results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_product_description` WHERE product_id=" . (int)$this->db->escape($product_id) . "");
          
        foreach ($results->rows as $result) {
            $tags[$result['language_id']] = $result['h1'];					
        }
        
        return $tags;
    }
    
    public function getH2Tags($product_id = 0) {
        $tags = array();
        
        $results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_product_description` WHERE product_id=" . (int)$this->db->escape($product_id) . "");
          
        foreach ($results->rows as $result) {
            $tags[$result['language_id']] = $result['h2'];					
        }
        
        return $tags;
    }

    public function uninstall() {
        $language_id = (int)$this->config->get('config_language_id');

        // Restore store meta-info
        $results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `code` = 'config' AND `key` LIKE '%config\_meta%'");

        foreach ($results->rows as $result) {
            if ($result['serialized']) {
                $values = json_decode($result['value'], true);

                if (is_array($values)) {
                    $result['value'] = !empty($values[$language_id]) ? $values[$language_id] : '';
                    $this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value`='" . $this->db->escape($result['value']) . "', `serialized`='0' WHERE `code`='" . $result['code'] . "' AND `key`='" . $result['key'] . "' AND `store_id`='" . (int)$result['store_id'] . "'");
                }
            }
        }
    }
}
