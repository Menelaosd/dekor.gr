/* Apokrifa desktop navigation. One dropdown at a time; mobile stays with mmenu. */
(function () {
  'use strict';
  var nav = document.querySelector('#desk-menu > .main-menu > .navbar-nav');
  if (!nav) return;
  var desktop = window.matchMedia('(min-width: 992px)');
  var items = Array.from(nav.children).filter(function (item) { return item.querySelector(':scope > .dropdown-menu'); });
  function show(active) {
    items.forEach(function (item) {
      var open = item === active && desktop.matches;
      item.setAttribute('data-ap-open', String(open));
      item.classList.remove('open');
      item.querySelector(':scope > a').setAttribute('aria-expanded', String(open));
    });
  }
  items.forEach(function (item) {
    var link = item.querySelector(':scope > a');
    // Own desktop interactions instead of combining Bootstrap click and CSS hover.
    link.removeAttribute('data-toggle');
    item.addEventListener('pointerenter', function (event) {
      if (desktop.matches && event.pointerType !== 'touch') show(item);
    });
    item.addEventListener('focusin', function () { if (desktop.matches) show(item); });
    link.addEventListener('click', function (event) {
      if (!desktop.matches) return;
      event.preventDefault();
      show(item);
    });
    link.addEventListener('keydown', function (event) {
      if (desktop.matches && (event.key === 'ArrowDown' || event.key === ' ')) {
        event.preventDefault();
        show(item);
        if (event.key === 'ArrowDown') item.querySelector('.dropdown-menu a').focus();
      }
    });
  });
  nav.addEventListener('pointerleave', function () { show(null); });
  nav.addEventListener('focusout', function (event) { if (!nav.contains(event.relatedTarget)) show(null); });
  nav.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      var item = event.target.closest('[data-ap-open="true"]');
      if (item) item.querySelector(':scope > a').focus();
      show(null);
    }
  });
  document.addEventListener('click', function (event) { if (!nav.contains(event.target)) show(null); });
  desktop.addEventListener('change', function () { show(null); });
  show(null);
}());
