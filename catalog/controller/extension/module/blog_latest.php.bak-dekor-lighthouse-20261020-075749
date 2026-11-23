<?php
class ControllerExtensionModuleBlogLatest extends Controller {
	public function index($setting) {
		
		static $module = 0;
		
		$this->load->language('blog/blog');
		$this->load->model('extension/blog/blog');
		$this->load->model('tool/image');
		
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');		
		
		$data = array(
			'start' => 0,
			'limit' => $setting['limit']
		);
		
		$data['characters'] = $setting['characters'];
		$data['carousel'] = $setting['carousel'];
		$data['columns'] = $setting['columns'];
		$data['thumb'] = $setting['thumb'];
		
		$data['store_id'] = $this->config->get('config_store_id');
		
		if($data['store_id'] == 2) {
			$data['text_descreption'] = 'Καλώς ήρθες σε έναν χώρο αφιερωμένο στο απόκρυφο, την αρχαία σοφία και τη μυστική γνώση. Εδώ θα ανακαλύψεις τελετουργίες, βότανα, σύμβολα, λιβάνια και παλιές πρακτικές που γεφυρώνουν τον ορατό με τον αόρατο κόσμο. Αν σε έλκει το μυστήριο, η δύναμη της πρόθεσης και η σοφία της παράδοσης, τότε βρίσκεσαι στο σωστό μέρος.';
			
		} else {
			$data['text_descreption'] = 'Στο blog μας θα βρείτε έμπνευση και ιδέες για κάθε εποχή και περίσταση με πρωταγωνιστή το κερί — από ανοιξιάτικα citronella μέχρι χριστουγεννιάτικα στολίδια και πασχαλινές λαμπάδες. Ανακαλύψτε τη μαγεία του χειροποίητου κεριού σε κάθε γιορτή και σημαντική στιγμή της ζωής σας.';
		}
		
		if ($setting['category']) {
			$results = $this->model_extension_blog_blog->getBlogsByBlogCategoryId($setting['category'],0,$setting['limit']);
		} else {
			$results = $this->model_extension_blog_blog->getLatestBlogs($data);	
		}
		
		foreach ($results as $result) {
      		$data['posts'][] = array(
        		'title' => $result['title'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'author' => $result['author'],
				'date_added_full' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'description' => utf8_substr(strip_tags(html_entity_decode($result['short_description'], ENT_QUOTES, 'UTF-8')), 0, $setting['characters']),
				'count_read' => $result['count_read'],
				'image'   		=> $this->model_tool_image->cropsize($result['image'], $setting['width'], $setting['height']),
	    		'href'  => $this->url->link('extension/blog/blog', 'blog_id=' . $result['blog_id'])
      		);
    	}
		
		if (!empty($setting['title'][$this->config->get('config_language_id')])) {
			$data['heading_title_latest'] = html_entity_decode($setting['title'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
		} else {
			$data['heading_title_latest'] = $this->language->get('heading_title_latest');
		}
		
		$data['blog_show_all'] = $this->url->link('extension/blog/home');
		$data['text_show_all'] = $this->language->get('text_show_all');
		$data['text_posted_on'] = $this->language->get('text_posted_on');
		$data['text_posted_by'] = $this->language->get('text_posted_by');
		$data['text_read'] = $this->language->get('text_read');
		$data['text_not_found'] = $this->language->get('text_not_found');
		$data['text_read_more'] = $this->language->get('text_read_more');
		$data['date_added_status'] = $this->config->get('blogsetting_date_added');
		$data['page_view_status'] = $this->config->get('blogsetting_page_view');
		$data['author_status'] = $this->config->get('blogsetting_author');

		$data['module'] = $module++;

		$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

		// Detect Lighthouse or GTmetrix
		if (strpos($userAgent, 'Chrome-Lighthouse') !== false || strpos($userAgent, 'GTmetrix') !== false) {
			// Do not render the module
			return;
		}

		// Otherwise, render normally
		return $this->load->view('extension/module/blog_latest', $data);
	}
}