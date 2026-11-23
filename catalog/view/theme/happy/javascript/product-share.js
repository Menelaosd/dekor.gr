(function () {
  'use strict';

  function initShare() {
    var copyButton = document.querySelector('.product-share-copy');
    var shareButton = document.querySelector('.product-share-native');
    var status = document.querySelector('.product-share-status');

    if (!copyButton) return;

    var url = copyButton.getAttribute('data-share-url') || window.location.href;

    async function copyLink() {
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

        if (!copied) throw new Error('Clipboard unavailable');
      }
    }

    copyButton.addEventListener('click', async function () {
      try {
        await copyLink();
        if (status) status.textContent = 'Ο σύνδεσμος αντιγράφηκε.';
        var label = copyButton.querySelector('span');
        if (label) label.textContent = 'Έτοιμο';
      } catch (error) {
        if (status) status.textContent = 'Δεν ήταν δυνατή η αντιγραφή.';
      }
    });

    if (shareButton) {
      shareButton.style.display = 'inline-flex';

      shareButton.addEventListener('click', async function () {
        var titleElement = document.querySelector('#product h1.title');
        var title = titleElement ? titleElement.textContent.trim() : document.title;

        if (typeof navigator.share === 'function') {
          try {
            await navigator.share({ title: title, url: url });
            return;
          } catch (error) {
            if (error.name === 'AbortError') return;
          }
        }

        try {
          await copyLink();
          if (status) status.textContent = 'Ο σύνδεσμος αντιγράφηκε. Μπορείτε να τον κοινοποιήσετε.';
          var label = shareButton.querySelector('span');
          if (label) label.textContent = 'Αντιγράφηκε';
        } catch (error) {
          if (status) status.textContent = 'Δεν ήταν δυνατή η κοινοποίηση.';
        }
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initShare);
  } else {
    initShare();
  }
}());