<?php
/* v:9.6 06042022*/
$tmanalytics .= '!function (w, d, t) {
  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++
)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script");n.type="text/javascript",n.async=!0,n.src=i+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};';
$tmanalytics .= "ttq.load('" . $tagmanager['tiktok_code'] . "'); ttq.page(); }(window, document, 'ttq');ttq.track('Browse');" . "\n";
if (isset($_data['tiktok'])) {
	switch ($page_type) {
				
		case "success":
		    $tmanalytics .= "ttq.track('Purchase'," .  json_encode($_data['tiktok']) . ");". "\n";
		    break;
		
		case "product":
		    $tmanalytics .= "ttq.track('ViewContent'," .  json_encode($_data['tiktok']) . ");". "\n";
		    break;
		
		case "listing":
		    if (isset($_data['tiktok']['query']) && !empty($_data['tiktok']['query'])) {
		        $tmanalytics .= "ttq.track('Search'," .  json_encode($_data['tiktok']) . ");". "\n";
		    } else {
		        $tmanalytics .= "ttq.track('ViewContent'," .  json_encode($_data['tiktok']) . ");". "\n";
		    }
		    break;
		
		case "checkout":
		    $tmanalytics .= "ttq.track('StartCheckout'," .  json_encode($_data['tiktok']) . ");". "\n";
		    break;
		
		case "cart":
		    $tmanalytics .= "ttq.track('ViewContent'," .  json_encode($_data['tiktok']) . ");". "\n";
		    break;
		
		case "confirm":
		    $tmanalytics .= "ttq.track('AddBilling'," .  json_encode($_data['tiktok']) . ");". "\n";
	        break;
	}
}