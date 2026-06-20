<?php
class ControllerExtensionModuleHoInstagramFeed extends Controller {
	public function index($setting) {

		// https://api.instagram.com/oauth/authorize/?client_id=CLIENT-ID&redirect_uri=REDIRECT-URI&response_type=token
		$client_id 		= '313667dbe0fd4c289ea7388168bba998';
		$redirect_uri 	= 'https://www.jocks.gr/';
		$api_token 		= '220299787.313667d.91bdb3766a084bdfa20e487c94e55eed';

		$recent_posts_url = "https://api.instagram.com/v1/users/self/media/recent/?access_token=".$api_token;
		$insta_file = DIR_SYSTEM . "storage/instafeed/instafeed.json"; 

		if (file_exists($insta_file)) {
			$insta_filetime = filemtime($insta_file);
			$now = time();
			if(($now - $insta_filetime) >= 3600) {
				// echo 'More than hour';
				$json = file_get_contents($recent_posts_url);
				$fp = fopen( $insta_file ,"wb");
				fwrite( $fp, $json );
				fclose( $fp );
			} else {
				// echo 'Less than hour';
				$json = file_get_contents($insta_file);
			}
		} else {
			$json = file_get_contents($recent_posts_url);
		}		

		$obj 	= json_decode($json);

		$data['obj'] = $obj;
		$posts = array();

		if (!empty($obj)) {
			foreach ($obj->data as $key => $post) { 

				if (isset($post->caption->text)) {
					$caption_text = $post->caption->text;
				} else {
					$caption_text = '';
				}

				$posts[] = array(
					'id' => 1,
					'img_thumb' => $post->images->thumbnail->url,
					'img_low' 	=> $post->images->low_resolution->url,
					'img_high' 	=> $post->images->standard_resolution->url,
					'caption' 	=> $this->wrapHashTags($caption_text),
					'link' 		=> $post->link,
					'likes' 	=> $post->likes->count,
					'user' => $post->user->username,
				);
			}
		}

		$data['posts'] = $posts;

		return $this->load->view('extension/module/ho_instagram_feed', $data);
	}

	protected function wrapHashTags($text){
	    $text = $this->wrapLinks($text);
	    return preg_replace('/#([\\d\\w]+)/', '<a href="https://www.instagram.com/explore/tags/$1" target="_blank">$0</a>', $text);
	}

	protected function wrapLinks($source){
	    $pattern = '/(https?:\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?)/i';
	    $replacement = '<a href="$1">$1</a>';
	    return preg_replace($pattern, $replacement, $source);
	    return $source;
	}
}