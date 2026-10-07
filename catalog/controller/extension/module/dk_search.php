<?php
/**
 * dekor live search endpoint: index.php?route=extension/module/dk_search&q=…
 * JSON with products (customer-group prices, package/unit prices, discount, stock),
 * categories, blog posts, "did you mean" and the link to the full search page.
 * Empty q -> popular searches.
 */
class ControllerExtensionModuleDkSearch extends Controller {
	public function index() {
		$this->load->model('extension/module/dk_search');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$m = $this->model_extension_module_dk_search;
		$raw = isset($this->request->get['q']) ? trim(html_entity_decode($this->request->get['q'], ENT_QUOTES, 'UTF-8')) : '';
		$raw = mb_substr($raw, 0, 80);
		$json = array('q' => $raw, 'products' => array(), 'total' => 0, 'categories' => array(), 'posts' => array(), 'suggestion' => '', 'exact_code' => false);

		if (mb_strlen($raw) < 2) {
			$json['popular'] = $m->getPopular(8);

			return $this->output($json);
		}

		$index = $m->getIndex();
		$tokens = $m->words($raw);
		$code = $m->code($raw);

		$scores = $tokens || $code ? $m->searchProducts($index, $tokens, $code) : array();

		// typo tolerance: nothing found -> closest words
		if (!$scores && $tokens) {
			$sug = $m->suggest($index, $tokens);

			if ($sug) {
				$scores = $m->searchProducts($index, $sug[0], '');
				$json['suggestion'] = implode(' ', $sug[1]);
				$tokens = $sug[0];
			}
		}

		$json['total'] = count($scores);
		$json['tokens'] = $tokens;
		$display = $json['suggestion'] ? $json['suggestion'] : $m->displayQuery($index, $tokens, $raw);
		$json['display'] = $display;
		$json['search_url'] = str_replace('&amp;', '&', $this->url->link('product/search', 'search=' . urlencode($display)));

		$limit = 8;
		$n = 0;

		foreach (array_keys($scores) as $product_id) {
			if ($n >= $limit) {
				break;
			}

			$result = $this->model_catalog_product->getProduct($product_id);

			if (!$result) {
				continue;
			}

			$card = $this->card($result);

			if ($code !== '' && !$n && isset($index['products'][$product_id]) && in_array($code, $index['products'][$product_id]['c'])) {
				$card['exact_code'] = true;
				$json['exact_code'] = true;
			}

			$json['products'][] = $card;
			$n++;
		}

		// categories + posts
		if ($tokens) {
			foreach ($m->searchList($index['categories'], $tokens, 5) as $category_id => $s) {
				$c = $index['categories'][$category_id];
				$json['categories'][] = array('name' => $c['name'], 'href' => str_replace('&amp;', '&', $this->url->link('product/category', 'path=' . ($c['path'] ? $c['path'] : $category_id))));
			}

			foreach ($m->searchList($index['posts'], $tokens, 3) as $blog_id => $s) {
				$p = $index['posts'][$blog_id];
				$json['posts'][] = array(
					'title' => $p['title'],
					'href'  => str_replace('&amp;', '&', $this->url->link('extension/blog/blog', 'blog_id=' . $blog_id)),
					'thumb' => $p['image'] && is_file(DIR_IMAGE . $p['image']) ? $this->model_tool_image->resize($p['image'], 96, 72) : '',
				);
			}
		}

		$this->output($json);
	}

	private function card($result) {
		$currency = $this->session->data['currency'];
		$show_price = $this->customer->isLogged() || !$this->config->get('config_customer_price');
		$package = ($result['package'] && $result['package'] > 1) ? (int)$result['package'] : 0;
		$has_special = !is_null($result['special']) && (float)$result['special'] >= 0;

		$price = $special = $package_price = $package_special = false;
		$discount = 0;

		if ($show_price) {
			$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);

			if ($package) {
				$package_price = $this->currency->format($this->tax->calculate($result['price'] / $package, $result['tax_class_id'], $this->config->get('config_tax')), $currency);
			}

			if ($has_special) {
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);

				if ($package) {
					$package_special = $this->currency->format($this->tax->calculate($result['special'] / $package, $result['tax_class_id'], $this->config->get('config_tax')), $currency);
				}

				if ((float)$result['price'] > 0) {
					$discount = (int)round((1 - (float)$result['special'] / (float)$result['price']) * 100);
				}
			}
		}

		$working = method_exists($this->model_catalog_product, 'checkCartStatus') ? $this->model_catalog_product->checkCartStatus($result['product_id']) : true;
		$disablecart = !empty($result['disablecart']) || $this->config->get('config_disable_cart');
		$storeonly = !empty($result['storeonly']);
		$in_stock = $result['quantity'] > 0 && $working;

		if ($storeonly) {
			$stock = array('label' => 'Μόνο στο κατάστημα', 'class' => 'store');
		} elseif ($in_stock) {
			$stock = array('label' => 'Άμεσα διαθέσιμο', 'class' => 'in');
		} else {
			$stock = array('label' => 'Καλέστε μας', 'class' => 'out');
		}

		$image = $result['image'] && is_file(DIR_IMAGE . $result['image']) ? $result['image'] : 'placeholder.png';

		return array(
			'product_id'      => (int)$result['product_id'],
			'name'            => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'),
			'model'           => $result['model'],
			'href'            => str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $result['product_id'])),
			'thumb'           => $this->model_tool_image->resize($image, 120, 120),
			'price'           => $price,
			'special'         => $special,
			'discount'        => $discount > 0 ? $discount : 0,
			'package'         => $package,
			'package_price'   => $package_price,
			'package_special' => $package_special,
			'stock'           => $stock,
			'can_add'         => $show_price && $in_stock && !$disablecart && !$storeonly,
			'minimum'         => $result['minimum'] > 0 ? (int)$result['minimum'] : 1,
			'exact_code'      => false,
		);
	}

	private function output($json) {
		$this->response->addHeader('Content-Type: application/json; charset=utf-8');
		$this->response->addHeader('Cache-Control: no-store');
		$this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}
}
