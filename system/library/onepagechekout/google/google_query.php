<?php
class ControllerCommonQuickLoginSocialGoogleGoogleQuery extends Controller {
	public function index() {
	
		$this->load->language('common/quick_login');
		$this->load->model('account/quick_login');
		$this->load->model('account/customer');
		require_once(DIR_APPLICATION. "controller/common/quick_login_social/google/Google/autoload.php"); //include google SDK
		
		######### Google API Configuration ##########
		
		if($this->config->get('quick_login_socialmedia')) {
			$quick_login_socialmedia_data = $this->config->get('quick_login_socialmedia');
		}else{
			$quick_login_socialmedia_data = array();
		}
		
		$client_id = (!empty($quick_login_socialmedia_data['google']['client_id'])) ? $quick_login_socialmedia_data['google']['client_id'] : '';
		$client_secret = (!empty($quick_login_socialmedia_data['google']['client_secret'])) ? $quick_login_socialmedia_data['google']['client_secret'] : '';
		$redirect_uri = (!empty($quick_login_socialmedia_data['google']['callbackdomain'])) ? $quick_login_socialmedia_data['google']['callbackdomain'] : ''; //return to home
		
		
		$client = new Google_Client();
		$client->setClientId($client_id);
		$client->setClientSecret($client_secret);
		$client->setRedirectUri($redirect_uri);
		$client->addScope("email");
		$client->addScope("profile");
		$service = new Google_Service_Oauth2($client);
		
		$authUrl = $client->createAuthUrl();
		
		return !empty($authUrl) ? $authUrl : '';
	}
}
?>