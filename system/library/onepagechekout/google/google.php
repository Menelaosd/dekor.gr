<?php
class ControllerCommonQuickLoginSocialGooglegoogle extends Controller {
	public function index() {
	
		$this->load->language('common/quick_login');
		$this->load->model('account/quick_login');
		$this->load->model('account/customer');
		require_once(DIR_APPLICATION. "controller/common/quick_login_social/google/Google/autoload.php"); //include google SDK
		
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
		
		if (isset($_GET['code'])) {
			$client->authenticate($_GET['code']);
			$_SESSION['access_token'] = $client->getAccessToken();
			header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
			exit;
		}
		
		if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
		   $client->setAccessToken($_SESSION['access_token']);
		} else {
		   $authUrl = $client->createAuthUrl();
		}
		
		if(!isset($authUrl)){
			$user_profile = $service->userinfo->get();
			if($user_profile['name']) {
				$names = explode(' ', $user_profile['name']);
			}else{
				$names= array();
			}
			$fbdata = array(
			 'firstname'	=> (!empty($names[0])) ? $names[0] : '',
			 'lastname'		=> (!empty($names[1])) ? $names[1] : '',
			 'email'		=> $user_profile['email'],
			);
			
			$customer_infos = $this->model_account_quick_login->checkUserBySocialMedia('google',$fbdata);
			if(!empty($_SESSION['access_token'])) {
				unset($_SESSION['access_token']);
			}
			
			if(!empty($customer_infos['customer']) && $customer_infos['customer'] == 'register') {
				$customer_info = $this->model_account_quick_login->getCustomer($customer_infos['customer_id']);
				if(!empty($customer_info['approved'])) {
					// Create token to login with
					$token = token(64);
					$this->model_account_quick_login->editToken($customer_info['customer_id'], $token);
					$this->response->redirect($this->url->link('account/login', '&token=' . $token));
				}else{
					$data['heading_title'] = $this->language->get('heading_title');
					$data['button_continue'] = $this->language->get('button_continue');
					$data['text_message'] = sprintf($this->language->get('text_approval'), $this->config->get('config_name'), $this->url->link('information/contact'));
					if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/quick_login_social/google/google_register.tpl')) {
						return $this->load->view($this->config->get('config_template') . '/template/common/quick_login_social/google/google_register.tpl', $data);
					} else {
						return $this->load->view('default/template/common/quick_login_social/google/google_register.tpl', $data);
					}
				}
				
			}else if(!empty($customer_infos['customer']) && $customer_infos['customer'] == 'exist') {
				$customer_info = $this->model_account_quick_login->getCustomer($customer_infos['customer_id']);
				if(!empty($customer_info['approved'])) {
					// Create token to login with
					$token = token(64);
					$this->model_account_quick_login->editToken($customer_info['customer_id'], $token);
					$this->response->redirect($this->url->link('account/login', '&token=' . $token));
				}else{
					$data['error_approved'] = $this->language->get('error_approved');
					
					if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/quick_login_social/google/google_login.tpl')) {
						return $this->load->view($this->config->get('config_template') . '/template/common/quick_login_social/google/google_login.tpl', $data);
					} else {
						return $this->load->view('default/template/common/quick_login_social/google/google_login.tpl', $data);
					}
				}
			}else{
				$this->response->redirect($this->url->link('common/home'));
			}
		}
	}
}
?>