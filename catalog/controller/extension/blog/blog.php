<?php 
class ControllerExtensionBlogBlog extends Controller {
	
	private $error = array();
	
	public function index() { 
	

	 
		$this->language->load('blog/blog');
		
		$this->load->model('extension/blog/blog');
		
		$this->load->model('extension/blog/blog_category');

		$this->load->model('tool/image');
		
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');
		
		$data['breadcrumbs'] = array();

      	$data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home')
      	);

      	$data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_blog'),
			'href'      => $this->url->link('extension/blog/home')
      	);
		
		$data['store'] = $this->config->get('config_name');
			
		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		
		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		if (isset($this->request->get['blogpath'])) {
		
			$path = '';

			$parts = explode('_', (string)$this->request->get['blogpath']);

			$blog_category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_extension_blog_blog_category->getBlogCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('extension/blog/category', 'blogpath=' . $path)
					);
				}
			}

			// Set the last category breadcrumb
			$category_info = $this->model_extension_blog_blog_category->getBlogCategory($blog_category_id);

			if ($category_info) {
				$url = '';

				$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('extension/blog/category', 'blogpath=' . $this->request->get['blogpath'] . $url)
				);
			}
		}		
		
		if (isset($this->request->get['blog_id'])) {
			$blog_id = $this->request->get['blog_id'];
		} else {
			$blog_id = 0;
		}

		$blog_info = $this->model_extension_blog_blog->getBlog($blog_id);
   		
		if ($blog_info) {
			$url = '';
			
			if (isset($this->request->get['blogpath'])) {
				$url .= '&blogpath=' . $this->request->get['blogpath'];
			}
			
			$data['breadcrumbs'][] = array(
			'text'      => $blog_info['title'],
			'href' => $this->url->link('extension/blog/blog', $url . '&blog_id=' . $this->request->get['blog_id'])
			);
			
			$data['new_read_counter_value'] = $blog_info['count_read']+1;
			$this->model_extension_blog_blog->updateBlogReadCounter($this->request->get['blog_id'], $data['new_read_counter_value']);
			if (isset($this->request->get['blog_id'])) {
				
			$data['post_date_added_status'] = $this->config->get('blogsetting_post_date_added');
			
			
			$data['post_page_view_status'] = $this->config->get('blogsetting_post_page_view');
			$data['post_author_status'] = $this->config->get('blogsetting_post_author');
			$data['share_status'] = $this->config->get('blogsetting_share');
			$data['main_thumb'] = $this->config->get('blogsetting_post_thumb');
			$data['date_added_status'] = $this->config->get('blogsetting_date_added');
			$data['page_view_status'] = $this->config->get('blogsetting_page_view');
			$data['author_status'] = $this->config->get('blogsetting_author');
			$data['rel_thumb_status'] = $this->config->get('blogsetting_rel_thumb');
			$data['rel_per_row'] = $this->config->get('blogsetting_rel_blog_per_row');
			$data['rel_prod_per_row'] = $this->config->get('blogsetting_rel_prod_per_row');
			
			$rel_img_width = $this->config->get('blogsetting_rel_thumbs_w');
			if (empty($rel_img_width)) {
			$rel_img_width = 408;
			}
			
			$rel_img_height = $this->config->get('blogsetting_rel_thumbs_h');
			if (empty($rel_img_height)) {
			$rel_img_height = 204;
			}
			
			$rel_prod_img_height = $this->config->get('blogsetting_rel_prod_height');
			if (empty($rel_prod_img_height)) {
			$rel_prod_img_height = 266;
			}
			
			$rel_prod_img_width = $this->config->get('blogsetting_rel_prod_width');
			if (empty($rel_prod_img_width)) {
			$rel_prod_img_width = 266;
			}
			
			// Related posts
			$data['related_blogs'] = array();
			
			$related_blogs = $this->model_extension_blog_blog->getRelatedBlog($this->request->get['blog_id']);
		
			foreach ($related_blogs as $result) {
      			$data['related_blogs'][] = array(
        		'title' => $result['title'],
				'count_read' => $result['count_read'],
				'short_description' => utf8_substr(strip_tags(html_entity_decode($result['short_description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('blogsetting_rel_characters')),
				'author' => $result['author'],
        		'date_added_full' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
        		'image' => $this->model_tool_image->resize($result['image'], $rel_img_width, $rel_img_height),
	    		'href'  => $this->url->link('extension/blog/blog', 'blog_id=' . $result['blog_id'])
      			);
    		  }
    		}
			
			if ($blog_info['page_title']) {
			$this->document->setTitle($blog_info['page_title']);
			} else {
			$this->document->setTitle($blog_info['title']);
			}
			
			$this->document->setDescription($blog_info['meta_description']);
			$this->document->setKeywords($blog_info['meta_keyword']);
			
			$this->document->addLink($this->url->link('extension/blog/blog', 'blog_id=' . $this->request->get['blog_id']), 'canonical');
										
      		$data['heading_title'] = $blog_info['title'];
			
			$data['description'] = html_entity_decode($this->replaceShortcode($blog_info['description']));

			$data['short_description'] = html_entity_decode($blog_info['short_description'], ENT_QUOTES, 'UTF-8');
			
			$img_width = $this->config->get('blogsetting_post_thumbs_w');
			$data['img_width'] = $this->config->get('blogsetting_post_thumbs_w');
			//if (empty($img_width)) {
			$img_width = 1440;
			$data['img_width'] = 1440;
			//}
			
			$img_height = $this->config->get('blogsetting_post_thumbs_h');
			$data['img_height'] = $this->config->get('blogsetting_post_thumbs_h');
			//if (empty($img_height)) {
			$img_height = 700;
			$data['img_height'] = 700;
			//}
	      		
			$data['blogsetting_post_thumb'] = $this->model_tool_image->cropsize($blog_info['image'], $img_width, $img_height);
			
			// Set og:image (and more if needed)
			if (!empty($blog_info['image']) && is_file(DIR_IMAGE . $blog_info['image'])) {
				$og_image = $this->model_tool_image->resize($blog_info['image'], 1200, 630);

				$this->document->addOgMeta('og:image', $og_image);
			}

			// Pass to view (OpenCart-style)
			$data['og_metas'] = $this->document->getOgMetas();
						
			$data['tags'] = array();

			if ($blog_info['tags']) {
				$tags = explode(',', $blog_info['tags']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('extension/blog/home', 'tag=' . trim($tag))
					);
				}
			}
			
			// Related products
			$data['products'] = array();
			
			$results = $this->model_extension_blog_blog->getProductRelated($this->request->get['blog_id']);
			
			$this->load->model('catalog/product');
			
			$module_products = [];

			foreach ($results as $result) {
				
				$module_products[] = [
					'product_id' => $result['product_id'],
					'name' => $result['name']
				];
				
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $rel_prod_img_width, $rel_prod_img_height);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $rel_prod_img_width, $rel_prod_img_height);
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,				
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'meta_description' => utf8_substr(trim(strip_tags(html_entity_decode($result['meta_description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}
			
			$data['tab_products_info_output'] = '';
			
			if($data['products']) {
				$this->load->model('setting/module');
				
				$tab_products_info = $this->model_setting_module->getModule(72);
				
				$tab_products_info['product_tabs'][0]['product'] = $module_products;
				$tab_products_info['module_id'] = 72;
				
				if($tab_products_info && $data['mod_title']) {
					$tab_products_info['main_description'][2]['heading'] = $data['mod_title'];
				}			
				
				if($tab_products_info && $data['mod_subtitle']) {
					$tab_products_info['main_description'][2]['subtitle'] = $data['mod_subtitle'];
				}
				
				$tab_products_info_output = $this->load->controller('extension/module/ho_producttabs', $tab_products_info);
				
				if ($tab_products_info_output) {
					$data['tab_products_info_output'] = $tab_products_info_output;
				}	
			}
			

			$data['date_added_full'] = date($this->language->get('date_format_short'), strtotime($blog_info['date_added']));
			$data['date_added_day'] = date('d', strtotime($blog_info['date_added']));
			$data['date_added_month'] = date('M', strtotime($blog_info['date_added']));
			
			$data['author'] = $blog_info['author'];
			
			
			$data['continue'] = $this->url->link('common/home');
			
			$data['blog_id'] = (int)$this->request->get['blog_id'];
					
	  		$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			
			$this->response->setOutput($this->load->view('extension/blog/blog', $data));

    	} else {
			
			$url = '';
			
      		$data['breadcrumbs'] [] = array(
        		'href'      => $this->url->link('extension/blog/blog', $url . '&blog_id=' . $this->request->get['blog_id']),
        		'text'      => $this->language->get('text_error')
      		);
				
	  		$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			
			$this->response->setOutput($this->load->view('error/not_found', $data));
    	}
	}

	private function replaceShortcode($text) {

		preg_match_all('/{ho_producttabs_([0-9]+)}/', $text, $matches);

		$module_ids = $matches[1];

		foreach ($module_ids as $module_id){
			$moduleContent = $this->getModuleContent($module_id);
			$text = preg_replace('{{ho_producttabs_'.$module_id.'}}', $moduleContent, $text);
		}
	
		return $text;
	}

	private function getModuleContent($moduleId) {

		$this->load->model('setting/module');

		$module = $this->model_setting_module->getModule($moduleId);

		$module['blog_tabs'] = true;

		$moduleContent = $this->load->controller('extension/module/ho_producttabs', $module);

		return $moduleContent;
	}
	
}