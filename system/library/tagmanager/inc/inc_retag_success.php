<?php
/* v:9.6 06042022*/
$tmanalytics .= '<script type="text/javascript">
										window.ad_order = "' . $_data['datalayer']['order_id'] .'"; 
    									window.ad_amount = "' . $_data['datalayer']['order_total'] .'";
    									window.ad_products = [';
    									
    					foreach($_data['datalayer']['ga4_items'] as $items) {
    						$tmanalytics .= '{"id": "' . $items['item_id'] . '", "number": "' . $items['quantity'] .'"  },';
    					}
    					$tmanalytics .= ' ];  window._retag = window._retag || [];	window._retag.push({code: "' . $tagmanager['admitad_retag_code5'] . '", level: 4});
    									(function () {
                                        var id = "admitad-retag";
                                        if (document.getElementById(id)) {return;}
                                        var s = document.createElement("script");
                                        s.async = true; s.id = id;
                                        var r = (new Date).getDate();
                                        s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.lenmit.com/static/js/retag.js?r="+r;
                                        var a = document.getElementsByTagName("script")[0]
                                        a.parentNode.insertBefore(s, a);
                                        })()	</script>'. "\n";
?>