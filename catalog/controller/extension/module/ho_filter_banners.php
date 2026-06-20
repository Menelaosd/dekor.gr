<?php
class ControllerExtensionModuleHoFilterBanners extends Controller {
	public function index($setting) {
		

		$this->load->model('extension/module/ho_filter_banners');
		$this->load->model('tool/image');
		
		$filters = $this->model_extension_module_ho_filter_banners->getFilters($setting['filter_group'],$setting['limit']);
		
		$data['module_name'] = $setting['name'];
		$data['readmore'] = $setting['readmore'];
		$data['layout_type'] = $setting['layout_type'];
		$data['filters'] = array();
		foreach($filters as $filter) {
			if (is_file(DIR_IMAGE . $filter['image'])) {
				$image = $this->model_tool_image->resizeCrop($filter['image'],$setting['imagewidth'],$setting['imageheight']);
			} else {
				$image = $this->model_tool_image->resizeCrop('no_image.png',$setting['imagewidth'],$setting['imageheight']);
			};
			$data['filters'][] = array(
				'name' => $filter['name'],
				'image' => $image,
				'filter_group_id' => $filter['filter_group_id'],
				'sort_order' => $filter['sort_order'],
				'href' => $this->url->link('product/category', 'bfilter=' . 'f'.$filter['filter_group_id'].':'.$filter['filter_id'].';')
			);
		};
		return $this->load->view('extension/module/ho_filter_banners', $data);
	}
}