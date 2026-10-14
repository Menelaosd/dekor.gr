<?php
class ControllerInformationWithdrawal extends Controller {
        private $error = array();

        public function index() {
                $this->document->setTitle('Ηλεκτρονική υπαναχώρηση από σύμβαση');
                $action = $this->url->link('information/withdrawal', '', true);

                $form = array(
                        'name' => '',
                        'email' => '',
                        'order_reference' => '',
                        'items' => '',
                        'consumer' => ''
                );

                if (!empty($this->session->data['withdrawal_form'])) {
                        $form = $this->session->data['withdrawal_form'];
                }

                if (empty($this->session->data['withdrawal_form_token'])) {
                        $this->session->data['withdrawal_form_token'] = bin2hex(random_bytes(32));
                }

                $stage = 'form';
                $error_warning = '';

                if (!empty($this->session->data['withdrawal_success'])) {
                        unset($this->session->data['withdrawal_success']);
                        $stage = 'success';
                } elseif ($this->request->server['REQUEST_METHOD'] == 'POST') {
                        $post_stage = isset($this->request->post['stage']) ? $this->request->post['stage'] : '';

                        if ($post_stage == 'review') {
                                $posted_token = isset($this->request->post['form_token']) ? $this->request->post['form_token'] : '';

                                if (empty($this->session->data['withdrawal_form_token']) || !hash_equals($this->session->data['withdrawal_form_token'], $posted_token)) {
                                        $error_warning = 'Η φόρμα έληξε. Ανανεώστε τη σελίδα και συμπληρώστε την ξανά.';
                                } else {
                                        $form['name'] = isset($this->request->post['name']) ? trim(strip_tags($this->request->post['name'])) : '';
                                        $form['email'] = isset($this->request->post['email']) ? trim($this->request->post['email']) : '';
                                        $form['order_reference'] = isset($this->request->post['order_reference']) ? trim(strip_tags($this->request->post['order_reference'])) : '';
                                        $form['items'] = isset($this->request->post['items']) ? trim(strip_tags($this->request->post['items'])) : '';
                                        $form['consumer'] = isset($this->request->post['consumer']) ? $this->request->post['consumer'] : '';

                                        if ($form['name'] == '' || utf8_strlen($form['name']) > 120) {
                                                $error_warning = 'Συμπληρώστε το ονοματεπώνυμό σας.';
                                        } elseif ($form['email'] == '' || utf8_strlen($form['email']) > 96 || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
                                                $error_warning = 'Συμπληρώστε έγκυρη διεύθυνση email.';
                                        } elseif ($form['order_reference'] == '' || utf8_strlen($form['order_reference']) > 100) {
                                                $error_warning = 'Συμπληρώστε τον αριθμό παραγγελίας ή άλλο στοιχείο που προσδιορίζει τη σύμβαση.';
                                        } elseif (utf8_strlen($form['items']) > 3000) {
                                                $error_warning = 'Η περιγραφή προϊόντων είναι πολύ μεγάλη.';
                                        } elseif ($form['consumer'] != '1') {
                                                $error_warning = 'Η λειτουργία αφορά καταναλωτικές αγορές. Επιβεβαιώστε ότι η παραγγελία έγινε ως καταναλωτής.';
                                        } else {
                                                $captcha_code = $this->config->get('config_captcha');

                                                if ($captcha_code && $this->config->get('captcha_' . $captcha_code . '_status') && in_array('return', (array)$this->config->get('config_captcha_page'))) {
                                                        $captcha_error = $this->load->controller('extension/captcha/' . $captcha_code . '/validate');

                                                        if ($captcha_error) {
                                                                $error_warning = 'Ο έλεγχος ασφαλείας δεν ολοκληρώθηκε. Δοκιμάστε ξανά.';
                                                        }
                                                }
                                        }

                                        if ($error_warning == '') {
                                                $this->session->data['withdrawal_form'] = $form;
                                                $this->session->data['withdrawal_confirm_token'] = bin2hex(random_bytes(32));
                                                $stage = 'confirm';
                                        }
                                }
                        } elseif ($post_stage == 'confirm') {
                                $posted_token = isset($this->request->post['confirm_token']) ? $this->request->post['confirm_token'] : '';

                                if (empty($this->session->data['withdrawal_form']) || empty($this->session->data['withdrawal_confirm_token']) || !hash_equals($this->session->data['withdrawal_confirm_token'], $posted_token)) {
                                        $error_warning = 'Η επιβεβαίωση έληξε. Ξεκινήστε ξανά τη διαδικασία.';
                                        unset($this->session->data['withdrawal_form'], $this->session->data['withdrawal_confirm_token']);
                                } else {
                                        $form = $this->session->data['withdrawal_form'];

                                        if (empty($this->session->data['withdrawal_submitted_at'])) {
                                                $now = new DateTime('now', new DateTimeZone('Europe/Athens'));
                                                $this->session->data['withdrawal_submitted_at'] = $now->format('d/m/Y H:i:s') . ' (ώρα Ελλάδας)';
                                        }

                                        $submitted_at = $this->session->data['withdrawal_submitted_at'];
                                        $items = $form['items'] != '' ? $form['items'] : 'Δεν δηλώθηκαν επιμέρους προϊόντα.';
                                        $declaration = "Ηλεκτρονική δήλωση υπαναχώρησης από εξ αποστάσεως σύμβαση\n\n"
                                                . "Ονοματεπώνυμο: " . $form['name'] . "\n"
                                                . "Email καταναλωτή: " . $form['email'] . "\n"
                                                . "Αριθμός παραγγελίας / στοιχείο σύμβασης: " . $form['order_reference'] . "\n"
                                                . "Προϊόντα: " . $items . "\n"
                                                . "Ημερομηνία και ώρα υποβολής: " . $submitted_at . "\n\n"
                                                . "Ο καταναλωτής επιβεβαίωσε ηλεκτρονικά την υποβολή της δήλωσης υπαναχώρησης.";

                                        try {
                                                if (empty($this->session->data['withdrawal_store_mail_sent'])) {
                                                        $this->sendWithdrawalMail(
                                                                $this->config->get('config_email'),
                                                                $form['email'],
                                                                'Ηλεκτρονική δήλωση υπαναχώρησης - DEKOR',
                                                                $declaration
                                                        );
                                                        $this->session->data['withdrawal_store_mail_sent'] = true;
                                                }

                                                if (empty($this->session->data['withdrawal_customer_mail_sent'])) {
                                                        $acknowledgement = "Λάβαμε την ηλεκτρονική δήλωση υπαναχώρησής σας.\n\n"
                                                                . $declaration
                                                                . "\n\nΤο παρόν email επιβεβαιώνει την παραλαβή της δήλωσης και δεν αποτελεί αξιολόγηση της νομικής εφαρμογής της.";

                                                        $this->sendWithdrawalMail(
                                                                $form['email'],
                                                                $this->config->get('config_email'),
                                                                'Απόδειξη παραλαβής δήλωσης υπαναχώρησης - DEKOR',
                                                                $acknowledgement
                                                        );
                                                        $this->session->data['withdrawal_customer_mail_sent'] = true;
                                                }

                                                unset(
                                                        $this->session->data['withdrawal_form'],
                                                        $this->session->data['withdrawal_form_token'],
                                                        $this->session->data['withdrawal_confirm_token'],
                                                        $this->session->data['withdrawal_submitted_at'],
                                                        $this->session->data['withdrawal_store_mail_sent'],
                                                        $this->session->data['withdrawal_customer_mail_sent']
                                                );

                                                $this->session->data['withdrawal_success'] = true;
                                                $this->response->redirect($action);
                                                return;
                                        } catch (Exception $e) {
                                                $error_warning = 'Δεν ολοκληρώθηκε η αποστολή της δήλωσης. Δοκιμάστε ξανά ή επικοινωνήστε με την DEKOR.';
                                                $stage = 'confirm';
                                        }
                                }
                        } else {
                                $error_warning = 'Μη έγκυρο αίτημα. Ξεκινήστε ξανά τη διαδικασία.';
                        }
                }

                $data['action'] = $action;
                $data['stage'] = $stage;
                $data['form'] = $form;
                $data['error_warning'] = $error_warning;
                $data['form_token'] = $this->session->data['withdrawal_form_token'];
                $data['confirm_token'] = isset($this->session->data['withdrawal_confirm_token']) ? $this->session->data['withdrawal_confirm_token'] : '';
                $data['captcha'] = '';

                if ($stage == 'form') {
                        $captcha_code = $this->config->get('config_captcha');

                        if ($captcha_code && $this->config->get('captcha_' . $captcha_code . '_status') && in_array('return', (array)$this->config->get('config_captcha_page'))) {
                                $data['captcha'] = $this->load->controller('extension/captcha/' . $captcha_code, array());
                        }
                }

                $data['breadcrumbs'] = array(
                        array('text' => 'Αρχική', 'href' => $this->url->link('common/home')),
                        array('text' => 'Υπαναχώρηση από σύμβαση', 'href' => $action)
                );

                $data['column_left'] = $this->load->controller('common/column_left');
                $data['column_right'] = $this->load->controller('common/column_right');
                $data['content_top'] = $this->load->controller('common/content_top');
                $data['content_bottom'] = $this->load->controller('common/content_bottom');
                $data['footer'] = $this->load->controller('common/footer');
                $data['header'] = $this->load->controller('common/header');

                $this->response->setOutput($this->load->view('information/withdrawal', $data));
        }

        private function sendWithdrawalMail($to, $reply_to, $subject, $text) {
                $mail = new Mail($this->config->get('config_mail_engine'));
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                $mail->setTo($to);
                $mail->setFrom($this->config->get('config_email'));
                $mail->setReplyTo($reply_to);
                $mail->setSender('DEKOR CANDLES E.E.');
                $mail->setSubject($subject);
                $mail->setText($text);
                $mail->send();
        }
}
