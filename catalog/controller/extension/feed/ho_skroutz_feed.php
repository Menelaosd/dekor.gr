<?php
class ControllerExtensionFeedHoSkroutzFeed extends Controller {
	public function index() {
		if ($this->config->get('feed_ho_skroutz_feed_status')) {

			$this->load->model('extension/feed/ho_skroutz_feed');

			$debug = isset($_GET['debug']) ? $_GET['debug'] : 0;
			
			if ($debug == 1) {
				
				$products = $this->model_extension_feed_ho_skroutz_feed->getSkroutzProducts();
				echo "<pre>";
				print_r($products);
				echo "</pre>";

			} else {
				
				if ($this->config->get('feed_ho_skroutz_mode') == 1) { // FOR CACHING

					$file_path = DIR_DOWNLOAD ."/ho_skroutz_feed/feed.xml";
					$file_path_filetime = filemtime($file_path);
					$now = time();
					
					if(($now - $file_path_filetime) >= 1800) {
						$products = $this->model_extension_feed_ho_skroutz_feed->getSkroutzProducts();
						$this->model_extension_feed_ho_skroutz_feed->createXMLFile($products);
					}
					$xml = $this->model_extension_feed_ho_skroutz_feed->readXMLFile($file_path);
				
				} else { 

					// FOR LIVE FEED
					$products = $this->model_extension_feed_ho_skroutz_feed->getSkroutzProducts();
					$output = $this->model_extension_feed_ho_skroutz_feed->getSkroutzProductsXML($products);
					
					$xml = $output;
				}

				$this->response->addHeader('Content-Type: application/xml');
				$this->response->setOutput($xml);
			}
		}
	}
}