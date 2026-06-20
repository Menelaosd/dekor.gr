<?php
/* v:9.6 06042022*/
if (isset($this->request->get['path'])) {
    $path = '';
	$parts = explode('_', (string)$this->request->get['path']);
	$category_id = (int)array_pop($parts);
	$tmanalytics .= '<script type="text/javascript" nitro-exclude="">
				window.ad_category = "' . (isset($category_id) ? $category_id : '') . '"; 
				window._retag = window._retag || [];window._retag.push({code: "' . $tagmanager['admitad_retag_code2'] . '", level: 1});
				(function () {
                var id = "admitad-retag";
                if (document.getElementById(id)) {return;}
                var s=document.createElement("script");
                s.async = true; s.id = id;
                var r = (new Date).getDate();
                s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.lenmit.com/static/js/retag.js?r="+r;
                var a = document.getElementsByTagName("script")[0]
                a.parentNode.insertBefore(s, a);
                })()</script>'."\n";
}	

?>