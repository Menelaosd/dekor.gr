<?php
class ControllerExtensionModulePavmegamenu extends Controller {

	public $data;
	public function index($setting) {
		static $module = 0;
			
		$this->load->model('catalog/product'); 
		$this->load->model('tool/image');
		$this->load->model( 'menu/megamenu' );
		
		$this->language->load('module/pavmegamenu');
	
		$data['button_cart'] = $this->language->get('button_cart');
		if (file_exists('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/pavmegamenu/style.css')) {
			$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/pavmegamenu/style.css');
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/pavmegamenu/style.css');
		}
		
		 
		$params = $this->config->get( 'params' );
	 	
		$this->load->model('setting/setting');
		$params = $this->model_setting_setting->getSetting( 'pavmegamenu_params' );

		 
		if( isset($params['pavmegamenu_params']) && !empty($params['pavmegamenu_params']) ){
	 		$params = json_decode( $params['pavmegamenu_params'] );
	 	}
		
		//get store
		$store_id = $this->config->get('config_store_id');
		$data['store_id'] = $store_id;

		$parent = '1';
		$data['treemenu'] = $this->model_menu_megamenu->getTree( $parent, true, $params, $store_id);

		return $this->load->view('extension/module/pavmegamenu', $data);
	}



	public function mmenu($setting) {
		static $module = 0;
			
		$this->load->model('catalog/product'); 
		$this->load->model('tool/image');
		$this->load->model( 'menu/megamenu' );
		
		$this->language->load('module/pavmegamenu');
	
		$data['button_cart'] = $this->language->get('button_cart');
		if (file_exists('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/pavmegamenu/style.css')) {
			$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/pavmegamenu/style.css');
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/pavmegamenu/style.css');
		}
		
		 
		$params = $this->config->get( 'params' );
	 	
		$this->load->model('setting/setting');
		$params = $this->model_setting_setting->getSetting( 'pavmegamenu_params' );

		 
		if( isset($params['pavmegamenu_params']) && !empty($params['pavmegamenu_params']) ){
	 		$params = json_decode( $params['pavmegamenu_params'] );
	 	}
		
		//get store
		$store_id = $this->config->get('config_store_id');
		$data['store_id'] = $store_id;

		$parent = '1';
		$data['treemenu'] = $this->model_menu_megamenu->mMenu( $parent, true, $params, $store_id);

		return $data['treemenu'];
	}



	public function ajxgenmenu( ){ 
 	 	
	}

	public function renderwidget(){

		$this->load->model( 'menu/widget' );
		$this->model_menu_widget->loadWidgets();

		if( isset($this->request->post['widgets']) ){
		
			
			$widgets = $this->request->post['widgets'];
			$widgets = explode( '|wid-', '|'.$widgets );
			if( !empty($widgets) ){
				unset( $widgets[0] );
			
				$output = '';
				foreach( $widgets as $wid ){
					$output .= $this->model_menu_widget->renderButton( $wid );
				}

				echo $output;
			}
		 
		}
		exit();
	}
}
	
