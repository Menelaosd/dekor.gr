<?php
class ControllerExtensionShippingXshippingpro extends Controller {
    public function onOrderEmail($route, &$data) {
        $shipping_xshippingpro_desc_mail=$this->config->get('shipping_xshippingpro_desc_mail');
        if($shipping_xshippingpro_desc_mail) {
            $order_info = $this->model_checkout_order->getOrder($data['order_id']);
            $language_id = $order_info['language_id'];
            if (strpos($order_info['shipping_code'], 'xshippingpro') !== false) {
                $tab_id = str_replace('xshippingpro.xshippingpro', '', $order_info['shipping_code']);
                $method = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xshippingpro` WHERE tab_id='".(int)$tab_id."'")->row;
                
                $xshippingpro = $method['method_data'];
                $xshippingpro = @unserialize(@base64_decode($xshippingpro));
                if (!is_array($xshippingpro)) $xshippingpro = array();
                if (!isset($xshippingpro['desc'])) $xshippingpro['desc']=array();

                $data['shipping_method'].= (isset($xshippingpro['desc'][$language_id]) && $xshippingpro['desc'][$language_id]) ? '<br /><span style="color: #999999;font-size: 11px;display:block" class="x-shipping-desc">'.$xshippingpro['desc'][$language_id].'</span>' : '';
            }
        }
    }
    
    public function estimate_shipping() {
        $json=array();
        $this->load->model('extension/shipping/xshippingpro');
        $this->load->language('extension/shipping/xshippingpro');
        
        $xshippingpro_estimator =  $this->config->get('shipping_xshippingpro_estimator');
        $estimator_type = (isset($xshippingpro_estimator['type']) && $xshippingpro_estimator['type']) ? $xshippingpro_estimator['type'] : 'method';
        $address = array();
        if ($estimator_type == 'avail') {
            $address = array('only_address_rule' => true);
        }
        
        $json =  $this->model_extension_shipping_xshippingpro->getQuote($address);
        if ($estimator_type == 'avail') {
            if ($json) {
                $json = array();
                $json['message'] = $this->language->get('xshippingpro_available');
                $json['class'] = 'avail';
            } else {
                $json = array();
                $json['message'] = $this->language->get('xshippingpro_no_available');
                $json['class'] = 'no_avail';
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
