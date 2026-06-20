<?php
class ControllerExtensionModuleHoLatestblog extends Controller {

	private $config_file = '';
	private $sub_versions = array('lite', 'light', 'free');
	private $setting = array();

	public function index($setting) {
		$this->load->language('extension/module/ho_latest_blog');

		$this->load->model('extension/module/d_blog_module');
		$this->load->model('extension/d_blog_module/post');

		$this->load->model('tool/image');

		$this->config_file = $this->model_extension_module_d_blog_module->getConfigFile('d_blog_module', $this->sub_versions);
		$this->setting = $this->model_extension_module_d_blog_module->getConfigData('d_blog_module', 'd_blog_module_setting', $this->config->get('config_store_id'), $this->config_file);

		$data['posts'] = array();

		$filter_data = array(
			'order' => 'DESC',
			'start' => 0,
			'limit' => $setting['limit']
		);

		$results = $this->model_extension_d_blog_module_post->getPosts($filter_data);
		$data['heading_title'] = $setting['name'];

		if ($results) {
			foreach ($results as $result) {
				$post = $this->model_extension_d_blog_module_post->getPost($result['post_id']);

				if ($post['image']) {
					$image = $this->model_tool_image->resize($post['image'], $this->setting['post_thumb']['image_width'], $this->setting['post_thumb']['image_height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->setting['post_thumb']['image_width'], $this->setting['post_thumb']['image_height']);
				}

				$data['posts'][] = array(
					'post_id'  				=> $result['post_id'],
					'thumb'     			=> $image,
					'name'					=> $post['title'],
					'short_description'		=> $post['short_description'],
					'date_published'		=> $post['date_published'],
					'href'        			=> $this->url->link('extension/d_blog_module/post', 'post_id=' . $result['post_id'])
				);
			}
			//var_dump($data['posts']);
			return $this->load->view('extension/module/ho_latest_blog', $data);
		}
	}
}
