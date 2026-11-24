<?php
/**
 * dekor: Reports > Analytics measurement (ported from oc_ho_blank) through OpenCart events, code ho_analytics,
 * so no core file - and no OCMOD copy in system/storage/modification - changes. Installed / removed by
 * upgrade-3.0.5.1/ho_analytics_install.php. The work itself: system/library/ho/analytics.php (server side,
 * no cookie without consent, a failure never breaks the page).
 */
class ControllerEventHoAnalytics extends Controller {
	// catalog/controller/common/footer/before: one page view per page (every store page renders the footer once)
	public function pageView(&$route, &$args) {
		(new \Ho\Analytics($this->registry))->track();
	}

	// catalog/controller/checkout/cart/add/after: only when the product really went into the cart
	public function addToCart(&$route, &$args, &$output) {
		$json = json_decode((string)$this->response->getOutput(), true);

		// store-only products answer with a redirect to the product page (the cart drops them)
		if (empty($json['success']) || !empty($json['redirect']) || empty($this->request->post['product_id'])) {
			return;
		}

		$product_id = (int)$this->request->post['product_id'];
		$quantity = isset($this->request->post['quantity']) ? max(1, (int)$this->request->post['quantity']) : 1;

		$this->load->model('catalog/product');

		$product = $this->model_catalog_product->getProduct($product_id);
		$price = $product ? (float)(!empty($product['special']) ? $product['special'] : $product['price']) : 0;

		(new \Ho\Analytics($this->registry))->track('add_to_cart', array('route' => 'checkout/cart/add', 'item_id' => $product_id, 'quantity' => $quantity, 'value' => $price * $quantity));
	}

	// catalog/controller/checkout/success/before: the order, once (bank transfer, Viva, PayPal all land here)
	public function purchase(&$route, &$args) {
		if (empty($this->session->data['order_id'])) {
			return;
		}

		$this->load->model('checkout/order');

		$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if ($order) {
			(new \Ho\Analytics($this->registry))->track('purchase', array('route' => 'checkout/success', 'order_id' => (int)$order['order_id'], 'value' => (float)$order['total'], 'quantity' => (int)$this->cart->countProducts()));
		}
	}
}
