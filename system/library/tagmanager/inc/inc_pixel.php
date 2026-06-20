<?php
/* v:9.6 06042022*/
$fbq ='';
$post = '';
if (isset($_data['fb_data'])) {
	if (isset($tagmanager['fb_catalog_id']) && !empty($tagmanager['fb_catalog_id'])) {
		$_data['fb_data']['product_catalog_id'] = $tagmanager['fb_catalog_id'];
	}
	
	switch ($page_type) {
		
		//completeregistration
				
		case "success":
		    $fbq = "fbq('track','Purchase'," .  json_encode($_data['fb_data']) . ",{'eventID': event_id });";
		    break;
		
		case "product":
		     $fbq = "fbq('track','ViewContent'," .  json_encode($_data['fb_data']) . ",{'eventID': event_id });";
		    break;
		
		case "listing":
		    $fbq = "fbq('trackCustom','ViewCategory'," .  json_encode($_data['fb_data']) . ",{'eventID': event_id });";
		    break;
		case "checkout":
		    $fbq = "fbq('track','InitiateCheckout'," .  json_encode($_data['fb_data']) . ",{'eventID': event_id });";
		    break;
		
		case "cart":
		    $fbq = "fbq('track','InitiateCheckout'," .  json_encode($_data['fb_data']) . ",{'eventID': event_id });";
		    break;
		
		case "confirm":
		    $fbq = "fbq('track','AddPaymentInfo'," .  json_encode($_data['fb_data']) . ",{'eventID': event_id });";
	        break;
	}
	$_data['fb_data']['page_type'] = $page_type;
	$_data['fb_data']['route'] = $route; 
	$post = json_encode($_data['fb_data']);
}

$tmanalytics .= "
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');";
if ($tm_mode == 'jquery') {
$tmanalytics .= 'var json_data = "data=" + \'' . $post . '\';';
$tmanalytics .= "function pixelhit(){"."$.ajax({url: 'index.php?route=extension/analytics/tagmanager/pixel',type: 'post',data: json_data,dataType: 'json',
beforeSend: function() { },complete: function() { },success: function(mydata) {var consent = mydata['consent'];	var event_id = mydata['event_id'];
delete mydata['event_id'];delete mydata['consent'];fbq('set', 'autoConfig', 'false', '" . $tagmanager['pixelcode'] . "');fbq('consent', consent);
fbq('init', '" . $tagmanager['pixelcode'] . "',mydata);fbq('track', 'PageView',{'eventID': '0-'+event_id });" . $fbq ." },error: function(xhr, ajaxOptions, thrownError) {console.log(xhr.responseText);}});}";
} else {
$tmanalytics .= '
var json_data = "data=" + \'' . $post . '\';var xhttp = new XMLHttpRequest();xhttp.open("POST", "index.php?route=extension/analytics/tagmanager/pixel");xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded;charset=UTF-8");xhttp.overrideMimeType(\'text/xml\');xhttp.responseType = \'json\';
xhttp.onload = function () {if (xhttp.readyState === xhttp.DONE && xhttp.status === 200) {var mydata = xhttp.response;var consent = mydata[\'consent\'];var event_id = mydata[\'event_id\'];delete mydata[\'event_id\'];delete mydata[\'consent\'];
fbq(\'set\', \'autoConfig\', \'false\', \'' . $tagmanager['pixelcode'] . '\');fbq(\'consent\', consent);fbq(\'init\', \'' . $tagmanager['pixelcode'] . '\',mydata);fbq(\'track\', \'PageView\',{\'eventID\': \'0-\'+event_id });' .$fbq . '}};xhttp.send(json_data);' . "\n";
}
			
