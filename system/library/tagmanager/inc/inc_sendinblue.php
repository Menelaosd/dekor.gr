<?php
/* v:9.6 06042022*/
$sendinblue = false;
if (isset($_data['sendinblue'])) {
    $sendinblue = $_data['sendinblue'];
}
if (!$sendinblue) {
    $sendinblue = array(
        'email'         => (isset($tagmanager['email']) ? $tagmanager['email'] : ''),
        'first_name'    => (isset($tagmanager['first_name']) ? $tagmanager['first_name'] : ''),
        'last_name'     => (isset($tagmanager['last_name']) ? $tagmanager['last_name'] : ''),
        'phone'         => (isset($tagmanager['phone']) ? $tagmanager['phone'] : ''),
        'id'            => (isset($tagmanager['userid']) ? $tagmanager['userid'] : ''),
        'location'      => (isset($tagmanager['city']) ? $tagmanager['city'] : ''),
        'country'       => (isset($tagmanager['country']) ? $tagmanager['country'] : '')
        );
}
$tmanalytics .= '<script type="text/javascript" nitro-exclude="">
(function() {
    window.sib = {
        equeue: [],
        client_key: "' . $tagmanager['sendinblue_code'] .'"
    };';
if (isset($tagmanager['email']) && !empty($tagmanager['email'])) {    
    $tmanalytics .= 'window.sib.email_id = "' . $tagmanager['email'] . '";';
}
$tmanalytics .= "  
	window.sendinblue = {};  
    for (var j = ['track', 'identify', 'trackLink', 'page'], i = 0; i < j.length; i++) {
    (function(k) {
        window.sendinblue[k] = function() {
            var arg = Array.prototype.slice.call(arguments);
            (window.sib[k] || function() {
                    var t = {};
                    t[k] = arg;
                    window.sib.equeue.push(t);
                })(arg[0], arg[1], arg[2]);
            };
        })(j[i]);
    }";
$tmanalytics .= '    
    var n = document.createElement("script"),
        i = document.getElementsByTagName("script")[0];
    n.type = "text/javascript", n.id = "sendinblue-js", n.async = !0, n.src = "https://sibautomation.com/sa.js?key=" + window.sib.client_key, i.parentNode.insertBefore(n, i), window.sendinblue.page();
})();';
if (isset($sendinblue['properties'])) {
$tmanalytics .= "sendinblue.identify('" . $sendinblue['email'] . "', {
  'FIRSTNAME': '" . (isset($sendinblue['properties']['FIRSTNAME']) ? $sendinblue['properties']['FIRSTNAME'] : '') . "',
  'LASTNAME' : '" . (isset($sendinblue['properties']['LASTNAME']) ? $sendinblue['properties']['LASTNAME'] : '') . "',
  'id': '" . (isset($sendinblue['eventdata']['id']) ?  $sendinblue['eventdata']['id'] : '') . "',
  'LOCATION' : '" . (isset($sendinblue['properties']['LOCATION']) ? $sendinblue['properties']['LOCATION'] : '') . "',
  'COUNTRY' : '" . (isset($sendinblue['properties']['COUNTRY']) ? $sendinblue['properties']['COUNTRY'] : '') . "',
  'LANGUAGE' : '" . $tagmanager['language'] . "',
  'SMS'  : '" . (isset($sendinblue['properties']['TELEPHONE']) ? $sendinblue['properties']['TELEPHONE'] : '') . "',
  'TELEPHONE'  : '" . (isset($sendinblue['properties']['TELEPHONE']) ? $sendinblue['properties']['TELEPHONE'] : '') . "'});" ;  
}
$tmanalytics .= '</script>';
if (isset($_data['sendinblue'])) {
    switch ($page_type) {
	    case "success":
			$this->gtm->sendinbluePost($_data['sendinblue'], 'trackEvent');
	    	break;
			
		case "checkout":
			$this->gtm->sendinbluePost($_data['sendinblue'], 'trackEvent');
			break;
			
		case "cart":
			$this->gtm->sendinbluePost($_data['sendinblue'], 'trackEvent');
			break;
			
	}
}