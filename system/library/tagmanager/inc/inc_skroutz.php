<?php
/* v:9.6 06042022*/
$tmanalytics .= "(function(a,b,c,d,e,f,g){a['SkroutzAnalyticsObject']=e;a[e]= a[e] || function(){
    (a[e].q = a[e].q || []).push(arguments);};f=b.createElement(c);f.async=true;
    f.src=d;g=b.getElementsByTagName(c)[0];g.parentNode.insertBefore(f,g);
  })(window,document,'script','https://analytics.skroutz.gr/analytics.min.js','skroutz_analytics');
  skroutz_analytics('session', 'connect', '" . $tagmanager['skroutz_siteid'] . "'); ". "\n";
  
  if ($page_type == 'success') {
    if (isset($_data['datalayer']['skroutz_order'])) {
      $tmanalytics .= "whenAvailable(\"skroutz_analytics\", function(t) {skroutz_analytics('ecommerce', 'addOrder', " . json_encode($_data['datalayer']['skroutz_order']) . ");});";
    }
      if (isset($_data['skroutz_items'])){ 
		    foreach ($_data['skroutz_items'] as $item) { 
			    $tmanalytics .= "whenAvailable(\"skroutz_analytics\", function(t) {skroutz_analytics('ecommerce', 'addItem', " . json_encode($item) . ");});";
		    }
	    }
  }
$tmanalytics .=  "\n";
