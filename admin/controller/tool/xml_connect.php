<?php 
class ControllerToolXmlConnect extends Controller { 
	private $error = array();

	public function index() {
	
		$this->load->language('tool/xml_connect');

		$this->load->model('tool/xml_connect');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$filter_data = array(
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$results = $this->model_tool_xml_connect->getMessages($filter_data);
		$messages_total = $this->model_tool_xml_connect->getTotalMessages();
		
		foreach ($results as $result) {
			$data['messages'][] = array(
					'id'      	 => $result['id'],
					'comment' 	 => $result['comment'],
					'message'    => $result['message'],
					'product_id' => $result['product_id'],
					'date_created' => $result['date_created'],
					'edit'		 => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] , 'SSL')
			);
		}

		$this->document->setTitle($this->language->get('heading_title'));
		
		$data['heading_title'] = $this->language->get('heading_title');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('tool/xml_connect', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$url = '';

		if (isset($this->request->get['page'])) {
			//$url .= '&page=' . $this->request->get['page'];
		}

		$pagination = new Pagination();
		$pagination->total = $messages_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('tool/xml_connect', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', 'SSL');

		$data['pagination'] = $pagination->render();
					
		$this->response->setOutput($this->load->view('tool/xml_connect', $data));

	}


}