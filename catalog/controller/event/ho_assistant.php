<?php
/**
 * dekor: the shopping assistant's widget on every store page (ported from oc_ho_blank) through an OpenCart event,
 * code ho_assistant, so no core / OCMOD file changes. Installed / removed by upgrade-3.0.5.1/ho_assistant_install.php.
 * The chat itself: catalog/controller/extension/ho_assistant.php + catalog/view/javascript/ho_assistant/.
 */
class ControllerEventHoAssistant extends Controller {
	// catalog/controller/common/header/before: the widget's two scripts in <head> (the widget waits for the page)
	public function header(&$route, &$args) {
		$page = isset($this->request->get['route']) ? (string)$this->request->get['route'] : 'common/home';

		// not on the store's own checkout pages (the onepage checkout and its success page)
		if (preg_match('#^(checkout/checkout|checkout/success|extension/onepagecheckout/)#', $page)) {
			return;
		}

		if (!$this->load->controller('extension/ho_assistant/enabled')) {
			return;
		}

		$this->document->addScript($this->url->link('extension/ho_assistant/boot', 'language=' . (isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language'))));
		$this->document->addScript('catalog/view/javascript/ho_assistant/assistant.js?v=' . filemtime(DIR_APPLICATION . 'view/javascript/ho_assistant/assistant.js'));
	}
}
