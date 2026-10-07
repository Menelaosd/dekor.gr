<?php
class ControllerExtensionFeedGoogleSitemap extends Controller {
	public function index() {
		if ($this->config->get('feed_google_sitemap_status')) {
			$output  = '<?xml version="1.0" encoding="UTF-8"?>';
			$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

			$home_url = rtrim($this->config->get('config_ssl') ? $this->config->get('config_ssl') : $this->config->get('config_url'), '/') . '/';

			$output .= '<url>';
			$output .= '  <loc>' . $this->xmlUrl($home_url) . '</loc>';
			$output .= '  <changefreq>daily</changefreq>';
			$output .= '  <priority>1.0</priority>';
			$output .= '</url>';

			$this->load->model('catalog/product');
			$this->load->model('tool/image');

			$products = $this->model_catalog_product->getProducts();

			foreach ($products as $product) {
				$output .= '<url>';
				$output .= '  <loc>' . $this->xmlUrl($this->url->link('product/product', 'product_id=' . $product['product_id'])) . '</loc>';
				$output .= '  <changefreq>weekly</changefreq>';

				if (!empty($product['date_modified']) && strtotime($product['date_modified'])) {
					$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($product['date_modified'])) . '</lastmod>';
				}

				$output .= '  <priority>0.8</priority>';

				if ($product['image']) {
					$image = $this->model_tool_image->resize(
						$product['image'],
						$this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'),
						$this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')
					);

					$output .= '  <image:image>';
					$output .= '  <image:loc>' . $this->xmlUrl($image) . '</image:loc>';
					$output .= '  <image:caption>' . $this->xmlText($product['name']) . '</image:caption>';
					$output .= '  <image:title>' . $this->xmlText($product['name']) . '</image:title>';
					$output .= '  </image:image>';
				}

				$output .= '</url>';
			}

			$this->load->model('catalog/category');
			$output .= $this->getCategories(0);

			$this->load->model('catalog/manufacturer');
			$manufacturers = $this->model_catalog_manufacturer->getManufacturers();

			foreach ($manufacturers as $manufacturer) {
				$output .= '<url>';
				$output .= '  <loc>' . $this->xmlUrl($this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id'])) . '</loc>';
				$output .= '  <changefreq>weekly</changefreq>';
				$output .= '  <priority>0.6</priority>';
				$output .= '</url>';
			}

			$this->load->model('catalog/information');
			$informations = $this->model_catalog_information->getInformations();

			foreach ($informations as $information) {
				$output .= '<url>';
				$output .= '  <loc>' . $this->xmlUrl($this->url->link('information/information', 'information_id=' . $information['information_id'])) . '</loc>';
				$output .= '  <changefreq>weekly</changefreq>';
				$output .= '  <priority>0.5</priority>';
				$output .= '</url>';
			}

			$output .= '</urlset>';

			$this->response->addHeader('Content-Type: application/xml');
			$this->response->setOutput($output);
		}
	}

	protected function xmlUrl($url) {
		$url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$parts = parse_url($url);

		if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
			return htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8');
		}

		$path = isset($parts['path']) ? $parts['path'] : '';
		$segments = explode('/', $path);

		foreach ($segments as &$segment) {
			$segment = rawurlencode(rawurldecode($segment));
		}

		unset($segment);

		$normalized = $parts['scheme'] . '://' . $parts['host'];

		if (!empty($parts['port'])) {
			$normalized .= ':' . $parts['port'];
		}

		$normalized .= implode('/', $segments);

		if (isset($parts['query']) && $parts['query'] !== '') {
			$normalized .= '?' . $parts['query'];
		}

		return htmlspecialchars($normalized, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	}

	protected function xmlText($text) {
		return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	}

	protected function getCategories($parent_id, $current_path = '') {
		$output = '';
		$results = $this->model_catalog_category->getCategories($parent_id);

		foreach ($results as $result) {
			if (!$current_path) {
				$new_path = $result['category_id'];
			} else {
				$new_path = $current_path . '_' . $result['category_id'];
			}

			$output .= '<url>';
			$output .= '  <loc>' . $this->xmlUrl($this->url->link('product/category', 'path=' . $new_path)) . '</loc>';
			$output .= '  <changefreq>weekly</changefreq>';

			if (!empty($result['date_modified']) && strtotime($result['date_modified'])) {
				$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($result['date_modified'])) . '</lastmod>';
			}

			$output .= '  <priority>0.7</priority>';
			$output .= '</url>';
			$output .= $this->getCategories($result['category_id'], $new_path);
		}

		return $output;
	}
}
