<?php
/* v:9.6 06042022*/
$tmanalytics .= '<script nitro-exclude="" src="https://www.artfut.com/static/tagtag.min.js?campaign_code=' . $tagmanager['admitad_code'] . '" 
				async onerror=\'var self = this;window.ADMITAD=window.ADMITAD||{},ADMITAD.Helpers=ADMITAD.Helpers||{},
				ADMITAD.Helpers.generateDomains=function(){for(var e=new Date,	n=Math.floor(new Date(2020,e.getMonth(),e.getDate()).setUTCHours(0,0,0,0)/1e3),
				t=parseInt(1e12*(Math.sin(n)+1)).toString(30),i=["de"],o=[],a=0;a<i.length;++a)o.push({domain:t+"."+i[a],name:t});return o},
				ADMITAD.Helpers.findTodaysDomain=function(e){function n(){	var o=new XMLHttpRequest,a=i[t].domain,D="https://"+a+"/";	o.open("HEAD",D,!0),o.onload=function(){setTimeout(e,0,i[t])},o.onerror=function(){
				++t<i.length?setTimeout(n,0):setTimeout(e,0,void 0)},o.send()}var t=0,	i=ADMITAD.Helpers.generateDomains();n()},window.ADMITAD=window.ADMITAD||{},ADMITAD.Helpers.findTodaysDomain(function(e){
				if(window.ADMITAD.dynamic=e,window.ADMITAD.dynamic){var n=function(){	return function(){return self.src?self:""}}(),t=n(),i=(/campaign_code=([^&]+)/.exec(t.src)||[])[1]||"";
				t.parentNode.removeChild(t);var o=document.getElementsByTagName("head")[0],	a=document.createElement("script");	a.src="https://www."+window.ADMITAD.dynamic.domain+"/static/"+window.ADMITAD.dynamic.name.slice(1)+
				window.ADMITAD.dynamic.name.slice(0,1)+".min.js?campaign_code="+i,o.appendChild(a)}});\'></script>';
$tmanalytics .= "<script type=\"text/javascript\" nitro-exclude=\"\">
				var cookie_name = 'deduplication_cookie';var days_to_store = 90;var deduplication_cookie_value = 'admitad';
				var channel_name = 'utm_source';getSourceParamFromUri = function () {var pattern = channel_name + '=([^&]+)';
				var re = new RegExp(pattern);return (re.exec(document.location.search) || [])[1] || '';	};getSourceCookie = function () {var matches = document.cookie.match(new RegExp('(?:^|; )' + cookie_name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'
				));	return matches ? decodeURIComponent(matches[1]) : undefined;};setSourceCookie = function () {	var param = getSourceParamFromUri();if (!param) { return; }	var period = days_to_store * 60 * 60 * 24 * 1000;	
				var expiresDate = new Date((period) + +new Date);var cookieString = cookie_name + '=' + param + '; path=/; expires=' + expiresDate.toGMTString();
				document.cookie = cookieString;	document.cookie = cookieString + '; domain=.' + location.host;};setSourceCookie();
				</script>" . "\n";
if (isset($tagmanager['email']) && !empty($tagmanager['email'])) {				
$tmanalytics .= "<script type=\"text/javascript\">  ADMITAD = window.ADMITAD || {}; ADMITAD.Invoice = ADMITAD.Invoice || {};
   ADMITAD.Invoice.accountId = '" . $tagmanager['email'] . "';  </script>";
}
?>