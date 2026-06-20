<?php 
class ControllerExtensionModuleHoNewsletter extends Controller {

	public function index($setting) {
			$data['lang'] = $this->language->get('code');
		
		$data['lang_id'] = (int)$this->config->get('config_language_id'); 
		
		$data = array();

		$active_template = str_replace('twig', '', $setting['active_template']);

		return $this->load->view('extension/module/ho_newsletter_tpl/'.$active_template, $data);
	}

	public function subscribe() {
		
		if (isset($this->request->post['email'])) {
			
			$this->language->load('extension/module/ho_newsletter');
			$this->load->model('extension/module/ho_newsletter');
			$this->load->model('account/customer');

			$json = array();

			$data                = array();
			$data['store_id']    = $this->config->get('config_store_id');
			$data['customer_id'] = 0;
			$data['email']       = $this->request->post['email'];
			$data['store_id']    = 0;

			if (isset($this->request->post['coupon'])) {
				$coupon_send = $this->request->post['coupon'];
			} else {
				$coupon_send = 0;
			}

			if ($coupon_send) {

				$subject = "Ξεκίνα τις αγορές σου τώρα με ΚΟΥΠΟΝΙ -10%!";

				$html_data['logo'] 			= HTTP_SERVER . 'image/' . $this->config->get('config_logo');
				$html_data['store_url'] 	= HTTP_SERVER;
				$html_data['store_name'] 	= $this->config->get('store_name');

				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

				$mail->setTo($data['email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender(html_entity_decode('Homey.gr', ENT_QUOTES, 'UTF-8'));
				$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
				$mail->setHtml($this->load->view('mail/coupon', $html_data));
				// $mail->setText($text);
				$mail->send();
			}
			
			if (!$this->model_extension_module_ho_newsletter->checkExists($this->request->post['email'])) {
				if ($customer = $this->model_account_customer->getCustomerByEmail($this->request->post['email'])) {
					$data['customer_id'] = $customer['customer_id'];
				}
				$this->model_extension_module_ho_newsletter->storeSubscribe($data);

				$json['success'] = $this->language->get('success_post');

				$json['redirect'] = $this->url->link('common/home', '', 'SSL');
			} else {
				$json['error'] = $this->language->get('error_post');
			}
			$this->response->setOutput(json_encode($json));
		}
	}

}
