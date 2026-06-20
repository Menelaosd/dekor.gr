<?php
/* v:9.6 06042022*/
$tmanalytics .= '<script type="text/javascript" nitro-exclude="">
    	window._retag = window._retag || [];window._retag.push({code: "' . $tagmanager['admitad_retag_code1'] . '", level: 0});
    	(function () {
        var id = "admitad-retag";
        if (document.getElementById(id)) {return;}
        var s = document.createElement("script");
        s.async = true; s.id = id;
        var r = (new Date).getDate();
        s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.lenmit.com/static/js/retag.js?r="+r;
        var a = document.getElementsByTagName("script")[0]
        a.parentNode.insertBefore(s, a);
    })()</script>' . "\n";
        								?>