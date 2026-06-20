<?php
/* v:9.6 06042022*/
$tmanalytics .= '<script type="text/javascript" nitro-exclude="">
	window.ad_product = {';
	foreach($_data['datalayer']['ga4_items'] as $items) {
    	$tmanalytics .= '"id": "' . $items['item_id'] . '","vendor": "' . $items['item_brand'] .'","price": "' . $items['price'] . '","url": "' . (isset($items['item_url']) ? $items['item_url'] : '') . '","picture": "' . (isset($items['item_image']) ? $items['item_image'] : '') . '","name": "' . $items['item_name'] . '","category": "' . $items['item_list_id']. '"';
    }
    $tmanalytics .= '}; window._retag = window._retag || [];
    	window._retag.push({code: "' . $tagmanager['admitad_retag_code3'] . '", level: 2});
    	(function () {
        var id = "admitad-retag";
        if (document.getElementById(id)) {return;}
        var s = document.createElement("script");
        s.async = true; s.id = id;
        var r = (new Date).getDate();
        s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.lenmit.com/static/js/retag.js?r="+r;
        var a = document.getElementsByTagName("script")[0]
        a.parentNode.insertBefore(s, a);
        })()
		</script>' ."\n";
?>