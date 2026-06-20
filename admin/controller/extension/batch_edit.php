<?php
/**
 *
 * @author Clicker
 * Commercial Installation-Based License
 * This extension has Installation-based license is per OpenCart installation.
 * Support: info@clicker.com.ua
 * https://opencart.click
 *
 */
class ControllerExtensionBatchEdit extends Controller {
	public $selectbox_limit = 200;
	private $allowed_option_types = array();

	public function __construct($registry) {
		parent::__construct($registry);

		if (is_file(DIR_MODIFICATION . 'admin/model/extension/batch_edit.php')) {
			require_once(DIR_MODIFICATION . 'admin/model/extension/batch_edit.php');
		} else {
			require_once(DIR_APPLICATION . 'model/extension/batch_edit.php');
		}

		if (is_file(DIR_MODIFICATION . 'admin/model/extension/batch_edit_mod.php')) {
			require_once(DIR_MODIFICATION . 'admin/model/extension/batch_edit_mod.php');
		} else {
			require_once(DIR_APPLICATION . 'model/extension/batch_edit_mod.php');
		}

		//$this->load->model('extension/batch_edit_sql');

		$this->model_extension_batch_edit = new ModelExtensionBatchEditMod($registry);
		$this->allowed_option_types = $this->model_extension_batch_edit->allowed_option_types;

		//$this->load->model('extension/batch_edit');
	}

	public function index() {
		$data = $this->load->language('catalog/product');

		$data = array_merge($data, $this->load->language('extension/batch_edit'));

		$data['datepicker'] = !empty($data['datepicker']) ? $data['datepicker'] : 'en-gb';

		$this->document->setTitle($this->language->get('heading_batch_edit'));

		$data['heading_batch_edit'] = $this->language->get('heading_batch_edit');

		// old OC versions compatibility
		if (version_compare(VERSION, '3.0.0') >= 0) {
			$data['token_name'] = 'user_token';
			$data['token'] = $this->session->data['user_token'];
			$template_files = glob(DIR_APPLICATION . "view/template/extension/batch_edit_*.twig");
		} else {
			$data['token_name'] = 'token';
			$data['token'] = $this->session->data['token'];
			$template_files = glob(DIR_APPLICATION . "view/template/extension/batch_edit_*.tpl");

			if (!isset($data['z_translit_array'])) { //language file is missing
				// try to find language file in en-gb folder for OC prior to 2.2
				$file = DIR_LANGUAGE . 'en-gb' . '/extension/batch_edit.php';
				if (is_file($file)) {
					require_once($file);
					$data = array_merge($data, $_);
					$this->document->setTitle($data['heading_batch_edit']);
				}
			}
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name' => $this->language->get('text_default')
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name' => $store['name']
			);
		}

		$data['customer_groups'] = $this->model_extension_batch_edit->getCustomerGroups();

		$this->load->model('catalog/manufacturer');
		$this->load->model('catalog/category');

		$this->load->model('localisation/tax_class');
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		//$this->load->model('localisation/tax_rate');
		//$data['tax_rates'] = $this->model_localisation_tax_rate->getTaxRates();

		$this->load->model('localisation/currency');
		$data['currencies'] = $this->model_localisation_currency->getCurrencies();

		$this->load->model('localisation/stock_status');
		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();

		$this->load->model('localisation/length_class');
		$data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

		$this->load->model('localisation/weight_class');
		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['info_backup'] = str_replace('{url}', $this->url->link('tool/backup', $data['token_name'] . '=' . $data['token'], true), $data['info_backup']);

		$settings = $this->config->get('batch_edit_settings');

		$inputs = array();

		if ($settings) {
			foreach ($settings as $idx => $value) {
				$inputs['settings[' . $idx . ']'] = $value;
			}
		}

		if (!$inputs) {
			$inputs['settings[ask_execute]'] = 1;
			$inputs['settings[open_filter_tab]'] = 1;
			$inputs['settings[update_date_modified]'] = 1;
		}

		if (empty($inputs['settings[max_products]'])) {
			$inputs['settings[max_products]'] = 500;
		}

		if (empty($inputs['settings[default_price_round_math]'])) {
			$inputs['settings[default_price_round_math]'] = 'math';
			//$inputs['settings[price_round_math]'] = 'math';
		}

		if (!isset($inputs['settings[default_price_round]'])) {
			$inputs['settings[default_price_round]'] = 6;
			//$inputs['settings[price_round]'] = 6;
		}

		if (!isset($inputs['settings[time_limit]'])) {
			$inputs['settings[time_limit]'] = 30 * 60;
		}

		if (!isset($inputs['settings[string_explode_symbol]'])) {
			$inputs['settings[string_explode_symbol]'] = '||';
		}

		if (empty($inputs['settings[translit_array]'])) {
			$inputs['settings[translit_array]'] = html_entity_decode(trim($this->language->get('z_translit_array'), ', '), ENT_QUOTES, 'UTF-8');
		}

		if (empty($inputs['settings[preview_fields]'])) {
			$inputs['settings[preview_fields]'] = 'pd.name,p.model,p.price,p.quantity,has_specials,has_discounts,has_options,has_attributes,has_filters';
		} else {
			$inputs['settings[preview_fields]'] = trim($inputs['settings[preview_fields]'], ', ');
		}

		//if (!isset($inputs['settings[autosave_layout]'])) {
		//$inputs['settings[autosave_layout]'] = 1;
		//}

		$data['text_products_first'] = str_replace('{max_products}', $inputs['settings[max_products]'], $this->language->get('text_products_first'));

		$settings['inputs'] = $inputs;

		$data['settings'] = $settings ? json_encode($settings) : '{}';

		//$this->load->model('extension/batch_edit');

		$data['custom_fields'] = $this->model_extension_batch_edit->customFields();

		$data['version'] = $this->model_extension_batch_edit->getVersion();
		$data['oc_version'] = VERSION;
		$data['bpe_json'] = $this->model_extension_batch_edit->executeAjax(array('bpe_json' => 1));

		$text_fields = $this->model_extension_batch_edit->productFields();

		$data['text_fields'] = array();

		foreach ($text_fields as $idx => $value) {
			$data['text_fields'][$idx] = $value;
			$data['text_fields'][$idx]['label'] = $this->language->get($value['label']);
			//$data['text_fields'][$idx]['input'] = !empty($value['prefix']) ? ($value['prefix'] . '_' . $value['field']) : $value['field'];
		}

		$data['table_fields_product'] = $this->model_extension_batch_edit->getTableFields(DB_DATABASE, DB_PREFIX.'product');
		$data['table_fields_product_description'] = $this->model_extension_batch_edit->getTableFields(DB_DATABASE, DB_PREFIX.'product_description');

		$data['batch_edit_form'] = $this->config->get('batch_edit_form') ? json_encode($this->config->get('batch_edit_form')) : '{}';
		$data['batch_edit_layouts'] = $this->config->get('batch_edit_layouts') ? json_encode($this->config->get('batch_edit_layouts')) : '{}';

		// Add compatibility for other extensions
		$data['oc_filters'] = $this->model_extension_batch_edit->checkFunctionality('oc_filters');
		$data['ext_labels'] = $this->model_extension_batch_edit->checkFunctionality('clicker_labels');
		$data['ext_seo_fields'] = $this->model_extension_batch_edit->checkFunctionality('clicker_seo_fields');
		$data['ext_articles'] = $this->model_extension_batch_edit->checkFunctionality('clicker_articles');
		$data['ext_sync'] = $this->model_extension_batch_edit->checkFunctionality('clicker_sync');
		$data['ext_price_base'] = $this->model_extension_batch_edit->checkFunctionality('clicker_price_base');

		$data['product_related_tables'] = array(
			'product_related' => array(
				'table' => 'product_related',		// related table name
				'field_product_id' => 'product_id',	// field name for product_id
				'field_related_id' => 'related_id',	// field name for related_id
				'fill_product_id' => 1,				// fill related products for product_id
				'fill_related_id' => 1				// fill product_id for related products
			),
			// 'product_related1' => array(
			// 	'table' => 'product_related1',
			// 	'field_product_id' => 'product_id',
			// 	'field_related_id' => 'related_id',
			// 	'fill_product_id' => 1,
			// 	'fill_related_id' => 1
			// ),
			// 'product_recommended' => array(
			// 	'table' => 'product_recommended',
			// 	'field_product_id' => 'product_id',
			// 	'field_related_id' => 'recommended_id',
			// 	'fill_product_id' => 1,
			// 	'fill_related_id' => 0
			// ),
			// 'product_similar' => array(
			// 	'table' => 'product_similar',
			// 	'field_product_id' => 'product_id',
			// 	'field_related_id' => 'similar_id',
			// 	'fill_product_id' => 1,
			// 	'fill_related_id' => 1
			// ),
			// 'product_variant' => array(
			// 	'table' => 'product_variant',
			// 	'field_product_id' => 'product_id',
			// 	'field_related_id' => 'variant_id',
			// 	'fill_product_id' => 1,
			// 	'fill_related_id' => 0
			// ),
		);

		if (!empty($template_files)) {
			foreach ($template_files as $template_file) {
				$template_name = str_replace('batch_edit_', '', str_replace(array('.twig', '.tpl'), '', basename($template_file)));
				$data['tpl_' . $template_name] = '';
			}
			foreach ($template_files as $template_file) { // Build all templates
				$template_name = str_replace('batch_edit_', '', str_replace(array('.twig', '.tpl'), '', basename($template_file)));

				if (version_compare(VERSION, '2.2.0') >= 0) { // OC2.1 and earlier requires .tpl for template name
					$data['tpl_' . $template_name] = $this->load->view('extension/' . str_replace(array('.twig', '.tpl'), '', basename($template_file)), $data);
				} else {
					$data['tpl_' . $template_name] = $this->load->view('extension/' . basename($template_file), $data);
				}
			}
			foreach ($template_files as $template_file) { // Rebuild templates including dependencies for each other, second pass takes just 0.050s
				$template_name = str_replace('batch_edit_', '', str_replace(array('.twig', '.tpl'), '', basename($template_file)));

				if (version_compare(VERSION, '2.2.0') >= 0) { // OC2.1 and earlier requires .tpl for template name
					$data['tpl_' . $template_name] = $this->load->view('extension/' . str_replace(array('.twig', '.tpl'), '', basename($template_file)), $data);
				} else {
					$data['tpl_' . $template_name] = $this->load->view('extension/' . basename($template_file), $data);
				}
			}
		}

		if (version_compare(VERSION, '2.2.0') >= 0) { // OC2.1 and earlier requires .tpl for template name
			$this->response->setOutput($this->load->view('extension/batch_edit', $data));
		} else {
			$this->response->setOutput($this->load->view('extension/batch_edit.tpl', $data));
		}
	}

	public function ajax() {
		$data = $this->load->language('catalog/product');
		$data = array_merge($data, $this->load->language('extension/batch_edit'));
		//$this->load->model('extension/batch_edit');

		$data = array(
			'allowed_option_types' => $this->allowed_option_types,
		);

		// You can add your custom functionality code here to process request before original code
		//var_dump($this->request->get);
		//var_dump($this->request->post);

		$json = $this->model_extension_batch_edit->executeAjax($data);

		// You can add your custom functionality code here to process request after original code
		//var_dump($json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete_category() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/category');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort' => 'name',
				'order' => 'ASC',
				'start' => 0,
				'limit' => $this->selectbox_limit
			);

			$results = $this->model_catalog_category->getCategories($filter_data);

			foreach ($results as $result) {

				$result['category'] = explode('&nbsp;&nbsp;&gt;&nbsp;&nbsp;', $result['name']);
				$result['category'] = end($result['category']);

				$json[] = array(
					'category_id' => $result['category_id'],
					'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'category' => strip_tags(html_entity_decode($result['category'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete_manufacturer() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/manufacturer');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start' => 0,
				'limit' => $this->selectbox_limit
			);

			$results = $this->model_catalog_manufacturer->getManufacturers($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'manufacturer_id' => $result['manufacturer_id'],
					'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete_option() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->language('catalog/option');

			$this->load->model('catalog/option');

			$this->load->model('tool/image');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start' => 0,
				'limit' => $this->selectbox_limit
			);

			$options = $this->model_catalog_option->getOptions($filter_data);

			foreach ($options as $option) {
				if (!in_array($option['type'], $this->allowed_option_types)) {
					continue;
				}

				$option_value_data = array();

				if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' || $option['type'] == 'image') {
					$option_values = $this->model_catalog_option->getOptionValues($option['option_id']);

					foreach ($option_values as $option_value) {
						if (is_file(DIR_IMAGE . $option_value['image'])) {
							$image = $this->model_tool_image->resize($option_value['image'], 50, 50);
						} else {
							$image = $this->model_tool_image->resize('no_image.png', 50, 50);
						}

						$option_value_data[] = array(
							'option_value_id' => $option_value['option_value_id'],
							'name' => strip_tags(html_entity_decode($option_value['name'], ENT_QUOTES, 'UTF-8')),
							'image' => $image
						);
					}

					$sort_order = array();

					foreach ($option_value_data as $key => $value) {
						$sort_order[$key] = $value['name'];
					}

					array_multisort($sort_order, SORT_ASC, $option_value_data);
				}

				$type = '';

				if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' || $option['type'] == 'image') {
					$type = $this->language->get('text_choose');
				}

				if ($option['type'] == 'text' || $option['type'] == 'textarea') {
					$type = $this->language->get('text_input');
				}

				if ($option['type'] == 'file') {
					$type = $this->language->get('text_file');
				}

				if ($option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
					$type = $this->language->get('text_date');
				}

				$json[] = array(
					'option_id' => $option['option_id'],
					'name' => strip_tags(html_entity_decode($option['name'], ENT_QUOTES, 'UTF-8')),
					'category' => $type,
					'type' => $option['type'],
					'option_value' => $option_value_data
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete_attribute() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/attribute');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start' => 0,
				'limit' => $this->selectbox_limit
			);

			$results = $this->model_catalog_attribute->getAttributes($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'attribute_id' => $result['attribute_id'],
					'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'attribute_group' => strip_tags(html_entity_decode($result['attribute_group'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['attribute_group'] . ' - ' . $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete_attribute_value() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/attribute');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'filter_attribute_id' => !empty($this->request->get['filter_attribute_id']) ? (int)$this->request->get['filter_attribute_id'] : 0,
				'filter_language_id' => !empty($this->request->get['filter_language_id']) ? (int)$this->request->get['filter_language_id'] : 0,
				'start' => 0,
				'limit' => $this->selectbox_limit
			);

			$sql = "SELECT pa.`attribute_id`, ad.`name`, pa.`text` FROM `" . DB_PREFIX . "product_attribute` pa
					LEFT JOIN `" . DB_PREFIX . "attribute_description` ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "')
					WHERE pa.`text` != ''";

			if (!empty($filter_data['filter_name'])) {
				$sql .= " AND pa.`text` LIKE '%" . $this->db->escape($filter_data['filter_name']) . "%'";
			}

			if (!empty($filter_data['filter_attribute_id'])) {
				$sql .= " AND pa.`attribute_id` = '" . $filter_data['filter_attribute_id'] . "'";
			}

			if (!empty($filter_data['filter_language_id'])) {
				$sql .= " AND pa.`language_id` = '" . $filter_data['filter_language_id'] . "'";
			}

			$sql .= " GROUP BY ad.name, pa.`text`";
			//$sql .= " ORDER BY pa.`text`";
			$sql .= " LIMIT " . (int)$filter_data['start'] . ', ' . (int)$filter_data['limit'];

			$results = $this->db->query($sql);
			$results = $results->rows;

			foreach ($results as $result) {
				$json[] = array(
					'attribute_id' => $result['attribute_id'],
					'attribute_name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'text' => strip_tags(html_entity_decode($result['text'], ENT_QUOTES, 'UTF-8')),
					'value' => strip_tags(html_entity_decode($result['text'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['attribute_name'] . ' - ' . $value['value'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete_filter() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/filter');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => $this->selectbox_limit
			);

			$filters = $this->model_catalog_filter->getFilters($filter_data);

			foreach ($filters as $filter) {
				$json[] = array(
					'filter_id' => $filter['filter_id'],
					'name' => strip_tags(html_entity_decode($filter['name'], ENT_QUOTES, 'UTF-8')),
					'filter_group' => strip_tags(html_entity_decode($filter['group'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['filter_group'] . ' - ' .$value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete_layout() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$sql = "SELECT * FROM " . DB_PREFIX . "layout WHERE 1";
			if ($this->request->get['filter_name']) {
				$sql .= " AND `name` LIKE '%" . $this->db->escape($this->request->get['filter_name']) . "%'";
			}
			$sql .= " ORDER BY `name` ASC";

			$layouts = $this->db->query($sql);
			$layouts = $layouts->rows;

			foreach ($layouts as $layout) {
				$json[] = array(
					'layout_id' => (int)$layout['layout_id'],
					'name' => strip_tags(html_entity_decode($layout['name'], ENT_QUOTES, 'UTF-8')),
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete_label() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/label');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start' => 0,
				'limit' => $this->selectbox_limit
			);

			$results = $this->model_catalog_label->getLabels($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'label_id' => $result['label_id'],
					'name' => strip_tags(html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8')),
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete_filter_layout() {
		$json = array();

		if (isset($this->request->get['filter_name']) || $this->request->get['filter_id']) {
			$results = $this->config->get('batch_edit_layouts');

			if (!$results) return $json;

			foreach ($results as $result) {
				$name = strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'));

				if (!empty($this->request->get['filter_name']) && trim($this->request->get['filter_name'])
						&& stripos($name, strip_tags(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'))) === false) {
							continue;
						}

						if (!empty($this->request->get['filter_id']) && trim($this->request->get['filter_id'])
								&& strpos($result['id'], $this->request->get['filter_id']) === false) {
									continue;
								}

								$json[] = array(
									'layout_id' => $result['id'],
									'name' => $name,
									'data' => !empty($result['data']) ? json_encode($result['data']) : ''
								);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}

/* HELP FOR DEVELOPERS
 *
 * GENERATE SEO URL
$keyword = $this->model_extension_batch_edit->generateSeoUrl(
'{manufacturer}-{name}-{model}-{sku}-{product_id}', // url template
array( // field values
'product_id' => (int)$product_id,
'name' => strip_tags(trim(html_entity_decode('product name', ENT_QUOTES, 'UTF-8'))),
'model' => strip_tags(trim(html_entity_decode('model', ENT_QUOTES, 'UTF-8'))),
'sku' => strip_tags(trim(html_entity_decode('sku', ENT_QUOTES, 'UTF-8'))),
'upc' => strip_tags(trim(html_entity_decode('upc', ENT_QUOTES, 'UTF-8'))),
'ean' => strip_tags(trim(html_entity_decode('ean', ENT_QUOTES, 'UTF-8'))),
'jan' => strip_tags(trim(html_entity_decode('jan', ENT_QUOTES, 'UTF-8'))),
'isbn' => strip_tags(trim(html_entity_decode('isbn', ENT_QUOTES, 'UTF-8'))),
'mpn' => strip_tags(trim(html_entity_decode('mpn', ENT_QUOTES, 'UTF-8'))),
'manufacturer' => strip_tags(trim(html_entity_decode('MyBrand', ENT_QUOTES, 'UTF-8'))) : '',
),
array( // parameters
'space' => '-', // Symbol to replace space and new line chars
'case' => 'lowercase', // 'lowercase' || 'uppercase' || ''
'translit' => true, // true || false
'max_length' => 80, // Max length for each field
'unique_name' => true, // Check if product name already contains manufacturer/model/sku/etc and don't include these fields
)
);


 * TRANSLIT
$translit_string = $this->model_extension_batch_edit->translitString('my local string'); // Translit chars can be configured in Batch Editor settings

 * */