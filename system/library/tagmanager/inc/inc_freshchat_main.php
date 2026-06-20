<?php
/* v:9.6 06042022*/
$tmanalytics .= '<script type="text/javascript" nitro-exclude="">
  								function initFreshChat() {
   								 window.fcWidget.init({
      							token: "' . $tagmanager['freshchat_code'] . '",
      							host: "' . (!empty($tagmanager['freshchat_host']) ? $tagmanager['freshchat_host'] : 'https://wchat.freshchat.com') .'" });
  								}
  								function initialize(i,t){var e;i.getElementById(t)?initFreshChat():((e=i.createElement("script")).id=t,e.async=!0,e.src="https://wchat.freshchat.com/js/widget.js",e.onload=initFreshChat,i.head.appendChild(e))}function initiateCall(){initialize(document,"freshchat-js-sdk")}window.addEventListener?window.addEventListener("load",initiateCall,!1):window.attachEvent("load",initiateCall,!1);
								</script>';