<?php

/**
 * Twig 3 port of Twig_Extension_DTwigManager (Dreamvention d_twig_manager).
 *
 * OpenCart 3.0.5.1 ships Twig 3, where the Twig 1 base classes used by
 * DTwigManager.php (Twig_Extension, Twig_SimpleFunction, Twig_SimpleFilter)
 * no longer exist. Same functions, filters and globals, same behaviour.
 * Registered in system/library/template/twig.php.
 */
class Twig_Extension_DTwigManager3 extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    protected $registry;
    protected $is_admin;

    /**
     * @param Registry $registry
     */
    public function __construct(\Registry $registry)
    {
        $this->registry = $registry;

        $this->is_admin = defined('DIR_CATALOG');
    }

    public function getFunctions(): array
    {
        return array(
            new \Twig\TwigFunction('link', array($this, 'linkFunction')),
            new \Twig\TwigFunction('lang', array($this, 'langFunction')),
            new \Twig\TwigFunction('config', array($this, 'configFunction')),
            new \Twig\TwigFunction('paginate', array($this, 'paginateFunction')),
            new \Twig\TwigFunction('image', array($this, 'imageFunction')),
            new \Twig\TwigFunction('asset', array($this, 'assetFunction')),
            new \Twig\TwigFunction('load', array($this, 'loadFunction')),
            new \Twig\TwigFunction('can_access', array($this, 'canAccessFunction')),
            new \Twig\TwigFunction('can_modify', array($this, 'canModifyFunction')),
            new \Twig\TwigFunction('template', array($this, 'templateFunction')),
            new \Twig\TwigFunction('content', array($this, 'contentFunction')),
        );
    }

    public function linkFunction($route = null, $args = array(), $secure = false)
    {
        $url = $this->registry->get('url');
        $session = $this->registry->get('session');
        $token = isset($session->data['token']) ? $session->data['token'] : null;

        if ($this->is_admin && $token) {
            $args['token'] = $token;
        }

        if (is_array($args)) {
            $args = http_build_query($args);
        }

        if (!empty($route)) {
            return $url->link($route, $args, $secure);
        } else if ($secure) {
            return !empty($args) ? HTTPS_SERVER . 'index.php?' . $args : HTTPS_SERVER;
        }

        return !empty($args) ? HTTP_SERVER . 'index.php?' . $args : HTTP_SERVER;
    }

    public function langFunction($key, $file = null)
    {
        $language = $this->registry->get('language');

        if ($file) {
            $language->load($file);
        }

        return $language->get($key);
    }

    public function configFunction($key, $file = null)
    {
        $config = $this->registry->get('config');

        if ($file) {
            $config->load($file);
        }

        return $config->get($key);
    }

    public function canAccessFunction($value)
    {
        $user = $this->registry->get('user');

        if ($user) {
            return $user->hasPermission('access', $value);
        }

        return false;
    }

    public function canModifyFunction($value)
    {
        $user = $this->registry->get('user');

        if ($user) {
            return $user->hasPermission('modify', $value);
        }

        return false;
    }

    public function imageFunction($filename, $context = 'product', $width = null, $height = null)
    {
        if (!is_file(DIR_IMAGE . $filename)) {
            return;
        }

        $request = $this->registry->get('request');
        $config = $this->registry->get('config');

        if (!$width) {
            $width = $config->get('config_image_' . $context . '_width');
        }

        if (!$height) {
            $height = $config->get('config_image_' . $context . '_height');
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $old_image = $filename;
        $new_image = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;

        if (!is_file(DIR_IMAGE . $new_image) || (filectime(DIR_IMAGE . $old_image) > filectime(DIR_IMAGE . $new_image))) {
            $path = '';

            $directories = explode('/', dirname(str_replace('../', '', $new_image)));

            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;

                if (!is_dir(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }

            list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $old_image);

            if ($width_orig != $width || $height_orig != $height) {
                $image = new Image(DIR_IMAGE . $old_image);
                $image->resize($width, $height);
                $image->save(DIR_IMAGE . $new_image);
            } else {
                copy(DIR_IMAGE . $old_image, DIR_IMAGE . $new_image);
            }
        }

        if ($request->server['HTTPS']) {
            return $this->is_admin ? HTTPS_CATALOG . 'image/' . $new_image : HTTPS_SERVER . 'image/' . $new_image;
        } else {
            return $this->is_admin ? HTTP_CATALOG . 'image/' . $new_image : HTTP_SERVER . 'image/' . $new_image;
        }
    }

    public function assetFunction($path)
    {
        if (!$this->is_admin) {
            if (file_exists(DIR_TEMPLATE . $this->registry->get('config')->get('config_template') . '/' . $path)) {
                return 'catalog/view/theme/' . $this->registry->get('config')->get('config_template') . '/' . $path;
            } else if (file_exists(DIR_TEMPLATE . 'default/' . $path)) {
                return 'catalog/view/theme/default/' . $path;
            }
        } else if (file_exists(DIR_TEMPLATE . '../' . $path)) {
            return 'admin/view/' . $path;
        }

        return $path;
    }

    public function loadFunction($file)
    {
        $loader = $this->registry->get('load');

        return $loader->controller($file);
    }

    public function paginateFunction($total, $limit = 5, $route = null, $args = array(), $template = null)
    {
        $request = $this->registry->get('request');
        $page = isset($request->get['page']) ? $request->get['page'] : 1;
        $secure = $request->server['HTTPS'];

        $pagination = new \Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;

        $args['page'] = '{page}';

        $pagination->url = $this->linkFunction($route, $args, $secure);

        if ($template) {
            $loader = $this->registry->get('load');

            return $loader->view($template, get_object_vars($pagination));
        } else {
            return $pagination->render();
        }
    }

    public function contentFunction($view, $extension_file = 'twig')
    {
        return file_get_contents(DIR_TEMPLATE . $this->templateFunction($view, $extension_file));
    }

    public function templateFunction($view, $extension_file = 'twig')
    {
        if (!$this->is_admin) {
            $config = $this->registry->get('config');
            if ($config->get('config_theme') == 'default') {
                $theme = $config->get('theme_default_directory');
            } else {
                $theme = $config->get('config_theme');
            }

            if (!$theme) {
                $theme = $config->get('config_template');
            }

            if (is_file(DIR_TEMPLATE . $theme . '/template/' . $view . '.' . $extension_file)) {
                $view = $theme . '/template/' . $view . '.' . $extension_file;
            } elseif (is_file(DIR_TEMPLATE . 'default/template/' . $view . '.' . $extension_file)) {
                $view = 'default/template/' . $view . '.' . $extension_file;
            } else {
                $view = 'default/template/partial/d_empty.twig';
            }
        } else {
            $view .= $extension_file;
        }
        return $view;
    }

    public function getFilters(): array
    {
        return array(
            new \Twig\TwigFilter('money', array($this, 'moneyFilter')),
            new \Twig\TwigFilter('tax', array($this, 'taxFilter')),
            new \Twig\TwigFilter('len', array($this, 'lenFilter')),
            new \Twig\TwigFilter('wei', array($this, 'weiFilter')),
            new \Twig\TwigFilter('truncate', array($this, 'truncateFilter')),
            new \Twig\TwigFilter('encrypt', array($this, 'encryptFilter')),
            new \Twig\TwigFilter('decrypt', array($this, 'decryptFilter')),
        );
    }

    public function moneyFilter($number, $currency = '', $value = '', $format = true)
    {
        $lib = $this->registry->get('currency');

        return $lib->format($number, $currency, $value, $format);
    }

    public function taxFilter($value, $tax_class_id, $calculate = true)
    {
        $tax = $this->registry->get('tax');

        return $tax->calculate($value, $tax_class_id, $calculate);
    }

    public function lenFilter($value, $length_class_id, $decimal_point = '.', $thousand_point = ',')
    {
        $length = $this->registry->get('length');

        return $length->format($value, $length_class_id, $decimal_point, $thousand_point);
    }

    public function weiFilter($value, $weight_class_id, $decimal_point = '.', $thousand_point = ',')
    {
        $weight = $this->registry->get('weight');

        return $weight->format($value, $weight_class_id, $decimal_point, $thousand_point);
    }

    public function truncateFilter($value, $end = '...', $limit = null)
    {
        $config = $this->registry->get('config');

        if (!$limit) {
            $limit = $config->get('config_product_description_length');
        }

        $str = strip_tags(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));

        if (strlen($str) > $limit) {
            $str = utf8_substr($str, 0, $limit) . $end;
        }

        return $str;
    }

    public function encryptFilter($value)
    {
        $config = $this->registry->get('config');

        $encription = new \Encryption($config->get('config_encription'));

        return $encription->encrypt($value);
    }

    public function decryptFilter($value)
    {
        $config = $this->registry->get('config');

        $encription = new \Encryption($config->get('config_encription'));

        return $encription->decrypt($value);
    }

    public function getGlobals(): array
    {
        $document = $this->registry->get('document');

        $globals = array(
            'document_title'       => $document->getTitle(),
            'document_description' => $document->getDescription(),
            'document_keywords'    => $document->getKeywords(),
            'document_links'       => $document->getLinks(),
            'document_styles'      => $document->getStyles(),
            'document_scripts'     => $document->getScripts(),
            'route'                => isset($this->registry->get('request')->get['route']) ? $this->registry->get('request') : '',
        );

        if ($this->is_admin) {
            $user = $this->registry->get('user');
            $globals['user'] = $user;
            $globals['is_logged'] = $user->isLogged();
        } else {
            $customer = $this->registry->get('customer');
            $globals['customer'] = $customer;
            $globals['is_logged'] = $customer->isLogged();

            $globals['cart'] = $this->registry->get('cart');
        }

        return $globals;
    }
}
