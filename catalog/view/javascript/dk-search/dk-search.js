/*!
 * dekor live search — replaces the old live_search dropdown on #search.
 * Greek + Greeklish, product codes, typo suggestions, categories, blog posts,
 * recent + popular searches, keyboard navigation, add to cart, mobile sheet.
 */
(function () {
	'use strict';

	var ENDPOINT = 'index.php?route=extension/module/dk_search&q=';
	var RECENT_KEY = 'dk_search_recent';
	var DEBOUNCE = 160;

	// ------------------------------------------------------------ skeleton (mirror of the PHP model, for highlighting)
	var FOLD = { 'ά': 'α', 'έ': 'ε', 'ή': 'η', 'ί': 'ι', 'ό': 'ο', 'ύ': 'υ', 'ώ': 'ω', 'ϊ': 'ι', 'ϋ': 'υ', 'ΐ': 'ι', 'ΰ': 'υ', 'ς': 'σ' };
	var GR2 = [['ου', 'u'], ['αι', 'e'], ['ει', 'i'], ['οι', 'i'], ['υι', 'i'], ['αυ', 'av'], ['ευ', 'ev'], ['μπ', 'b'], ['ντ', 'd'], ['γκ', 'g'], ['γγ', 'g'], ['τσ', 'ts'], ['τζ', 'tz']];
	var GR1 = { 'α': 'a', 'β': 'v', 'γ': 'g', 'δ': 'd', 'ε': 'e', 'ζ': 'z', 'η': 'i', 'θ': 'th', 'ι': 'i', 'κ': 'k', 'λ': 'l', 'μ': 'm', 'ν': 'n', 'ξ': 'ks', 'ο': 'o', 'π': 'p', 'ρ': 'r', 'σ': 's', 'τ': 't', 'υ': 'i', 'φ': 'f', 'χ': 'h', 'ψ': 'ps', 'ω': 'o' };
	var LAT = [['ph', 'f'], ['ch', 'h'], ['kh', 'h'], ['gh', 'g'], ['dh', 'd'], ['ou', 'u'], ['ai', 'e'], ['ei', 'i'], ['oi', 'i'], ['mp', 'b'], ['mb', 'b'], ['nt', 'd'], ['gk', 'g'], ['gg', 'g'], ['ck', 'k']];
	var LAT1 = { 'w': 'o', 'y': 'i', 'x': 'h', 'c': 'k', 'q': 'k', 'b': 'v' };

	function skel(s) {
		s = String(s).toLowerCase().replace(/[άέήίόύώϊϋΐΰς]/g, function (c) { return FOLD[c]; });
		s = s.normalize ? s.normalize('NFD').replace(/[̀-ͯ]/g, '') : s;
		GR2.forEach(function (p) { s = s.split(p[0]).join(p[1]); });
		s = s.replace(/[α-ω]/g, function (c) { return GR1[c] || c; });
		LAT.forEach(function (p) { s = s.split(p[0]).join(p[1]); });
		s = s.replace(/[wyxcqb]/g, function (c) { return LAT1[c]; });
		return s.replace(/([a-z])\1+/g, '$1').replace(/[^a-z0-9]+/g, '');
	}

	// light Greek stemmer, same endings as the PHP model
	function stem(t) {
		if (t.length < 5 || /^\d+$/.test(t)) { return t; }
		var ends = ["ies", "ika", "iki", "ikes", "os", "es", "us", "is", "on", "as", "ia", "io", "a", "e", "i", "o", "u"];
		for (var i = 0; i < ends.length; i++) {
			var e = ends[i];
			if (t.length - e.length >= 3 && t.slice(-e.length) === e) { return t.slice(0, -e.length); }
		}
		return t;
	}

	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
	}

	function highlight(text, tokens) {
		if (!tokens || !tokens.length) {
			return esc(text);
		}
		return String(text).split(/(\s+)/).map(function (w) {
			var k = skel(w);
			var hit = k && tokens.some(function (t) { return t && (k.indexOf(t) === 0 || (k.indexOf(stem(t)) === 0 && k.length <= t.length + 2)); });
			return hit ? '<mark>' + esc(w) + '</mark>' : esc(w);
		}).join('');
	}

	// ------------------------------------------------------------ storage (never required)
	function getRecent() {
		try { return JSON.parse(localStorage.getItem(RECENT_KEY)) || []; } catch (e) { return []; }
	}
	function addRecent(q) {
		q = (q || '').trim();
		if (q.length < 2) { return; }
		try {
			var list = getRecent().filter(function (x) { return skel(x) !== skel(q); });
			list.unshift(q);
			localStorage.setItem(RECENT_KEY, JSON.stringify(list.slice(0, 6)));
		} catch (e) {}
	}
	function removeRecent(q) {
		try { localStorage.setItem(RECENT_KEY, JSON.stringify(getRecent().filter(function (x) { return x !== q; }))); } catch (e) {}
	}

	// ------------------------------------------------------------ widget
	var sheetWidget = null;

	function isMobile() {
		return window.matchMedia ? window.matchMedia('(max-width: 767px)').matches : window.innerWidth < 768;
	}

	function DkSearch(box, sheet) {
		this.box = box;
		this.sheet = sheet || null;
		this.isSheet = !!sheet;
		this.input = box.querySelector('input[name="search"]');
		this.button = box.querySelector('button');
		this.cache = {};
		this.popular = null;
		this.timer = null;
		this.ctrl = null;
		this.last = null;
		this.active = -1;
		this.init();
	}

	DkSearch.prototype.init = function () {
		var self = this;
		var input = this.input;

		// take over from the old live_search / OpenCart autocomplete on the same field
		if (window.jQuery) {
			jQuery(input).off();
			jQuery(this.box).find('.live-search, ul.dropdown-menu').remove();
		}

		input.setAttribute('autocomplete', 'off');
		input.setAttribute('role', 'combobox');
		input.setAttribute('aria-autocomplete', 'list');
		input.setAttribute('aria-expanded', 'false');
		input.setAttribute('aria-controls', 'dk-search-panel');

		this.panel = document.createElement('div');
		this.panel.className = 'dk-search-panel';
		this.panel.id = this.isSheet ? 'dk-sheet-panel' : 'dk-search-panel';
		this.panel.setAttribute('role', 'listbox');
		input.setAttribute('aria-controls', this.panel.id);
		// in the mobile sheet the panel sits below the bar and takes the rest of the screen
		(this.isSheet ? this.sheet : this.box).appendChild(this.panel);
		this.box.classList.add('dk-search-ready');

		input.addEventListener('focus', function () {
			// phones: the header field only opens the full-screen sheet
			if (!self.isSheet && isMobile() && sheetWidget) {
				input.blur();
				sheetWidget.openSheet(input.value);
				return;
			}
			self.open();
			if (!input.value.trim()) { self.renderEmpty(); } else { self.query(input.value); }
		});
		input.addEventListener('input', function () { self.schedule(); });
		input.addEventListener('keydown', function (e) { self.onKey(e); });

		if (this.button) {
			this.button.addEventListener('click', function (e) {
				e.preventDefault(); e.stopImmediatePropagation();
				if (!self.isSheet && isMobile() && sheetWidget && !input.value.trim()) { sheetWidget.openSheet(''); return; }
				self.go();
			}, true);
		}

		if (!this.isSheet) {
			document.addEventListener('mousedown', function (e) {
				if (!self.box.contains(e.target)) { self.close(); }
			});

			// "/" opens the search from anywhere
			document.addEventListener('keydown', function (e) {
				if (e.key === '/' && !/input|textarea|select/i.test((document.activeElement || {}).tagName || '')) {
					e.preventDefault();
					input.focus();
				}
			});
		}

		this.panel.addEventListener('click', function (e) {
			var t = e.target.closest('[data-act]');
			if (!t) { return; }
			var act = t.getAttribute('data-act');
			if (act === 'fill') { e.preventDefault(); input.value = t.getAttribute('data-q'); self.query(input.value, true); input.focus(); }
			if (act === 'del') { e.preventDefault(); e.stopPropagation(); removeRecent(t.getAttribute('data-q')); self.renderEmpty(); input.focus(); }
			if (act === 'cart') {
				e.preventDefault();
				if (window.cart && cart.add) {
					cart.add(t.getAttribute('data-id'), t.getAttribute('data-min') || 1);
					t.classList.add('is-added');
					setTimeout(function () { t.classList.remove('is-added'); }, 1600);
				}
			}
			if (act === 'link') { addRecent(input.value); }
		});
	};

	DkSearch.prototype.open = function () {
		this.box.classList.add('dk-open');
		if (this.isSheet) { this.sheet.classList.add('is-open'); this.sheet.setAttribute('aria-hidden', 'false'); document.documentElement.classList.add('dk-sheet-open'); }
		this.input.setAttribute('aria-expanded', 'true');
	};

	DkSearch.prototype.close = function () {
		this.box.classList.remove('dk-open');
		if (this.isSheet) { this.sheet.classList.remove('is-open'); this.sheet.setAttribute('aria-hidden', 'true'); document.documentElement.classList.remove('dk-sheet-open'); }
		this.input.setAttribute('aria-expanded', 'false');
		this.active = -1;
	};

	DkSearch.prototype.openSheet = function (value) {
		this.input.value = value || '';
		this.open();
		if (this.input.value.trim()) { this.query(this.input.value); } else { this.renderEmpty(); }
		var input = this.input;
		setTimeout(function () { input.focus(); }, 30);
	};

	DkSearch.prototype.schedule = function () {
		var self = this;
		clearTimeout(this.timer);
		var q = this.input.value;
		if (!q.trim()) { this.renderEmpty(); return; }
		this.timer = setTimeout(function () { self.query(q); }, DEBOUNCE);
	};

	DkSearch.prototype.fetchJson = function (q) {
		var self = this;
		var key = skel(q) || q;
		if (this.cache[key]) { return Promise.resolve(this.cache[key]); }
		if (this.ctrl && this.ctrl.abort) { this.ctrl.abort(); }
		this.ctrl = window.AbortController ? new AbortController() : null;
		return fetch(ENDPOINT + encodeURIComponent(q), { credentials: 'same-origin', signal: this.ctrl ? this.ctrl.signal : undefined })
			.then(function (r) { return r.json(); })
			.then(function (j) { self.cache[key] = j; return j; });
	};

	DkSearch.prototype.query = function (q, now) {
		var self = this;
		q = q.trim();
		if (q.length < 2) { this.renderEmpty(); return; }
		this.open();
		if (!this.cache[skel(q) || q]) { this.renderLoading(); }
		this.fetchJson(q).then(function (j) {
			if (self.input.value.trim() !== q && !now) { return; }
			self.last = j;
			self.render(j);
		}).catch(function () {});
	};

	DkSearch.prototype.go = function () {
		var q = this.input.value.trim();
		var active = this.panel.querySelectorAll('[data-nav]')[this.active];
		if (active) { addRecent(q); window.location = active.getAttribute('href'); return; }
		if (!q) { return; }
		addRecent(q);
		var url = this.last && this.last.q === q && this.last.search_url ? this.last.search_url : 'index.php?route=product/search&search=' + encodeURIComponent(q);
		window.location = url;
	};

	DkSearch.prototype.onKey = function (e) {
		var items = this.panel.querySelectorAll('[data-nav]');
		if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
			if (!items.length) { return; }
			e.preventDefault();
			this.open();
			this.active = (this.active + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
			items.forEach(function (el, i) { el.classList.toggle('is-active', i === this.active); }, this);
			items[this.active].scrollIntoView({ block: 'nearest' });
		} else if (e.key === 'Enter') {
			e.preventDefault();
			this.go();
		} else if (e.key === 'Escape') {
			this.close();
			this.input.blur();
		} else {
			this.active = -1;
		}
	};

	// ------------------------------------------------------------ rendering
	DkSearch.prototype.renderLoading = function () {
		var rows = '';
		for (var i = 0; i < 4; i++) {
			rows += '<div class="dk-s-skel"><span class="dk-s-skel-img"></span><span class="dk-s-skel-lines"><i></i><i></i></span></div>';
		}
		this.panel.innerHTML = '<div class="dk-s-body">' + rows + '</div>';
	};

	DkSearch.prototype.renderEmpty = function () {
		var self = this;
		var recent = getRecent();
		var html = '';

		if (recent.length) {
			html += '<div class="dk-s-sec"><div class="dk-s-title">Πρόσφατες αναζητήσεις</div><div class="dk-s-chips">' +
				recent.map(function (q) {
					return '<a href="#" class="dk-s-chip is-recent" data-act="fill" data-q="' + esc(q) + '"><span class="dk-s-ico-clock"></span>' + esc(q) + '<span class="dk-s-chip-x" data-act="del" data-q="' + esc(q) + '" aria-label="Διαγραφή">&times;</span></a>';
				}).join('') + '</div></div>';
		}

		var renderPopular = function () {
			if (self.popular && self.popular.length) {
				html += '<div class="dk-s-sec"><div class="dk-s-title">Δημοφιλείς αναζητήσεις</div><div class="dk-s-chips">' +
					self.popular.map(function (q) { return '<a href="#" class="dk-s-chip" data-act="fill" data-q="' + esc(q) + '"><span class="dk-s-ico-trend"></span>' + esc(q) + '</a>'; }).join('') + '</div></div>';
			}
			html += '<div class="dk-s-hint">Γράψτε ελληνικά ή greeklish (π.χ. <b>keri sogias</b>) ή κωδικό προϊόντος</div>';
			self.panel.innerHTML = '<div class="dk-s-body">' + html + '</div>';
		};

		if (this.popular) {
			renderPopular();
		} else {
			fetch(ENDPOINT, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
				self.popular = j.popular || [];
				if (!self.input.value.trim()) { html = html.split('<div class="dk-s-sec"><div class="dk-s-title">Δημοφιλείς')[0]; renderPopular(); }
			}).catch(function () { renderPopular(); });
		}
	};

	DkSearch.prototype.priceHtml = function (p) {
		if (p.price === false || p.price === null) { return ''; }
		var main = p.special || p.price;
		var old = p.special ? '<del>' + esc(p.price) + '</del>' : '';
		if (p.package) {
			var unit = p.package_special || p.package_price;
			return '<div class="dk-s-price">' + old + '<b>' + esc(main) + '</b>' +
				'<small>' + (unit ? esc(unit) + ' / τεμ. · ' : '') + esc(p.package) + ' τεμ.</small></div>';
		}
		return '<div class="dk-s-price">' + old + '<b>' + esc(main) + '</b></div>';
	};

	DkSearch.prototype.render = function (j) {
		var self = this;
		var tokens = j.tokens || [];
		var html = '';
		this.active = -1;

		if (j.suggestion) {
			html += '<div class="dk-s-note">Μήπως εννοούσατε <a href="#" data-act="fill" data-q="' + esc(j.suggestion) + '"><b>' + esc(j.suggestion) + '</b></a>;</div>';
		} else if (j.display && /[α-ωά-ώ]/i.test(j.display) && !/[α-ωά-ώ]/i.test(j.q)) {
			html += '<div class="dk-s-note">Αποτελέσματα για <b>' + esc(j.display) + '</b></div>';
		}

		if (j.categories && j.categories.length) {
			html += '<div class="dk-s-sec dk-s-cats"><div class="dk-s-title">Κατηγορίες</div><div class="dk-s-chips">' +
				j.categories.map(function (c) { return '<a class="dk-s-chip is-cat" data-nav data-act="link" href="' + esc(c.href) + '">' + highlight(c.name, tokens) + '</a>'; }).join('') + '</div></div>';
		}

		if (j.products && j.products.length) {
			html += '<div class="dk-s-sec"><div class="dk-s-title">Προϊόντα <span>' + j.total + '</span></div><div class="dk-s-list">' +
				j.products.map(function (p) {
					var badge = p.discount ? '<span class="dk-s-badge">-' + p.discount + '%</span>' : '';
					var code = p.exact_code ? '<span class="dk-s-exact">Ακριβής κωδικός</span>' : '';
					var add = p.can_add ? '<button type="button" class="dk-s-add" data-act="cart" data-id="' + p.product_id + '" data-min="' + p.minimum + '" aria-label="Στο καλάθι"><span></span></button>' : '';
					return '<div class="dk-s-item">' +
						'<a class="dk-s-link" data-nav data-act="link" href="' + esc(p.href) + '">' +
							'<span class="dk-s-img">' + badge + '<img src="' + esc(p.thumb) + '" alt="" loading="lazy" width="60" height="60"></span>' +
							'<span class="dk-s-info"><span class="dk-s-name">' + highlight(p.name, tokens) + '</span>' +
							'<span class="dk-s-meta"><span class="dk-s-code">' + esc(p.model) + '</span>' + code + '<span class="dk-s-stock is-' + esc(p.stock.class) + '">' + esc(p.stock.label) + '</span></span></span>' +
							self.priceHtml(p) +
						'</a>' + add + '</div>';
				}).join('') + '</div></div>';
		}

		if (j.posts && j.posts.length) {
			html += '<div class="dk-s-sec"><div class="dk-s-title">Από το blog</div><div class="dk-s-posts">' +
				j.posts.map(function (b) {
					return '<a class="dk-s-post" data-nav data-act="link" href="' + esc(b.href) + '">' + (b.thumb ? '<img src="' + esc(b.thumb) + '" alt="" loading="lazy">' : '') + '<span>' + highlight(b.title, tokens) + '</span></a>';
				}).join('') + '</div></div>';
		}

		if (!html || (!j.products.length && !j.categories.length && !j.posts.length)) {
			html = '<div class="dk-s-empty"><b>Δεν βρέθηκαν αποτελέσματα για «' + esc(j.q) + '»</b><span>Δοκιμάστε λιγότερες ή άλλες λέξεις, ή τον κωδικό του προϊόντος.</span></div>';
		}

		var foot = j.total ? '<a class="dk-s-all" data-nav data-act="link" href="' + esc(j.search_url) + '">Δείτε όλα τα αποτελέσματα <b>(' + j.total + ')</b><span>&rarr;</span></a>' : '';

		this.panel.innerHTML = '<div class="dk-s-body">' + html + '</div>' + foot;
	};

	// ------------------------------------------------------------ boot (after the old live_search initialised itself)
	function buildSheet() {
		var header = document.querySelector('#search input[name="search"]');
		var sheet = document.createElement('div');
		sheet.className = 'dk-sheet';
		sheet.setAttribute('aria-hidden', 'true');
		sheet.innerHTML = '<div class="dk-sheet-top">' +
			'<button type="button" class="dk-sheet-back" aria-label="Κλείσιμο"></button>' +
			'<div class="dk-sheet-box"><input type="search" name="search" enterkeyhint="search" placeholder="' + esc(header ? header.getAttribute('placeholder') : 'Αναζήτηση') + '">' +
			'<button type="button" class="dk-sheet-go" aria-label="Αναζήτηση"></button></div></div>';
		document.body.appendChild(sheet);

		var w = new DkSearch(sheet.querySelector('.dk-sheet-box'), sheet);
		sheet.querySelector('.dk-sheet-back').addEventListener('click', function () {
			if (header) { header.value = w.input.value; }
			w.close();
			w.input.blur();
		});

		return w;
	}

	function boot() {
		if (document.querySelector('#search input[name="search"]')) {
			sheetWidget = buildSheet();
		}

		document.querySelectorAll('#search').forEach(function (box) {
			if (box.querySelector('input[name="search"]') && !box.classList.contains('dk-search-ready')) {
				new DkSearch(box);
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { setTimeout(boot, 0); });
	} else {
		setTimeout(boot, 0);
	}
})();
