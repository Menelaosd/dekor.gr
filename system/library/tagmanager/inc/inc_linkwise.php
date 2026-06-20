<?php
/* v:9.6 06042022*/
if (empty($tagmanager['linkwise_decimal'])) {
    $tagmanager['linkwise_decimal'] = ',';
}
$tmanalytics .= '<script async src="//go.linkwi.se/delivery/js/tl.js"></script> <script nitro-exclude=""> window.lw=window.lw||function(){(lw.q=lw.q||[]).push(arguments)};lw .l=+new Date;
lw("setProgram", "' . $tagmanager['linkwise_code'] .'"); lw("setDecimal", "' . $tagmanager['linkwise_decimal'] . '"); </script>';

$currency_code_numeric = 0;
switch ($tagmanager['currency']) {
    case "USD":
        $currency_code_numeric = 840;
        break;    
    case "EUR":
        $currency_code_numeric = 978;
        break;    
    case "BGN":
        $currency_code_numeric = 975;
        break;    
    case "CAD":
        $currency_code_numeric = 124;
        break;    
    case "AUD":
        $currency_code_numeric = 036;
        break;    
    case "GBP":
        $currency_code_numeric = 826;
        break;    
    case "RON":
        $currency_code_numeric = 946;
        break;    
    case "RUB":
        $currency_code_numeric = 643;
        break;    
    case "TRY":
        $currency_code_numeric = 949;
        break;        
}
        
if ($page_type == "success") {
    $tmanalytics .= '<script async src="//go.linkwi.se/delivery/js/tlwt.js"></script> <script nitro-exclude=""> window.lw=window.lw||function(){(lw.q=lw.q||[]).push(arguments)};lw .l=+new Date;
        lw("setProgram", "' . $tagmanager['linkwise_code'] . '"); lw("setDecimal", "' . $tagmanager['linkwise_decimal'] . '");
        lw("setCurrency", "' . $currency_code_numeric . '");';
    if (isset($_data['linkwise'])) {
        $counter = 1;
        foreach ($_data['linkwise']['items'] as $item) {
             $tmanalytics .= 'lw("addItem", {id: "' . $item['product_id'] . '" ,price: "' . $item['price'] . '" ,quantity: "' . $item['quantity'] . '",payout: "1"});';
             $linkwise_items = '&amp;itemid[' . $counter .']=' . $item['product_id'] . '&amp;itemprice[' . $counter .']=' .  $item['price'] . '&amp;itemquantity[' . $counter . ']=' .   $item['quantity'] . '&amp;itempayout['. $counter .']=1';
             $counter++;
        }
    }
    $tmanalytics .= 'lw("thankyou", {orderid: "' . $_data['linkwise']['order']['order_id'] . '",status: "pending"});</script>';
    
    $data['linkwise_body'] = '<noscript><img src="//go.linkwi.se/delivery/acl.php?program=' . $tagmanager['linkwise_code'] .'&amp;decimal =' . $tagmanager['linkwise_decimal'] . $linkwise_items .'&amp;coupon_price=&amp;status=pending&amp;orderid=' . $_data['linkwise']['order']['order_id'] .'" style="width:0px;height:0px;"/></noscript>';
}    
