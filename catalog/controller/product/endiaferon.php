<?php
class ControllerProductEndiaferon extends Controller {
	private $error = array();

	public function index() {
			$data['lang'] = $this->language->get('code');
		
		$data['lang_id'] = (int)$this->config->get('config_language_id'); 
		
		$json=array();
		if (!empty($this->request->post['onoma'])) {
			$data['onoma'] = $this->request->post['onoma'];
		} else {
			$data['onoma'] = '';
			$json['error']['onoma'] = "Συμπληρώστε το παραπάνω πεδίο";
		}

		if (!empty($this->request->post['epitheto'])) {
			$data['epitheto'] = $this->request->post['epitheto'];
		} else {
			$data['epitheto'] = '';
			$json['error']['epitheto'] = "Συμπληρώστε το παραπάνω πεδίο";
		}

		if (!empty($this->request->post['tilefono'])) {
			$data['tilefono'] = $this->request->post['tilefono'];
		} else {
			$data['tilefono'] = '';
			$json['error']['tilefono'] = "Συμπληρώστε το παραπάνω πεδίο";
		}

		if (!empty($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
			$json['error']['email'] = "Συμπληρώστε το παραπάνω πεδίο";
		}

		if (isset($this->request->post['kodikos_proiontos'])) {
			$data['kodikos_proiontos'] = $this->request->post['kodikos_proiontos'];
		} else {
			$data['kodikos_proiontos'] = '';
		}

		if (isset($this->request->post['onoma_proiontos'])) {
			$data['onoma_proiontos'] = $this->request->post['onoma_proiontos'];
		} else {
			$data['onoma_proiontos'] = '';
		}

		if (!empty($this->request->post['montelo'])) {
			$data['montelo'] = $this->request->post['montelo'];
		} else {
			$data['montelo'] = 'Δεν υπάρχει';
			
		}

		if (isset($this->request->post['id_product'])) {
			$data['id_product'] = $this->request->post['id_product'];
		} else {
			$data['id_product'] = '';
		}
		
		//var_dump($data);
		if (!$json) {
		$onomateponimo = $data['onoma'].' '.$data['epitheto'];
		$onoma_etairias = "Melissokomiki";
		$subject_endiaferon = "Ενδιαφέρον για το Πρόιον ".$data['onoma_proiontos'];
		$subject_endiaferon_2 = "Δηλώσατε ενδιαφέρον για το Πρόιον ".$data['onoma_proiontos'];
		$email_text = "Έχετε ένα καινούργιο χρήστη που εκδήλωσε ενδιαφέρον για κάποιο πρόιον : <br><br><b>Όνομα Προίοντος</b> : ".$data['onoma_proiontos']." <br><br><b>Μοντέλο : ".$data['montelo']."</b><br><br><b>Όνοματεπώνυμο :</b> ".$onomateponimo."<br><br><b>Tηλέφωνο επικοινωνίας : </b> ".$data['tilefono']."<br><br><b>Εmail επικοινωνίας : ".$data['email']."</b>." ;
		$email_text_2 = "Ευχαριστούμε που δηλώσατε ενδιαφέρον για το Πρόιον ".$data['onoma_proiontos'].".<br><br> Σύντομα κάποιος εκπροσωπός μας θα επικοινωνήσει μαζί σας.<br><br> Σας ευχαριστούμε πολύ."; 

		$email_text=html_entity_decode($email_text);
		$email_text_2=html_entity_decode($email_text_2);

			$this->load->language('information/contact');

			$this->document->setTitle($this->language->get('heading_title'));


			//email admin
			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setReplyTo($this->request->post['email']);
			$mail->setSender(html_entity_decode($onomateponimo, ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode($subject_endiaferon, ENT_QUOTES, 'UTF-8'));
			$mail->setHtml($email_text);
			$mail->send();
			//email user
			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($data['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setReplyTo($this->request->post['email']);
			$mail->setSender(html_entity_decode($onoma_etairias, ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode($subject_endiaferon_2, ENT_QUOTES, 'UTF-8'));
			$mail->setHtml($email_text_2);
			$mail->send();
			$json['success'] = "Προσθέθήκατε με επιτυχία στην λίστα ειδοποιήσεων για το προϊόν. Θα σας ειδοποιήσουμε μόλις έχουμε νεότερα. Ευχαριστούμε!";


			}else{


			}
			
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			}




		
	public function success() {
		$this->load->language('information/contact');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/contact')
		);

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));
	}
}

