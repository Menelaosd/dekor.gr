<?php
/**
 * Reports › Analytics: visitors, acquisition, behaviour, products and sales in one place, with charts.
 * Data: model report/ho_analytics. dekor: ported from oc_ho_blank (BLANK3); measurement + menu run through
 * OpenCart events (code ho_analytics, see upgrade-3.0.5.1/ho_analytics_install.php), with a store filter.
 */
class ControllerReportHoAnalytics extends Controller {
	public function index() {
		$this->load->language('report/ho_analytics');
		$this->load->model('report/ho_analytics');

		// the table, the first time the page is opened after the switch went on
		if (!$this->model_report_ho_analytics->installed()) {
			$this->model_report_ho_analytics->install();
		}

		$this->model_report_ho_analytics->upgrade();

		$data['stores'] = $this->model_report_ho_analytics->getStores();

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('view/stylesheet/ho_analytics.css?v=' . filemtime(DIR_APPLICATION . 'view/stylesheet/ho_analytics.css'));
		// dekor: the range picker is not in this admin's header (oc_ho_blank loads it there)
		$this->document->addStyle('view/javascript/jquery/datetimepicker/daterangepicker.css');
		$this->document->addScript('view/javascript/jquery/datetimepicker/daterangepicker.js');
		$this->document->addScript('view/javascript/chartjs/chart.umd.min.js');
		$this->document->addScript('view/javascript/ho_analytics.js?v=' . filemtime(DIR_APPLICATION . 'view/javascript/ho_analytics.js'));

		$data['breadcrumbs'] = array(
			array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)),
			array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('report/ho_analytics', 'user_token=' . $this->session->data['user_token'], true))
		);

		$data['config'] = json_encode(array(
			'data'     => html_entity_decode($this->url->link('report/ho_analytics/data', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8'),
			'realtime' => html_entity_decode($this->url->link('report/ho_analytics/realtime', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8'),
			'export'   => html_entity_decode($this->url->link('report/ho_analytics/export', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8'),
			'currency' => $this->config->get('config_currency'),
			'locale'   => str_replace('_', '-', substr($this->language->get('code'), 0, 2)) == 'el' ? 'el-GR' : 'en-GB',
			'text'     => $this->language->all()
		), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

		foreach ($this->language->all() as $key => $value) {
			$data[$key] = $value;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('report/ho_analytics', $data));
	}

	/**
	 * One tab's data for a range: ?tab=overview|acquisition|behaviour|products|sales&from=Y-m-d&to=Y-m-d&compare=1
	 * &device=&channel=  -> JSON
	 */
	public function data() {
		$json = array();

		if (!$this->user->hasPermission('access', 'report/ho_analytics')) {
			$json['error'] = 'permission';
		} else {
			$this->load->model('report/ho_analytics');

			$m = $this->model_report_ho_analytics;
			list($from, $to) = $this->range();
			$filter = $this->filters();
			$tab = isset($this->request->get['tab']) ? (string)$this->request->get['tab'] : 'overview';
			$days = (int)((strtotime($to) - strtotime($from)) / 86400) + 1;
			$unit = $days <= 1 ? 'hour' : ($days <= 120 ? 'day' : ($days <= 400 ? 'week' : 'month'));

			$m->setRange($from, $to, $filter);

			$json['range'] = array('from' => $from, 'to' => $to, 'days' => $days, 'unit' => $unit);

			switch ($tab) {
				case 'overview':
					$json['totals'] = $m->totals();
					$json['series'] = $this->fill($m->series($unit), $from, $to, $unit);
					$json['channels'] = $m->breakdown('channel', 8);
					$json['devices'] = $m->breakdown('device', 3);
					$json['funnel'] = $m->funnel();
					$json['heatmap'] = $m->heatmap();
					$json['pages'] = $m->pages(8);
					$json['products'] = $m->products(8);
					break;
				case 'acquisition':
					$json['channels'] = $m->acquisition('channel');
					$json['sources'] = $m->acquisition('source', 30);
					$json['campaigns'] = $m->acquisition('campaign', 30);
					$json['referrers'] = $m->acquisition('referrer', 30);
					$json['series'] = $this->fill($m->series($unit), $from, $to, $unit);
					break;
				case 'behaviour':
					$json['pages'] = $m->pages(50);
					$json['landings'] = $m->landings(30);
					$json['types'] = $m->pageTypes();
					$json['searches'] = $m->searches(30);
					$json['funnel'] = $m->funnel();
					$json['audience'] = array('device' => $m->breakdown('device'), 'browser' => $m->breakdown('browser'), 'os' => $m->breakdown('os'), 'language' => $m->breakdown('language'), 'country' => $m->breakdown('country'));
					$json['returning'] = $m->newReturning();
					$json['heatmap'] = $m->heatmap();
					break;
				case 'products':
					$json['products'] = $m->products(100);
					break;
				case 'sales':
					$json['totals'] = $m->totals();
					$json['series'] = $this->fill($m->series($unit), $from, $to, $unit);
					$json['categories'] = $m->salesBy('category');
					$json['payment'] = $m->salesBy('payment');
					$json['shipping'] = $m->salesBy('shipping');
					$json['customers'] = $m->salesBy('customer');
					$json['sources'] = $m->salesBy('source');
					$json['hours'] = $m->salesBy('hour', 24);
					$json['coupons'] = $m->salesBy('coupon');
					$json['products'] = $m->products(15);
					break;
			}

			// the same length right before, for the arrows and the dashed line
			if (!empty($this->request->get['compare']) && in_array($tab, array('overview', 'acquisition', 'sales'))) {
				$prev_to = date('Y-m-d', strtotime($from . ' -1 day'));
				$prev_from = date('Y-m-d', strtotime($prev_to . ' -' . ($days - 1) . ' days'));

				$m->setRange($prev_from, $prev_to, $filter);

				$json['previous'] = array(
					'range'  => array('from' => $prev_from, 'to' => $prev_to),
					'totals' => $m->totals(),
					'series' => array_values($this->fill($m->series($unit), $prev_from, $prev_to, $unit))
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function realtime() {
		$json = array();

		if ($this->user->hasPermission('access', 'report/ho_analytics')) {
			$this->load->model('report/ho_analytics');

			$this->model_report_ho_analytics->setRange(date('Y-m-d'), date('Y-m-d'), $this->filters());

			$json = $this->model_report_ho_analytics->realtime();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * A table as CSV: ?table=channels|sources|campaigns|referrers|pages|landings|searches|products&from&to
	 */
	public function export() {
		if (!$this->user->hasPermission('access', 'report/ho_analytics')) {
			$this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->load->model('report/ho_analytics');

		$m = $this->model_report_ho_analytics;
		list($from, $to) = $this->range();

		$m->setRange($from, $to, $this->filters());

		$table = isset($this->request->get['table']) ? (string)$this->request->get['table'] : 'channels';
		$sources = array(
			'channels'  => function () use ($m) { return $m->acquisition('channel', 500); },
			'sources'   => function () use ($m) { return $m->acquisition('source', 500); },
			'campaigns' => function () use ($m) { return $m->acquisition('campaign', 500); },
			'referrers' => function () use ($m) { return $m->acquisition('referrer', 500); },
			'pages'     => function () use ($m) { return $m->pages(1000); },
			'landings'  => function () use ($m) { return $m->landings(1000); },
			'searches'  => function () use ($m) { return $m->searches(1000); },
			'products'  => function () use ($m) { return $m->products(1000); }
		);

		$rows = isset($sources[$table]) ? $sources[$table]() : array();

		$out = fopen('php://temp', 'r+');

		// Excel opens UTF-8 with Greek only with the BOM
		fwrite($out, "\xEF\xBB\xBF");

		if ($rows) {
			fputcsv($out, array_keys(reset($rows)), ';');

			foreach ($rows as $row) {
				fputcsv($out, array_values($row), ';');
			}
		}

		rewind($out);

		$this->response->addHeader('Content-Type: text/csv; charset=utf-8');
		$this->response->addHeader('Content-Disposition: attachment; filename="analytics-' . $table . '-' . $from . '_' . $to . '.csv"');
		$this->response->setOutput(stream_get_contents($out));
	}

	// the range asked (defaults: the last 28 days), never in the future, at most 3 years
	private function range() {
		$valid = function ($d) {
			return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) ? $d : '';
		};

		$to = isset($this->request->get['to']) ? $valid($this->request->get['to']) : '';
		$from = isset($this->request->get['from']) ? $valid($this->request->get['from']) : '';

		$to = $to && $to <= date('Y-m-d') ? $to : date('Y-m-d');
		$from = $from && $from <= $to ? $from : date('Y-m-d', strtotime($to . ' -27 days'));

		if (strtotime($to) - strtotime($from) > 3 * 366 * 86400) {
			$from = date('Y-m-d', strtotime($to . ' -3 years'));
		}

		return array($from, $to);
	}

	private function filters() {
		$filter = array();

		if (!empty($this->request->get['device']) && in_array($this->request->get['device'], array('desktop', 'mobile', 'tablet'))) {
			$filter['device'] = $this->request->get['device'];
		}

		if (!empty($this->request->get['channel']) && preg_match('/^[a-z]{2,16}$/', $this->request->get['channel'])) {
			$filter['channel'] = $this->request->get['channel'];
		}

		// dekor: '' = all stores, else one store's visits and orders
		if (isset($this->request->get['store']) && $this->request->get['store'] !== '' && ctype_digit((string)$this->request->get['store'])) {
			$filter['store_id'] = (int)$this->request->get['store'];
		}

		return $filter;
	}

	// every bucket of the range, empty ones as zeros (a line with gaps reads as «no data» instead of «no visits»)
	private function fill($series, $from, $to, $unit) {
		$out = array();
		$empty = array('pageviews' => 0, 'users' => 0, 'new_users' => 0, 'sessions' => 0, 'bounce_rate' => 0, 'duration' => 0, 'conversion_rate' => 0, 'carts' => 0, 'purchases' => 0, 'orders' => 0, 'revenue' => 0, 'aov' => 0);
		$t = strtotime($from);
		$end = strtotime($to . ' 23:59:59');
		$formats = array('hour' => 'Y-m-d H:00', 'day' => 'Y-m-d', 'week' => 'o-\WW', 'month' => 'Y-m');
		$steps = array('hour' => '+1 hour', 'day' => '+1 day', 'week' => '+1 week', 'month' => '+1 month');

		if ($unit == 'month') {
			$t = strtotime(date('Y-m-01', $t));
		}

		while ($t <= $end) {
			$key = date($formats[$unit], $t);
			$out[$key] = array_merge($empty, isset($series[$key]) ? $series[$key] : array());
			$t = strtotime($steps[$unit], $t);
		}

		return $out;
	}

	/**
	 * dekor: event admin/view/common/column_left/before (code ho_analytics): «Analytics» first under Reports.
	 */
	public function menu(&$route, &$data) {
		if (!$this->user->hasPermission('access', 'report/ho_analytics') || empty($data['menus'])) {
			return;
		}

		foreach ($data['menus'] as &$menu) {
			if (isset($menu['id']) && $menu['id'] == 'menu-report') {
				array_unshift($menu['children'], array(
					'name'     => 'Analytics',
					'href'     => $this->url->link('report/ho_analytics', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				));
			}
		}
	}
}
