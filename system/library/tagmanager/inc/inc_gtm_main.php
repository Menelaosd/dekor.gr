<?php
/* v:9.6 06042022*/
if (isset($tagmanager['server']) && $tagmanager['server'] && isset($tagmanager['server_url']) && !empty($tagmanager['server_url'])) {
	$server_url = $tagmanager['server_url'];
} else {
	$server_url = 'www.googletagmanager.com';
}
$tmanalytics .= "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://" . $server_url ."/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . $tagmanager['code'] ."');";
switch ($page_type) {
			
	case "success":
	
		if (isset($_data['gadata'])) {
			$tmanalytics .= "dataLayer.push(" . json_encode($_data['gadata']) . ");";
		}
		
		if (isset($_data['aw_ec_data'])) {
			if (isset($tagmanager['adword_ec']) && $tagmanager['adword_ec']) {
				$tmanalytics .= "dataLayer.push(" . json_encode($_data['aw_ec_data']) . ");";
			}
		}

		if (isset($_data['datalayer'])) {
			$tmanalytics .= "dataLayer.push(" . json_encode($_data['datalayer']) . ");";
		}
		if ($tagmanager['conversion_id2'] && $tagmanager['adword'] == '1' && $tagmanager['adword2'] == '1' && $tagmanager['conversion_route2'] == 'purchase') {
			$tmanalytics .= "dataLayer.push({'event'	: 'conversion2', 'adwordConversionValue2' : '" . $_data['revenue'] ."'});";
		}
		
		break;
	
	case "product":
		
		if (isset($_data['google_ec'])){ 
			$tmanalytics .= "dataLayer.push(" . json_encode($_data['google_ec']) . ");";
		}
		
		if (isset($_data['datalayer'])){
			$tmanalytics .= "dataLayer.push(" . json_encode($_data['datalayer']) . ");";
		}

		break;
	
	case "listing":
		
		if (!isset($_data['ttheme'])) { 
			if (isset($_data['google_ec'])){ 
				$count = 0;
				foreach ($_data['google_ec'] as $gdata){ 
					if ($count > 0) {
						$tmanalytics .= "setTimeout(function() {dataLayer.push(" . json_encode($gdata) . ");}, delayInMilliseconds);";
					} else {
						$tmanalytics .= "dataLayer.push(" . json_encode($gdata) . ");";	
					}
					$count++;
				}
			}
			if (isset($_data['datalayer'])){
				$tmanalytics .= "dataLayer.push(" . json_encode($_data['datalayer']) . ");";
			}
		}
		
		break;
	
	case "checkout":
		
		if (isset($_data['gadata'])){ 
			$tmanalytics .= "dataLayer.push(" . json_encode($_data['gadata']) . ");";
		}
		if (isset($_data['datalayer'])){ 
			$tmanalytics .= "dataLayer.push(" . json_encode($_data['datalayer']) . ");";
		}
		
		break;
	
	case "cart":
	
	    if (isset($this->session->data['addtocart'])) {
	    	$tmanalytics .= (!empty($this->session->data['addtocart']) ? $this->session->data['addtocart'] : '');
			unset($this->session->data['addtocart']);
			if (isset($_data['datalayer'])){
		        $tmanalytics .= "setTimeout(function() {dataLayer.push(" . json_encode($_data['datalayer']) . ");}, delayInMilliseconds);";
		    }
		} else {
		    if (isset($_data['datalayer'])){
		        $tmanalytics .= "dataLayer.push(" . json_encode($_data['datalayer']) . ");";
		    }
		}

		break;
	
	case "confirm":
		
		if (isset($_data['gadata'])){ 
			$tmanalytics .= "dataLayer.push(" . json_encode($_data['gadata']) . ");";
		}
		
		if (isset($_data['datalayer'])){ 
			$tmanalytics .= "dataLayer.push(" . json_encode($_data['datalayer']) . ");";
		}

		break;
	
	case "contact":
		
		$tmanalytics .= "dataLayer.push({'event' : 'contact', 'eventAction' : 'contact','eventLabel': 'contact'});";
		
		if ($tagmanager['conversion_id2'] && $tagmanager['adword'] == '1' && $tagmanager['adword2'] == '1' && $tagmanager['conversion_route2'] == 'contact') {
			$tmanalytics .= "dataLayer.push({'event'	: 'conversion2', 'adwordConversionValue2' : '" . $tagmanager['conversion_value2'] ."'});";
		}
	
		break;
		
	case "signup":
		
		if ($tagmanager['conversion_id2'] && $tagmanager['adword'] == '1' && $tagmanager['adword2'] == '1' && $tagmanager['conversion_route2'] == 'signup') {
			$tmanalytics .= "dataLayer.push({'event'	: 'conversion2', 'adwordConversionValue2' : '" . $tagmanager['conversion_value2'] ."'});";
		}

		break;
	
	case "home":
		
		$tmanalytics .= "dataLayer.push({'event' : 'home', 'eventAction' : 'home','eventLabel': 'Home Page'});";
		
		break;
}