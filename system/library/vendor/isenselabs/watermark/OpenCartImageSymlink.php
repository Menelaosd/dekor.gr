<?php

if (!class_exists('OpenCartImageSymlink')) {
    class OpenCartImageSymlink {
        private $group_size = 1000;
        private $group_separator = '-';
        private $group_base = 'cache';
        private $ds = DIRECTORY_SEPARATOR;
        private $mode = 0755;
        private $main_dir = 'main';
        private $additional_dir = 'additional';
        private $store_ids = array('0');
        private $language_ids = array();
        private $registry;

        public function __construct($registry) {
            $this->registry = $registry;

            $store_query = $this->db->query("SELECT store_id FROM `" . DB_PREFIX . "store`");

            foreach ($store_query->rows as $store_row) {
                $this->store_ids[] = $store_row['store_id'];
            }

            $language_query = $this->db->query("SELECT language_id FROM `" . DB_PREFIX . "language`");

            foreach ($language_query->rows as $language_row) {
                $this->language_ids[] = $language_row['language_id'];
            }
        }

        public function __get($key) {
            return $this->registry->get($key);
        }

        public function __set($key, $value) {
            $this->registry->set($key, $value);
        }

        public function getGroupBase() {
            return $this->group_base;
        }

        public function setGroupBase($new_base) {
            $this->group_base = $new_base;
        }

        public function getGroupDir() {
            return $this->setupGroupBase();
        }

        public function deleteProductDir($product_id) {
            if (false !== $product_dir = $this->setupProductDir($product_id)) {
                return $this->deleteDir($product_dir);
            }

            return false;
        }

        /*
            This method is used in the events after addProduct and editProduct. It deletes all existing symlinks and initializes generation of new symlinks with new mtimes. This forces OpenCart's ModelToolImage::resize to create a new image/cache instance.
        */

        public function update($product_id) {
            // Unfortunately UNIX does not allow us to simply touch existing links. Therefore, to preserve the original mtime, we must delete all product-related symlinks
            $this->deleteProductDir($product_id);

            // The product has been created/updated, so we want to create symlinks for its images.
            $main_info = $this->db->query("SELECT image FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'")->row;

            $this->linkImage($product_id, !empty($main_info['image']) ? $main_info['image'] : '', false);

            $additional_infos = $this->db->query("SELECT image FROM `" . DB_PREFIX . "product_image` WHERE product_id = '" . (int)$product_id . "'")->rows;

            foreach ($additional_infos as $additional_info) {
                $this->linkImage($product_id, !empty($additional_info['image']) ? $additional_info['image'] : '', true);
            }
        }

        /*
            This method is called by the update() method, and also individually for every getProduct or getProductImages from the catalog. It prepares the product-specific symlink container directory. Afterwards, it triggers permuteLanguageStore() and returns the result only for the current language and store.
        */

        public function linkImage($product_id, $original, $is_additional = false, $with_prefix = true) {
            // No point in doing anything if $original is not readable or if it does not exist...
            $prefix = $this->getPrefix();
            $original_path = $prefix . $original;

            if (is_file($original_path) && is_readable($original_path) && (false !== $product_dir = $this->setupProductDir($product_id))) {
                // Next, we set up the dirs for the main image or additional images and then create symlinks.
                $dir_by_type = $is_additional ? $this->additional_dir : $this->main_dir;

                if (false !== $target_dir = $this->setupDir($product_dir . $this->ds . $dir_by_type)) {
                    if (false !== $target_path = $this->permuteLanguageStore($original_path, $target_dir, $product_id)) {
                        return $with_prefix ? $target_path : substr($target_path, strlen($prefix));
                    }
                }
            }

            return false;
        }

        /*
            Here is where the actual symlinks are getting created. Mind that they are made on the basis of:

            $target_dir exists and it is writable - ensured by linkImage()
            $original_path exists and it is writable- ensured by linkImage()

            ===

            The symlink filename is a combination of:

            $original_filename;

            filemtime($original_path) - in case anyone touches the original file, or replaces it with another image, this must cause a different symlink;

            We are taking the md5 substring because we are only generating a unique ID based on the original image path and mtime.

            $store_id + $language_id - they are used to uniquely identify a symlinked image with a specific store-language pair. This ensures that older versions of LabelMaker and WaterMark will be able to apply different labels depending on store and language.
        */

        private function makeSymlink($store_id, $language_id, $original_path, $target_dir, $product_id) {
            // At this point we are certain $target_dir exists and that $original_path also exists.
            $target_extension =  '.' . pathinfo($original_path, PATHINFO_EXTENSION);
            $original_filename = pathinfo($original_path, PATHINFO_FILENAME);
            $md5_substr = substr(md5($original_path . filemtime($original_path) . $this->getProductDateModified($product_id)), 0, 4);
            $target_basename = $md5_substr . '-' . $original_filename . '-' . $store_id . '-' . $language_id . $target_extension;

            $target_path = str_replace('\\', '/', $target_dir . $this->ds . $target_basename);

            // If the link does not exist, create it. Otherwise, just return it.
            if (!file_exists($target_path)) {
                if ($this->noSymlinkOrCopy($original_path, $target_path)) {
                    if ($this->config->get('config_error_log')) {
                        $this->log->write("[OpenCartImageSymlink]: Could not create symlink/copy of " . $original_path . " to: " . $target_path);
                    }
                } else {
                    return $target_path;
                }
            } else {
                return $target_path;
            }

            return false;
        }

        /*
            This method returns the date_modified value for the product
        */

        private function getProductDateModified($product_id) {
            return $this->registry->get('db')->query("SELECT date_modified FROM `" . DB_PREFIX . "product` WHERE product_id='" . (int)$product_id. "'")->row['date_modified'];
        }

        /* 
            This method tries to first create a symlink. If it does not work, it falls back to copy.
        */

        private function noSymlinkOrCopy($original_path, $target_path) {
            if (function_exists('symlink')) {
                return !@symlink($original_path, $target_path) && !@copy($original_path, $target_path);
            } else {
                return !@copy($original_path, $target_path);
            }
        }

        /*
            This method iterates through all existing stores and languages and generates a symlinked version of the main image for every store. It also returns the symlinked image for the current store and language.
        */

        private function permuteLanguageStore($original_path, $target_dir, $product_id) {
            $result = array();

            foreach ($this->store_ids as $store_id) {
                foreach ($this->language_ids as $language_id) {
                    $result[$store_id][$language_id] = $this->makeSymlink($store_id, $language_id, $original_path, $target_dir, $product_id);
                }
            }

            $current_language_id = $this->config->has('config_language_id') ? $this->config->get('config_language_id') : $this->language_ids[0];
            $current_store_id = $this->config->has('config_store_id') ? $this->config->get('config_store_id') : $this->store_ids[0];

            // Assuming that config_store_id and config_language_id are always set and take one of the values of $this->store_ids and $this->language_ids.
            return $result[$current_store_id][$current_language_id];
        }

        private function setupDir($dir) {
            clearstatcache(true);

            if (@is_dir($dir)) {
                if (!@is_writable($dir) && !(@chmod($dir, $this->mode) && @is_writable($dir))) {
                    if ($this->config->get('config_error_log')) {
                        $this->log->write("[OpenCartImageSymlink]: Directory " . $dir . " exists, but it is not writable. chmod to " . $this->mode . " also failed.");
                    }

                    return false;
                }
            } else if (false === @mkdir($dir, $this->mode)) {
                if ($this->config->get('config_error_log')) {
                    $this->log->write("[OpenCartImageSymlink]: Could not create directory: " . $dir);
                }

                return false;
            }

            return realpath($dir);
        }

        private function getPrefix() {
            return realpath(DIR_IMAGE) . $this->ds;
        }

        private function setupGroupBase() {
            return $this->setupDir($this->getPrefix() . $this->group_base);
        }

        private function setupGroupDir($product_id) {
            if (false !== $group_base = $this->setupGroupBase()) {
                $group = (int)((int)$product_id / $this->group_size);

                $begin_id = $group * $this->group_size + 1;
                
                $end_id = ($group + 1) * $this->group_size;

                $dir_name = $begin_id . $this->group_separator . $end_id;

                return $this->setupDir($group_base . $this->ds . $dir_name);
            }

            return false;
        }

        private function setupProductDir($product_id) {
            if (false !== $group_dir = $this->setupGroupDir($product_id)) {
                return $this->setupDir($group_dir . $this->ds . $product_id);
            }

            return false;
        }

        private function deleteDir($dir) {
            clearstatcache(true);

            $handle = opendir($dir);

            while (false !== ($entry = readdir($handle))) {
                if (in_array($entry, array('.', '..'))) {
                    continue;
                }

                $item = $dir . $this->ds . $entry;

                if (is_dir($item)) {
                    $this->deleteDir($item);
                } else {
                    @unlink($item);
                }
            }

            closedir($handle);

            if (false === @rmdir($dir)) {
                if ($this->config->get('config_error_log')) {
                    $this->log->write("[OpenCartImageSymlink]: Could not delete directory: " . $dir);
                }

                return false;
            }

            return true;
        }

        
    }
}