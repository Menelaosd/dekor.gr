<?php
/* v:9.6 06042022*/
$tmanalytics .= '<script type="text/javascript" nitro-exclude="">
    			window.ad_products = [';
				foreach($_data['datalayer']['ga4_items'] as $items) {
$tmanalytics .= '{"id": "' . $items['item_id'] . '", "number": "' . $items['quantity'] .'"  },';
				}
$tmanalytics .= ' ]; window._retag = window._retag || [];window._retag.push({code: "' . $tagmanager['admitad_retag_code4'] . '", level: 3});
				(function () {
                var id = "admitad-retag";
                if (document.getElementById(id)) {return;}
                var s = document.createElement("script");
                s.async = true; s.id = id;
                var r = (new Date).getDate();
                s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.lenmit.com/static/js/retag.js?r="+r;
                var a = document.getElementsByTagName("script")[0]
                a.parentNode.insertBefore(s, a);
                })()	</script>' . "\n";
?>