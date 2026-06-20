<?php
class ModelToolLlmsGenerator extends Model {
    public function generateLlmsContent() {
        $this->load->language('extension/module/llms_generator');
        
        $output = "# LLMS.TXT\n\n";

        $products = $this->getProducts();
        foreach ($products as $product) {
            $output .= "- [{$product['name']}]({$product['url']}): " . $this->language->get('type_product') . "\n";
        }

        $categories = $this->getCategories();
        foreach ($categories as $category) {
            $output .= "- [{$category['name']}]({$category['url']}): " . $this->language->get('type_category') . "\n";
        }

        $manufacturers = $this->getManufacturers();
        foreach ($manufacturers as $manufacturer) {
            $output .= "- [{$manufacturer['name']}]({$manufacturer['url']}): " . $this->language->get('type_manufacturer') . "\n";
        }

        $informations = $this->getInformations();
        foreach ($informations as $information) {
            $output .= "- [{$information['title']}]({$information['url']}): " . $this->language->get('type_information') . "\n";
        }

        return $output;
    }

    public function saveLlmsFile($content) {
        $file_path = DIR_CATALOG . '../llms.txt';
        
        // Prepend UTF-8 BOM
        $content = "\xEF\xBB\xBF" . $content;
        
        $result = file_put_contents($file_path, $content);
        
        if ($result === false) {
            throw new Exception($this->language->get('error_file_write'));
        }
        
        return true;
    }

    private function getProducts() {
        $language_id = (int)$this->config->get('config_language_id');
        
        $query = $this->db->query("SELECT pd.name, p.product_id FROM " . DB_PREFIX . "product p 
            LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
            WHERE p.status = '1' AND pd.language_id = '" . $language_id . "'");
            
        $products = [];
        foreach ($query->rows as $row) {
            $url = $this->getSeoUrl('product_id=' . $row['product_id']);
            if (!empty($url)) {
                $products[] = [
                    'name' => htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'),
                    'url' => $url
                ];
            }
        }
        return $products;
    }

    private function getCategories() {
        $language_id = (int)$this->config->get('config_language_id');
        
        $query = $this->db->query("SELECT cd.name, c.category_id FROM " . DB_PREFIX . "category c 
            LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) 
            WHERE c.status = '1' AND cd.language_id = '" . $language_id . "'");
            
        $categories = [];
        foreach ($query->rows as $row) {
            $url = $this->getSeoUrl('category_id=' . $row['category_id']);
            if (!empty($url)) {
                $categories[] = [
                    'name' => htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'),
                    'url' => $url
                ];
            }
        }
        return $categories;
    }

    private function getManufacturers() {
        $query = $this->db->query("SELECT m.name, m.manufacturer_id FROM " . DB_PREFIX . "manufacturer m ORDER BY m.name");
        
        $manufacturers = [];
        foreach ($query->rows as $row) {
            $url = $this->getSeoUrl('manufacturer_id=' . $row['manufacturer_id']);
            if (!empty($url)) {
                $manufacturers[] = [
                    'name' => htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'),
                    'url' => $url
                ];
            }
        }
        return $manufacturers;
    }

    private function getInformations() {
        $language_id = (int)$this->config->get('config_language_id');
        
        $query = $this->db->query("SELECT id.title, i.information_id FROM " . DB_PREFIX . "information i 
            LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) 
            WHERE i.status = '1' AND id.language_id = '" . $language_id . "'");
            
        $informations = [];
        foreach ($query->rows as $row) {
            $url = $this->getSeoUrl('information_id=' . $row['information_id']);
            if (!empty($url)) {
                $informations[] = [
                    'title' => htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'),
                    'url' => $url
                ];
            }
        }
        return $informations;
    }

    private function getSeoUrl($query) {
        $language_id = (int)$this->config->get('config_language_id');
        $store_id = (int)$this->config->get('config_store_id');
        
        $seo_query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE query = '" . $this->db->escape($query) . "' AND store_id = '" . $store_id . "' AND language_id = '" . $language_id . "'");
        
        if ($seo_query->num_rows) {
            return ($this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG) . $seo_query->row['keyword'];
        }
        
        return '';
    }
}