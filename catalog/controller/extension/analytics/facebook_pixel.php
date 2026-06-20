<?php
class ControllerExtensionAnalyticsFacebookPixel extends Controller {
    public function index() {
		return html_entity_decode($this->config->get('analytics_facebook_pixel_code'), ENT_QUOTES, 'UTF-8');
	}
}
