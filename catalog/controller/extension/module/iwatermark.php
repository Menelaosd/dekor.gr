<?php

class ControllerExtensionModuleIwatermark extends Controller {
    public function getProduct(&$route, &$args, &$output) {
        if (!empty($output['product_id']) && !empty($output['image']) && $this->config->get('module_iwatermark_status')) {
            $this->load->model('extension/module/iwatermark');

            $output['image'] = $this->model_extension_module_iwatermark->imageSymlinkLinkImage($output['product_id'], $output['image'], false, false);
        }
    }

    public function getProducts(&$route, &$args, &$output) {
        if ($this->config->get('module_iwatermark_status')) {
            $this->load->model('extension/module/iwatermark');

            foreach ($output as &$product) {
                if (!empty($product['product_id']) && !empty($product['image'])) {
                    $product['image'] = $this->model_extension_module_iwatermark->imageSymlinkLinkImage($product['product_id'], $product['image'], false, false);
                }
            }
        }
    }

    public function getProductImages(&$route, &$args, &$output) {
        if ($this->config->get('module_iwatermark_status')) {
            $this->load->model('extension/module/iwatermark');
            
            foreach ($output as &$product_image) {
                if (!empty($product_image['product_id']) && !empty($product_image['image'])) {
                    $product_image['image'] = $this->model_extension_module_iwatermark->imageSymlinkLinkImage($product_image['product_id'], $product_image['image'], true, false);
                }
            }
        }
    }

    public function getProductImagesAsKeyValue(&$route, &$args, &$output) {
        if ($this->config->get('module_iwatermark_status') && !empty($output)) {
            $this->load->model('extension/module/iwatermark');
            
            foreach ($output as $product_id => &$product_image) {
                if (!empty($product_image)) {
                    $product_image = $this->model_extension_module_iwatermark->imageSymlinkLinkImage($product_id, $product_image, true, false);
                }
            }
        }
    }

    public function getPowerImages(&$route, &$args, &$output) {
        if ($this->config->get('module_iwatermark_status')) {
            $this->load->model('extension/module/iwatermark');

            foreach ($output as &$product_image) {
                if (!empty($args[0]) && !empty($product_image['image'])) {
                    $product_image['image'] = $this->model_extension_module_iwatermark->imageSymlinkLinkImage($args[0], $product_image['image'], true, false);
                }
            }
        }
    }

    public function quickPowerImage(&$route, &$args, &$output) {
        if ($this->config->get('module_iwatermark_status') && !empty($output['images'])) {
            $this->load->model('extension/module/iwatermark');

            foreach ($output['images'] as &$product_image) {
                if (!empty($args[0]) && !empty($product_image['image'])) {
                    $product_image['image'] = $this->model_extension_module_iwatermark->imageSymlinkLinkImage($args[0], $product_image['image'], true, false);
                }
            }
        }
    }
}