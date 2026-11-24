/*
 * Reports › Analytics: the page. Data from report/ho_analytics/data (one tab at a time) and /realtime; charts by
 * Chart.js 4 (view/javascript/chartjs). Settings and texts: window.HOAN (written by the controller).
 */
// (loaded in the admin's <head>: it starts once the page and window.HOAN are there)
document.addEventListener('DOMContentLoaded', function () {
	'use strict';

	var C = window.HOAN || {};
	var T = C.text || {};
	var body = document.querySelector('[data-hoan-body]');

	if (!body || !window.Chart) {
		return;
	}

	var PALETTE = ['#3451e6', '#0e9f8e', '#e3a008', '#d6457a', '#7c5cdb', '#2f8fd8', '#8a93a6', '#2e9d5b', '#b4541f', '#5b6b8c'];
	var CHANNEL_COLOR = { organic: '#0e9f8e', paid: '#e3a008', social: '#d6457a', marketplace: '#7c5cdb', email: '#2f8fd8', ai: '#b4541f', referral: '#8a93a6', direct: '#3451e6' };
	var DEVICE_COLOR = { desktop: '#3451e6', mobile: '#0e9f8e', tablet: '#e3a008' };

	var state = { tab: 'overview', preset: '28', from: '', to: '', compare: true, device: '', channel: '', store: '', metric: 'users' };
	var charts = [];
	var liveTimer = null;
	var request = 0;

	Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
	Chart.defaults.font.size = 12;
	Chart.defaults.color = '#626b7d';
	Chart.defaults.borderColor = '#eef0f4';
	Chart.defaults.plugins.legend.display = false;
	Chart.defaults.plugins.tooltip.backgroundColor = '#161b26';
	Chart.defaults.plugins.tooltip.padding = 10;
	Chart.defaults.plugins.tooltip.cornerRadius = 6;
	Chart.defaults.plugins.tooltip.boxPadding = 4;
	Chart.defaults.maintainAspectRatio = false;
	Chart.defaults.animation.duration = 450;

	// ------------------------------------------------------------------------------------------- formatting

	var nf = new Intl.NumberFormat(C.locale || 'el-GR');
	var nf1 = new Intl.NumberFormat(C.locale || 'el-GR', { maximumFractionDigits: 1 });
	var money = new Intl.NumberFormat(C.locale || 'el-GR', { style: 'currency', currency: C.currency || 'EUR', maximumFractionDigits: 0 });
	var money2 = new Intl.NumberFormat(C.locale || 'el-GR', { style: 'currency', currency: C.currency || 'EUR' });

	function esc(s) {
		return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function t(key, fallback) {
		return T[key] !== undefined ? T[key] : (fallback !== undefined ? fallback : key);
	}

	function duration(sec) {
		sec = Math.round(sec || 0);
		var m = Math.floor(sec / 60);

		return m ? m + t('text_minute_short', 'm') + ' ' + (sec % 60) + t('text_second_short', 's') : sec + t('text_second_short', 's');
	}

	var METRICS = {
		users: { label: 'text_users', fmt: nf.format },
		new_users: { label: 'text_new_users', fmt: nf.format },
		sessions: { label: 'text_sessions', fmt: nf.format },
		pageviews: { label: 'text_pageviews', fmt: nf.format },
		pages_per_session: { label: 'text_pages_per_session', fmt: function (v) { return nf1.format(v); } },
		duration: { label: 'text_duration', fmt: duration },
		bounce_rate: { label: 'text_bounce_rate', fmt: function (v) { return nf1.format(v) + '%'; }, lowerIsBetter: true },
		conversion_rate: { label: 'text_conversion_rate', fmt: function (v) { return nf1.format(v) + '%'; } },
		add_to_cart: { label: 'text_add_to_cart', fmt: nf.format },
		purchases: { label: 'text_purchases', fmt: nf.format },
		revenue: { label: 'text_revenue', fmt: money.format },
		orders: { label: 'text_orders', fmt: nf.format },
		aov: { label: 'text_aov', fmt: money2.format },
		items: { label: 'text_items', fmt: nf.format },
		buyers: { label: 'text_buyers', fmt: nf.format }
	};

	// series key behind a headline number (for the sparkline and the big chart)
	// (only numbers with their own line: a rate or an average is not drawn as something else)
	var SERIES_OF = { users: 'users', new_users: 'new_users', sessions: 'sessions', pageviews: 'pageviews', bounce_rate: 'bounce_rate', duration: 'duration', conversion_rate: 'conversion_rate', add_to_cart: 'carts', purchases: 'purchases', revenue: 'revenue', orders: 'orders', aov: 'aov' };

	function label(group, key) {
		return t('text_' + group + '_' + key, key || '—');
	}

	// ------------------------------------------------------------------------------------------- range

	function ymd(d) {
		return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
	}

	function presetRange(p) {
		var now = new Date();
		var to = new Date(now);
		var from = new Date(now);

		switch (p) {
			case 'today':
				break;
			case 'yesterday':
				from.setDate(from.getDate() - 1);
				to.setDate(to.getDate() - 1);
				break;
			case 'month':
				from = new Date(now.getFullYear(), now.getMonth(), 1);
				break;
			case 'last_month':
				from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
				to = new Date(now.getFullYear(), now.getMonth(), 0);
				break;
			case 'year':
				from = new Date(now.getFullYear(), 0, 1);
				break;
			default:
				from.setDate(from.getDate() - (parseInt(p, 10) || 28) + 1);
		}

		return [ymd(from), ymd(to)];
	}

	function showDates() {
		var input = document.querySelector('[data-hoan-dates]');
		var f = state.from.split('-').reverse().join('/');
		var tt = state.to.split('-').reverse().join('/');

		input.value = state.from === state.to ? f : f + ' – ' + tt;
	}

	// ------------------------------------------------------------------------------------------- chrome

	function destroyCharts() {
		charts.forEach(function (c) { c.destroy(); });
		charts = [];
	}

	function chart(canvas, config) {
		var c = new Chart(canvas, config);

		charts.push(c);

		return c;
	}

	function card(title, inner, opts) {
		opts = opts || {};

		return '<section class="hoan-card' + (opts.cls ? ' ' + opts.cls : '') + '"' + (opts.id ? ' id="' + opts.id + '"' : '') + '>' +
			'<div class="hoan-card__head"><h2 class="hoan-card__title">' + esc(title) + '</h2>' + (opts.tools || '') + '</div>' + inner +
			(opts.note ? '<p class="hoan-card__note">' + esc(opts.note) + '</p>' : '') + '</section>';
	}

	function canvas(id, size) {
		return '<div class="hoan-canvas' + (size ? ' hoan-canvas--' + size : '') + '"><canvas id="' + id + '"></canvas></div>';
	}

	function exportLink(table) {
		var q = new URLSearchParams({ table: table, from: state.from, to: state.to, device: state.device, channel: state.channel, store: state.store });

		return '<a class="hoan-chip" href="' + esc(C.export + '&' + q.toString()) + '">' + esc(t('text_export')) + '</a>';
	}

	function empty() {
		return '<div class="hoan-empty"><strong>' + esc(t('text_no_data')) + '</strong>' + esc(t('text_no_data_hint')) + '</div>';
	}

	function delta(cur, prev, lowerIsBetter) {
		if (prev === undefined || prev === null) {
			return '';
		}

		if (!prev) {
			// nothing to compare with (a «new» on every tile would read as noise)
			return '<span class="hoan-kpi__delta is-flat" title="' + esc(t('text_previous')) + '">–</span>';
		}

		var d = (cur - prev) / Math.abs(prev) * 100;
		var good = lowerIsBetter ? d < 0 : d > 0;
		var cls = Math.abs(d) < .5 ? 'is-flat' : (good ? 'is-up' : 'is-down');

		return '<span class="hoan-kpi__delta ' + cls + '" title="' + esc(t('text_previous')) + '">' + (d >= 0 ? '▲ ' : '▼ ') + nf1.format(Math.abs(d)) + '%</span>';
	}

	// ------------------------------------------------------------------------------------------- shared pieces

	// clickable: the tile picks the big chart's line (overview); elsewhere a plain tile that does not look pressable
	function kpis(keys, data, prev, clickable) {
		var html = '<div class="hoan-grid hoan-grid--kpi">';

		keys.forEach(function (k) {
			var m = METRICS[k];

			html += (clickable ? '<button type="button" class="hoan-kpi' + (state.metric === k ? ' is-active' : '') + '" data-hoan-metric="' + k + '">' : '<div class="hoan-kpi is-static">') +
				'<span class="hoan-kpi__label">' + esc(t(m.label)) + '</span>' +
				'<span class="hoan-kpi__value">' + esc(m.fmt(data[k] || 0)) + '</span>' +
				(prev ? delta(data[k] || 0, prev[k] || 0, m.lowerIsBetter) : '') +
				(SERIES_OF[k] ? '<span class="hoan-kpi__spark"><canvas data-hoan-spark="' + k + '"></canvas></span>' : '') +
				(clickable ? '</button>' : '</div>');
		});

		return html + '</div>';
	}

	function sparks(series) {
		var values = Object.keys(series).map(function (k) { return series[k]; });

		document.querySelectorAll('[data-hoan-spark]').forEach(function (cv) {
			var key = SERIES_OF[cv.getAttribute('data-hoan-spark')];

			chart(cv, {
				type: 'line',
				data: { labels: values.map(function (_, i) { return i; }), datasets: [{ data: values.map(function (v) { return v[key] || 0; }), borderColor: '#3451e6', borderWidth: 1.5, pointRadius: 0, tension: .35, fill: true, backgroundColor: 'rgba(52,81,230,.08)' }] },
				options: { animation: false, plugins: { tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false, beginAtZero: true } }, events: [] }
			});
		});
	}

	function bucketLabel(key, unit) {
		if (unit === 'hour') {
			return key.slice(11, 16);
		}

		if (unit === 'day') {
			var p = key.split('-');

			return p[2] + '/' + p[1];
		}

		return key;
	}

	function trendChart(id, data, metric) {
		var keys = Object.keys(data.series);
		var unit = data.range.unit;
		var cur = keys.map(function (k) { return data.series[k][SERIES_OF[metric] || metric] || 0; });
		var sets = [{ label: t('text_current'), data: cur, borderColor: '#3451e6', backgroundColor: 'rgba(52,81,230,.10)', fill: true, borderWidth: 2, tension: .3, pointRadius: keys.length > 40 ? 0 : 2.5, pointHoverRadius: 5 }];

		if (data.previous && data.previous.series) {
			sets.push({ label: t('text_previous'), data: data.previous.series.map(function (v) { return v[SERIES_OF[metric] || metric] || 0; }), borderColor: '#a3abbb', borderDash: [5, 4], borderWidth: 1.5, tension: .3, pointRadius: 0, fill: false });
		}

		var fmt = METRICS[metric] ? METRICS[metric].fmt : nf.format;

		if (metric === 'revenue' || metric === 'aov') {
			fmt = money.format;
		}

		chart(document.getElementById(id), {
			type: 'line',
			data: { labels: keys.map(function (k) { return bucketLabel(k, unit); }), datasets: sets },
			options: {
				interaction: { mode: 'index', intersect: false },
				plugins: { legend: { display: sets.length > 1, position: 'bottom', labels: { boxWidth: 12, boxHeight: 2 } }, tooltip: { callbacks: { label: function (c) { return ' ' + c.dataset.label + ': ' + fmt(c.parsed.y); } } } },
				scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 10, maxRotation: 0 } }, y: { beginAtZero: true, ticks: { callback: function (v) { return fmt(v); }, maxTicksLimit: 6 } } }
			}
		});
	}

	function donut(id, rows, valueKey, colorFor, nameFor) {
		var total = rows.reduce(function (s, r) { return s + (+r[valueKey] || 0); }, 0);
		var colors = rows.map(function (r, i) { return colorFor ? colorFor(r.name, i) : PALETTE[i % PALETTE.length]; });

		chart(document.getElementById(id), {
			type: 'doughnut',
			data: { labels: rows.map(function (r) { return nameFor ? nameFor(r.name) : r.name; }), datasets: [{ data: rows.map(function (r) { return +r[valueKey] || 0; }), backgroundColor: colors, borderWidth: 2, borderColor: '#fff', hoverOffset: 4 }] },
			options: { cutout: '68%', plugins: { tooltip: { callbacks: { label: function (c) { return ' ' + c.label + ': ' + nf.format(c.parsed) + ' (' + (total ? nf1.format(c.parsed / total * 100) : 0) + '%)'; } } } } }
		});

		var html = '<div class="hoan-legend">';

		rows.forEach(function (r, i) {
			var v = +r[valueKey] || 0;

			html += '<div class="hoan-legend__row"><span class="hoan-legend__dot" style="background:' + colors[i] + '"></span><span class="hoan-legend__name">' + esc(nameFor ? nameFor(r.name) : r.name) + '</span><span class="hoan-legend__value">' + esc(valueKey === 'revenue' ? money.format(v) : nf.format(v)) + '</span><span class="hoan-legend__share">' + (total ? nf1.format(v / total * 100) : 0) + '%</span></div>';
		});

		return html + '</div>';
	}

	function bars(id, labels, values, opts) {
		opts = opts || {};

		chart(document.getElementById(id), {
			type: 'bar',
			data: { labels: labels, datasets: [{ data: values, backgroundColor: opts.colors || '#3451e6', borderRadius: 4, maxBarThickness: 28 }] },
			options: {
				indexAxis: opts.horizontal ? 'y' : 'x',
				plugins: { tooltip: { callbacks: { label: function (c) { return ' ' + (opts.fmt || nf.format)(opts.horizontal ? c.parsed.x : c.parsed.y); } } } },
				scales: {
					x: { grid: { display: !!opts.horizontal }, beginAtZero: true, ticks: opts.horizontal ? { callback: function (v) { return (opts.fmt || nf.format)(v); }, maxTicksLimit: 5 } : { maxRotation: 0 } },
					y: { grid: { display: !opts.horizontal }, beginAtZero: true, ticks: opts.horizontal ? {} : { callback: function (v) { return (opts.fmt || nf.format)(v); }, maxTicksLimit: 6 } }
				}
			}
		});
	}

	function funnel(f) {
		var steps = [['sessions', 'text_funnel_sessions'], ['product', 'text_funnel_product'], ['cart', 'text_funnel_cart'], ['checkout', 'text_funnel_checkout'], ['purchase', 'text_funnel_purchase']];
		var top = f.sessions || 0;

		if (!top) {
			return empty();
		}

		var html = '<div class="hoan-funnel">';
		var prev = top;

		steps.forEach(function (s, i) {
			var v = f[s[0]] || 0;
			var w = top ? Math.max(v / top * 100, v ? 3 : 0) : 0;
			var step = i ? (prev ? v / prev * 100 : 0) : 100;

			html += '<div class="hoan-funnel__row"><span class="hoan-funnel__label">' + esc(t(s[1])) + '</span><span class="hoan-funnel__track"><span class="hoan-funnel__fill" style="width:' + w + '%;background:' + PALETTE[i] + '"></span></span><span class="hoan-funnel__rate"><b>' + nf.format(v) + '</b>' + (i ? ' · ' + nf1.format(step) + '%' : '') + '</span></div>';
			prev = v;
		});

		return html + '</div>';
	}

	function heatmap(h) {
		var days = t('text_day_names', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun').split(',');
		var max = 0;

		Object.keys(h || {}).forEach(function (d) {
			Object.keys(h[d]).forEach(function (hr) { max = Math.max(max, h[d][hr]); });
		});

		if (!max) {
			return empty();
		}

		var html = '<div class="hoan-heat"><span class="hoan-heat__label"></span>';

		for (var hr = 0; hr < 24; hr++) {
			html += '<span class="hoan-heat__hour">' + (hr % 3 === 0 ? hr : '') + '</span>';
		}

		for (var d = 1; d <= 7; d++) {
			html += '<span class="hoan-heat__label">' + esc(days[d - 1]) + '</span>';

			for (var hh = 0; hh < 24; hh++) {
				var v = h[d] && h[d][hh] ? h[d][hh] : 0;
				// square-root scale: quieter hours still show
				var a = v ? .1 + Math.sqrt(v / max) * .9 : 0;

				html += '<span title="' + esc(days[d - 1] + ' ' + hh + ':00 · ' + nf.format(v)) + '" style="background:' + (v ? 'rgba(52,81,230,' + a.toFixed(2) + ')' : '') + '"></span>';
			}
		}

		return html + '</div>';
	}

	// rows: array of objects; cols: [[key, header, fmt, bar?]]; first col may be a function returning html
	function table(rows, cols, opts) {
		opts = opts || {};

		if (!rows || !rows.length) {
			return empty();
		}

		var max = {};

		cols.forEach(function (c) {
			if (c[3]) {
				max[c[0]] = Math.max.apply(null, rows.map(function (r) { return +r[c[0]] || 0; })) || 1;
			}
		});

		var html = '<div class="hoan-scroll"><table class="hoan-table"><thead><tr>';

		cols.forEach(function (c) { html += '<th>' + esc(t(c[1], c[1])) + '</th>'; });
		html += '</tr></thead><tbody>';

		rows.slice(0, opts.limit || 500).forEach(function (r) {
			html += '<tr>';

			cols.forEach(function (c, i) {
				var v = typeof c[0] === 'function' ? c[0](r) : r[c[0]];
				var out = i === 0 && typeof c[0] === 'function' ? v : esc(c[2] ? c[2](v, r) : v);

				if (c[3]) {
					html += '<td class="hoan-bar-cell" style="--w:' + ((+r[c[0]] || 0) / max[c[0]] * 100).toFixed(1) + '%"><span>' + out + '</span></td>';
				} else {
					html += '<td>' + out + '</td>';
				}
			});

			html += '</tr>';
		});

		return html + '</tbody></table></div>';
	}

	function pageName(r) {
		var type = label('type', r.page_type);
		// pages without a name of their own (cart, checkout, search ...): the kind of page
		var name = r.name || (['product', 'category', 'info'].indexOf(r.page_type) < 0 ? type : '');

		return '<span class="hoan-table__name">' + esc(name || r.path || '/') + '</span><span class="hoan-table__sub">' + esc((name ? type + ' · ' : '') + (r.path || '/')) + '</span>';
	}

	var pct = function (v) { return nf1.format(v || 0) + '%'; };
	var num = function (v) { return nf.format(v || 0); };
	var cur = function (v) { return money2.format(v || 0); };

	// ------------------------------------------------------------------------------------------- tabs

	var VIEWS = {
		overview: function (d) {
			var keys = ['users', 'sessions', 'pageviews', 'bounce_rate', 'duration', 'conversion_rate', 'revenue', 'orders', 'aov'];
			var prev = d.previous ? d.previous.totals : null;

			// the big chart follows a headline number that has a line over time
			if (keys.indexOf(state.metric) < 0 || !SERIES_OF[state.metric]) {
				state.metric = 'users';
			}

			var html = kpis(keys, d.totals, prev, true);

			html += '<div class="hoan-grid hoan-grid--2">' +
				card(t(METRICS[state.metric].label) + ' · ' + t('text_trend'), canvas('hoan-trend')) +
				card(t('text_channels'), d.channels.length ? canvas('hoan-channels', 'xs') + '<div data-hoan-legend="channels"></div>' : empty()) + '</div>';

			html += '<div class="hoan-grid hoan-grid--3">' +
				card(t('text_funnel'), funnel(d.funnel)) +
				card(t('text_devices'), d.devices.length ? canvas('hoan-devices', 'xs') + '<div data-hoan-legend="devices"></div>' : empty()) +
				card(t('text_heatmap'), heatmap(d.heatmap)) + '</div>';

			html += '<div class="hoan-grid hoan-grid--half">' +
				card(t('text_top_pages'), table(d.pages, [[pageName, 'text_page'], ['views', 'text_views', num, true], ['users', 'text_users', num]])) +
				card(t('text_top_products'), table(d.products, [[function (r) { return '<span class="hoan-table__name">' + esc(r.name) + '</span>'; }, 'text_product'], ['views', 'text_views', num], ['carts', 'text_carts', num], ['sold', 'text_sold', num], ['revenue', 'text_revenue', cur, true]])) + '</div>';

			body.innerHTML = html;

			trendChart('hoan-trend', d, state.metric);
			sparks(d.series);

			if (d.channels.length) {
				document.querySelector('[data-hoan-legend="channels"]').innerHTML = donut('hoan-channels', d.channels, 'sessions', function (n) { return CHANNEL_COLOR[n] || '#8a93a6'; }, function (n) { return label('channel', n); });
			}

			if (d.devices.length) {
				document.querySelector('[data-hoan-legend="devices"]').innerHTML = donut('hoan-devices', d.devices, 'sessions', function (n) { return DEVICE_COLOR[n] || '#8a93a6'; }, function (n) { return label('device', n); });
			}
		},

		acquisition: function (d) {
			var cols = [['sessions', 'text_sessions', num, true], ['users', 'text_users', num], ['bounce_rate', 'text_bounce_rate', pct], ['pages_per_session', 'text_pages_per_session', function (v) { return nf1.format(v); }], ['carts', 'text_carts', num], ['purchases', 'text_purchases', num], ['conversion_rate', 'text_conversion_rate', pct], ['revenue', 'text_revenue', cur]];
			var named = function (fn) { return [function (r) { return '<span class="hoan-table__name">' + esc(fn ? fn(r.name) : r.name) + '</span>'; }, 'text_name']; };

			var html = '<div class="hoan-grid hoan-grid--2">' +
				card(t('text_channels') + ' · ' + t('text_sessions'), d.channels.length ? canvas('hoan-ch-bars') : empty()) +
				card(t('text_channels') + ' · ' + t('text_revenue'), d.channels.some(function (r) { return r.revenue > 0; }) ? canvas('hoan-ch-rev', 'xs') + '<div data-hoan-legend="rev"></div>' : empty()) + '</div>';

			html += card(t('text_channels'), table(d.channels, [named(function (n) { return label('channel', n); })].concat(cols)), { tools: exportLink('channels') });
			html += '<div class="hoan-grid"></div>' + card(t('text_sources'), table(d.sources, [named()].concat(cols)), { tools: exportLink('sources') });
			html += '<div class="hoan-grid"></div><div class="hoan-grid hoan-grid--half">' +
				card(t('text_campaigns'), table(d.campaigns, [named(), cols[0], cols[5], cols[6], cols[7]]), { tools: exportLink('campaigns') }) +
				card(t('text_referrers'), table(d.referrers, [named(), cols[0], cols[1], cols[2], cols[7]]), { tools: exportLink('referrers') }) + '</div>';

			body.innerHTML = html;

			if (d.channels.length) {
				bars('hoan-ch-bars', d.channels.map(function (r) { return label('channel', r.name); }), d.channels.map(function (r) { return r.sessions; }), { horizontal: true, colors: d.channels.map(function (r) { return CHANNEL_COLOR[r.name] || '#8a93a6'; }) });
			}

			var rev = d.channels.filter(function (r) { return r.revenue > 0; });

			if (rev.length) {
				document.querySelector('[data-hoan-legend="rev"]').innerHTML = donut('hoan-ch-rev', rev, 'revenue', function (n) { return CHANNEL_COLOR[n] || '#8a93a6'; }, function (n) { return label('channel', n); });
			}
		},

		behaviour: function (d) {
			var r = d.returning;
			var users = (r.new_users || 0) + (r.returning_users || 0);
			var html = '<div class="hoan-grid hoan-grid--half">' +
				card(t('text_funnel'), funnel(d.funnel)) +
				card(t('text_page_types'), d.types.length ? canvas('hoan-types', 'sm') : empty()) + '</div>';

			html += card(t('text_pages'), table(d.pages, [[pageName, 'text_page'], ['views', 'text_views', num, true], ['users', 'text_users', num], ['entrances', 'text_entrances', num]]), { tools: exportLink('pages') });
			html += '<div class="hoan-grid"></div><div class="hoan-grid hoan-grid--half">' +
				card(t('text_landings'), table(d.landings, [[pageName, 'text_page'], ['sessions', 'text_sessions', num, true], ['bounce_rate', 'text_bounce_rate', pct], ['conversion_rate', 'text_conversion_rate', pct], ['revenue', 'text_revenue', cur]]), { tools: exportLink('landings') }) +
				card(t('text_site_search'), table(d.searches, [[function (x) { return '<span class="hoan-table__name">' + esc(x.term) + '</span>'; }, 'text_term'], ['searches', 'text_searches', num, true], ['users', 'text_users', num]]), { tools: exportLink('searches') }) + '</div>';

			html += '<div class="hoan-grid hoan-grid--3">' +
				card(t('text_devices'), d.audience.device.length ? canvas('hoan-dev', 'xs') + '<div data-hoan-legend="dev"></div>' : empty()) +
				card(t('text_new_returning'), users ? canvas('hoan-ret', 'xs') + '<div data-hoan-legend="ret"></div>' : empty(), { note: t('text_consent_note').replace('%s', nf.format(r.consented || 0)) }) +
				card(t('text_heatmap'), heatmap(d.heatmap)) + '</div>';

			html += '<div class="hoan-grid hoan-grid--3">' +
				card(t('text_browsers'), d.audience.browser.length ? canvas('hoan-br', 'sm') : empty()) +
				card(t('text_os'), d.audience.os.length ? canvas('hoan-os', 'sm') : empty()) +
				card(t('text_languages') + (d.audience.country.length ? ' · ' + t('text_countries') : ''), d.audience.language.length ? canvas('hoan-lang', 'sm') : empty()) + '</div>';

			body.innerHTML = html;

			if (d.types.length) {
				bars('hoan-types', d.types.map(function (x) { return label('type', x.name); }), d.types.map(function (x) { return +x.views; }), { horizontal: true });
			}

			if (d.audience.device.length) {
				document.querySelector('[data-hoan-legend="dev"]').innerHTML = donut('hoan-dev', d.audience.device, 'users', function (n) { return DEVICE_COLOR[n] || '#8a93a6'; }, function (n) { return label('device', n); });
			}

			if (users) {
				document.querySelector('[data-hoan-legend="ret"]').innerHTML = donut('hoan-ret', [{ name: t('text_new_users'), users: r.new_users }, { name: t('text_returning'), users: r.returning_users }], 'users');
			}

			[['hoan-br', 'browser'], ['hoan-os', 'os']].forEach(function (p) {
				if (d.audience[p[1]].length) {
					bars(p[0], d.audience[p[1]].map(function (x) { return x.name; }), d.audience[p[1]].map(function (x) { return +x.users; }), { horizontal: true, colors: PALETTE });
				}
			});

			var lang = d.audience.country.length ? d.audience.country : d.audience.language;

			if (lang.length) {
				bars('hoan-lang', lang.map(function (x) { return String(x.name).toUpperCase(); }), lang.map(function (x) { return +x.users; }), { horizontal: true, colors: PALETTE });
			}
		},

		products: function (d) {
			var html = card(t('text_top_products'), d.products.length ? canvas('hoan-bubble') : empty());

			html += '<div class="hoan-grid"></div>' + card(t('text_top_products'), table(d.products, [[function (r) { return '<span class="hoan-table__name">' + esc(r.name) + '</span><span class="hoan-table__sub">#' + r.product_id + '</span>'; }, 'text_product'], ['views', 'text_views', num, true], ['viewers', 'text_users', num], ['carts', 'text_carts', num], ['cart_rate', 'text_cart_rate', pct], ['sold', 'text_sold', num], ['revenue', 'text_revenue', cur, true]]), { tools: exportLink('products') });

			body.innerHTML = html;

			if (d.products.length) {
				var maxSold = Math.max.apply(null, d.products.map(function (p) { return p.sold; })) || 1;

				chart(document.getElementById('hoan-bubble'), {
					type: 'bubble',
					data: { datasets: [{ data: d.products.map(function (p) { return { x: p.views, y: p.revenue, r: 4 + p.sold / maxSold * 18, name: p.name, sold: p.sold, carts: p.carts }; }), backgroundColor: 'rgba(52,81,230,.35)', borderColor: '#3451e6', borderWidth: 1 }] },
					options: {
						plugins: { tooltip: { callbacks: { label: function (c) { var p = c.raw; return [' ' + p.name, ' ' + t('text_views') + ': ' + nf.format(p.x) + ' · ' + t('text_carts') + ': ' + nf.format(p.carts), ' ' + t('text_sold') + ': ' + nf.format(p.sold) + ' · ' + money.format(p.y)]; } } } },
						scales: { x: { beginAtZero: true, title: { display: true, text: t('text_views') } }, y: { beginAtZero: true, title: { display: true, text: t('text_revenue') }, ticks: { callback: function (v) { return money.format(v); } } } }
					}
				});
			}
		},

		sales: function (d) {
			var keys = ['revenue', 'orders', 'aov', 'items', 'buyers', 'conversion_rate'];
			var prev = d.previous ? d.previous.totals : null;
			var html = kpis(keys, d.totals, prev, false);

			html += '<div class="hoan-grid hoan-grid--2">' + card(t('text_sales_trend'), canvas('hoan-sales')) + card(t('text_by_customer'), d.customers.length ? canvas('hoan-cust', 'xs') + '<div data-hoan-legend="cust"></div>' : empty()) + '</div>';
			html += '<div class="hoan-grid hoan-grid--3">' +
				card(t('text_by_category'), d.categories.length ? canvas('hoan-cat', 'xs') + '<div data-hoan-legend="cat"></div>' : empty()) +
				card(t('text_by_payment'), d.payment.length ? canvas('hoan-pay', 'xs') + '<div data-hoan-legend="pay"></div>' : empty()) +
				card(t('text_by_shipping'), d.shipping.length ? canvas('hoan-ship', 'xs') + '<div data-hoan-legend="ship"></div>' : empty()) + '</div>';
			html += '<div class="hoan-grid hoan-grid--2">' + card(t('text_by_hour'), d.hours.length ? canvas('hoan-hours', 'sm') : empty()) + card(t('text_by_source'), d.sources.length ? canvas('hoan-src', 'xs') + '<div data-hoan-legend="src"></div>' : empty()) + '</div>';
			html += '<div class="hoan-grid hoan-grid--half">' +
				card(t('text_top_products'), table(d.products.filter(function (p) { return p.sold > 0; }), [[function (r) { return '<span class="hoan-table__name">' + esc(r.name) + '</span>'; }, 'text_product'], ['sold', 'text_sold', num], ['revenue', 'text_revenue', cur, true]])) +
				card(t('text_discounts'), table(d.coupons, [[function (r) { return '<span class="hoan-table__name">' + esc(r.name) + '</span>'; }, 'text_name'], ['orders', 'text_orders', num], ['revenue', 'text_revenue', cur, true]])) + '</div>';

			body.innerHTML = html;
			sparks(d.series);

			var keysS = Object.keys(d.series);
			var unit = d.range.unit;
			var sets = [
				{ type: 'bar', label: t('text_revenue'), data: keysS.map(function (k) { return d.series[k].revenue || 0; }), backgroundColor: 'rgba(52,81,230,.75)', borderRadius: 3, yAxisID: 'y', maxBarThickness: 26, order: 2 },
				{ type: 'line', label: t('text_orders'), data: keysS.map(function (k) { return d.series[k].orders || 0; }), borderColor: '#0e9f8e', backgroundColor: '#0e9f8e', borderWidth: 2, tension: .3, pointRadius: keysS.length > 40 ? 0 : 2.5, yAxisID: 'y1', order: 1 }
			];

			if (d.previous) {
				sets.push({ type: 'line', label: t('text_previous') + ' · ' + t('text_revenue'), data: d.previous.series.map(function (v) { return v.revenue || 0; }), borderColor: '#a3abbb', borderDash: [5, 4], borderWidth: 1.5, pointRadius: 0, yAxisID: 'y', order: 0 });
			}

			chart(document.getElementById('hoan-sales'), {
				data: { labels: keysS.map(function (k) { return bucketLabel(k, unit); }), datasets: sets },
				options: {
					interaction: { mode: 'index', intersect: false },
					plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 12, boxHeight: 8 } }, tooltip: { callbacks: { label: function (c) { return ' ' + c.dataset.label + ': ' + (c.dataset.yAxisID === 'y' ? money.format(c.parsed.y) : nf.format(c.parsed.y)); } } } },
					scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 12, maxRotation: 0 } }, y: { beginAtZero: true, ticks: { callback: function (v) { return money.format(v); }, maxTicksLimit: 6 } }, y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { precision: 0, maxTicksLimit: 6 } } }
				}
			});

			[['cust', 'hoan-cust', d.customers, function (n) { return t('text_customer_' + n, n); }], ['cat', 'hoan-cat', d.categories], ['pay', 'hoan-pay', d.payment], ['ship', 'hoan-ship', d.shipping], ['src', 'hoan-src', d.sources, function (n) { return label('channel', n); }]].forEach(function (p) {
				if (p[2].length) {
					document.querySelector('[data-hoan-legend="' + p[0] + '"]').innerHTML = donut(p[1], p[2], 'revenue', p[0] === 'src' ? function (n) { return CHANNEL_COLOR[n] || '#8a93a6'; } : null, p[3]);
				}
			});

			if (d.hours.length) {
				var byHour = [];

				for (var h = 0; h < 24; h++) {
					var row = d.hours.filter(function (x) { return +x.name === h; })[0];

					byHour.push(row ? row.orders : 0);
				}

				bars('hoan-hours', byHour.map(function (_, i) { return i + ':00'; }), byHour, {});
			}
		},

		realtime: function (d) {
			var html = '<div class="hoan-grid hoan-grid--3">' +
				card(t('text_active_now'), '<div class="hoan-big">' + nf.format(d.active || 0) + '</div><p class="hoan-card__note">' + esc(t('text_active_30')) + ': ' + nf.format(d.active_30 || 0) + '</p>') +
				card(t('text_channels'), d.channels && d.channels.length ? canvas('hoan-rt-ch', 'xs') + '<div data-hoan-legend="rtch"></div>' : empty()) +
				card(t('text_devices'), d.devices && d.devices.length ? canvas('hoan-rt-dev', 'xs') + '<div data-hoan-legend="rtdev"></div>' : empty()) + '</div>';

			html += card(t('text_per_minute'), canvas('hoan-rt-min', 'sm'));
			html += '<div class="hoan-grid"></div><div class="hoan-grid hoan-grid--half">' +
				card(t('text_live_pages'), table(d.pages, [[pageName, 'text_page'], ['views', 'text_views', num, true]])) +
				card(t('text_live_events'), d.events && d.events.length ? '<ul class="hoan-feed">' + d.events.map(function (e) {
					// minutes by the server's clock (the browser's may differ)
					var mins = Math.max(0, +e.ago || 0);

					return '<li><span><b>' + esc(t('text_event_' + e.type, e.type)) + '</b> · ' + esc(label('device', e.device)) + ' · ' + esc(label('channel', e.channel)) + (e.order_id > 0 ? ' · #' + e.order_id : '') + '</span><span>' + (+e.value ? esc(money2.format(e.value)) + ' ' : '') + '<small>' + esc(t('text_ago').replace('%s', mins)) + '</small></span></li>';
				}).join('') + '</ul>' : empty()) + '</div>';

			body.innerHTML = html;

			bars('hoan-rt-min', d.per_minute.map(function (_, i) { return '-' + (29 - i) + "'"; }), d.per_minute, { colors: '#0e9f8e' });

			if (d.channels && d.channels.length) {
				document.querySelector('[data-hoan-legend="rtch"]').innerHTML = donut('hoan-rt-ch', d.channels, 'users', function (n) { return CHANNEL_COLOR[n] || '#8a93a6'; }, function (n) { return label('channel', n); });
			}

			if (d.devices && d.devices.length) {
				document.querySelector('[data-hoan-legend="rtdev"]').innerHTML = donut('hoan-rt-dev', d.devices, 'users', function (n) { return DEVICE_COLOR[n] || '#8a93a6'; }, function (n) { return label('device', n); });
			}
		}
	};

	// ------------------------------------------------------------------------------------------- loading

	function save() {
		var q = new URLSearchParams({ tab: state.tab, preset: state.preset, from: state.from, to: state.to, compare: state.compare ? 1 : 0, device: state.device, channel: state.channel, store: state.store, metric: state.metric });

		// the full address: a bare '#…' resolves against the admin's <base href> and drops route + user_token
		history.replaceState(null, '', location.pathname + location.search + '#' + q.toString());
	}

	function load() {
		var id = ++request;

		clearInterval(liveTimer);
		body.classList.add('is-loading');
		save();

		document.querySelectorAll('[data-hoan-tab]').forEach(function (b) {
			var on = b.getAttribute('data-hoan-tab') === state.tab;

			b.classList.toggle('is-active', on);
			b.setAttribute('aria-selected', on ? 'true' : 'false');
		});

		var url = state.tab === 'realtime' ? C.realtime + '&store=' + encodeURIComponent(state.store) : C.data + '&' + new URLSearchParams({ tab: state.tab, from: state.from, to: state.to, compare: state.compare ? 1 : 0, device: state.device, channel: state.channel, store: state.store }).toString();

		return fetch(url, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (d) {
			if (id !== request) {
				return;
			}

			destroyCharts();
			body.classList.remove('is-loading');

			if (d.error) {
				body.innerHTML = empty();

				return;
			}

			VIEWS[state.tab](d);

			if (state.tab === 'realtime') {
				live(d.active);
				liveTimer = setInterval(load, 15000);
			}
		}).catch(function () {
			body.classList.remove('is-loading');
		});
	}

	function live(n) {
		var el = document.querySelector('[data-hoan-live]');

		if (el) {
			el.textContent = nf.format(n || 0);
		}
	}

	function pollLive() {
		fetch(C.realtime, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (d) { live(d.active); }).catch(function () {});
	}

	// ------------------------------------------------------------------------------------------- controls

	var preset = document.querySelector('[data-hoan-preset]');
	var dates = document.querySelector('[data-hoan-dates]');

	// what the address remembers (a reload keeps the view)
	if (location.hash.length > 1) {
		var q = new URLSearchParams(location.hash.slice(1));

		['tab', 'preset', 'from', 'to', 'device', 'channel', 'store', 'metric'].forEach(function (k) {
			if (q.get(k) !== null) {
				state[k] = q.get(k);
			}
		});

		state.compare = q.get('compare') !== '0';

		if (!VIEWS[state.tab]) {
			state.tab = 'overview';
		}
	}

	if (state.preset !== 'custom' || !state.from) {
		var r = presetRange(state.preset);

		state.from = r[0];
		state.to = r[1];
	}

	preset.value = state.preset;
	document.querySelector('[data-hoan-device]').value = state.device;
	document.querySelector('[data-hoan-channel]').value = state.channel;

	// dekor: store filter (only shown with more than one store)
	var storeSel = document.querySelector('[data-hoan-store]');

	if (storeSel) {
		storeSel.value = state.store;
		storeSel.addEventListener('change', function () { state.store = this.value; load(); });
	} else {
		state.store = '';
	}
	document.querySelector('[data-hoan-compare]').checked = state.compare;
	showDates();

	preset.addEventListener('change', function () {
		state.preset = preset.value;

		if (state.preset === 'custom') {
			if (window.jQuery && jQuery.fn.daterangepicker) {
				jQuery(dates).trigger('click');
			}

			return;
		}

		var r = presetRange(state.preset);

		state.from = r[0];
		state.to = r[1];
		showDates();
		load();
	});

	if (window.jQuery && jQuery.fn.daterangepicker && window.moment) {
		jQuery(dates).daterangepicker({ startDate: moment(state.from), endDate: moment(state.to), maxDate: moment(), opens: 'left', autoApply: true, locale: { format: 'DD/MM/YYYY', firstDay: 1 } }, function (start, end) {
			state.preset = 'custom';
			preset.value = 'custom';
			state.from = start.format('YYYY-MM-DD');
			state.to = end.format('YYYY-MM-DD');
			showDates();
			load();
		});
	}

	document.querySelector('[data-hoan-device]').addEventListener('change', function () { state.device = this.value; load(); });
	document.querySelector('[data-hoan-channel]').addEventListener('change', function () { state.channel = this.value; load(); });
	document.querySelector('[data-hoan-compare]').addEventListener('change', function () { state.compare = this.checked; load(); });

	document.addEventListener('click', function (e) {
		var tab = e.target.closest('[data-hoan-tab], [data-hoan-tab-link]');

		if (tab) {
			e.preventDefault();
			state.tab = tab.getAttribute('data-hoan-tab') || tab.getAttribute('data-hoan-tab-link');
			load();

			return;
		}

		var kpi = e.target.closest('[data-hoan-metric]');

		if (kpi && state.tab === 'overview' && SERIES_OF[kpi.getAttribute('data-hoan-metric')]) {
			state.metric = kpi.getAttribute('data-hoan-metric');
			load();
		}
	});

	load();
	pollLive();
	setInterval(pollLive, 60000);
});
