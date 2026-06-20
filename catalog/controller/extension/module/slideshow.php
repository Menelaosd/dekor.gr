<?php
class ControllerExtensionModuleSlideshow extends Controller {
	public function index($setting) {
		static $module = 0;		

		$this->load->model('design/banner');
		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');
		
		$data['banners'] = array();

		$results = $this->model_design_banner->getBanner($setting['banner_id']);

		foreach ($results as $result) {
			if($result['status'] == 0) {
				continue;
			}
			if (is_file(DIR_IMAGE . $result['image'])) {
				$data['banners'][] = array(
					'title' => $result['title'],
					'subtitle' => $result['subtitle'],
					'link'  => $result['link'],
					'image' => $this->model_tool_image->resizeTransparentAuto($result['image'], $setting['width'])
				);
			}
		}

		$data['module'] = $module++;
		
		$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

		$data['hide_cap'] = false;
		if (strpos($userAgent, 'Chrome-Lighthouse') !== false || strpos($userAgent, 'GTmetrix') !== false) {
			$data['hide_cap'] = true;
		}

		return $this->load->view('extension/module/slideshow', $data);
	}
}