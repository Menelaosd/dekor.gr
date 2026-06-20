<?php
class ControllerExtensionFeedHoFacebookFeeds extends Controller {
	public function index() {

		$module_id = $this->request->get['id'];
		
		$this->load->model('setting/module');

		$settings = $this->model_setting_module->getModule($module_id);

		if(isset($this->request->get['debug'])){
			$debug = $this->request->get['debug'];
		} else {
			$debug = 0;
		}

		if ($settings['status']) {

			$products = array();

			$this->load->model('catalog/product');
			$this->load->model('catalog/category');
			
			if ($settings['category']) {
				foreach ($settings['category'] as $category) {
					
					$filter_data = array(
						'filter_category_id' 	=> $category , //$category_id,
						'filter_sub_category'   => true
					);

					$category_products = $this->model_catalog_product->getProducts($filter_data);

					foreach ($category_products as $p_key => $category_product) {
						$products[] = $category_product;
					}
				}
			} else {

				$filter_data = array();

				$category_products = $this->model_catalog_product->getProducts($filter_data);

				foreach ($category_products as $p_key => $category_product) {
					$products[] = $category_product;
				}
			}

			$output ='<?xml version="1.0" encoding="UTF-8"?>';
			$output .='<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">';
			$output .='<title>'.$this->config->get('config_name').'</title>';
			$output .='<link rel="self" href="'.HTTP_SERVER.'"/>';

			if ($debug != 1){
				foreach ($products as $key => $prod) {
				    
					$product_categories = array();
					$product_categories = $this->model_catalog_product->getProductCategoriesXML($prod['product_id']);
					
					$category_path = '';
					
					$product_categories_final 				= array();
					$product_categories_final_arr 			= array();
					$product_categories_final_name 			= '';					
					$product_categories_final_group_name 	= '';				

					if($product_categories) {

						foreach ($product_categories as $keyGroup =>$product_categories_group) {
							foreach($product_categories_group as $category) {
								$category_info = $this->model_catalog_category->getCategory($category['path_id']);
								if ($category_info) {
									$product_categories_final[$keyGroup][] = $category_info['name'];
								};								
								
							};
						}
						
						foreach($product_categories_final as $key => $product_category_final) {
							$product_categories_final_arr[] = implode(' > ',$product_category_final);
						}
						
						
						$product_categories_final_group_name  = implode(' | ',$product_categories_final_arr);
						
						$category_description = $this->model_catalog_category->getCategory($product_categories[0]);						
						$category_parent = $this->model_catalog_category->getCategory($category_description['parent_id']);
						$category_path = $category_description['name'];
					};
				    
					$output .="<entry>";
						// IMAGE & LINK
						$p_out_link = HTTP_SERVER."index.php?route=product/product&product_id=".$prod['product_id']; // Final URL
						$p_out_image_link = HTTP_SERVER.'image/'.$prod['image']; // Image URL

						if (!empty($prod['special'])) {
							$p_out_price = number_format($prod['special'], 2, '.', '')." EUR"; // Price
						} else {
							$p_out_price = number_format($prod['price'], 2, '.', '')." EUR";
						}

						// OUTPUT
						$output .="<g:id>".$prod['product_id']."</g:id>";
						$output .="<g:title><![CDATA[".$prod['name']."]]></g:title>";
						$output .="<g:description><![CDATA[".$this->unwantedChars(strip_tags($prod['description']))."]]></g:description>";
						$output .="<g:link><![CDATA[".$p_out_link."]]></g:link>";
						$output .="<g:image_link><![CDATA[".$p_out_image_link."]]></g:image_link>";
						$output .="<g:brand><![CDATA[".$prod['manufacturer']."]]></g:brand>";
						$output .="<g:mpn><![CDATA[".$prod['model']."]]></g:mpn>";
						// $output .="<g:google_product_category>166</g:google_product_category>";
						$output .="<g:condition>new</g:condition>";
                        
                        $output .=trim("<g:product_type><![CDATA[".$product_categories_final_group_name ."]]></g:product_type>"). PHP_EOL;
                        
						if($prod['quantity'] >= 1){
							$instock = "Y";
							$availability = "in stock";
						} else {
							$instock = "N";
							$availability = "out of stock";
						}

						$output .="<g:availability>".$availability."</g:availability>";
						$output .="<g:price>".$p_out_price."</g:price>";
					$output .="</entry>";
				}

				$output .="</feed>";
				
				$this->response->addHeader('Content-Type: application/xml');
				$this->response->setOutput($output);
			} else {

				echo "<pre>";
				print_r($products);
				print_r($settings);
				echo "</pre>";
			}
		}
	}

	function unwantedChars ($string){
		$string = preg_replace('/[\x00-\x1F\x7F]/u', '', $string);
		// $string = iconv("utf-8", "utf-8//ignore", $string);
		return $string;
	}
}