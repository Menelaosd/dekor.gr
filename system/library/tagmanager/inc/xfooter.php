<?php /* v:5.1 */ $this->load->model('extension/module/tagmanager'); $tagmanager = $this->model_extension_module_tagmanager->getTagmanger(); if (!isset($tmanalytics)) { $tmanalytics = ''; } if (isset($tagmanager['code']) && $tagmanager['status']=='1') { $j3popup = (isset($this->request->get['popup']) ? $this->request->get['popup'] : '') ; if(substr(VERSION,0,1)=='1' ) { $this->data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home'); if ($this->data['route'] == 'journal2/quickview') { $j3popup = 'quickview'; } }

include('event_scripts.php');

if(substr(VERSION,0,1)=='1' ) { $this->data['tagmanager'] = $tagmanager; $this->data['j3popup'] = $j3popup; $this->data['tmanalytics'] = $tmanalytics; } else { $this->data['tagmanager'] = $tagmanager; $this->data['j3popup'] = $j3popup; $this->data['tmanalytics'] = $tmanalytics; } }

?>