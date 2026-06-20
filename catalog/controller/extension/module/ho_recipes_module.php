<?php
class ControllerExtensionModuleHoRecipesModule extends Controller {
	public function index($setting) {
		$this->load->language('ho_recipes/ho_recipes');
		$this->load->model('catalog/ho_recipes');
		$this->load->model('tool/image');
		
		$data['module_name'] = $setting['name'];
		$data['readmore'] = $setting['readmore'];
		$data['text_view_recipe'] = $this->language->get('text_view_recipe');
		$data['recipes'] = array();
		$recipes = $this->model_catalog_ho_recipes->getRandomRecipes($setting['limit']);
		$icons = array(
			'recipe_simple' => '<i class="far fa-star"></i>',
			'recipe_easy' => '<i class="far fa-star"></i>',
			'recipe_medium' => '<i class="fas fa-star-half"></i>',
			'recipe_hard' => '<i class="fas fa-star"></i>'
		);
		foreach($recipes as $related_recipe) {
			if ($related_recipe['image']) {
				$image = $this->model_tool_image->resizeCrop($related_recipe['image'],$setting['imagewidth'],$setting['imageheight']);
			} else {
				$image = $this->model_tool_image->resizeCrop('placeholder.png',$setting['imagewidth'],$setting['imageheight']);
			}

			if ($related_recipe['diff']) {
				$diff = $icons[$related_recipe['diff']].' '.$this->language->get($related_recipe['diff']);
			} else {
				$diff = '';
			}

			$data['recipes'][] = array(
				'recipe_id'            => $related_recipe['recipe_id'],
				'title'            => $related_recipe['title'],
				'intro'            => $related_recipe['intro'],
				'servings'            => $related_recipe['servings'],
				'preptime'            => $related_recipe['preptime'],
				'diff'            => $diff,
				'tip'            => $related_recipe['tip'],
				'image'       		=> $image,
				'author_name'            => $related_recipe['author_name'],
				'author_title'            => $related_recipe['author_title'],
				'author_bio'            => $related_recipe['author_bio'],
				'description'      => $related_recipe['description'],
				'meta_title'       => $related_recipe['meta_title'],
				'meta_description' => $related_recipe['meta_description'],
				'meta_keyword'     => $related_recipe['meta_keyword'],
				'href'             => $this->url->link('ho_recipes/ho_recipes', 'recipe_id=' . $related_recipe['recipe_id'])
			);
		};
		return $this->load->view('extension/module/ho_recipes_module', $data);
	}
}