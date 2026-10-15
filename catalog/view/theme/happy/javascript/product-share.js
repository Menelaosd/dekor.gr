(function () {
  'use strict';
  var button = document.querySelector('.product-share-copy');
  if (!button) return;
  button.addEventListener('click', async function () {
    var status = document.querySelector('.product-share-status');
    var label = button.querySelector('span');
    var url = button.getAttribute('data-share-url');
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(url);
      } else {
        var input = document.createElement('textarea');
        input.value = url;
        input.style.cssText = 'position:fixed;opacity:0;pointer-events:none';
        document.body.appendChild(input);
        input.select();
        var copied = document.execCommand('copy');
        input.remove();
        button.focus();
        if (!copied) throw new Error('Clipboard unavailable');
      }
      label.textContent = 'Έτοιμο';
      status.textContent = 'Ο σύνδεσμος αντιγράφηκε.';
    } catch (error) {
      status.textContent = 'Δεν ήταν δυνατή η αντιγραφή. Αντιγράψτε τη διεύθυνση από τη γραμμή του browser.';
    }
  });
}());
