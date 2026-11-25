/*
 * HappyOnline shopping assistant — the chat widget (bottom right). Settings and texts: window.HO_ASSISTANT (the
 * extension/ho_assistant/boot script). Everything is built here, nothing in the page templates, so it works the same
 * on the store theme and on kit pages. Server: catalog/controller/extension/ho_assistant.php.
 * Loaded in <head>: it starts once the page is there.
 */
(function start() {
	'use strict';

	if (!document.body) {
		document.addEventListener('DOMContentLoaded', start);

		return;
	}

	var C = window.HO_ASSISTANT;

	if (!C || document.querySelector('.hoa')) {
		return;
	}

	var T = C.text || {};
	var KEY = 'hoa-open';
	var busy = false;
	var loaded = false;

	// ------------------------------------------------------------------------------------------- helpers

	function esc(s) {
		return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function t(k) {
		return T[k] !== undefined ? T[k] : k;
	}

	function el(html) {
		var d = document.createElement('div');

		d.innerHTML = html.trim();

		return d.firstChild;
	}

	function post(url, data) {
		var body = new URLSearchParams();

		Object.keys(data || {}).forEach(function (k) { body.append(k, data[k]); });

		return fetch(url, { method: 'POST', body: body, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.json(); });
	}

	// a small, safe markdown: **bold**, *italic*, lists, line breaks, links to this shop only
	function md(text) {
		var out = [];
		var list = null;

		esc(text).split(/\n/).forEach(function (line) {
			var m = line.match(/^\s*(?:[-*•]|(\d+)[.)])\s+(.*)$/);

			if (m) {
				var type = m[1] ? 'ol' : 'ul';

				if (list !== type) {
					if (list) {
						out.push('</' + list + '>');
					}

					out.push('<' + type + '>');
					list = type;
				}

				out.push('<li>' + inline(m[2]) + '</li>');

				return;
			}

			if (list) {
				out.push('</' + list + '>');
				list = null;
			}

			if (line.trim()) {
				out.push('<p>' + inline(line) + '</p>');
			}
		});

		if (list) {
			out.push('</' + list + '>');
		}

		return out.join('');
	}

	function inline(s) {
		return s
			.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
			.replace(/(^|[^*])\*([^*\s][^*]*?)\*(?!\*)/g, '$1<em>$2</em>')
			.replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, function (all, label, url) {
				var u = url.replace(/&amp;/g, '&');

				try {
					var parsed = new URL(u, location.href);

					if (parsed.host === location.host) {
						return '<a href="' + esc(parsed.href) + '">' + label + '</a>';
					}
				} catch (e) {}

				return label;
			});
	}

	var ICON = {
		box: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>',
		spark: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z"/></svg>',
		close: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>',
		reset: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>',
		send: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
		next: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>',
		back: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>',
		info: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 7.5h.01"/></svg>',
		check: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>',
		lock: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex:none;margin-top:1px"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>'
	};

	// ------------------------------------------------------------------------------------------- the widget

	var link = document.createElement('link');

	link.rel = 'stylesheet';
	link.href = C.css;
	document.head.appendChild(link);

	var root = el(
		'<div class="hoa" data-hoa>' +
			'<button type="button" class="hoa-launch" aria-haspopup="dialog" aria-expanded="false" aria-label="' + esc(t('text_launcher')) + '"><span class="hoa-launch__icon">' + ICON.spark + '</span><span class="hoa-launch__label">' + esc(t('text_launcher')) + '</span></button>' +
			'<section class="hoa-panel" role="dialog" aria-modal="false" aria-label="' + esc(C.name) + '">' +
				'<div class="hoa-head">' +
					'<span class="hoa-avatar">' + ICON.spark + '</span>' +
					'<span class="hoa-title"><strong><span class="hoa-title__name">' + esc(C.name) + '</span><span class="hoa-badge">' + esc(t('text_ai_badge')) + '</span></strong><small>Online</small></span>' +
					'<button type="button" class="hoa-icon-btn" data-hoa-reset title="' + esc(t('text_new_chat')) + '" aria-label="' + esc(t('text_new_chat')) + '">' + ICON.reset + '</button>' +
					'<button type="button" class="hoa-icon-btn" data-hoa-close title="' + esc(t('text_close')) + '" aria-label="' + esc(t('text_close')) + '">' + ICON.close + '</button>' +
				'</div>' +
				'<div class="hoa-body"><div class="hoa-log" data-hoa-log aria-live="polite"></div><div class="hoa-sheet" data-hoa-sheet aria-hidden="true"></div></div>' +
				'<div class="hoa-foot">' +
					'<form class="hoa-compose" data-hoa-form><textarea rows="1" data-hoa-input placeholder="' + esc(t('text_placeholder')) + '" aria-label="' + esc(t('text_placeholder')) + '" maxlength="600"></textarea>' +
					'<button type="submit" class="hoa-send" aria-label="' + esc(t('text_send')) + '" disabled>' + ICON.send + '</button></form>' +
					'<p class="hoa-disclaimer">' + esc(t('text_disclaimer')) + '</p>' +
					'<button type="button" class="hoa-clear" data-hoa-reset>' + esc(t('text_clear')) + '</button>' +
				'</div>' +
			'</section>' +
		'</div>'
	);

	document.body.appendChild(root);

	var launch = root.querySelector('.hoa-launch');
	var log = root.querySelector('[data-hoa-log]');
	var sheet = root.querySelector('[data-hoa-sheet]');
	var form = root.querySelector('[data-hoa-form]');
	var input = root.querySelector('[data-hoa-input]');
	var sendBtn = root.querySelector('.hoa-send');

	// clear of the shop's own bottom bars (kit cart / checkout bars, phone navigation)
	function lift() {
		var bar = document.querySelector('.ho-cart__bar, .ho-co-mbar, .ho-bnav');
		var h = 0;

		if (bar && getComputedStyle(bar).display !== 'none') {
			var r = bar.getBoundingClientRect();

			if (r.height && r.top < innerHeight && r.bottom > innerHeight - 4) {
				h = r.height;
			}
		}

		root.style.setProperty('--hoa-bottom', (20 + h) + 'px');
	}

	lift();
	addEventListener('resize', lift);
	addEventListener('scroll', lift, { passive: true });

	// out of the way while a Bootstrap offcanvas / modal is open (the mini cart #offcanvasCart, store's or kit's):
	// it would cover the drawer's checkout button
	function cover(e) {
		var on = e.type.indexOf('show.') === 0 || !!document.querySelector('.offcanvas.show, .modal.show');

		root.classList.toggle('is-covered', on);
	}

	['show.bs.offcanvas', 'hidden.bs.offcanvas', 'show.bs.modal', 'hidden.bs.modal'].forEach(function (type) {
		document.addEventListener(type, cover);
	});

	function scrollDown() {
		requestAnimationFrame(function () { log.scrollTop = log.scrollHeight; });
	}

	function open() {
		root.classList.add('is-open');
		launch.setAttribute('aria-expanded', 'true');

		try { sessionStorage.setItem(KEY, '1'); } catch (e) {}

		if (!loaded) {
			loaded = true;
			history();
		}

		setTimeout(function () { input.focus({ preventScroll: true }); }, 250);
	}

	function close() {
		root.classList.remove('is-open');
		launch.setAttribute('aria-expanded', 'false');

		try { sessionStorage.removeItem(KEY); } catch (e) {}

		launch.focus({ preventScroll: true });
	}

	// ------------------------------------------------------------------------------------------- the conversation

	function welcome() {
		var chips = (C.suggestions || []).map(function (s) {
			return '<button type="button" class="hoa-chip" data-hoa-ask="' + esc(s) + '">' + esc(s) + '</button>';
		}).join('');

		log.appendChild(el('<div class="hoa-welcome"><div class="hoa-msg"><div class="hoa-bubble">' + md(C.greeting) + '</div></div>' + (chips ? '<div class="hoa-chips">' + chips + '</div>' : '') + '</div>'));
	}

	function history() {
		log.innerHTML = '';
		welcome();

		fetch(C.urls.history, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (d) {
			(d.log || []).forEach(function (m) { render(m, true); });
			scrollDown();
		}).catch(function () {});
	}

	function render(m, quiet) {
		if (m.role === 'user') {
			log.appendChild(el('<div class="hoa-msg hoa-msg--user"><div class="hoa-bubble">' + esc(m.text) + '</div></div>'));
		} else {
			if (m.text) {
				log.appendChild(el('<div class="hoa-msg"><div class="hoa-bubble">' + md(m.text) + '</div></div>'));
			}

			(m.ui || []).forEach(function (u) { ui(u, quiet); });
		}

		scrollDown();
	}

	function ui(u, quiet) {
		if (u.type === 'products' && u.items && u.items.length) {
			log.appendChild(cards(u.items));
		} else if (u.type === 'cart') {
			log.appendChild(cartCard(u));
			badge(u.count);
		} else if (u.type === 'checkout' && !quiet) {
			openCheckout();
		} else if (u.type === 'choices' && u.options && u.options.length) {
			// the values of the filter the assistant asks about: one click answers
			log.appendChild(el('<div class="hoa-chips hoa-chips--refine">' + u.options.map(function (o) {
				return '<button type="button" class="hoa-chip" data-hoa-ask="' + esc(o) + '">' + esc(o) + '</button>';
			}).join('') + '</div>'));
		} else if (u.type === 'order') {
			log.appendChild(doneCard(u));
		}
	}

	function cards(items) {
		var wrap = el('<div class="hoa-cards"></div>');

		items.forEach(function (p) {
			// dekor: «+ ΦΠΑ» on a wholesale store, the piece price under a package's price
			var vat = p.vat ? ' <small class="hoa-card__vat">' + esc(t('text_vat')) + '</small>' : '';
			// the price as before (package price «+ ΦΠΑ»); a package adds two small lines under thin rules:
			// «Τιμή τεμαχίου … 0,12€» and «Η συσκευασία περιέχει … 100 Τεμ.»
			var price = (p.special
					? '<span class="hoa-card__price is-sale">' + esc(p.special) + vat + ' <del>' + esc(p.price) + '</del></span>'
					: '<span class="hoa-card__price">' + esc(p.price) + vat + '</span>') +
				(p.package
					? '<span class="hoa-pack">' +
						'<span class="hoa-pack__row"><span>' + esc(t('text_unit_price')) + '</span><b>' + esc(p.unit) + '</b></span>' +
						'<span class="hoa-pack__row"><span>' + esc(t('text_package_short')) + '</span><b>' + esc(p.package) + ' ' + esc(t('text_pieces')) + '</b></span>' +
					'</span>'
					: '');
			var action = (!p.in_stock || p.can_add === false)
				? '<a class="hoa-btn hoa-btn--ghost" href="' + esc(p.href) + '">' + esc(t('text_view')) + '</a>'
				: (p.options
					? '<button type="button" class="hoa-btn hoa-btn--ghost" data-hoa-options="' + esc(p.name) + '" data-hoa-pid="' + p.product_id + '">' + esc(t('text_options')) + '</button>'
					: '<button type="button" class="hoa-btn" data-hoa-add="' + p.product_id + '">' + esc(t('text_add')) + '</button>');

			wrap.appendChild(el(
				'<article class="hoa-card' + (items.length === 1 ? ' hoa-card--single' : '') + '">' +
					'<a class="hoa-card__media" href="' + esc(p.href) + '" tabindex="-1"><img src="' + esc(p.thumb) + '" alt="" loading="lazy">' + (p.percent ? '<span class="hoa-card__off">-' + p.percent + '%</span>' : '') + '</a>' +
					'<div class="hoa-card__body">' +
						(p.brand ? '<span class="hoa-card__brand">' + esc(p.brand) + '</span>' : '') +
						'<a class="hoa-card__name" href="' + esc(p.href) + '">' + esc(p.name) + '</a>' +
						(p.code ? '<span class="hoa-card__code">' + esc(t('text_code')) + ' ' + esc(p.code) + '</span>' : '') +
						price +
						(p.store_only
							? '<span class="hoa-card__stock is-store">' + esc(t('text_store_only')) + '</span>'
							: '<span class="hoa-card__stock' + (p.in_stock ? '' : ' is-out') + '">' + esc(p.in_stock ? t('text_in_stock') : t('text_out_of_stock')) + '</span>') +
						(p.few ? '<span class="hoa-card__few">' + esc(p.few) + '</span>' : '') +
						// the card's buttons in one block: equal height, at the bottom of the card (in line across the row)
						'<div class="hoa-card__actions">' +
							(p.detail ? '' : '<button type="button" class="hoa-btn hoa-btn--info" data-hoa-info="' + p.product_id + '" data-hoa-name="' + esc(p.name) + '">' + ICON.info + esc(t('text_info')) + '</button>') +
							action +
						'</div>' +
					'</div>' +
				'</article>'
			));
		});

		return carousel(wrap);
	}

	// the card row scrolls sideways: arrows on a computer, drag with the mouse, swipe on a phone
	function carousel(row) {
		var box = el('<div class="hoa-carousel"></div>');
		var prev = el('<button type="button" class="hoa-arrow hoa-arrow--prev" aria-label="' + esc(t('text_prev') || '‹') + '">' + ICON.back + '</button>');
		var next = el('<button type="button" class="hoa-arrow hoa-arrow--next" aria-label="' + esc(t('text_next') || '›') + '">' + ICON.next + '</button>');
		var drag = null;
		var moved = false;

		function update() {
			var max = row.scrollWidth - row.clientWidth;

			prev.classList.toggle('is-hidden', row.scrollLeft <= 4);
			next.classList.toggle('is-hidden', row.scrollLeft >= max - 4);
		}

		function step(dir) {
			var card = row.querySelector('.hoa-card');
			var width = card ? card.getBoundingClientRect().width + 10 : 180;

			row.scrollBy({ left: dir * width * Math.max(1, Math.floor(row.clientWidth / width)), behavior: 'smooth' });
		}

		box.appendChild(row);
		box.appendChild(prev);
		box.appendChild(next);

		prev.addEventListener('click', function () { step(-1); });
		next.addEventListener('click', function () { step(1); });
		row.addEventListener('scroll', update, { passive: true });

		row.addEventListener('pointerdown', function (e) {
			if (e.pointerType !== 'mouse' || e.button !== 0) {
				return;
			}

			drag = { x: e.clientX, left: row.scrollLeft };
			moved = false;
		});

		window.addEventListener('pointermove', function (e) {
			if (!drag) {
				return;
			}

			if (Math.abs(e.clientX - drag.x) > 5) {
				moved = true;
				row.classList.add('is-dragging');
			}

			if (moved) {
				row.scrollLeft = drag.left - (e.clientX - drag.x);
			}
		});

		window.addEventListener('pointerup', function () {
			if (drag && moved) {
				row.classList.remove('is-dragging');
			}

			drag = null;
		});

		// a drag is not a click on the card under the mouse
		row.addEventListener('click', function (e) {
			if (moved) {
				e.preventDefault();
				e.stopPropagation();
				moved = false;
			}
		}, true);

		row.addEventListener('dragstart', function (e) { e.preventDefault(); });

		setTimeout(update, 0);
		window.addEventListener('resize', update);

		return box;
	}

	function cartCard(u) {
		if (!u.items || !u.items.length) {
			return el('<div class="hoa-msg"><div class="hoa-bubble">' + esc(t('text_cart_empty')) + '</div></div>');
		}

		var lines = u.items.map(function (i) {
			return '<div class="hoa-cart__line"><img src="' + esc(i.thumb) + '" alt=""><span><b>' + esc(i.name) + '</b><small>' + esc((i.options ? i.options + ' · ' : '') + '× ' + i.quantity) + '</small></span><em>' + esc(i.total) + '</em></div>';
		}).join('');

		return el(
			'<div class="hoa-cart"><div class="hoa-cart__head">' + esc(t('text_cart')) + ' <span>' + u.count + '</span></div>' + lines +
			'<div class="hoa-cart__foot"><div class="hoa-cart__total"><span>' + esc(t('text_total')) + '</span><span>' + esc(u.total) + '</span></div>' +
			'<a class="hoa-btn hoa-btn--ghost" href="' + esc(C.cart) + '">' + esc(t('text_go_cart')) + '</a>' +
			'<button type="button" class="hoa-btn" data-hoa-checkout>' + esc(t('text_order_now')) + '</button></div></div>'
		);
	}

	function doneCard(u) {
		return el(
			'<div class="hoa-done"><span class="hoa-done__check">' + ICON.check + '</span><strong>' + esc(u.title) + '</strong><p>' + esc(u.message) + '</p>' +
			(u.bank ? '<div class="hoa-done__bank"><b>' + esc(t('text_bank_title')) + '</b><br>' + u.bank + '<br><span class="hoa-muted">' + esc(t('text_bank_reference')) + '</span></div>' : '') +
			'</div>'
		);
	}

	// the header's cart count (store theme and kits use the same helper / class)
	function badge(count) {
		if (typeof window.hoCartCount === 'function') {
			window.hoCartCount(String(count));
		} else {
			var n = String(count).replace(/<[^>]*>/g, '').match(/\d+/);

			document.querySelectorAll('.cart-total').forEach(function (b) { b.textContent = n ? n[0] : '0'; });
		}
	}

	function typing() {
		var n = el('<div class="hoa-msg" data-hoa-typing><div class="hoa-typing"><span class="hoa-dots"><i></i><i></i><i></i></span>' + esc(t('text_thinking')) + '</div></div>');

		log.appendChild(n);
		scrollDown();

		return n;
	}

	function send(text, extra) {
		text = (text || '').trim();

		if (!text || busy) {
			return;
		}

		busy = true;
		input.value = '';
		autosize();
		render({ role: 'user', text: text });

		var wait = typing();

		var data = { message: text };

		// on a product's page: its id, so «this one» means that product (only when the page has exactly one)
		var ids = Array.prototype.filter.call(document.querySelectorAll('input[name="product_id"]'), function (i) { return !i.closest('.hoa') && /^\d+$/.test(i.value); });

		if (ids.length === 1) {
			data.page_product_id = ids[0].value;
		}

		Object.keys(extra || {}).forEach(function (k) { data[k] = extra[k]; });

		post(C.urls.message, data).then(function (d) {
			wait.remove();

			if (d.error) {
				log.appendChild(el('<div class="hoa-msg"><div class="hoa-error">' + esc(d.error) + '</div></div>'));
				scrollDown();

				return;
			}

			render(d);
		}).catch(function () {
			wait.remove();
			log.appendChild(el('<div class="hoa-msg"><div class="hoa-error">' + esc(t('text_error')) + '</div></div>'));
		}).then(function () {
			busy = false;
			sendBtn.disabled = !input.value.trim();
		});
	}

	function autosize() {
		input.style.height = 'auto';
		input.style.height = Math.min(input.scrollHeight, 120) + 'px';
		sendBtn.disabled = !input.value.trim() || busy;
	}

	// ------------------------------------------------------------------------------------------- dekor: options under the cards

	function openOptions(btn) {
		var row = btn.closest('.hoa-carousel') || btn.closest('.hoa-card');
		var old = row.parentNode.querySelector('.hoa-opts');

		if (old) {
			old.remove();
		}

		var box = el('<div class="hoa-opts"><div class="hoa-typing"><span class="hoa-dots"><i></i><i></i><i></i></span></div></div>');

		row.insertAdjacentElement('afterend', box);
		scrollDown();

		fetch(C.urls.options + '&product_id=' + encodeURIComponent(btn.getAttribute('data-hoa-pid')), { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (d) {
			if (d.error || !d.options) {
				box.innerHTML = '<p class="hoa-error">' + esc(d.error || t('text_error')) + '</p>';

				return;
			}

			var fields = d.options.map(function (o) {
				var label = '<label for="hoa-opt-' + o.id + '">' + esc(o.name) + (o.required ? ' <b class="hoa-req">*</b>' : '') + '</label>';
				var input;

				if (o.type === 'checkbox') {
					input = o.values.map(function (v) {
						return '<label class="hoa-choice"><input type="checkbox" name="option[' + o.id + '][]" value="' + v.id + '"' + (v.available ? '' : ' disabled') + '><span>' + esc(v.name) + (v.price ? ' <small class="hoa-muted">(' + esc(v.price) + ')</small>' : '') + '</span></label>';
					}).join('');
				} else if (o.values && o.values.length) {
					input = '<select id="hoa-opt-' + o.id + '" name="option[' + o.id + ']"><option value="">' + esc(t('text_select')) + '</option>' + o.values.map(function (v) {
						return '<option value="' + v.id + '"' + (v.available ? '' : ' disabled') + '>' + esc(v.name) + (v.price ? ' (' + esc(v.price) + ')' : '') + (v.available ? '' : ' — ' + esc(t('text_out_of_stock'))) + '</option>';
					}).join('') + '</select>';
				} else if (o.type === 'textarea') {
					input = '<textarea id="hoa-opt-' + o.id + '" name="option[' + o.id + ']" rows="2">' + esc(o.value) + '</textarea>';
				} else {
					input = '<input id="hoa-opt-' + o.id + '" type="text" name="option[' + o.id + ']" value="' + esc(o.value) + '">';
				}

				return '<div class="hoa-field hoa-field--full" data-hoa-opt="' + o.id + '">' + label + input + '<span class="hoa-field__err"></span></div>';
			}).join('');

			box.innerHTML =
				'<div class="hoa-opts__head"><strong>' + esc(d.name) + '</strong><button type="button" class="hoa-icon-btn" data-hoa-opt-close aria-label="' + esc(t('text_close')) + '">×</button></div>' +
				'<form class="hoa-grid" onsubmit="return false">' + fields +
					'<div class="hoa-field" data-hoa-opt="quantity"><label for="hoa-opt-qty">' + esc(t('text_quantity')) + '</label><input id="hoa-opt-qty" type="number" name="quantity" min="' + d.minimum + '" value="' + d.minimum + '"><span class="hoa-field__err"></span></div>' +
					'<input type="hidden" name="product_id" value="' + d.product_id + '">' +
				'</form>' +
				'<p class="hoa-form-err" data-hoa-opt-err></p>' +
				(d.can_add
					? '<button type="button" class="hoa-btn hoa-btn--lg" data-hoa-opt-add>' + esc(t('text_add')) + '</button>'
					: '<a class="hoa-btn hoa-btn--ghost hoa-btn--lg" href="' + esc(d.href) + '">' + esc(t('text_view')) + '</a>');
			scrollDown();
		}).catch(function () {
			box.innerHTML = '<p class="hoa-error">' + esc(t('text_error')) + '</p>';
		});
	}

	function addWithOptions(box) {
		var form = box.querySelector('form');
		var btn = box.querySelector('[data-hoa-opt-add]');
		var data = {};
		var counts = {};

		box.querySelectorAll('.has-error').forEach(function (n) { n.classList.remove('has-error'); });
		box.querySelectorAll('.hoa-field__err').forEach(function (n) { n.textContent = ''; });
		box.querySelector('[data-hoa-opt-err]').textContent = '';

		new FormData(form).forEach(function (v, k) {
			if (k.slice(-2) === '[]') {
				var base = k.slice(0, -2);

				counts[base] = counts[base] || 0;
				data[base + '[' + counts[base]++ + ']'] = v;
			} else {
				data[k] = v;
			}
		});

		btn.disabled = true;

		post('index.php?route=checkout/cart/add', data).then(function (d) {
			btn.disabled = false;

			// OpenCart answers a missing choice with errors AND a redirect to the product page: show the errors here
			if (d.error) {
				Object.keys(d.error.option || {}).forEach(function (id) {
					var f = box.querySelector('[data-hoa-opt="' + id + '"]');

					if (f) {
						f.classList.add('has-error');
						f.querySelector('.hoa-field__err').textContent = d.error.option[id];
					}
				});

				if (d.error.recurring || d.error.warning) {
					box.querySelector('[data-hoa-opt-err]').textContent = d.error.recurring || d.error.warning;
				}

				return;
			}

			// a shop-only product: the store sends it to its page
			if (d.redirect && !d.success) {
				location = d.redirect;

				return;
			}

			if (d.success) {
				badge(d.total);
				box.outerHTML = '<div class="hoa-added"><span>' + ICON.check + esc(t('text_added_bar')) + '</span><button type="button" class="hoa-btn" data-hoa-checkout>' + esc(t('text_order_now')) + '</button></div>';
				scrollDown();
			}
		}).catch(function () {
			btn.disabled = false;
			box.querySelector('[data-hoa-opt-err]').textContent = t('text_error');
		});
	}

	// ------------------------------------------------------------------------------------------- the order form

	var checkoutData = null;

	function field(name, label, value, type, full) {
		return '<div class="hoa-field' + (full ? ' hoa-field--full' : '') + '" data-hoa-field="' + name + '"><label for="hoa-' + name + '">' + esc(label) + '</label>' +
			'<input id="hoa-' + name + '" name="' + name + '" type="' + (type || 'text') + '" value="' + esc(value || '') + '" autocomplete="' + ({ firstname: 'given-name', lastname: 'family-name', email: 'email', telephone: 'tel', address_1: 'street-address', city: 'address-level2', postcode: 'postal-code' }[name] || 'off') + '"><span class="hoa-field__err"></span></div>';
	}

	function openCheckout() {
		sheet.innerHTML = '<div class="hoa-sheet__head"><button type="button" class="hoa-icon-btn" data-hoa-sheet-close aria-label="' + esc(t('text_back')) + '">' + ICON.back + '</button><strong>' + esc(t('text_checkout_title')) + '</strong></div><div class="hoa-sheet__body"><div class="hoa-typing"><span class="hoa-dots"><i></i><i></i><i></i></span></div></div>';
		sheet.classList.add('is-open');
		sheet.setAttribute('aria-hidden', 'false');
		root.classList.add('is-checkout'); // the panel grows for the form

		fetch(C.urls.checkout, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (d) {
			checkoutData = d;
			drawCheckout(d);
		});
	}

	function closeCheckout() {
		sheet.classList.remove('is-open');
		sheet.setAttribute('aria-hidden', 'true');
		root.classList.remove('is-checkout');
	}

	// dekor: «Απόδειξη / Τιμολόγιο» = the store's customer groups; the invoice one shows the company and its fields
	function invoiceBlock(d) {
		var p = d.prefill || {};
		var groups = d.groups || [];

		if (!groups.length) {
			return '';
		}

		var chosen = +p.customer_group_id || groups[0].id;

		if (!groups.some(function (g) { return +g.id === chosen; })) {
			chosen = groups[0].id;
		}

		var fields = '';

		groups.forEach(function (g) {
			if (!g.invoice) {
				return;
			}

			var cf = p.custom_field || {};

			fields += '<div class="hoa-grid hoa-invoice" data-hoa-invoice="' + g.id + '"' + (+g.id === +chosen ? '' : ' hidden') + '>' +
				field('company', t('entry_company'), p.company, 'text', true) +
				g.fields.map(function (f) {
					return field('custom_field_' + f.id, f.name, cf[f.id] || cf[String(f.id)] || '', 'text', f.vat || g.fields.length % 2 === 1);
				}).join('') +
			'</div>';
		});

		return '<p class="hoa-legend">' + esc(t('entry_invoice_type')) + '</p>' +
			'<div class="hoa-seg" data-hoa-field="customer_group_id">' + groups.map(function (g) {
				return '<label class="hoa-seg__opt"><input type="radio" name="customer_group_id" value="' + g.id + '"' + (+g.id === +chosen ? ' checked' : '') + '><span>' + esc(g.name) + '</span></label>';
			}).join('') + '<span class="hoa-field__err"></span></div>' + fields;
	}

	// only the chosen document's fields travel (a hidden invoice block must not send its values)
	function syncInvoice() {
		var f = orderForm();

		if (!f) {
			return;
		}

		var chosen = f.querySelector('input[name="customer_group_id"]:checked');

		f.querySelectorAll('[data-hoa-invoice]').forEach(function (box) {
			var on = chosen && box.getAttribute('data-hoa-invoice') === chosen.value;

			box.hidden = !on;
			box.querySelectorAll('input').forEach(function (i) { i.disabled = !on; });
		});
	}

	function drawCheckout(d) {
		var p = d.prefill || {};

		// below the store's minimum order (before VAT): no form at all — the message and back to shopping
		if (d.minimum) {
			sheet.querySelector('.hoa-sheet__body').innerHTML =
				'<div class="hoa-login"><p class="hoa-form-err">' + esc(d.minimum) + '</p>' +
				'<button type="button" class="hoa-btn hoa-btn--lg" data-hoa-sheet-close>' + esc(t('text_continue_shopping')) + '</button>' +
				'<a class="hoa-btn hoa-btn--ghost hoa-btn--lg" href="' + esc(C.cart) + '">' + esc(t('text_go_cart')) + '</a></div>';

			return;
		}

		// a store without guest orders: log in / register first (the cart stays)
		if (d.login) {
			sheet.querySelector('.hoa-sheet__body').innerHTML =
				'<div class="hoa-login"><p>' + esc(t('text_login_required')) + '</p>' +
				'<a class="hoa-btn hoa-btn--lg" href="' + esc(d.login.url) + '">' + esc(t('text_login')) + '</a>' +
				'<a class="hoa-btn hoa-btn--ghost hoa-btn--lg" href="' + esc(d.login.register) + '">' + esc(t('text_register')) + '</a></div>';

			return;
		}

		var zones = '<option value="">' + esc(t('text_select')) + '</option>' + (d.zones || []).map(function (z) {
			return '<option value="' + z.id + '"' + (+z.id === +p.zone_id ? ' selected' : '') + '>' + esc(z.name) + '</option>';
		}).join('');
		var agree = d.terms ? '<label class="hoa-agree" data-hoa-field="agree"><input type="checkbox" name="agree" value="1"><span>' + esc(t('entry_agree')).replace('%s', '<a href="' + esc(d.terms) + '" target="_blank" rel="noopener">' + esc(t('text_terms')) + '</a>') + '<br><span class="hoa-field__err"></span></span></label>' : '';

		sheet.querySelector('.hoa-sheet__body').innerHTML =
			'<form data-hoa-order novalidate>' +
				'<p class="hoa-note">' + ICON.lock + '<span>' + esc(t('text_checkout_note')) + '</span></p>' +
				(d.minimum ? '<p class="hoa-form-err hoa-min" data-hoa-min>' + esc(d.minimum) + '</p>' : '<p class="hoa-form-err hoa-min" data-hoa-min hidden></p>') +
				invoiceBlock(d) +
				'<div class="hoa-grid">' +
					field('firstname', t('entry_firstname'), p.firstname) + field('lastname', t('entry_lastname'), p.lastname) +
					field('email', t('entry_email'), p.email, 'email', true) + field('telephone', t('entry_telephone'), p.telephone, 'tel', true) +
					field('address_1', t('entry_address'), p.address_1, 'text', true) + field('city', t('entry_city'), p.city) + field('postcode', t('entry_postcode'), p.postcode) +
					'<div class="hoa-field hoa-field--full" data-hoa-field="zone_id"><label for="hoa-zone">' + esc(t('entry_zone')) + '</label><select id="hoa-zone" name="zone_id">' + zones + '</select><span class="hoa-field__err"></span></div>' +
				'</div>' +
				'<div data-hoa-methods></div>' +
				'<div class="hoa-field" style="margin-top:12px"><label for="hoa-comment">' + esc(t('entry_comment')) + '</label><textarea id="hoa-comment" name="comment" rows="2"></textarea></div>' +
				'<div class="hoa-totals" data-hoa-totals></div>' + agree +
				'<p class="hoa-form-err" data-hoa-form-err></p>' +
				'<button type="submit" class="hoa-btn hoa-btn--lg" data-hoa-confirm disabled>' + esc(t('text_confirm')) + '</button>' +
			'</form>';

		syncInvoice();
		quote();
	}

	function orderForm() {
		return sheet.querySelector('[data-hoa-order]');
	}

	function values() {
		var f = orderForm();
		var out = {};

		new FormData(f).forEach(function (v, k) { out[k] = v; });

		return out;
	}

	var quoteSeq = 0;

	function quote() {
		var id = ++quoteSeq;

		post(C.urls.quote, values()).then(function (d) {
			if (id !== quoteSeq || !orderForm()) {
				return;
			}

			var v = values();
			var html = '';

			if (d.need_postcode) {
				html += '<p class="hoa-legend">' + esc(t('entry_shipping')) + '</p><p class="hoa-muted" data-hoa-err="shipping_method">' + esc(t('text_need_postcode')) + '</p>';
			} else if (d.shipping && d.shipping.length) {
				html += '<p class="hoa-legend">' + esc(t('entry_shipping')) + '</p>' + d.shipping.map(function (s) {
					// dekor: the same words as the store's checkout (built and escaped on the server: «title - price», notes)
					return '<label class="hoa-choice"><input type="radio" name="shipping_method" value="' + esc(s.code) + '"' + (s.code === (v.shipping_method || d.shipping_code) ? ' checked' : '') + '><span>' + (s.html || (esc(s.title) + (s.text ? ' - ' + esc(s.text) : ''))) + '</span></label>';
				}).join('') + '<span class="hoa-field__err" data-hoa-err="shipping_method"></span>';
			}

			html += '<p class="hoa-legend">' + esc(t('entry_payment')) + '</p>';

			if (d.payment && d.payment.length) {
				html += d.payment.map(function (pm, i) {
					return '<label class="hoa-choice"><input type="radio" name="payment_method" value="' + esc(pm.code) + '"' + ((v.payment_method ? pm.code === v.payment_method : !i) ? ' checked' : '') + '><span>' + esc(pm.title) + (pm.online ? '<small class="hoa-muted" style="display:block">' + esc(t('text_pay_online')) + '</small>' : '') + '</span></label>';
				}).join('') + '<span class="hoa-field__err" data-hoa-err="payment_method"></span>';
			}

			if (d.other_payments || !(d.payment && d.payment.length)) {
				html += '<p class="hoa-muted">' + esc(t('text_card_note')) + ' <a href="' + esc(C.checkoutUrl) + '">' + esc(t('text_go_checkout')) + ' →</a></p>';
			}

			if (d.boxnow) {
				html += '<p class="hoa-muted">' + esc(t('text_boxnow_note')) + ' <a href="' + esc(C.checkoutUrl) + '">' + esc(t('text_go_checkout')) + ' →</a></p>';
			}

			// the store's minimum order (before VAT): shown above, the order cannot be confirmed below it
			var min = orderForm().querySelector('[data-hoa-min]');

			if (min) {
				min.textContent = d.minimum || '';
				min.hidden = !d.minimum;
			}

			orderForm().querySelector('[data-hoa-methods]').innerHTML = html;
			orderForm().querySelector('[data-hoa-totals]').innerHTML = (d.totals || []).map(function (r) {
				return '<div><span>' + esc(r.title) + '</span><span>' + esc(r.text) + '</span></div>';
			}).join('');

			var btn = orderForm().querySelector('[data-hoa-confirm]');

			btn.textContent = t('text_confirm') + (d.total ? ' · ' + d.total : '');
			btn.disabled = !(d.payment && d.payment.length) || !!d.need_postcode || !!d.minimum || !!d.login;
		});
	}

	function placeOrder() {
		var f = orderForm();
		var btn = f.querySelector('[data-hoa-confirm]');
		var data = values();

		f.querySelectorAll('.has-error').forEach(function (n) { n.classList.remove('has-error'); });
		f.querySelectorAll('.hoa-field__err').forEach(function (n) { n.textContent = ''; });
		f.querySelector('[data-hoa-form-err]').textContent = '';

		btn.disabled = true;

		post(C.urls.order, data).then(function (d) {
			if (d.error) {
				var first = null;

				Object.keys(d.error).forEach(function (k) {
					var box = f.querySelector('[data-hoa-field="' + k + '"]');
					var err = box ? box.querySelector('.hoa-field__err') : f.querySelector('[data-hoa-err="' + k + '"]');

					if (k === 'form') {
						f.querySelector('[data-hoa-form-err]').textContent = d.error[k];
					}

					if (box) {
						box.classList.add('has-error');
					}

					if (err) {
						err.textContent = d.error[k];
					}

					first = first || box || err;
				});

				if (first && first.scrollIntoView) {
					first.scrollIntoView({ block: 'center', behavior: 'smooth' });
				}

				btn.disabled = false;

				return;
			}

			// a card / wallet payment — to the bank's page with the same form the store's checkout posts;
			// the payment's callback brings the customer back to the store's checkout/success
			if (d.pay && d.pay.action) {
				var go = document.createElement('form');

				go.method = d.pay.method === 'get' ? 'get' : 'post';
				go.action = d.pay.action;
				go.style.display = 'none';

				Object.keys(d.pay.fields || {}).forEach(function (k) {
					var i = document.createElement('input');

					i.type = 'hidden';
					i.name = k;
					i.value = d.pay.fields[k];
					go.appendChild(i);
				});

				f.querySelector('[data-hoa-form-err]').textContent = '';
				btn.textContent = d.message || '…';

				try { sessionStorage.removeItem(KEY); } catch (e) {}

				document.body.appendChild(go);
				go.submit();

				return;
			}

			closeCheckout();
			log.appendChild(doneCard(d));
			badge(0);
			scrollDown();
		}).catch(function () {
			f.querySelector('[data-hoa-form-err]').textContent = t('error_order');
			btn.disabled = false;
		});
	}

	// ------------------------------------------------------------------------------------------- events

	launch.addEventListener('click', open);
	root.querySelector('[data-hoa-close]').addEventListener('click', close);
	// the header's ↻ and the «Καθαρισμός συνομιλίας» under the composer: a new conversation
	root.querySelectorAll('[data-hoa-reset]').forEach(function (b) {
		b.addEventListener('click', function () {
			post(C.urls.reset, {}).then(function () {
				closeCheckout();
				log.innerHTML = '';
				welcome();
				input.focus({ preventScroll: true });
			});
		});
	});

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		send(input.value);
	});

	input.addEventListener('input', autosize);
	input.addEventListener('keydown', function (e) {
		if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
			e.preventDefault();
			send(input.value);
		}
	});

	root.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			if (sheet.classList.contains('is-open')) {
				closeCheckout();
			} else {
				close();
			}
		}
	});

	root.addEventListener('click', function (e) {
		var ask = e.target.closest('[data-hoa-ask]');

		if (ask) {
			send(ask.getAttribute('data-hoa-ask'));

			return;
		}

		// dekor: «Επιλογές» opens every option of the product under the cards; «Στο καλάθι» there uses the store's cart
		var optBtn = e.target.closest('[data-hoa-options][data-hoa-pid]');

		if (optBtn) {
			openOptions(optBtn);

			return;
		}

		var optAdd = e.target.closest('[data-hoa-opt-add]');

		if (optAdd) {
			addWithOptions(optAdd.closest('.hoa-opts'));

			return;
		}

		if (e.target.closest('[data-hoa-opt-close]')) {
			var optBox = e.target.closest('.hoa-opts');

			if (optBox) {
				optBox.remove();
			}

			return;
		}

		var add = e.target.closest('[data-hoa-add]');

		if (add && !add.classList.contains('is-done')) {
			add.disabled = true;

			post('index.php?route=checkout/cart/add', { product_id: add.getAttribute('data-hoa-add'), quantity: 1 }).then(function (d) {
				add.disabled = false;

				if (d.redirect) {
					location = d.redirect;

					return;
				}

				// the store wants a choice first (options): the assistant asks it
				if (d.error) {
					send(t('text_options') + ': ' + add.closest('.hoa-card').querySelector('.hoa-card__name').textContent);

					return;
				}

				if (d.success) {
					add.classList.add('is-done');
					add.textContent = '✓ ' + t('text_added');
					badge(d.total);

					// the way on to the order, under the row (once)
					var row = add.closest('.hoa-carousel');

					if (row && !(row.nextElementSibling && row.nextElementSibling.classList.contains('hoa-added'))) {
						row.insertAdjacentElement('afterend', el('<div class="hoa-added"><span>' + ICON.check + esc(t('text_added_bar')) + '</span><button type="button" class="hoa-btn" data-hoa-checkout>' + esc(t('text_order_now')) + '</button></div>'));
						scrollDown();
					}
				}
			});

			return;
		}

		// «Πληροφορίες»: the assistant describes this product (its id goes along, unseen, so it opens the right one)
		var info = e.target.closest('[data-hoa-info]');

		if (info) {
			send(t('text_info_ask') + ' «' + info.getAttribute('data-hoa-name') + '»', { product_id: info.getAttribute('data-hoa-info') });

			return;
		}

		var opt = e.target.closest('[data-hoa-options]');

		if (opt) {
			send(t('text_options') + ': ' + opt.getAttribute('data-hoa-options'));

			return;
		}

		if (e.target.closest('[data-hoa-checkout]')) {
			openCheckout();

			return;
		}

		if (e.target.closest('[data-hoa-sheet-close]')) {
			closeCheckout();
		}
	});

	sheet.addEventListener('change', function (e) {
		// a corrected field loses its error message
		var box = e.target.closest('[data-hoa-field]');

		if (box) {
			box.classList.remove('has-error');
			box.querySelectorAll('.hoa-field__err').forEach(function (n) { n.textContent = ''; });
		}

		if (e.target.name === 'customer_group_id') {
			syncInvoice();
		}

		if (e.target.name === 'zone_id' || e.target.name === 'shipping_method' || e.target.name === 'postcode' || e.target.name === 'customer_group_id') {
			quote();
		}
	});

	// shipping can be priced per postcode: a new quote once the postcode looks complete (not only when the field is left)
	var lastPostcode = '';
	var postcodeTimer = null;

	sheet.addEventListener('input', function (e) {
		if (e.target.name !== 'postcode') {
			return;
		}

		clearTimeout(postcodeTimer);

		postcodeTimer = setTimeout(function () {
			var value = e.target.value.replace(/\s/g, '');

			if ((value.length >= 3 || lastPostcode.length >= 3) && value !== lastPostcode) {
				lastPostcode = value;
				quote();
			}
		}, 500);
	});

	sheet.addEventListener('submit', function (e) {
		if (e.target.matches('[data-hoa-order]')) {
			e.preventDefault();
			placeOrder();
		}
	});

	// open again on the next page if it was open
	try {
		if (sessionStorage.getItem(KEY)) {
			open();
		}
	} catch (e) {}
})();
