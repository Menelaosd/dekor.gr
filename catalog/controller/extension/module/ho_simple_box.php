<?php
class ControllerExtensionModuleHoSimpleBox extends Controller {
	public function index($setting) {
		require_once DIR_SYSTEM . "library/mobile/Mobile_Detect.php";
		$detect = new Mobile_Detect;	

		$data['store_id'] = $this->config->get('config_store_id');
		
		$this->load->model('catalog/information');
		
		$data['introtext'] = '';
		if($data['store_id'] == 2) {
			$homeInfo = $this->model_catalog_information->getInformation(29);
			$data['introtext'] = html_entity_decode($homeInfo['description']);
		} else {
			$homeInfo = $this->model_catalog_information->getInformation(28);
			$data['introtext'] = html_entity_decode($homeInfo['description']);
		};
		
		static $module = 0;		
					$data['lang'] = $this->language->get('code');		
		$data['lang_id'] = (int)$this->config->get('config_language_id');

		$this->load->model('tool/image');

        $data['store_id'] = $this->config->get('config_store_id');

		$data['banners'] = array();
		$data['module'] = $module++;
		$data['box'] = $setting;
		
		$data['infoboxes'] = array();
		$this->load->model('design/banner');
		$infoboxes = $this->model_design_banner->getBanner(18);
		foreach ($infoboxes as $infobox) {
			if (is_file(DIR_IMAGE . $infobox['image'])) {
				$data['infoboxes'][] = array(
					'title' => $infobox['title'],
					'link'  => $infobox['link'],
					'hypertitle' => $infobox['hypertitle'],
					'raw_image'  => $this->model_tool_image->resizeRaw($infobox['image']),
					'subtitle' => nl2br($infobox['subtitle']),
					'buttontext' => $infobox['buttontext'],
					'icon' => $this->model_tool_image->resize($infobox['image'], 120, 120)
				);
			}
		}
		if ($data['box']['text']) {
			$data['box']['text'] = html_entity_decode($data['box']['text'], ENT_QUOTES, 'UTF-8');
		}
		if ($data['box']['text2']) {
			$data['box']['text2'] = html_entity_decode($data['box']['text2'], ENT_QUOTES, 'UTF-8');
		}
		if ($data['box']['image']) {
			$data['box']['image'] = HTTP_SERVER.'image/'.$data['box']['image'];
		}
		if ($data['box']['image2']) {
			$data['box']['image2'] = HTTP_SERVER.'image/'.$data['box']['image2'];
		}
		
		$this->load->model('catalog/information');
		
		$information_info = $this->model_catalog_information->getInformation(32);
		
		$data['text_content'] = '';
		$data['text_content_title'] = '';
		if ($information_info) {
			$data['text_content'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');
			$data['text_content_title'] = $information_info['title'];
		}

		$active_template = str_replace('twig', '', $setting['active_template']);

		require_once DIR_SYSTEM . "library/mobile/Mobile_Detect.php";
		$detect = new Mobile_Detect;	
		if(strpos($detect->getHttpHeaders()['HTTP_USER_AGENT'], 'Chrome-Lighthouse') !== false || isset($this->request->get['google_test'])){
			return $this->load->view('extension/module/ho_simple_box_tpl/'.$active_template, $data);
		} else {
			return $this->load->view('extension/module/ho_simple_box_tpl/'.$active_template, $data);
		}
	}
}