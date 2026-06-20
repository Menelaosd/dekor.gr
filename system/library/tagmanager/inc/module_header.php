<?php /* v:9.6 06042022 */ $tagmanager = $this->gtm->settings; if (isset($tagmanager['code']) && $tagmanager['status']=='1') { $array = explode("_", basename(__FILE__, '.php')); $listname = ucfirst(end($array));

if (isset($this->request->get['route'])) { $route = $this->request->get['route']; } else { $route = ''; }

if(substr(VERSION,0,1)=='1' ) { $this->data['tagmanager'] = $tagmanager; $this->data['listname'] = $listname; $this->data['route'] = $route; } else { $data['tagmanager'] = $tagmanager; $data['listname'] = $listname; $data['route'] = $route; } } ?>