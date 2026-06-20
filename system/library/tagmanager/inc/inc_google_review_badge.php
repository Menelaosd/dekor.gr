<?php
/* v:9.6 06042022*/
$tmanalytics .= '<script src="https://apis.google.com/js/platform.js?onload=renderBadge" async defer></script>';
$tmanalytics .= '<script>window.renderBadge = function() {   var ratingBadgeContainer = document.createElement("div");
				  document.body.appendChild(ratingBadgeContainer);';
$tmanalytics .= " window.gapi.load('ratingbadge', function() {";
$tmanalytics .= ' window.gapi.ratingbadge.render(ratingBadgeContainer, {"merchant_id": ' . $tagmanager['merchant_id'] .'}); }); }</script>';