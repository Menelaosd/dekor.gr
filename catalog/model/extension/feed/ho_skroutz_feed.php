<?php
class ModelExtensionFeedHoSkroutzFeed extends Model {

	public function getSkroutzProducts() {

		if ($this->config->get('feed_ho_skroutz_option_size') > 0) {
			$size_id = (int)$this->config->get('feed_ho_skroutz_option_size');
		} else {
			$size_id = 0;
		}

		if ($this->config->get('feed_ho_skroutz_option_color') > 0) {
			$color_id = (int)$this->config->get('feed_ho_skroutz_option_color');
		} else {
			$color_id = 0;
		}

		$sql = "
			SELECT	p.*, 
				pd.name as product_title, 
				pd.description,
				md.name as manufacturer,
				p.stock_status_id,				
				(SELECT 
					GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' > ') AS name
					FROM " . DB_PREFIX . "category_path cp 
					LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) 
					LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) 
					LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) 
					LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) 
					WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' 
					AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'
					AND cp.category_id = (SELECT category_id FROM ".DB_PREFIX."product_to_category pc1 WHERE p.product_id = pc1.product_id LIMIT 1)
					)
					AS category_name,";
		if ($size_id > 0) {
			$sql .= "(SELECT 
					GROUP_CONCAT(ovd.name) as option_sizes
					FROM " . DB_PREFIX . "product as p1
					LEFT JOIN " . DB_PREFIX . "product_option_value pov ON (p1.product_id = pov.product_id)
					LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON ovd.option_value_id = pov.option_value_id
					WHERE p1.product_id = p.product_id
					AND pov.quantity > 0
					AND ovd.option_id = " . $size_id . "
					) as option_sizes,";
		}
		if ($color_id > 0) {
			$sql .= "(SELECT 
					GROUP_CONCAT(ovd1.name) as color_sizes
					FROM " . DB_PREFIX . "product as p2
					LEFT JOIN " . DB_PREFIX . "product_option_value pov1 ON (p2.product_id = pov1.product_id)
					LEFT JOIN " . DB_PREFIX . "option_value_description ovd1 ON ovd1.option_value_id = pov1.option_value_id
					WHERE p2.product_id = p.product_id
					AND pov1.quantity > 0
					AND ovd1.option_id = " . $color_id . "
					) as color_sizes,";
		}
		$sql .= "(SELECT 
					category_id 
					FROM ".DB_PREFIX."product_to_category pc 
					WHERE p.product_id = pc.product_id 
					LIMIT 1) 
					AS category_id,
				(SELECT ss.name 
					FROM ".DB_PREFIX."stock_status ss 
					WHERE ss.stock_status_id = p.stock_status_id
					AND ss.language_id = " . (int)$this->config->get('config_language_id') . ") 
					AS stock_status,
				(SELECT price 
					FROM ".DB_PREFIX."product_special ps 
					WHERE ps.product_id = p.product_id 
					AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) 
					ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) 
					AS special
				FROM ".DB_PREFIX."product AS p
				LEFT JOIN ".DB_PREFIX."product_description AS pd ON p.product_id = pd.product_id
				LEFT JOIN ".DB_PREFIX."manufacturer md ON p.manufacturer_id = md.manufacturer_id 
				WHERE p.status = 1
				AND p.quantity > 0
				AND p.is_skroutz <> 0
		";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getSkroutzProductsXML($products) {

		date_default_timezone_set('Europe/Athens');

		$output = '<?xml version="1.0" encoding="UTF-8"?>';
		$output .= "<mywebstore>";
		$output .= "<created_at>".date("Y-m-d H:i:s")."</created_at>";
		$output .= "<products>";

		foreach ($products as $key => $prod) {

			if ($prod['category_name']) { // DONT DISPLAY PRODUCT IF NO CATEGORY
				$output .= "<product>";

				// MAIN INFO
				$output .= "<id>".$prod['product_id']."</id>";
				$output .= "<name><![CDATA[".$prod['product_title']."]]></name>";
				
				// PRODUCT URL
	    		$output .= "<link><![CDATA[".HTTP_SERVER."index.php?route=product/product&product_id=".$prod['product_id']."]]></link>";

				if ($prod['image'] != ""){
					if (HTTP_SERVER){
						$http_image = HTTP_SERVER."image/";
					} else {
						$http_image = "/image/";
					}
					$output .= '<image><![CDATA['.$http_image.$prod['image'].']]></image>';
				}

				$output .= "<category_id><![CDATA[".$prod['category_id']."]]></category_id>";
				$output .= "<category><![CDATA[".$prod['category_name']."]]></category>";

				// PRICES
				if ($prod['special'] != "") {
					$prod['price_tax'] = $this->tax->calculate($prod['special'], $prod['tax_class_id'], $this->config->get('config_tax'));
					$output .= "<price_with_vat><![CDATA[".number_format($prod['price_tax'],2)."]]></price_with_vat>";
				} else {
					$prod['price_tax'] = $this->tax->calculate($prod['price'], $prod['tax_class_id'], $this->config->get('config_tax'));
					$output .= "<price_with_vat><![CDATA[".number_format($prod['price_tax'],2)."]]></price_with_vat>";
				}

				// MANUFACTURER
				if ($this->config->get('feed_ho_skroutz_feed_display_manufacturer')) {
					if ($prod['manufacturer']) {
						$output .= "<manufacturer><![CDATA[".$prod['manufacturer']."]]></manufacturer>";
					}
				}

				// MPN ~ MODEL ~ EAN
				$output .= "<mpn><![CDATA[".$prod['model']."]]></mpn>";
				
				// MANUFACTURER
				if ($prod['ean']) {
					$output .= "<ean><![CDATA[".$prod['ean']."]]></ean>";
				}

				// SIZES
				if (isset($prod['option_sizes'])) {
					if ($prod['option_sizes']) {
						$output .= "<sizes>".$prod['option_sizes']."</sizes>";
					}
				}

				// COLOR
				if (isset($prod['color_sizes'])) {
					if ($prod['color_sizes']) {
						$prod['colors'] = explode(",", $prod['color_sizes']);
						foreach ($prod['colors'] as $color) {
							$output .= "<color>".$color."</color>";
						}
					}
				}

				// AVAILABILITY
				if ($prod['quantity'] > 0) {
					$output .= "<instock>Y</instock>";
				} else {
					$output .= "<instock>N</instock>";
				}


				if ($prod['quantity'] > 0) {
					if ($prod['stock_status_id'] == $this->config->get('feed_ho_skroutz_availability_1')) {
						$prod['stock_status_text'] = 'Available in store / Delivery 1 to 3 days';
					} elseif ($prod['stock_status_id'] == $this->config->get('feed_ho_skroutz_availability_2')) {
						$prod['stock_status_text'] = 'Delivery 1 to 3 days';
					} elseif ($prod['stock_status_id'] == $this->config->get('feed_ho_skroutz_availability_3')) {
						$prod['stock_status_text'] = 'Delivery 4 to 10 days';
					} elseif ($prod['stock_status_id'] == $this->config->get('feed_ho_skroutz_availability_4')) {
						$prod['stock_status_text'] = 'Upon order';
					} else {
						$prod['stock_status_text'] = $prod['stock_status'];
					}

					$output .= "<availability>".$prod['stock_status_text']."</availability>";
				}

				// WEIGHT
				if ($this->config->get('feed_ho_skroutz_feed_display_weight')) {
					if ($prod['weight'] > 0) {
						$output .= "<weight><![CDATA[".number_format($prod['weight']*1000,0,"","")."]]></weight>";
					}
				}
				
				if ($this->config->get('feed_ho_skroutz_feed_additional_images')) {
					
					$this->load->model('catalog/product');
					
					$additional_images = $this->model_catalog_product->getProductImages($prod['product_id']);
					foreach ($additional_images as $additional_image) {
						$output .= "<additionalimage>".HTTP_SERVER."image/".$additional_image['image']."</additionalimage>";
					}
				}

				$output .= "</product>";
			}
		}

		$output .= "</products>";
		$output .= "</mywebstore>";

		return $output;
	}

	public function createXMLFile($products){
		
		$path_name = DIR_DOWNLOAD ."/ho_skroutz_feed/";
		if (!file_exists($path_name)) {
			mkdir($path_name);
		}

		$file = @fopen($path_name . 'feed.xml', "w+");

		if($file){
			if(flock($file, LOCK_SH | LOCK_NB)){
				if(flock($file, LOCK_EX)){
					ftruncate($file, 0);

					$xml = self::getSkroutzProductsXML($products);

					fwrite($file, $xml);

					fflush($file);
				}
				flock($file, LOCK_UN);
				@fclose($file);
			}
			return true;
		} else {
			return false;
		}
	}

	public function readXMLFile($file_path){

		if(is_file($file_path)){
			return file_get_contents($file_path);
		} else {
			return "";
		}
	}

}