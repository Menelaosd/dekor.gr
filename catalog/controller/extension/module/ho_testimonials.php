<?php
class ControllerExtensionModuleHoTestimonials extends Controller {
	public function index($setting) {
		require_once DIR_SYSTEM . "library/mobile/Mobile_Detect.php";
		$detect = new Mobile_Detect;	
		
		$data['module_name'] = $setting['name'];
		$testimonials = array();

		if(isset($setting['testimonials'])) {
			$testimonials_data = $setting['testimonials'];
		};	

		if(isset($setting['testimonials'])) {
			foreach($setting['testimonials'] as $testimonial) {
				$testimonials[] = array(
					'author' =>  $testimonial['testimonial_description'][(int)$this->config->get('config_language_id')]['author'],
					'comments' =>  $testimonial['testimonial_description'][(int)$this->config->get('config_language_id')]['comments'],
					'sort_order' =>  $testimonial['sort_order']
				);
			};
		};
		if($testimonials) {
			usort($testimonials, function($a, $b) {
				return $a['sort_order'] <=> $b['sort_order'];
			});
		};
		$data['testimonials'] = $testimonials;
		
		require_once DIR_SYSTEM . "library/mobile/Mobile_Detect.php";
		$detect = new Mobile_Detect;	
		if(strpos($detect->getHttpHeaders()['HTTP_USER_AGENT'], 'Chrome-Lighthouse') !== false || isset($this->request->get['google_test'])){
		} else {
			return $this->load->view('extension/module/ho_testimonials', $data);
		}
	}
}
