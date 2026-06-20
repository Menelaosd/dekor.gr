<?php
class ControllerExtensionModuleHoSlideshow extends Controller {
	public function index($setting) {
		static $module = 0;		
					$data['lang'] = $this->language->get('code');
		
		$data['lang_id'] = (int)$this->config->get('config_language_id'); 
		$this->load->model('design/banner');
		$this->load->model('tool/image');

		// $this->document->addStyle('catalog/view/javascript/jquery/owl-carousel/owl.carousel.css');
		// $this->document->addScript('catalog/view/javascript/jquery/owl-carousel/owl.carousel.min.js');

		$data['banners'] = array();

		$results = $this->model_design_banner->getBanner($setting['banner_id']);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$data['banners'][] = array(
					'title' => $result['title'],
					'subtitle' => $result['subtitle'],
					'link'  => $result['link'],
					'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				);
			}
		}

		$data['module'] = $module++;
		$active_template = str_replace('twig', '', $setting['active_template']);
		
		return $this->load->view('extension/module/ho_slideshow_tpl/'.$active_template, $data);
	}
}
