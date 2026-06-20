<?php
/******************************************************
 * @package Google Tag Manager for OC1.5x, OC2x,3x
 * @version 9.6
 * @author Muhammad Akram
 * @link https://aits.xyz
 * @copyright Copyright (C)2021 aits.xyz All rights reserved.
 * @email:info@aits.pk.
 * $date: 12 APRIL 2022
 *******************************************************/
class Tagmanager extends Controller
{

    public $settings;
    private $tagmanager_data;
    private $error = array();
    public $PREFIX;

    public function __construct($registry)
    {
        parent::__construct($registry);
        if (substr(VERSION, 0, 1) == '3')
        {
            $PREFIX = 'analytics_';
        }
        else
        {
            $PREFIX = '';
        }
        $this->PREFIX = $PREFIX;
        $tagmanager_data = $this
            ->config
            ->get($PREFIX . 'tagmanager_data');
        $tagmanager_data['status'] = $this
            ->config
            ->get($PREFIX . 'tagmanager_status');
        $tagmanager_data['currency'] = (isset($this
            ->session
            ->data['currency']) ? $this
            ->session
            ->data['currency'] : $this
            ->config
            ->get('config_currency'));
        if (empty($tagmanager_data['alt_currency']) || $tagmanager_data['alt_currency_status'] != '1')
        {
            $tagmanager_data['alt_currency'] = $tagmanager_data['currency'];
        }
        $this->tagmanager_data = $tagmanager_data;
        $this->mode = (isset($tagmanager_data['mode']) ? $tagmanager_data['mode'] : true);
        $this->settings = $tagmanager_data;
    }

    public function config()
    {

        $ver = 'OC ' . VERSION . ' - 9.6';
        $tagmanager = array();
        $tagmanager_data = $this->tagmanager_data;

        if (!isset($tagmanager_data['status']) || !$tagmanager_data['status'])
        {
            return false;
        }

        $limit = 10;
        $max_items = 10;
        $delay = 5000;
        $store_id = $this
            ->config
            ->get('config_store_id');
        $fbc = '';
        $fbp = '';
        $manual_tax = 0;
        $manual_tax_status = false;
        $linkwise_tax = 0;
        $country_id = $this
            ->config
            ->get('config_country_id');
        $store_country = $this->getCountry($country_id);
        $store_country = $store_country['iso_code_2'];
        $user_agent = $this->getHttpUserAgent();
        $ip_address = $this->getIpAddress();
        $bot = $this->botDetect();
        $host_server = $this->getHost();
        $cid = $this->getTrackingCookies();

        $tagmanager_data['vs'] = $this->getVS();

        $order_total_plus = array(
            'cod_fee',
            'handling',
            'klarna_fee',
            'low_order_fee',
            'advancedcodfee',
            'xfeepro'
        );
        $order_total_minus = array(
            'credit',
            'reward',
            'voucher',
            'payment_discount'
        );

        if (isset($tagmanager_data['skroutz_status']) && $tagmanager_data['skroutz_status'] && $tagmanager_data['skroutz_manual_tax'])
        {
            $manual_tax = (int)$tagmanager_data['skroutz_manual_tax_value'];
            $manual_tax_status = $tagmanager_data['skroutz_manual_tax'];
            if (isset($manual_tax) && $manual_tax > 0)
            {
                $manual_tax = 1 + ($manual_tax / 100);
            }
        }
        if (isset($tagmanager_data['linkwise_status']) && $tagmanager_data['linkwise_status'])
        {
            $linkwise_tax = 1 + (24 / 100);
        }

        $tagmanager_data['linkwise_tax'] = $linkwise_tax;

        if (isset($tagmanager_data['performant_status']) && $tagmanager_data['performant_status'] && $tagmanager_data['performant_tax'])
        {
            $performant_tax = (int)$tagmanager_data['performant_tax_value'];
            $performant_tax = 1 + ($performant_tax / 100);
            $tagmanager_data['performant_tax_override'] = $tagmanager_data['performant_tax'];
            $tagmanager_data['performant_tax'] = $performant_tax;
        }
        else
        {
            $tagmanager_data['performant_tax_override'] = $tagmanager_data['performant_tax'];
            $tagmanager_data['performant_tax'] = $tagmanager_data['performant_tax_value'];
        }

        if ($tagmanager_data['pixel'])
        {
            $fbc = $this->getFbc();
            $fbp = $this->getFbp();
        }

        $external_id = $this->readGTMCookie('OCSESSID');

        if (empty($external_id))
        {
            $external_id = $this
                ->session
                ->getId();
        }
        $tagmanager_customer_data = $this->getUser();
        $tagmanager_data = array_merge($tagmanager_data, $tagmanager_customer_data);

        $tagmanager_config = array(
            'external_id' => $external_id,
            'ver' => $ver,
            'cid' => $cid,
            'user_agent' => $user_agent,
            'bot' => $bot,
            'ip_address' => $ip_address,
            'fbc' => $fbc,
            'fbp' => $fbp,
            'language' => (isset($_COOKIE['language']) ? $_COOKIE['language'] : '') ,
            'host_url' => $host_server,
            'host' => (isset($this
                ->request
                ->server['SERVER_NAME']) ? $this
                ->request
                ->server['SERVER_NAME'] : '') ,
            'path' => (isset($this
                ->request
                ->server['REQUEST_URI']) ? $this
                ->request
                ->server['REQUEST_URI'] : '') ,
            'currency' => (isset($this
                ->session
                ->data['currency']) ? $this
                ->session
                ->data['currency'] : $this
                ->config
                ->get('config_currency')) ,
            'total_plus' => $order_total_plus,
            'total_minus' => $order_total_minus,
            'tax' => $manual_tax,
            'override_tax' => $manual_tax_status,
            'limit' => $limit,
            'max_list_items' => $max_items,
            'max_module_items' => $max_items,
            'delay' => $delay,
            'return_status' => array(
                '7',
                '11'
            ) ,
            'store_country' => $store_country,
            'cdn' => 'cdn.aits.xyz',
            'url' => $this->getRequestUri() ,
        );

        $tagmanager = array_merge($tagmanager_data, $tagmanager_config);

        if ($bot)
        {
            $tagmanager['status'] = 0;
        }

        return $tagmanager;
    }

    public function isActive()
    {
        $data = $this->settings;
        if (isset($data['status']) && $data['status'])
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getTagmanger()
    {
        return $this->config();
    }

    public function getVS()
    {
        $vs = $this->getNewURL();
        return base64_encode($vs);
    }

    private function getTrackingCookies()
    {
        $cid = (isset($_COOKIE['_ga']) ? $_COOKIE['_ga'] : '');
        $cid = preg_replace('/GA[0-9]+\.[0-9]+\./', '', $cid);
        return $cid;
    }

    public function eventid()
    {

        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data) , 4));

    }

    public function tagmangerPmap($model = '', $sku = '', $product_id = '')
    {
        $tagmanager = $this->settings;
        $pmap = $tagmanager['pmap'];

        $curr = $this
            ->config
            ->get('config_currency');
        $supported_currencies = array(
            'GBP',
            'USD',
            'EUR',
            'AUD',
            'BRL',
            'CZK',
            'JPY',
            'CHF',
            'CAD',
            'DKK',
            'INR',
            'MXN',
            'NOK',
            'PLN',
            'RUB',
            'SEK',
            'TRY'
        );

        if (!in_array($curr, $supported_currencies))
        {
            $curr = 'GBP';
        }

        if ($curr == 'GBP')
        {
            $currency = 'gb';
        }
        elseif ($curr == 'USD')
        {
            $currency = 'us';
        }
        elseif ($curr == 'AUD')
        {
            $currency = 'au';
        }
        elseif ($curr == 'CAD')
        {
            $currency = 'ca';
        }
        elseif ($curr == 'CHF')
        {
            $currency = 'ch';
        }
        elseif ($curr == 'MXN')
        {
            $currency = 'mx';
        }
        elseif ($curr == 'INR')
        {
            $currency = 'in';
        }

        if ($pmap == 'product_id')
        {
            $pid = $product_id;
        }
        elseif ($pmap == 'model')
        {
            $pid = $model;
        }
        elseif ($pmap == 'sku')
        {
            $pid = $sku;
        }
        elseif ($pmap == 'model_product_id')
        {
            $pid = $model . '_' . $product_id;
        }
        elseif ($pmap == 'product_id_currency')
        {
            $pid = $product_id . '_' . $currency;
        }
        elseif ($pmap == 'product_id_language')
        {
            $pid = $product_id . '_' . $this
                ->config
                ->get('config_language');
        }
        else
        {
            $pid = $product_id;
        }

        if (isset($tagmanager['id_prefix']) && !empty($tagmanager['id_prefix']))
        {
            $pid = trim($tagmanager['id_prefix']) . $pid;
        }

        if (isset($tagmanager['id_suffix']) && !empty($tagmanager['id_suffix']))
        {
            $pid = $pid . trim($tagmanager['id_suffix']);
        }

        return (string)$pid;
    }

    public function tagmangerPtitle($name = '', $brand = '', $model = '', $product_id = '')
    {
        $tagmanager = $this->settings;
        $ptitle = $tagmanager['ptitle'];

        if ($ptitle == 'name')
        {
            $ptitle = $name;
        }
        elseif ($ptitle == 'brand_model')
        {
            $ptitle = $brand . ' ' . $model;
        }
        else
        {
            $ptitle = $name;
        }

        $ptitle = $this->cleanStr($ptitle);
        return $ptitle;
    }

    public function getProductGTIN($product_id)
    {
        $tagmanager = $this->settings;
        if (isset($product_id) && !empty($product_id))
        {

            $data = false;

            if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
            {
                $data = $this
                    ->cache
                    ->get('tagmanager.gtin.' . $product_id);
            }

            if (!$data)
            {
                $data = array();
                $query = $this
                    ->db
                    ->query("SELECT ean FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "' LIMIT 1 ");

                if ($query->num_rows == 1)
                {
                    $data = (isset($query->row['ean']) ? $query->row['ean'] : '');
                }

                if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
                {
                    $this
                        ->cache
                        ->set('tagmanager.gtin.' . $product_id, $data);
                }
            }

            return $data;
        }
    }

    public function getProductSKU($product_id)
    {
        $tagmanager = $this->settings;
        if (isset($product_id) && !empty($product_id))
        {

            $data = false;

            if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
            {
                $data = $this
                    ->cache
                    ->get('tagmanager.sku.' . $product_id);
            }

            if (!$data)
            {
                $data = array();
                $query = $this
                    ->db
                    ->query("SELECT sku FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "' LIMIT 1 ");

                if ($query->num_rows == 1)
                {
                    $data = (isset($query->row['sku']) ? $query->row['sku'] : '');
                }

                if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
                {
                    $this
                        ->cache
                        ->set('tagmanager.sku.' . $product_id, $data);
                }
            }

            return $data;
        }
    }

    public function getProductCatName($product_id)
    {

        $tagmanager = $this->settings;

        if (isset($product_id) && empty($product_id))
        {
            return false;
        }

        $data = false;
        $cat_level = array();
        $item_list_id = '';
        $item_list_name = '';
        $cat = '';
        $i = 1;
        $path = '';
        $category_info = false;

        if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
        {
            $data = $this
                ->cache
                ->get('tagmanager.catdata.' . $product_id);
        }

        if ($data)
        {
            return $data;
        }

        $data = array();

        $query = $this
            ->db
            ->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' ORDER BY category_id DESC LIMIT 1 ");

        if ($query->num_rows == 1)
        {
            $return_data = $this->getparent($query->row['category_id']);
            $return_data = array_reverse($return_data);
            $cat_level = $return_data;

            $error_check = $this->check_array($return_data);

            if (isset($return_data) && $error_check)
            {

                foreach ($return_data as $result)
                {
                    if ($i > 1)
                    {
                        $cat .= ' > ';
                    }
                    $cat .= $result['name'];
                    $catid = $query->row['category_id'];
                    $i++;
                }

                $cat_data = $this->cleanStr($cat);
                $item_list_id = $query->row['category_id'];
                $item_list_name = $this->cleanStr($cat);
                $i = 1;

                if (isset($cat_level))
                {
                    foreach ($cat_level as $result)
                    {

                        if ($i == 1)
                        {
                            $item_category = $this->cleanStr($result['name']);
                        }
                        else
                        {
                            $
                            {
                                'item_category' . $i
                            } = $this->cleanStr($result['name']);
                        }

                        $i++;

                        if ($i > 6)
                        {
                            break;
                        }
                    }
                }
            }
        }
        $data = array(
            'category' => (isset($cat_data) ? $cat_data : '') ,
            'item_list_id' => (isset($category_info['category_id']) ? $category_info['category_id'] : $item_list_id) ,
            'item_list_name' => (isset($category_info['name']) ? $category_info['name'] : $item_list_name) ,
            'item_category' => (isset($item_category) ? $item_category : '') ,
            'item_category2' => (isset($item_category2) ? $item_category2 : '') ,
            'item_category3' => (isset($item_category3) ? $item_category3 : '') ,
            'item_category4' => (isset($item_category4) ? $item_category4 : '') ,
            'item_category5' => (isset($item_category5) ? $item_category5 : '') ,
        );

        if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
        {
            $this
                ->cache
                ->set('tagmanager.catdata.' . $product_id, $data);
        }
        return $data;
    }

    public function getProductCatID($product_id)
    {
        $tagmanager = $this->settings;
        if (isset($product_id) && !empty($product_id))
        {
            $cat_data = false;
            if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
            {
                $cat_data = $this
                    ->cache
                    ->get('tagmanager.cat.' . $product_id);
            }
            if (!$cat_data)
            {

                $query = $this
                    ->db
                    ->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' ORDER BY category_id DESC LIMIT 1 ");

                if ($query->num_rows == 1)
                {
                    $cat_data = $query->row['category_id'];
                }
                else
                {
                    $cat_data = 0;
                }
            }
            if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
            {
                $this
                    ->cache
                    ->set('tagmanager.cat.' . $product_id, $cat_data);
            }
            return $cat_data;
        }
    }

    public function getparent($cid)
    {
        $tagmanager = $this->settings;
        $data = false;
        if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
        {
            $data = $this
                ->cache
                ->get('tagmanager.parent.' . $cid);
        }
        if (!$data)
        {
            $data = array();
            $temp = $this
                ->db
                ->query("SELECT c.category_id, cd1.name AS name, c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (c.category_id = cd1.category_id)  WHERE cd1.language_id = '" . (int)$this
                ->config
                ->get('config_language_id') . "' AND c.category_id = '" . (int)$cid . "'");

            if ($temp->num_rows == 1)
            {
                $data[] = $temp->row;

                if ($temp->row['parent_id'] != 0)
                {
                    $data = array_merge($data, $this->getparent($temp->row['parent_id']));
                }
            }
            if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
            {
                $this
                    ->cache
                    ->set('tagmanager.parent.' . $cid, $data);
            }
        }
        return $data;
    }

    public function getProductBrandName($product_id)
    {
        $tagmanager = $this->settings;
        $brand_data = '';
        if (isset($product_id) && !empty($product_id))
        {
            if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
            {
                $brand_data = $this
                    ->cache
                    ->get('tagmanager.brand.' . $product_id);
            }
            if (!$brand_data)
            {
                $query = $this
                    ->db
                    ->query("SELECT m.name from " . DB_PREFIX . "manufacturer m left join " . DB_PREFIX . "product p on m.manufacturer_id = p.manufacturer_id  WHERE p.product_id = " . $product_id);
                if (isset($query->row['name']))
                {
                    $brand = $query->row['name'];
                }
                else
                {
                    $brand = '';
                }
                $brand_data = $this->cleanStr($brand);
                if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
                {
                    $this
                        ->cache
                        ->set('tagmanager.brand.' . $product_id, $brand_data);
                }
            }
            return $brand_data;
        }
    }

    public function getProductImages($product_id)
    {
        $query = $this
            ->db
            ->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC LIMIT 1");

        return $query->rows;
    }

    public function getUser()
    {

        $tagmanager = $this->settings;

        $store_cookie = false;
        $customer_data = array();

        $tagmanager_customer_data = array(
            'userid' => '',
            'useremail' => '',
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'phone' => '',
            'city' => '',
            'region' => '',
            'country' => '',
            'country_code' => '',
            'newsletter' => '',
            'em' => '',
            'ph' => '',
            'fn' => '',
            'ln' => '',
        );

        if (isset($tagmanager['customer_data']) && $tagmanager['customer_data'])
        {
            if ($this
                ->customer
                ->isLogged() || isset($this
                ->session
                ->data['guest']))
            {
                $customer_data = $this->getCustomerData();
                $tagmanager_customer_data = array(
                    'userid' => (isset($customer_data['userid']) ? $customer_data['userid'] : '') ,
                    'useremail' => (isset($customer_data['em']) ? $customer_data['em'] : '') ,
                    'email' => (isset($customer_data['email']) ? $customer_data['email'] : '') ,
                    'first_name' => (isset($customer_data['firstname']) ? $customer_data['firstname'] : '') ,
                    'last_name' => (isset($customer_data['lastname']) ? $customer_data['lastname'] : '') ,
                    'phone' => (isset($customer_data['telephone']) ? $customer_data['telephone'] : '') ,
                    'city' => (isset($customer_data['city']) ? $customer_data['city'] : '') ,
                    'region' => (isset($customer_data['zone']) ? $customer_data['zone'] : '') ,
                    'postcode' => (isset($customer_data['postcode']) ? $customer_data['postcode'] : '') ,
                    'country' => (isset($customer_data['country']) ? $customer_data['country'] : '') ,
                    'country_code' => (isset($customer_data['country_code']) ? $customer_data['country_code'] : '') ,
                    'newsletter' => (isset($customer_data['newsletter']) ? $customer_data['newsletter'] : '') ,
                    'em' => (isset($customer_data['email']) ? $this->getHash($customer_data['email']) : '') ,
                    'ph' => (isset($customer_data['telephone']) ? $this->getHash($customer_data['telephone']) : '') ,
                    'fn' => (isset($customer_data['firstname']) ? $this->getHash($customer_data['firstname']) : '') ,
                    'ln' => (isset($customer_data['lastname']) ? $this->getHash($customer_data['lastname']) : '') ,
                    'ct' => (isset($customer_data['city']) ? $this->getHash($customer_data['city']) : '') ,
                    'pc' => (isset($customer_data['postcode']) ? $this->getHash($customer_data['postcode']) : '') ,
                    'st' => (isset($customer_data['zone']) ? $this->getHash($customer_data['zone']) : '') ,
                    'cc' => (isset($customer_data['country_code']) ? $this->getHash($customer_data['country_code']) : '') ,
                );
            }
        }

        return $tagmanager_customer_data;

    }

    public function getCustomerData()
    {

        $customer_data = array();
        $store_cookie = false;
        $cdata = false;

        if (isset($this
            ->session
            ->data['tuser']) && !empty($this
            ->session
            ->data['tuser']))
        {
            $customer = $this
                ->session
                ->data['tuser'];
            if (isset($customer) && $customer)
            {
                $customer_data = unserialize($customer);
                $customer_data['userid'] = $customer_data['customer_id'];
                return $customer_data;
            }
        }

        if ($this
            ->customer
            ->isLogged())
        {
            $userid = $this
                ->customer
                ->getId();
            $customer_id = (int)$userid;
            if (isset($customer_id) && $customer_id > 0)
            {
                if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
                {
                    $cdata = $this
                        ->cache
                        ->get('tagmanager.customer.' . $customer_id);
                }

                if (!$cdata)
                {
                    $query = $this
                        ->db
                        ->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");
                    if ($query->num_rows)
                    {
                        $email = strtolower(trim(str_replace(' ', '', $query->row['email'])));;
                        $firstname = $query->row['firstname'];
                        $lastname = $query->row['lastname'];
                        $telephone = $query->row['telephone'];
                        $newsletter = $query->row['newsletter'];
                        $city = '';
                        $country = '';
                        $zone = '';
                        $country_code = '';
                        $postcode = '';
                        $address_id = (int)$query->row['address_id'];
                        if (isset($address_id) && $address_id > 0)
                        {
                            $address_query = $this
                                ->db
                                ->query("SELECT DISTINCT * FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$address_id . "' AND customer_id = '" . (int)$customer_id . "'");
                            if ($address_query->num_rows)
                            {
                                $city = (isset($address_query->row['city']) ? $address_query->row['city'] : '');
                                $postcode = (isset($address_query->row['postcode']) ? $address_query->row['postcode'] : '');

                                $country_query = $this
                                    ->db
                                    ->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$address_query->row['country_id'] . "'");
                                if ($country_query->num_rows)
                                {
                                    $country = $country_query->row['name'];
                                    $country_code = $country_query->row['iso_code_2'];
                                }
                                else
                                {
                                    $country = '';
                                    $country_code = '';
                                }
                                $zone_query = $this
                                    ->db
                                    ->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$address_query->row['zone_id'] . "'");
                                if ($zone_query->num_rows)
                                {
                                    $zone = $zone_query->row['name'];
                                    $zone_code = $zone_query->row['code'];
                                }
                                else
                                {
                                    $zone = '';
                                    $zone_code = '';
                                }
                            }
                        }
                    }
                    $customer_data = array(
                        'customer_id' => $customer_id,
                        'email' => $email,
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'telephone' => $telephone,
                        'city' => $city,
                        'postcode' => $postcode,
                        'zone' => $zone,
                        'country' => $country,
                        'country_code' => $country_code,
                        'newsletter' => $newsletter,
                    );
                    if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
                    {
                        $this
                            ->cache
                            ->set('tagmanager.customer.' . $customer_id, $customer_data);
                    }
                }
                else
                {
                    $customer_data = $cdata;
                }
                $store_cookie = true;
            }
        }
        elseif (isset($this
            ->session
            ->data['guest']))
        {
            $customer_id = '';
            $userid = '';
            $email = isset($this
                ->session
                ->data['guest']['email']) ? $this
                ->session
                ->data['guest']['email'] : '';
            $email = strtolower(trim(str_replace(' ', '', $email)));
            $firstname = isset($this
                ->session
                ->data['guest']['firstname']) ? $this
                ->session
                ->data['guest']['firstname'] : '';
            $lastname = isset($this
                ->session
                ->data['guest']['lastname']) ? $this
                ->session
                ->data['guest']['lastname'] : '';
            $telephone = isset($this
                ->session
                ->data['guest']['telephone']) ? $this
                ->session
                ->data['guest']['telephone'] : '';
            $city = isset($this
                ->session
                ->data['payment_address']['city']) ? $this
                ->session
                ->data['payment_address']['city'] : '';
            $zone = isset($this
                ->session
                ->data['payment_address']['zone']) ? $this
                ->session
                ->data['payment_address']['zone'] : '';
            $country = isset($this
                ->session
                ->data['payment_address']['country']) ? $this
                ->session
                ->data['payment_address']['country'] : '';
            $country_code = isset($this
                ->session
                ->data['payment_address']['iso_code_2']) ? $this
                ->session
                ->data['payment_address']['iso_code_2'] : '';
            $postcode = isset($this
                ->session
                ->data['payment_address']['postcode']) ? $this
                ->session
                ->data['payment_address']['postcode'] : '';

            $customer_data = array(
                'customer_id' => $customer_id,
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'telephone' => $telephone,
                'city' => $city,
                'postcode' => $postcode,
                'zone' => $zone,
                'country' => $country,
                'country_code' => $country_code,
                'newsletter' => '',
            );
            $store_cookie = true;
        }
        else
        {
            $customer_data = array(
                'userid' => '',
                'customer_id' => '',
                'email' => '',
                'firstname' => '',
                'lastname' => '',
                'telephone' => '',
                'city' => '',
                'postcode' => '',
                'zone' => '',
                'country' => '',
                'country_code' => '',
                'em' => '',
                'fn' => '',
                'ln' => '',
                'ph' => '',
                'newsletter' => '',
            );
        }

        if ($store_cookie)
        {
            $this->saveCustomerData($customer_data);
        }

        $customer_data['em'] = $this->getHash($customer_data['email']);
        $customer_data['fn'] = $this->getHash($customer_data['firstname']);
        $customer_data['ln'] = $this->getHash($customer_data['lastname']);
        $customer_data['ph'] = $this->getHash($customer_data['telephone']);
        $customer_data['userid'] = $customer_data['customer_id'];

        return $customer_data;
    }

    public function saveCustomerData($data)
    {
        if (!isset($data))
        {
            return false;
        }
        $customer = serialize($data);
        $this
            ->session
            ->data['tuser'] = $customer;
        return;
    }

    public function resetCustomerData()
    {
        $this
            ->session
            ->data['tuser'] = '';
        return;
    }

    public function saveOrderID($order_id = 0)
    {
        $gtm_orderid = (isset($this
            ->session
            ->data['tm_order_id']) ? $this
            ->session
            ->data['tm_order_id'] : 0);
        $gtm_orderid = (int)$gtm_orderid;
        $order_id = (int)$order_id;

        if (!$order_id)
        {
            return false;
        }
        if (empty($order_id))
        {
            return false;
        }
        if ((int)$order_id == 0)
        {
            return false;
        }
        $this
            ->session
            ->data['tm_order_id'] = $order_id;
        $this->saveGTMCookie('gtm_orderid', $order_id);
    }

    public function saveGTMCookie($name, $data)
    {
        if (!isset($data) || !isset($name))
        {
            return false;
        }
        if ($this->check_array($data))
        {
            $data = serialize($data);
        }
        $host = (isset($this
            ->request
            ->server['HTTP_HOST']) ? $this
            ->request
            ->server['HTTP_HOST'] : '');
        $samesite = 'strict';
        $httponly = 'HttpOnly';
        $secure = 'secure';
        $expire = time() + 600;
        $path = '/';

        if (isset($data) && $data)
        {
            if (PHP_VERSION_ID < 70300)
            {
                setcookie($name, $data, $expire, $path . '; samesite=' . $samesite, $host, $secure, $httponly);
            }
            else
            {
                setcookie($name, $data, ['expires' => $expire, 'path' => $path, 'domain' => $host, 'samesite' => $samesite, 'secure' => $secure, 'httponly' => $httponly, ]);
            }
        }

        return;

    }

    public function resetGTMCookie($name)
    {
        if (!isset($name))
        {
            return false;
        }
        $host = (isset($this
            ->request
            ->server['HTTP_HOST']) ? $this
            ->request
            ->server['HTTP_HOST'] : '');
        $samesite = 'strict';
        $httponly = 'HttpOnly';
        $secure = 'secure';
        $expire = time() - 7200;
        $path = '/';

        if (isset($name) && $name)
        {
            if (PHP_VERSION_ID < 70300)
            {
                setcookie($name, '', $expire, $path . '; samesite=' . $samesite, $host, $secure, $httponly);
            }
            else
            {
                setcookie($name, '', ['expires' => $expire, 'path' => $path, 'domain' => $host, 'samesite' => $samesite, 'secure' => $secure, 'httponly' => $httponly, ]);
            }
        }
        return;

    }

    public function readGTMCookie($name)
    {
        $data = false;
        if (isset($name))
        {
            $data = (isset($_COOKIE[$name]) ? $_COOKIE[$name] : false);
        }
        return $data;
    }

    public function readConsent()
    {

        $tagmanager = $this->settings;

        if ($tagmanager['eu_cookie_enforce'] == '1')
        {
            $data = array(
                'cc_enabled' => 1,
                'gdpr_analytics' => 'denied',
                'gdpr_marketing' => 'denied',
                'consent' => 'revoke',
                'allowAdFeatures' => 'false',
                'tracking_block' => true,
                'marketing_block' => true,
            );
        }
        else
        {
            $data = array(
                'cc_enabled' => 1,
                'gdpr_analytics' => 'granted',
                'gdpr_marketing' => 'granted',
                'consent' => 'grant',
                'allowAdFeatures' => 'true',
                'tracking_block' => false,
                'marketing_block' => false,
            );
            return $data;
        }

        $cc_accepted = (isset($_COOKIE["cookieControl"]) ? $_COOKIE["cookieControl"] : false);

        if (isset($_COOKIE["cookieControlPrefs"]))
        {
            $cc_data = (array)json_decode($_COOKIE["cookieControlPrefs"]);
            foreach ($cc_data as $cc_option)
            {
                if ($cc_option == 'analytics')
                {
                    $data['cc_analytics'] = 1;
                    $data['gdpr_analytics'] = 'granted';
                    $data['allowAdFeatures'] = 'false';
                    $data['tracking_block'] = false;
                    $data['consent'] = 'grant';
                }
                if ($cc_option == 'marketing')
                {
                    $data['cc_marketing'] = 1;
                    $data['gdpr_marketing'] = 'granted';
                    $data['allowAdFeatures'] = 'true';
                    $data['marketing_block'] = false;
                    $data['consent'] = 'grant';
                }
            }
            return $data;
        }

        $isense_cookie = (isset($_COOKIE["cookieconsent_status"]) ? $_COOKIE["cookieconsent_status"] : false);

        if (isset($isense_cookie) && $isense_cookie)
        {
            $isense_cookie_analytics = (isset($_COOKIE["cookieconsent_preferences_disabled"]) ? $_COOKIE["cookieconsent_preferences_disabled"] : false);
            if (isset($isense_cookie_analytics))
            {
                if ($isense_cookie_analytics == 'allow')
                {
                    $data = array(
                        'cc_enabled' => 1,
                        'gdpr_analytics' => 'granted',
                        'gdpr_marketing' => 'granted',
                        'consent' => 'grant',
                        'allowAdFeatures' => 'true',
                        'tracking_block' => false,
                        'marketing_block' => false,
                    );
                }
                else
                {
                    $data = array(
                        'cc_enabled' => 1,
                        'gdpr_analytics' => 'denied',
                        'gdpr_marketing' => 'denied',
                        'consent' => 'revoke',
                        'allowAdFeatures' => 'false',
                        'tracking_block' => true,
                        'marketing_block' => true,
                    );
                }
                $isense_cookie_analytics = (array)json_decode($isense_cookie_analytics);
                foreach ($isense_cookie_analytics as $cc_option)
                {
                    if ($cc_option == 'analytics')
                    {
                        $data['cc_analytics'] = 1;
                        $data['gdpr_analytics'] = 'denied';
                        $data['allowAdFeatures'] = 'false';
                        $data['tracking_block'] = true;
                        $data['consent'] = 'revoke';
                    }
                    if ($cc_option == 'marketing')
                    {
                        $data['cc_marketing'] = 1;
                        $data['gdpr_marketing'] = 'denied';
                        $data['allowAdFeatures'] = 'false';
                        $data['marketing_block'] = true;
                        $data['consent'] = 'revoke';
                    }
                }
            }
        }
        return $data;
    }

    /* outputs */

    public function getDataLayerSettings($setting_tags = array() , $tagmanager, $dimemsion = array())
    {

        if (isset($tagmanager['custom_dimension']) && $tagmanager['custom_dimension'])
        {
            for ($i = 1;$i <= 8;$i++)
            {
                $case_chercker = '';
                if (isset($tagmanager['custom_dimension' . $i . '_text']) && $tagmanager['custom_dimension' . $i . '_text'] != 'disable')
                {
                    $case_chercker = $tagmanager['custom_dimension' . $i . '_text'];
                    $
                    {
                        'dimension_value' . $i
                    } = false;
                    switch ($case_chercker)
                    {

                        case "ecomm_prodid":
                            if (isset($dimemsion['ecomm_prodid']))
                            {
                                $y = 0;
                                $dimvalue = '';
                                $ecom_array = $this
                                    ->gtm
                                    ->check_array($dimemsion['ecomm_prodid']);
                                if ($ecom_array)
                                {
                                    foreach ($dimemsion['ecomm_prodid'] as $dim)
                                    {
                                        if ($y > 0)
                                        {
                                            $dimvalue .= ',';
                                        }
                                        $dimvalue .= (isset($dim) ? $dim : false);
                                        $y++;
                                    }
                                }
                                else
                                {
                                    $dimvalue = $dimemsion['ecomm_prodid'];
                                }
                            }
                            else
                            {
                                $dimvalue = false;
                            }
                            $
                            {
                                    'dimension_value' . $i
                            } = $dimvalue;
                        break;
                        case "ecomm_pagetype":
                            $
                            {
                                    'dimension_value' . $i
                            } = (isset($dimemsion['ecomm_pagetype']) ? $dimemsion['ecomm_pagetype'] : false);
                        break;
                        case "ecomm_totalvalue":
                            $
                            {
                                    'dimension_value' . $i
                            } = (isset($dimemsion['ecomm_totalvalue']) ? $dimemsion['ecomm_totalvalue'] : false);
                        break;
                        case "dynx_itemid":
                            $
                            {
                                    'dimension_value' . $i
                            } = (isset($dimemsion['dynx_itemid']) ? $dimemsion['dynx_itemid'] : false);
                        break;
                        case "dynx_itemid2":
                            $
                            {
                                    'dimension_value' . $i
                            } = (isset($dimemsion['dynx_itemid2']) ? $dimemsion['dynx_itemid2'] : false);
                        break;
                        case "dynx_pagetype":
                            $
                            {
                                    'dimension_value' . $i
                            } = (isset($dimemsion['dynx_pagetype']) ? $dimemsion['dynx_pagetype'] : false);
                        break;
                        case "dynx_totalvalue":
                            $
                            {
                                    'dimension_value' . $i
                            } = (isset($dimemsion['dynx_totalvalue']) ? $dimemsion['dynx_totalvalue'] : false);
                        break;
                        case "user_id":
                            $
                            {
                                    'dimension_value' . $i
                            } = (isset($tagmanager['userid']) ? $tagmanager['userid'] : false);
                        break;
                        case "disable":
                            $
                            {
                                    'dimension_value' . $i
                            } = false;
                        break;
                    }

                }
            }
            for ($i = 1;$i <= 8;$i++)
            {
                if (isset($tagmanager['custom_dimension' . $i]) && $tagmanager['custom_dimension' . $i] != '0' && isset($
                {
                    'dimension_value' . $i
                }) && $
                {
                    'dimension_value' . $i
                })
                {
                    $setting_tags[] = array(
                        'dimension_index' . $i => $tagmanager['custom_dimension' . $i],
                        'dimension_text' . $i => $
                        {
                            'dimension_value' . $i
                        }
                        ,
                    );
                }
            }
        }

        $cookie_data = array(
            'cc_enabled' => 1,
            'gdpr_analytics' => 'granted',
            'gdpr_marketing' => 'granted',
            'consent' => 'grant',
            'allowAdFeatures' => 'true',
            'tracking_block' => false,
            'marketing_block' => false,
        );

        if ($tagmanager['eu_cookie'] == '1')
        {
            $cookie_data = $this->readConsent();
        }

        $setting_tags[] = array(
            'allowAdFeatures' => $cookie_data['allowAdFeatures'],
            'debug' => 'false',
            'analytics_storage' => $cookie_data['gdpr_analytics'],
            'ad_storage' => $cookie_data['gdpr_marketing'],
            'consent' => $cookie_data['consent'],
        );

        $setting_tags[] = array(
            'tracking' => 'multi',
        );

        if (isset($tagmanager['ua_status']) && $tagmanager['ua_status'])
        {
            if (isset($tagmanager['gid']))
            {
                $setting_tags[] = array(
                    'gid' => $tagmanager['gid'],
                    'ua_status' => $tagmanager['ua_status']
                );
            }
        }

        if (isset($tagmanager['ga4_status']) && $tagmanager['ga4_status'])
        {
            if (isset($tagmanager['ga4_mid']))
            {
                $setting_tags[] = array(
                    'ga4_mid' => $tagmanager['ga4_mid'],
                    'ga4_status' => $tagmanager['ga4_status'],
                );
            }
        }

        if ($tagmanager['conversion_id'] && $tagmanager['adword'] == '1')
        {
            $setting_tags[] = array(
                'adwordEnable' => $tagmanager['adword'],
                'adwordConversionID' => $tagmanager['conversion_id'],
                'adwordConversionLabel' => $tagmanager['conversion_label'],
                'adwordCurrency' => $tagmanager['currency']
            );
            if ($tagmanager['conversion_id2'] && $tagmanager['adword2'] == '1')
            {
                $setting_tags[] = array(
                    'adword2Enable' => $tagmanager['adword2'],
                    'adwordConversionID2' => $tagmanager['conversion_id2'],
                    'adwordConversionLabel2' => $tagmanager['conversion_label2'],
                    'adwordConversionValue2' => $tagmanager['conversion_value2'],
                );
            }
        }
        if (isset($tagmanager['remarketing']) && $tagmanager['remarketing'] == '1')
        {
            $setting_tags[] = array(
                'RemarketingEnable' => '1'
            );
        }
        if (isset($tagmanager['userid']) && $tagmanager['userid_status'] == '1')
        {
            $setting_tags[] = array(
                'userid' => $tagmanager['userid']
            );
        }
        if (isset($tagmanager['useremail']) && !empty($tagmanager['useremail']))
        {
            $setting_tags[] = array(
                'um' => $tagmanager['useremail']
            );
        }
        if (isset($tagmanager['email']) && !empty($tagmanager['email']))
        {
            $setting_tags[] = array(
                'ue' => $tagmanager['email']
            );
        }

        $setting_tags[] = array(
            'currencyCode' => $tagmanager['currency'],
            'user_agent' => $tagmanager['user_agent'],
            'store_country' => (isset($tagmanager['store_country']) ? $tagmanager['store_country'] : '') ,
        );

        if (isset($tagmanager['bing_uetid']) && !empty($tagmanager['bing_uetid']) && $tagmanager['bing_status'] == '1')
        {
            $setting_tags[] = array(
                'bingEnable' => '1',
                'bingid' => $tagmanager['bing_uetid']
            );
        }

        if (isset($tagmanager['snap_pixel_id']) && !empty($tagmanager['snap_pixel_id']) && $tagmanager['snap_pixel_status'] == '1')
        {
            $setting_tags[] = array(
                'snappixelenable' => '1',
                'snappixelid' => $tagmanager['snap_pixel_id']
            );
        }

        if (isset($tagmanager['hotjar_siteid']) && !empty($tagmanager['hotjar_siteid']) && $tagmanager['hotjar_status'] == '1')
        {
            $setting_tags[] = array(
                'hotjarenable' => '1',
                'hotjarid' => $tagmanager['hotjar_siteid']
            );
        }

        if (isset($tagmanager['google_optimize']) && !empty($tagmanager['google_optimize']) && $tagmanager['google_optimize_status'] == '1')
        {
            $setting_tags[] = array(
                'goptimizeenable' => '1',
                'goptimize' => $tagmanager['google_optimize']
            );
        }

        if (isset($tagmanager['yandex_code']) && !empty($tagmanager['yandex_code']) && $tagmanager['yandex_status'] == '1')
        {
            $setting_tags[] = array(
                'YandexEnable' => '1',
                'YandexCode' => $tagmanager['yandex_code']
            );
        }

        if (isset($tagmanager['twitter_tag']) && !empty($tagmanager['twitter_tag']) && $tagmanager['twitter_status'] == '1')
        {
            $setting_tags[] = array(
                'twitter_status' => '1',
                'twitter_tag' => $tagmanager['twitter_tag']
            );
        }

        if (isset($tagmanager['pinterest_tag']) && !empty($tagmanager['pinterest_tag']) && $tagmanager['pinterest_status'] == '1')
        {
            $setting_tags[] = array(
                'pinterest_status' => '1',
                'pinterest_tag' => $tagmanager['pinterest_tag']
            );
        }

        if (isset($tagmanager['glami_code']) && !empty($tagmanager['glami_code']) && $tagmanager['glami_status'] == '1')
        {
            $setting_tags[] = array(
                'GlamiEnable' => '1',
                'glami_code' => $tagmanager['glami_code']
            );
        }

        if (isset($tagmanager['clarity_id']) && !empty($tagmanager['clarity_id']) && $tagmanager['clarity_status'] == '1')
        {
            $setting_tags[] = array(
                'clarity_status' => '1',
                'clarity_id' => $tagmanager['clarity_id']
            );
        }

        if (isset($tagmanager['alt_currency']))
        {
            $setting_tags[] = array(
                'alt_currency' => $tagmanager['alt_currency']
            );
        }

        if (isset($tagmanager['ver']))
        {
            $setting_tags[] = array(
                'ver' => $tagmanager['ver']
            );
        }

        if (isset($route))
        {
            $setting_tags[] = array(
                'route' => $route
            );
        }

        $tag_initiate = array();

        foreach ($setting_tags as $result)
        {
            foreach ($result as $key => $value)
            {
                $tag_initiate[$key] = $value;
            }
        }

        return $tag_initiate;

    }

    public function prepareSteps($pageurl, $pagename)
    {

        $data = array();
        $event_id = $this->eventid();

        if (!isset($this
            ->session
            ->data['steps']))
        {
            $this
                ->session
                ->data['steps'] = 1;
        }

        $actionField = array(
            'step' => $this
                ->session
                ->data['steps'],
            'option' => $pagename
        );

        $ecommerce = array(
            'checkout' => $actionField
        );

        if (!isset($this
            ->session
            ->data['reload_check']))
        {
            $this
                ->session
                ->data['reload_check'] = array();
        }
        else
        {
            foreach ($this
                ->session
                ->data['reload_check'] as $check)
            {
                if ($check['pageurl'] == $pageurl)
                {
                    $error = true;
                }
            }
        }

        if (!isset($error))
        {

            $this
                ->session
                ->data['steps']++;

            $ec_ecommerce = array(
                'ecommerce' => $ecommerce
            );

            $data['gadata_ec'] = array(
                'event' => 'checkoutOption',
                'eventAction' => 'checkout',
                'eventLabel' => $pagename,
                'ec_ecommerce' => $ec_ecommerce
            );
            $data['gadata_goals'] = array(
                'event' => 'goalUrl',
                'eventAction' => 'checkout',
                'eventLabel' => $pagename,
                'goalPageUrl' => '/checkout/' . $pageurl,
                'goalPageTitle' => $pagename
            );

            $this
                ->session
                ->data['reload_check'][] = array(
                'pageurl' => $pageurl,
                'step' => $this
                    ->session
                    ->data['steps']
            );
        }

        return $data;

    }

    public function prepareAddtoCart($product_id, $product_info, $quantity, $option, $product_options)
    {

        $tagmanager = $this->config();
        $tiktok_data = array();
        $event_id = '5-' . $this->eventid();
        $op_text = '';
        $fb_data = array();

        if (isset($option) && isset($product_options))
        {
            $op = array();
            $keys = array_keys($option);
            $arraySize = count($option);
            for ($i = 0;$i < $arraySize;$i++)
            {
                if (is_array($option[$keys[$i]]))
                {
                    foreach ($option[$keys[$i]] as $opv)
                    {
                        $op[] = array(
                            'option_id' => $keys[$i],
                            'option_values' => $opv
                        );
                    }
                }
                else
                {

                    $op[] = array(
                        'option_id' => $keys[$i],
                        'option_values' => $option[$keys[$i]]
                    );
                }
            }

            foreach ($product_options as $product_option)
            {
                foreach ($op as $po)
                {
                    if ($product_option['product_option_id'] == $po['option_id'])
                    {
                        if (substr(VERSION, 0, 1) == '1')
                        {
                            $tmp_opv = $product_option['option_value'];
                        }
                        else
                        {
                            $tmp_opv = $product_option['product_option_value'];
                        }
                        foreach ($tmp_opv as $value)
                        {
                            if ($po['option_values'] == $value['product_option_value_id'])
                            {
                                $op_text .= $value['name'] . ", ";
                            }
                        }
                    }
                }

            }
        }

        $pprice = 0;
        $fprice = 0;

        if ($this
            ->customer
            ->isLogged() || !$this
            ->config
            ->get('config_customer_price'))
        {
            $unit_price = $this
                ->tax
                ->calculate($product_info['price'], $product_info['tax_class_id'], $this
                ->config
                ->get('config_tax'));
            $org_price = $unit_price;
        }
        if ((float)$product_info['special'])
        {
            $unit_price = $this
                ->tax
                ->calculate($product_info['special'], $product_info['tax_class_id'], $this
                ->config
                ->get('config_tax'));
            $discount = $org_price - $unit_price;
        }

        /*$unit_price = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));*/
        $pprice = $unit_price * $quantity;
        $pprice = $this
            ->currency
            ->format($pprice, $this
            ->session
            ->data['currency'], '', false);
        $fprice = $this
            ->currency
            ->format($pprice, $tagmanager['alt_currency'], '', false);
        if (!isset($product_info['sku']))
        {
            $product_info['sku'] = $product_info['model'];
        }
        $pid = $this->tagmangerPmap($product_info['model'], $product_info['sku'], $product_info['product_id'], $tagmanager);
        $brand = $this->getProductBrandName($product_info['product_id']);

        $cat_data = $this->getProductCatName($product_id);

        if (isset($cat_data))
        {
            $category_name = $cat_data['category'];
            $item_list_id = $cat_data['item_list_id'];
            $item_list_name = $cat_data['item_list_name'];
            $item_category = $cat_data['item_category'];
            $item_category2 = $cat_data['item_category2'];
            $item_category3 = $cat_data['item_category3'];
            $item_category4 = $cat_data['item_category4'];
            $item_category5 = $cat_data['item_category5'];
        }
        $title = $this->tagmangerPtitle($product_info['name'], $brand, $product_info['model'], $product_info['product_id']);

        if ($tagmanager['alt_currency_status'] && $tagmanager['alt_currency'] != $tagmanager['currency'])
        {
            $ftotal = $fprice;
            $fcurrency = $tagmanager['alt_currency'];
        }
        else
        {
            $ftotal = $pprice;
            $fcurrency = $tagmanager['currency'];
        }

        if ($tagmanager['tiktok_status'])
        {

            $tiktok_contents = array();
            $tiktok_contents[] = array(
                'id' => $pid,
                'quantity' => $quantity
            );

            $tiktok_data = array(
                'contents' => $tiktok_contents,
                'content_category' => (isset($item_list_name) ? $item_list_name : '') ,
                'content_name' => $title,
                'content_type' => 'product',
                'price' => (isset($pprice) ? number_format((float)$pprice, 2, '.', '') : '0') ,
                'currency' => $tagmanager['currency'],
                'quantity' => $quantity,
                'content_id' => $pid,
                'value' => (isset($pprice) ? number_format((float)$pprice, 2, '.', '') : '0') ,
            );
        }

        $ga4_data[] = array(
            'item_id' => (isset($pid) ? $pid : '') ,
            'item_name' => (isset($title) ? $title : '') ,
            'item_brand' => (isset($brand) ? $brand : '') ,
            'item_list_name' => (isset($item_list_name) ? $item_list_name : '') ,
            'item_list_id' => (isset($item_list_id) ? $item_list_id : '') ,
            'item_category' => (isset($item_category) ? $item_category : '') ,
            'item_category2' => (isset($item_category2) ? $item_category2 : '') ,
            'item_category3' => (isset($item_category3) ? $item_category3 : '') ,
            'item_category4' => (isset($item_category4) ? $item_category4 : '') ,
            'item_category5' => (isset($item_category5) ? $item_category5 : '') ,
            'item_variant' => $op_text,
            'affiliation' => '',
            'discount' => 0,
            'coupon' => '',
            'price' => (isset($pprice) ? number_format((float)$pprice, 2, '.', '') : '0') ,
            'curency' => $tagmanager['currency'],
            'quantity' => $quantity
        );

        $ecproduct = array(
            'name' => $title,
            'id' => $pid,
            'price' => number_format((float)$pprice, 2, '.', '') ,
            'brand' => $brand,
            'category' => (isset($category_name) ? $category_name : '') ,
            'quantity' => $quantity,
            'variant' => $op_text,
            'currency' => $tagmanager['currency'],
            'fprice' => number_format((float)$ftotal, 2, '.', '') ,
            'fcurrency' => $fcurrency,
            'ga4_data' => $ga4_data,
            'event_id' => $event_id,
        );

        if ($tagmanager['pixel'])
        {

            $fb_contents = array();
            $fb_contents[] = array(
                'id' => $pid,
                'quantity' => $quantity
            );

            $fb_data = array(
                'contents' => $fb_contents,
                'content_type' => 'product',
                'value' => number_format((float)$ftotal, 2, '.', '') ,
                'currency' => $fcurrency,
                'product_catalog_id' => $tagmanager['fb_catalog_id'],
                'quantity' => $quantity,
                'content_ids' => $pid,
                'event_id' => $event_id,
            );
            if ($tagmanager['fb_api'])
            {
                $pixel_post = $this->pixelSetup($tagmanager, 'AddToCart', $fb_data);
            }
        }

        if (isset($tagmanager['sendinblue_status']) && $tagmanager['sendinblue_status'])
        {

            $sendinblue = array(
                'email' => $tagmanager['email'],
                'event' => 'add_to_cart',
                'cuid' => $this->getCuid() ,
                'properties' => array(
                    'FIRSTNAME' => $tagmanager['first_name'],
                    'LASTNAME' => $tagmanager['last_name'],
                ) ,
                'eventdata' => array(
                    'id' => $this->GUID() ,
                    'data' => array()
                )
            );

            $subtotal = $this
                ->cart
                ->getSubTotal();
            $total = $this
                ->cart
                ->getTotal();
            $tax_total = $total - $subtotal;

            $sendinblue['eventdata']['data']['subtotal'] = number_format((float)$subtotal, 2, '.', '');
            $sendinblue['eventdata']['data']['shipping'] = 0;
            $sendinblue['eventdata']['data']['total_before_tax'] = number_format((float)$subtotal, 2, '.', '');
            $sendinblue['eventdata']['data']['tax'] = number_format((float)$tax_total, 2, '.', '');
            $sendinblue['eventdata']['data']['discount'] = 0;
            $sendinblue['eventdata']['data']['total'] = number_format((float)$total, 2, '.', '');
            $sendinblue['eventdata']['data']['url'] = str_replace('&amp;', '&', $this
                ->url
                ->link('checkout/checkout', '', 'SSL'));
            $sendinblue['eventdata']['data']['currency'] = $tagmanager['currency'];

            $sendinblue_products = array();

            foreach ($ga4_data as $product)
            {

                $sendinblue_products[] = array(
                    'id' => $product['item_id'],
                    'name' => $product['item_name'],
                    'quantity' => $product['quantity'],
                    'price' => $product['price'],
                    'url' => str_replace('&amp;', '&', $this
                        ->url
                        ->link('product/product', 'product_id=' . $product_id))
                );
            }

            $sendinblue['eventdata']['data']['products'] = $sendinblue_products;

            $this->sendinbluePost($sendinblue, 'trackEvent');

        }

        $ecdata = array(
            'tmerror' => 'false',
            'action' => 'addToCart',
            'data' => $ecproduct,
            'fb_data' => $fb_data,
            'tiktok' => $tiktok_data,
        );

        return $ecdata;
    }

    public function prepareRemoveCart($product_id, $product_info, $quantity)
    {

        $tagmanager = $this->config();
        $event_id = '10-' . $this->eventid();
        $error_check = $this->check_array($product_info);

        if (!$error_check)
        {
            return false;
        }

        $pprice = 0;
        $fprice = 0;

        $unit_price = $this
            ->tax
            ->calculate($product_info['price'], $product_info['tax_class_id'], $this
            ->config
            ->get('config_tax'));
        $pprice = $unit_price * $quantity;
        $pprice = $this
            ->currency
            ->format($pprice, $this
            ->session
            ->data['currency'], '', false);
        $fprice = $this
            ->currency
            ->format($pprice, $tagmanager['alt_currency'], '', false);
        if (!isset($product_info['sku']))
        {
            $product_info['sku'] = $product_info['model'];
        }
        $pid = $this->tagmangerPmap($product_info['model'], $product_info['sku'], $product_info['product_id'], $tagmanager);
        $brand = $this->getProductBrandName($product_info['product_id']);
        $cat_data = $this->getProductCatName($product_id);

        if (isset($cat_data))
        {
            $category_name = $cat_data['category'];
            $item_list_id = $cat_data['item_list_id'];
            $item_list_name = $cat_data['item_list_name'];
            $item_category = $cat_data['item_category'];
            $item_category2 = $cat_data['item_category2'];
            $item_category3 = $cat_data['item_category3'];
            $item_category4 = $cat_data['item_category4'];
            $item_category5 = $cat_data['item_category5'];
        }
        $title = $this->tagmangerPtitle($product_info['name'], $brand, $product_info['model'], $product_info['product_id']);

        if ($tagmanager['alt_currency_status'] && $tagmanager['alt_currency'] != $tagmanager['currency'])
        {
            $ftotal = $fprice;
            $fcurrency = $tagmanager['alt_currency'];
        }
        else
        {
            $ftotal = $pprice;
            $fcurrency = $tagmanager['currency'];
        }

        $ga4_data[] = array(
            'item_id' => (isset($pid) ? $pid : '') ,
            'item_name' => (isset($title) ? $title : '') ,
            'item_brand' => (isset($brand) ? $brand : '') ,
            'item_list_name' => (isset($item_list_name) ? $item_list_name : '') ,
            'item_list_id' => (isset($item_list_id) ? $item_list_id : '') ,
            'item_category' => (isset($item_category) ? $item_category : '') ,
            'item_category2' => (isset($item_category2) ? $item_category2 : '') ,
            'item_category3' => (isset($item_category3) ? $item_category3 : '') ,
            'item_category4' => (isset($item_category4) ? $item_category4 : '') ,
            'item_category5' => (isset($item_category5) ? $item_category5 : '') ,
            'item_variant' => '',
            'affiliation' => '',
            'discount' => 0,
            'coupon' => '',
            'price' => (isset($pprice) ? number_format((float)$pprice, 2, '.', '') : '0') ,
            'curency' => $tagmanager['currency'],
            'quantity' => $quantity
        );

        $ecproduct = array(
            'name' => $title,
            'id' => $pid,
            'price' => number_format((float)$pprice, 2, '.', '') ,
            'brand' => $brand,
            'category' => (isset($category_name) ? $category_name : '') ,
            'quantity' => $quantity,
            'currency' => $tagmanager['currency'],
            'fprice' => number_format((float)$ftotal, 2, '.', '') ,
            'fcurrency' => $fcurrency,
            'ga4_data' => $ga4_data,
            'event_id' => $event_id,

        );

        $ecdata = array(
            'tmerror' => 'false',
            'action' => 'RemoveCart',
            'data' => $ecproduct
        );

        return $ecdata;
    }

    public function prepareAddtoWishlist($product_id, $product_info)
    {

        $tagmanager = $this->config();
        $event_id = '4-' . $this->eventid();
        $error_check = $this->check_array($product_info);

        if (!$error_check)
        {
            return false;
        }

        $pprice = 0;
        $fprice = 0;

        $pprice = $this
            ->tax
            ->calculate($product_info['price'], $product_info['tax_class_id'], $this
            ->config
            ->get('config_tax'));
        $pprice = $this
            ->currency
            ->format($pprice, $this
            ->session
            ->data['currency'], '', false);
        $fprice = $this
            ->currency
            ->format($pprice, $tagmanager['alt_currency'], '', false);
        if (!isset($product_info['sku']))
        {
            $product_info['sku'] = $product_info['model'];
        }
        $pid = $this->tagmangerPmap($product_info['model'], $product_info['sku'], $product_info['product_id'], $tagmanager);
        $brand = $this->getProductBrandName($product_info['product_id']);
        $cat_data = $this->getProductCatName($product_id);

        if (isset($cat_data))
        {
            $category_name = $cat_data['category'];
            $item_list_id = $cat_data['item_list_id'];
            $item_list_name = $cat_data['item_list_name'];
            $item_category = $cat_data['item_category'];
            $item_category2 = $cat_data['item_category2'];
            $item_category3 = $cat_data['item_category3'];
            $item_category4 = $cat_data['item_category4'];
            $item_category5 = $cat_data['item_category5'];
        }
        $title = $this->tagmangerPtitle($product_info['name'], $brand, $product_info['model'], $product_info['product_id']);

        if ($tagmanager['alt_currency_status'] && $tagmanager['alt_currency'] != $tagmanager['currency'])
        {
            $ftotal = $fprice;
            $fcurrency = $tagmanager['alt_currency'];
        }
        else
        {
            $ftotal = $pprice;
            $fcurrency = $tagmanager['currency'];
        }

        $ga4_data[] = array(
            'item_id' => (isset($pid) ? $pid : '') ,
            'item_name' => (isset($title) ? $title : '') ,
            'item_brand' => (isset($brand) ? $brand : '') ,
            'item_list_name' => (isset($item_list_name) ? $item_list_name : '') ,
            'item_list_id' => (isset($item_list_id) ? $item_list_id : '') ,
            'item_category' => (isset($item_category) ? $item_category : '') ,
            'item_category2' => (isset($item_category2) ? $item_category2 : '') ,
            'item_category3' => (isset($item_category3) ? $item_category3 : '') ,
            'item_category4' => (isset($item_category4) ? $item_category4 : '') ,
            'item_category5' => (isset($item_category5) ? $item_category5 : '') ,
            'item_variant' => '',
            'affiliation' => '',
            'discount' => 0,
            'coupon' => '',
            'price' => (isset($pprice) ? number_format((float)$pprice, 2, '.', '') : '0') ,
            'curency' => $tagmanager['currency'],
            'quantity' => 1
        );

        $ecproduct = array(
            'name' => $title,
            'id' => $pid,
            'price' => number_format((float)$pprice, 2, '.', '') ,
            'brand' => $brand,
            'quantity' => 1,
            'category' => (isset($category_name) ? $category_name : '') ,
            'currency' => $tagmanager['currency'],
            'fprice' => number_format((float)$ftotal, 2, '.', '') ,
            'fcurrency' => $fcurrency,
            'ga4_data' => $ga4_data,
            'event_id' => $event_id,
        );

        $ecdata = array(
            'tmerror' => 'false',
            'action' => 'addToWishlist',
            'data' => $ecproduct,
        );

        if ($tagmanager['pixel'] && $tagmanager['fb_api'])
        {

            $fb_data = array(
                'content_ids' => $pid,
                'content_type' => $title,
                'value' => number_format((float)$ftotal, 2, '.', '') ,
                'currency' => $fcurrency,
                'product_catalog_id' => $tagmanager['fb_catalog_id'],
                'event_id' => $event_id,
            );

            $pixel_post = $this->pixelSetup($tagmanager, 'AddToWishlist', $fb_data);
        }

        return $ecdata;
    }

    public function prepareAddtoCompare($product_id, $product_info)
    {

        $tagmanager = $this->config();
        $event_id = '11-' . $this->eventid();
        $error_check = $this->check_array($product_info);

        if (!$error_check)
        {
            return false;
        }

        $pprice = 0;
        $fprice = 0;

        $pprice = $this
            ->tax
            ->calculate($product_info['price'], $product_info['tax_class_id'], $this
            ->config
            ->get('config_tax'));
        $pprice = $this
            ->currency
            ->format($pprice, $this
            ->session
            ->data['currency'], '', false);
        $fprice = $this
            ->currency
            ->format($pprice, $tagmanager['alt_currency'], '', false);
        if (!isset($product_info['sku']))
        {
            $product_info['sku'] = $product_info['model'];
        }
        $pid = $this->tagmangerPmap($product_info['model'], $product_info['sku'], $product_info['product_id'], $tagmanager);
        $brand = $this->getProductBrandName($product_info['product_id']);
        $cat_data = $this->getProductCatName($product_id);

        if (isset($cat_data))
        {
            $category_name = $cat_data['category'];
            $item_list_id = $cat_data['item_list_id'];
            $item_list_name = $cat_data['item_list_name'];
            $item_category = $cat_data['item_category'];
            $item_category2 = $cat_data['item_category2'];
            $item_category3 = $cat_data['item_category3'];
            $item_category4 = $cat_data['item_category4'];
            $item_category5 = $cat_data['item_category5'];
        }
        $title = $this->tagmangerPtitle($product_info['name'], $brand, $product_info['model'], $product_info['product_id']);

        if ($tagmanager['alt_currency_status'] && $tagmanager['alt_currency'] != $tagmanager['currency'])
        {
            $ftotal = $fprice;
            $fcurrency = $tagmanager['alt_currency'];
        }
        else
        {
            $ftotal = $pprice;
            $fcurrency = $tagmanager['currency'];
        }

        $ga4_data[] = array(
            'item_id' => (isset($pid) ? $pid : '') ,
            'item_name' => (isset($title) ? $title : '') ,
            'item_brand' => (isset($brand) ? $brand : '') ,
            'item_list_name' => (isset($item_list_name) ? $item_list_name : '') ,
            'item_list_id' => (isset($item_list_id) ? $item_list_id : '') ,
            'item_category' => (isset($item_category) ? $item_category : '') ,
            'item_category2' => (isset($item_category2) ? $item_category2 : '') ,
            'item_category3' => (isset($item_category3) ? $item_category3 : '') ,
            'item_category4' => (isset($item_category4) ? $item_category4 : '') ,
            'item_category5' => (isset($item_category5) ? $item_category5 : '') ,
            'item_variant' => '',
            'affiliation' => '',
            'discount' => 0,
            'coupon' => '',
            'price' => (isset($pprice) ? number_format((float)$pprice, 2, '.', '') : '0') ,
            'curency' => $tagmanager['currency'],
            'quantity' => 1
        );

        $ecproduct = array(
            'name' => $title,
            'id' => $pid,
            'price' => number_format((float)$pprice, 2, '.', '') ,
            'brand' => $brand,
            'quantity' => 1,
            'category' => (isset($category_name) ? $category_name : '') ,
            'currency' => $tagmanager['currency'],
            'fprice' => number_format((float)$ftotal, 2, '.', '') ,
            'fcurrency' => $fcurrency,
            'ga4_data' => $ga4_data,
            'event_id' => $event_id,

        );

        $ecdata = array(
            'tmerror' => 'false',
            'action' => 'addToCompare',
            'data' => $ecproduct,
        );

        return $ecdata;
    }

    public function prepareProduct($data)
    {

        $tagmanager = $this->config();
        $event_id = '1-' . $this->eventid();
        $ecproduct = array();
        $tiktok_data = array();
        $ecproduct[] = $data['ecproduct'];
        $ecproducts = $data['ecproducts'];
        $ga4_product = $data['ga4_products'];
        $fprice = $data['fprice'];
        $listname = (!empty($data['listname']) ? $data['listname'] : 'Category');
        $catname = (!empty($data['catname']) ? $data['catname'] : '');
        $brandname = (!empty($data['brandname']) ? $data['brandname'] : '');
        $ecom_prodid = $data['ecom_prodid'];
        $remarketing_ids[] = $data['remarketing_ids'];
        $ecom_pagetype = $data['ecom_pagetype'];
        $ecom_totalvalue = $data['ecom_totalvalue'];
        $dynx_itemid = $data['dynx_itemid'];
        $dynx_itemid2 = $data['dynx_itemid2'];
        $dynx_pagetype = $data['listname'];
        $dynx_totalvalue = $data['ecom_totalvalue'];
        $limit = $tagmanager['limit'];
        $max_list_items = $tagmanager['max_list_items'];
        $max_module_items = $tagmanager['max_module_items'];
        $fb_data = false;
        $sendinblue = false;

        $actionField = array(
            'Product-View' => $data['ecproduct']['name']

        );

        $detail = array(
            'actionField' => $actionField,
            'products' => $ecproduct
        );

        $ecommerce = array(
            'detail' => $detail
        );

        $ec_ecommerce = array(
            'ecommerce' => $ecommerce
        );

        $result = array(
            'event' => 'productDetailView',
            'eventAction' => 'view_item',
            'eventLabel' => 'view_item',
            'ec_ecommerce' => $ec_ecommerce,
            'Value' => number_format((float)$ecom_totalvalue, 2, '.', '') ,
        );

        if ($tagmanager['alt_currency_status'])
        {
            $ftotal = $fprice;
            $fcurrency = $tagmanager['alt_currency'];
        }
        else
        {
            $ftotal = $data['ecproduct']['price'];
            $fcurrency = $tagmanager['currency'];
        }

        if ($tagmanager['tiktok_status'])
        {

            $tiktok_data = array(
                'content_category' => $data['ecproduct']['category'],
                'content_name' => $data['ecproduct']['name'],
                'content_type' => 'product',
                'price' => number_format((float)$data['ecproduct']['price'], 2, '.', '') ,
                'currency' => $tagmanager['currency'],
                'content_id' => $data['ecproduct']['id'],
                'value' => number_format((float)$data['ecproduct']['price'], 2, '.', '') ,
            );
        }

        $dataLayer = array(
            'event' => 'productView',
            'eventAction' => 'productView',
            'eventLabel' => 'Product Detail View',
            'ga4_items' => $ga4_product,
            'content_name' => $data['ecproduct']['name'],
            'content_category' => $data['ecproduct']['category'],
            'content_ids' => $data['ecproduct']['id'],
            'content_type' => 'product',
            'pixel_value' => number_format((float)$ftotal, 2, '.', '') ,
            'fb_currency' => $fcurrency,
            'category' => $catname,
            'brand' => $brandname,
            'remarketing_ids' => $remarketing_ids,
            'dynx_itemid' => ($tagmanager['dynx_itemid'] ? $dynx_itemid : '') ,
            'dynx_itemid2' => ($tagmanager['dynx_itemid2'] ? $dynx_itemid2 : '') ,
            'dynx_pagetype' => ($tagmanager['dynx_pagetype'] ? 'view_item' : '') ,
            'dynx_totalvalue' => ($tagmanager['dynx_totalvalue'] ? number_format((float)$ecom_totalvalue, 2, '.', '') : '') ,
            'ecomm_totalvalue' => ($tagmanager['ecomm_totalvalue'] ? number_format((float)$ecom_totalvalue, 2, '.', '') : '') ,
            'ecomm_pagetype' => ($tagmanager['ecomm_pagetype'] ? 'view_item' : '') ,
            'ecomm_prodid' => ($tagmanager['ecomm_prodid'] ? $ecom_prodid : '') ,
            'currency' => $tagmanager['currency'],
            'value' => number_format((float)$ecom_totalvalue, 2, '.', '') ,
            'event_id' => $event_id,
        );

        $recommerce = array(
            'currencyCode' => $tagmanager['currency'],
            'impressions' => $ecproducts
        );

        $ec_ecommerce = array(
            'ecommerce' => $recommerce
        );

        $related = array(
            'event' => 'productImpression',
            'eventAction' => 'view_item_list',
            'eventLabel' => 'view_item_list',
            'ec_ecommerce' => $ec_ecommerce
        );

        if (isset($ecproducts) && sizeof($ecproducts) > 0)
        {
            $ecdata = array(
                'tmerror' => 'false',
                'type' => 'product',
                'google_ec' => $result,
                'datalayer' => $dataLayer,
                'related' => $related,
                'tiktok' => $tiktok_data,
            );
        }
        else
        {
            $ecdata = array(
                'tmerror' => 'false',
                'page_type' => 'product',
                'google_ec' => $result,
                'datalayer' => $dataLayer,
                'tiktok' => $tiktok_data,
            );
        }

        if ($tagmanager['pixel'])
        {

            $fb_event = array(
                'track' => 'ViewContent'
            );

            $fb_data = array(
                'content_name' => $data['ecproduct']['name'],
                'content_category' => $data['ecproduct']['category'],
                'content_ids' => $data['ecproduct']['id'],
                'content_type' => 'product',
                'value' => number_format((float)$ftotal, 2, '.', '') ,
                'currency' => $fcurrency,
            );
            if (isset($tagmanager['fb_catalog_id']) && !empty($tagmanager['fb_catalog_id']))
            {
                $fb_data['product_catalog_id'] = $tagmanager['fb_catalog_id'];
            }

        }

        $ecdata['fb_data'] = $fb_data;

        return $ecdata;

    }

    public function prepareProducts($data)
    {

        $tagmanager = $this->config();

        $tiktok_data = array();
        $fb_data = false;
        $fb_event = '';
        $sendinblue = false;
        $products = $data['ecproducts'];
        $fbproducts = $data['ecom_prodid'];
        $ga4_products = $data['ga4_products'];
        $listname = (!empty($data['listname']) ? $data['listname'] : 'Category');
        $listname = strtolower($listname);
        $catname = (!empty($data['catname']) ? $data['catname'] : '');
        $brandname = (!empty($data['brandname']) ? $data['brandname'] : '');
        $ecom_prodid = $data['ecom_prodid'];
        $remarketing_ids = $data['remarketing_ids'];
        $ecom_pagetype = $data['ecom_pagetype'];
        $ecom_totalvalue = $data['ecom_totalvalue'];
        $dynx_itemid = $data['dynx_itemid'];
        $dynx_itemid2 = $data['dynx_itemid2'];
        $dynx_pagetype = $data['listname'];
        $dynx_totalvalue = $data['ecom_totalvalue'];
        $limit = $tagmanager['limit'];
        $max_list_items = $tagmanager['max_list_items'];
        $max_module_items = $tagmanager['max_module_items'];
        $item_listname = (!empty($data['listname']) ? $data['listname'] : $catname);

        if (isset($this
            ->request
            ->get['search']))
        {
            $search = $this
                ->request
                ->get['search'];
        }
        else
        {
            $search = '';
        }

        if ($listname == 'search')
        {
            $event_id = '2-' . $this->eventid();
            $remarketing_page = 'view_search_result';
            $pixelpage = 'viewSearch';
            $item_listname = 'Search Results';
        }
        else
        {
            $event_id = '9-' . $this->eventid();
            $remarketing_page = 'view_item_list';
            $pixelpage = 'ViewCategory';
        }

        $i = 1;
        $count = 0;
        $google_ec = array();
        $ecproducts = array();

        foreach ($products as $product)
        {
            if ($i > $max_list_items)
            {
                break;
            }
            if ($count < $limit)
            {
                $ecproducts[] = $product;
            }
            else
            {
                $count = 0;
                $ecommerce = array(
                    'currencyCode' => $tagmanager['currency'],
                    'impressions' => $ecproducts
                );

                $ec_ecommerce = array(
                    'ecommerce' => $ecommerce
                );

                $result = array(
                    'event' => 'productImpression',
                    'eventAction' => 'Product Listing',
                    'eventLabel' => $remarketing_page,
                    'ec_ecommerce' => $ec_ecommerce
                );
                $google_ec[] = $result;

                if (isset($ecproducts))
                {
                    unset($ecproducts);
                }
                $ecproducts[] = $product;

            }

            $count++;
            $i++;

        }
        if (isset($ecproducts) && !empty($ecproducts))
        {

            $ecommerce = array(
                'currencyCode' => $tagmanager['currency'],
                'impressions' => $ecproducts
            );

            $ec_ecommerce = array(
                'ecommerce' => $ecommerce
            );

            $result = array(
                'event' => 'productImpression',
                'eventAction' => 'view_item_list',
                'eventLabel' => $remarketing_page,
                'ec_ecommerce' => $ec_ecommerce
            );
            $google_ec[] = $result;
        }

        if ($tagmanager['tiktok_status'])
        {

            $y = 0;
            $tcontentid = '';
            if ($this->check_array($ecom_prodid))
            {
                foreach ($ecom_prodid as $tids)
                {
                    if ($y > 0)
                    {
                        $tcontentid .= ',';
                    }
                    $tcontentid .= $tids;
                    $y++;
                }
            }

            $tiktok_data = array(
                'content_category' => $catname,
                'content_type' => 'product',
                'content_id' => $tcontentid,
                'query' => $search,
                'currency' => $tagmanager['currency'],
                'value' => number_format((float)$ecom_totalvalue, 2, '.', '')
            );
        }

        $dataLayer = array(
            'event' => ($listname == 'search' ? 'searchResult' : 'listingView') ,
            'eventAction' => ($listname == 'search' ? 'searchResult' : 'listingView') ,
            'eventLabel' => ($listname == 'search' ? 'Search Results' : 'Category View') ,
            'content_name' => $catname,
            'content_category' => $catname,
            'content_ids' => $fbproducts,
            'ga4_items' => $ga4_products,
            'content_type' => 'product',
            'search' => $search,
            'pixel_value' => '',
            'fb_currency' => '',
            'category' => $catname,
            'brand' => $brandname,
            'remarketing_ids' => $remarketing_ids,
            'dynx_itemid' => ($tagmanager['dynx_itemid'] ? $dynx_itemid : '') ,
            'dynx_itemid2' => ($tagmanager['dynx_itemid2'] ? $dynx_itemid2 : '') ,
            'dynx_pagetype' => ($tagmanager['dynx_pagetype'] ? $listname : '') ,
            'dynx_totalvalue' => ($tagmanager['dynx_totalvalue'] ? number_format((float)$ecom_totalvalue, 2, '.', '') : '') ,
            'ecomm_totalvalue' => ($tagmanager['ecomm_totalvalue'] ? number_format((float)$ecom_totalvalue, 2, '.', '') : '') ,
            'ecomm_pagetype' => ($tagmanager['ecomm_pagetype'] ? $listname : '') ,
            'ecomm_prodid' => ($tagmanager['ecomm_prodid'] ? $ecom_prodid : '') ,
            'currency' => $tagmanager['currency'],
            'value' => number_format((float)$ecom_totalvalue, 2, '.', '') ,
            'event_id' => $event_id,
        );

        $ecdata = array(
            'tmerror' => 'false',
            'page_type' => 'listing',
            'google_ec' => $google_ec,
            'datalayer' => $dataLayer,
            'tiktok' => $tiktok_data,
        );

        if ($tagmanager['pixel'])
        {

            if ($listname == 'search')
            {

                $fb_data = array(
                    'content_name' => $catname,
                    'content_category' => $catname,
                    'content_ids' => $fbproducts,
                    'content_type' => 'product',
                    'search_string' => $search,
                );
                if (isset($tagmanager['fb_catalog_id']) && !empty($tagmanager['fb_catalog_id']))
                {
                    $fb_data['product_catalog_id'] = $tagmanager['fb_catalog_id'];
                }
                $fb_event = 'Search';

            }
            else
            {
                $fb_data = array(
                    'content_name' => $catname,
                    'content_category' => $catname,
                    'content_ids' => $fbproducts,
                    'content_type' => 'product',
                );
                if (isset($tagmanager['fb_catalog_id']) && !empty($tagmanager['fb_catalog_id']))
                {
                    $fb_data['product_catalog_id'] = $tagmanager['fb_catalog_id'];
                }
                $fb_event = 'ViewCategory';
            }
        }
        $ecdata['fb_data'] = $fb_data;

        return $ecdata;

    }

    public function prepareModuleProducts($data)
    {

        $tagmanager = $this->settings;
        $event_id = $this->eventid();
        $limit = $tagmanager['limit'];
        $max_list_items = $tagmanager['max_list_items'];
        $max_module_items = $tagmanager['max_module_items'];
        $products = $data;

        $i = 1;
        $count = 0;

        $counter = 0;
        $google_ec = array();
        $ecproducts = array();

        foreach ($products as $product)
        {
            if (empty($product['name']) || empty($product['id']))
            {
                continue;
            }
            if ($i > $max_module_items)
            {
                break;
            }
            if ($count < $limit)
            {
                $ecproducts[] = array(
                    'name' => $product['name'],
                    'id' => $product['id'],
                    'price' => $product['price'],
                    'brand' => $product['brand'],
                    'category' => $product['category'],
                    'list' => $product['list'],
                    'position' => $i
                );
            }
            else
            {
                $count = 0;
                $ecommerce = array(
                    'currencyCode' => $tagmanager['currency'],
                    'impressions' => $ecproducts
                );

                $ec_ecommerce = array(
                    'ecommerce' => $ecommerce
                );

                $result = array(
                    'event' => 'productImpression',
                    'eventAction' => 'view_item_list' . ($counter > 0 ? $counter : '') ,
                    'eventLabel' => 'view_item_list' . ($counter > 0 ? $counter : '') ,
                    'ec_ecommerce' => $ec_ecommerce
                );
                $google_ec[] = $result;

                if (isset($ecproducts))
                {
                    unset($ecproducts);
                }
                $ecproducts[] = array(
                    'name' => $product['name'],
                    'id' => $product['id'],
                    'price' => $product['price'],
                    'brand' => $product['brand'],
                    'category' => $product['category'],
                    'list' => $product['list'],
                    'position' => $i
                );

                $counter++;
            }

            $count++;
            $i++;

        }
        if (isset($ecproducts) && !empty($ecproducts))
        {
            $ecommerce = array(
                'currencyCode' => $tagmanager['currency'],
                'impressions' => $ecproducts
            );

            $ec_ecommerce = array(
                'ecommerce' => $ecommerce
            );

            $result = array(
                'event' => 'productImpression',
                'eventAction' => 'view_item_list',
                'eventLabel' => 'view_item_list',
                'ec_ecommerce' => $ec_ecommerce
            );
            $google_ec[] = $result;
        }

        return $google_ec;
    }

    public function prepareCart()
    {

        $data = $this->getCartProducts();
        $event_id = '12-' . $this->eventid();
        $tagmanager = $this->config();
        $tiktok_data = array();
        $fb_data = false;
        $sendinblue = false;

        $data_error = array();
        if (!isset($data['ec_cartproducts']) || !isset($data['ga4_data']))
        {
            $data_error = array(
                'tmerror' => 'true'
            );
            return $data_error;
        }

        $ecproducts = $data['ec_cartproducts'];
        $ga4_products = $data['ga4_data'];
        $ecom_prodid = $data['ecom_prodid'];
        $remarketing_ids = $data['remarketing_ids'];
        $ecom_pagetype = 'cart';
        $ecom_totalvalue = number_format((float)$data['ecom_totalvalue'], 2, '.', '');
        $dynx_itemid = $data['dynx_itemid'];
        $dynx_itemid2 = $data['dynx_itemid2'];
        $dynx_pagetype = 'cart';
        $dynx_totalvalue = number_format((float)$data['ecom_totalvalue'], 2, '.', '');

        if ($tagmanager['tiktok_status'])
        {
            $y = 0;
            $tcontentid = '';
            if ($this->check_array($data['ecom_prodid']))
            {
                foreach ($data['ecom_prodid'] as $tids)
                {
                    if ($y > 0)
                    {
                        $tcontentid .= ',';
                    }
                    $tcontentid .= $tids;
                    $y++;
                }
            }

            $tiktok_data = array(
                'content_name' => 'Cart',
                'content_type' => 'product',
                'currency' => $tagmanager['currency'],
                'value' => number_format((float)$ecom_totalvalue, 2, '.', '') ,
                'content_id' => $tcontentid,
            );
        }

        if ($tagmanager['alt_currency_status'] && $tagmanager['alt_currency'] != $tagmanager['currency'])
        {
            $ftotal = $data['ftotal'];
            $fcurrency = $tagmanager['alt_currency'];
        }
        else
        {
            $ftotal = $data['ecom_totalvalue'];
            $fcurrency = $tagmanager['currency'];
        }

        $dataLayer = array(
            'event' => 'CART_VIEW',
            'eventAction' => 'CART_VIEW',
            'eventLabel' => 'CART_VIEW',
            'ga4_items' => $ga4_products,
            'content_name' => $ecom_pagetype,
            'content_category' => $ecom_pagetype,
            'content_ids' => $ecom_prodid,
            'content_type' => 'product',
            'contents' => $data['fb_contents'],
            'number_items' => $data['fb_items'],
            'pixel_value' => number_format((float)$ftotal, 2, '.', '') ,
            'fb_currency' => $fcurrency,
            'remarketing_ids' => $remarketing_ids,
            'dynx_itemid' => ($tagmanager['dynx_itemid'] ? $dynx_itemid : '') ,
            'dynx_itemid2' => ($tagmanager['dynx_itemid2'] ? $dynx_itemid2 : '') ,
            'dynx_pagetype' => ($tagmanager['dynx_pagetype'] ? $dynx_pagetype : '') ,
            'dynx_totalvalue' => ($tagmanager['dynx_totalvalue'] ? number_format((float)$ecom_totalvalue, 2, '.', '') : '') ,
            'ecomm_totalvalue' => ($tagmanager['ecomm_totalvalue'] ? number_format((float)$ecom_totalvalue, 2, '.', '') : '') ,
            'ecomm_pagetype' => ($tagmanager['ecomm_pagetype'] ? $ecom_pagetype : '') ,
            'ecomm_prodid' => ($tagmanager['ecomm_prodid'] ? $ecom_prodid : '') ,
            'currency' => $tagmanager['currency'],
            'value' => number_format((float)$ecom_totalvalue, 2, '.', '') ,
            'event_id' => $event_id,
        );

        if ($tagmanager['pixel'])
        {

            $fb_data = array(
                'contents' => (isset($data['fb_contents']) ? $data['fb_contents'] : false) ,
                'content_type' => 'product',
                'value' => number_format((float)$ftotal, 2, '.', '') ,
                'currency' => $fcurrency,
                'content_ids' => $ecom_prodid,
            );
            if (isset($tagmanager['fb_catalog_id']) && !empty($tagmanager['fb_catalog_id']))
            {
                $fb_data['product_catalog_id'] = $tagmanager['fb_catalog_id'];
            }

        }

        if (isset($tagmanager['sendinblue_status']) && $tagmanager['sendinblue_status'])
        {

            $sendinblue = array(
                'email' => $tagmanager['email'],
                'event' => 'view_cart',
                'cuid' => $this->getCuid() ,
                'properties' => array(
                    'FIRSTNAME' => $tagmanager['first_name'],
                    'LASTNAME' => $tagmanager['last_name'],
                ) ,
                'eventdata' => array(
                    'id' => $this->GUID() ,
                    'data' => array()
                )
            );

            $subtotal = $this
                ->cart
                ->getSubTotal();
            $total = $this
                ->cart
                ->getTotal();
            $tax_total = $total - $subtotal;

            $sendinblue['eventdata']['data']['subtotal'] = number_format((float)$subtotal, 2, '.', '');
            $sendinblue['eventdata']['data']['shipping'] = 0;
            $sendinblue['eventdata']['data']['total_before_tax'] = number_format((float)$subtotal, 2, '.', '');
            $sendinblue['eventdata']['data']['tax'] = number_format((float)$tax_total, 2, '.', '');
            $sendinblue['eventdata']['data']['discount'] = 0;
            $sendinblue['eventdata']['data']['total'] = number_format((float)$total, 2, '.', '');
            $sendinblue['eventdata']['data']['url'] = str_replace('&amp;', '&', $this
                ->url
                ->link('checkout/checkout', '', 'SSL'));
            $sendinblue['eventdata']['data']['currency'] = $tagmanager['currency'];
            $sendinblue['eventdata']['data']['products'] = (isset($data['sendinblue_products']) ? $data['sendinblue_products'] : array());

        }

        $ecdata = array(
            'tmerror' => 'false',
            'page_type' => 'cart',
            'datalayer' => $dataLayer,
            'fb_data' => $fb_data,
            'sendinblue' => $sendinblue,
            'tiktok' => $tiktok_data,
        );

        return $ecdata;

    }

    public function prepareCheckout($prepare = null)
    {

        $data = $this->getCartProducts();
        $event_id = '6-' . $this->eventid();
        $data_error = array();
        $tiktok_data = array();
        if (!isset($data['ec_cartproducts']) || !isset($data['ga4_data']))
        {
            $data_error = array(
                'tmerror' => 'true'
            );
            return $data_error;
        }
        $ga4_products = $data['ga4_data'];
        $tagmanager = $this->config();
        $result = array();
        $orderProducts = array();
        $remarketing_ids = array();
        $ecom_prodid = array();
        $fb_data = false;
        $sendinblue = false;
        $ecom_totalvalue = 0;

        $i = 1;

        if (!isset($prepare))
        {
            $prepare = array(
                'page' => 'checkout',
                'step' => '1',
                'mode' => 'onecheckout'
            );
        }

        $actionField = array(
            'step' => (int)$prepare['step'],
            'option' => $prepare['page']
        );

        $cart = array(
            'actionField' => $actionField,
            'products' => $data['ec_cartproducts']
        );

        $ecommerce = array(
            'checkout' => $cart
        );

        $ec_ecommerce = array(
            'ecommerce' => $ecommerce
        );

        $result = array(
            'event' => 'checkout',
            'eventAction' => 'checkout',
            'eventLabel' => 'checkout',
            'ec_ecommerce' => $ec_ecommerce
        );

        if ($tagmanager['alt_currency_status'] && $tagmanager['alt_currency'] != $tagmanager['currency'])
        {
            $ftotal = $data['ftotal'];
            $fcurrency = $tagmanager['alt_currency'];
        }
        else
        {
            $ftotal = $data['ecom_totalvalue'];
            $fcurrency = $tagmanager['currency'];
        }

        if ($tagmanager['tiktok_status'])
        {
            $y = 0;
            $tcontentid = '';
            if ($this->check_array($data['ecom_prodid']))
            {
                foreach ($data['ecom_prodid'] as $tids)
                {
                    if ($y > 0)
                    {
                        $tcontentid .= ',';
                    }
                    $tcontentid .= $tids;
                    $y++;
                }
            }

            $tiktok_data = array(
                'content_id' => $tcontentid,
                'content_name' => 'Checkout',
                'content_type' => 'product',
                'currency' => $tagmanager['currency'],
                'value' => number_format((float)$data['ecom_totalvalue'], 2, '.', '') ,
            );
        }

        $dataLayer = array(
            'event' => 'initiateCheckout',
            'eventAction' => 'initiateCheckout',
            'eventLabel' => 'Checkout Initiated',
            'content_name' => 'Checkout',
            'content_category' => 'Checkout',
            'ga4_items' => $ga4_products,
            'content_ids' => $data['ecom_prodid'],
            'contents' => $data['fb_contents'],
            'number_items' => $data['fb_items'],
            'content_type' => 'product',
            'pixel_value' => number_format((float)$ftotal, 2, '.', '') ,
            'fb_currency' => $fcurrency,
            'remarketing_ids' => $data['remarketing_ids'],
            'dynx_itemid' => ($tagmanager['dynx_itemid'] ? $data['dynx_itemid'] : '') ,
            'dynx_itemid2' => ($tagmanager['dynx_itemid2'] ? $data['dynx_itemid2'] : '') ,
            'dynx_pagetype' => ($tagmanager['dynx_pagetype'] ? 'add_to_cart' : '') ,
            'dynx_totalvalue' => ($tagmanager['dynx_totalvalue'] ? number_format((float)$data['ecom_totalvalue'], 2, '.', '') : '') ,
            'ecomm_totalvalue' => ($tagmanager['ecomm_totalvalue'] ? number_format((float)$data['ecom_totalvalue'], 2, '.', '') : '') ,
            'ecomm_pagetype' => ($tagmanager['ecomm_pagetype'] ? 'add_to_cart' : '') ,
            'ecomm_prodid' => ($tagmanager['ecomm_prodid'] ? $data['ecom_prodid'] : '') ,
            'currency' => $tagmanager['currency'],
            'value' => number_format((float)$data['ecom_totalvalue'], 2, '.', '') ,
            'event_id' => $event_id,
        );

        if ($tagmanager['pixel'])
        {

            $fb_data = array(
                'content_category' => 'Checkout',
                'content_ids' => $data['ecom_prodid'],
                'contents' => $data['fb_contents'],
                'currency' => $fcurrency,
                'num_items' => $data['fb_items'],
                'value' => number_format((float)$ftotal, 2, '.', '') ,
                'content_type' => 'product',
            );
            if (isset($tagmanager['fb_catalog_id']) && !empty($tagmanager['fb_catalog_id']))
            {
                $fb_data['product_catalog_id'] = $tagmanager['fb_catalog_id'];
            }
        }

        if (isset($tagmanager['sendinblue_status']) && $tagmanager['sendinblue_status'])
        {

            $sendinblue = array(
                'email' => $tagmanager['email'],
                'event' => 'checkout',
                'cuid' => $this->getCuid() ,
                'properties' => array(
                    'FIRSTNAME' => $tagmanager['first_name'],
                    'LASTNAME' => $tagmanager['last_name'],
                ) ,
                'eventdata' => array(
                    'id' => $this->GUID() ,
                    'data' => array()
                )
            );

            $subtotal = $this
                ->cart
                ->getSubTotal();
            $total = $this
                ->cart
                ->getTotal();
            $tax_total = $total - $subtotal;

            $sendinblue['eventdata']['data']['subtotal'] = number_format((float)$subtotal, 2, '.', '');
            $sendinblue['eventdata']['data']['shipping'] = 0;
            $sendinblue['eventdata']['data']['total_before_tax'] = number_format((float)$subtotal, 2, '.', '');
            $sendinblue['eventdata']['data']['tax'] = number_format((float)$tax_total, 2, '.', '');
            $sendinblue['eventdata']['data']['discount'] = 0;
            $sendinblue['eventdata']['data']['total'] = number_format((float)$total, 2, '.', '');
            $sendinblue['eventdata']['data']['url'] = str_replace('&amp;', '&', $this
                ->url
                ->link('checkout/checkout', '', 'SSL'));
            $sendinblue['eventdata']['data']['currency'] = $tagmanager['currency'];

            $sendinblue_products = $data['sendinblue_products'];
            $sendinblue['eventdata']['data']['products'] = $sendinblue_products;

        }

        $ecdata = array(
            'tmerror' => 'false',
            'gadata' => $result,
            'datalayer' => $dataLayer,
            'sendinblue' => $sendinblue,
            'fb_data' => $fb_data,
            'currency' => $tagmanager['currency'],
            'tiktok' => $tiktok_data,
        );

        return $ecdata;
    }

    public function prepareConfirm($prepare = null)
    {

        $data = $this->getCartProducts();
        $event_id = '7-' . $this->eventid();
        $data_error = array();
        if (!isset($data['ec_cartproducts']))
        {
            $data_error = array(
                'tmerror' => 'true'
            );
            return $data_error;
        }
        $ga4_products = $data['ga4_data'];
        $tagmanager = $this->config();
        $result = array();
        $orderProducts = array();
        $remarketing_ids = array();
        $ecom_prodid = array();
        $ecom_totalvalue = 0;

        $i = 1;

        if (!isset($prepare))
        {
            $prepare = array(
                'page' => 'checkout',
                'step' => (isset($this
                    ->session
                    ->data['steps']) ? $this
                    ->session
                    ->data['steps'] + 1 : 2) ,
                'mode' => 'onecheckout'
            );
        }

        $actionField = array(
            'step' => (int)$prepare['step'],
            'option' => $prepare['page']
        );

        $cart = array(
            'actionField' => $actionField,
            'products' => $data['ec_cartproducts']
        );

        $ecommerce = array(
            'checkout' => $cart
        );

        $ec_ecommerce = array(
            'ecommerce' => $ecommerce
        );

        $result = array(
            'event' => 'checkout',
            'eventAction' => 'checkout',
            'eventLabel' => 'checkout',
            'ec_ecommerce' => $ec_ecommerce
        );

        if ($tagmanager['alt_currency_status'] && $tagmanager['alt_currency'] != $tagmanager['currency'])
        {
            $ftotal = $data['ftotal'];
            $fcurrency = $tagmanager['alt_currency'];
        }
        else
        {
            $ftotal = $data['ecom_totalvalue'];
            $fcurrency = $tagmanager['currency'];
        }

        $dataLayer = array(
            'event' => 'confirmCheckout',
            'eventAction' => 'confirmCheckout',
            'eventLabel' => 'Order Confirm',
            'content_name' => 'Checkout',
            'ga4_items' => $ga4_products,
            'content_category' => 'Confirm',
            'content_ids' => $data['ecom_prodid'],
            'contents' => $data['fb_contents'],
            'number_items' => $data['fb_items'],
            'content_type' => 'product',
            'pixel_value' => number_format((float)$ftotal, 2, '.', '') ,
            'fb_currency' => $fcurrency,
            'remarketing_ids' => $data['remarketing_ids'],
            'dynx_itemid' => ($tagmanager['dynx_itemid'] ? $data['dynx_itemid'] : '') ,
            'dynx_itemid2' => ($tagmanager['dynx_itemid2'] ? $data['dynx_itemid2'] : '') ,
            'dynx_pagetype' => ($tagmanager['dynx_pagetype'] ? 'add_to_cart' : '') ,
            'dynx_totalvalue' => ($tagmanager['dynx_totalvalue'] ? number_format((float)$data['ecom_totalvalue'], 2, '.', '') : '') ,
            'ecomm_totalvalue' => ($tagmanager['ecomm_totalvalue'] ? number_format((float)$data['ecom_totalvalue'], 2, '.', '') : '') ,
            'ecomm_pagetype' => ($tagmanager['ecomm_pagetype'] ? 'add_to_cart' : '') ,
            'ecomm_prodid' => ($tagmanager['ecomm_prodid'] ? $data['ecom_prodid'] : '') ,
            'currency' => $tagmanager['currency'],
            'value' => number_format((float)$data['ecom_totalvalue'], 2, '.', '') ,
            'event_id' => $event_id,
        );

        $ecdata = array(
            'tmerror' => 'false',
            'gadata' => $result,
            'datalayer' => $dataLayer,
            'currency' => $tagmanager['currency']
        );

        if ($tagmanager['pixel'])
        {

            $fb_data = array(
                'content_category' => 'Confirm',
                'content_type' => 'product',
                'content_ids' => $data['ecom_prodid'],
                'contents' => $data['fb_contents'],
                'currency' => $fcurrency,
                'value' => number_format((float)$ftotal, 2, '.', '') ,
                'num_items' => $data['fb_items'],
            );
            if (isset($tagmanager['fb_catalog_id']) && !empty($tagmanager['fb_catalog_id']))
            {
                $fb_data['product_catalog_id'] = $tagmanager['fb_catalog_id'];
            }

            $pixel_post = $this->pixelSetup($tagmanager, 'AddPaymentInfo', $fb_data);
        }

        return $ecdata;
    }

    public function prepareOrder($order_id)
    {

        $tagmanager = $this->config();
        $event_id = '8-' . $this->eventid();

        if (empty($order_id))
        {
            if (isset($tagmanager['debug']) && $tagmanager['debug'])
            {
                $this->tmerror('Tagmanager: Procedure Call prepareorder. Result: Order Id Empty');
            }
            return $data['tmerror'] = 'Empty Order';
        }

        $data = $this->getOrder($order_id);

        if (!$data)
        {
            if (isset($tagmanager['debug']) && $tagmanager['debug'])
            {
                $this->tmerror('Tagmanager: Order Id Not Found in prepareOrder');
            }
            return false;
        }

        $fb_contents = array();
        $result = array();
        $orderProducts = array();
        $affiliate_gateway = array();
        $remarketing_ids = array();
        $ecom_prodid = array();
        $ecom_totalvalue = 0;
        $dynx_itemid = '';
        $dynx_itemid2 = '';
        $skroutz_items = array();
        $sendinblue = array();
        $ec_customer_data = array();
        $ga4_products = $data['ec_orderProducts']['ga4_data'];
        $ec_orderProducts = $data['ec_orderProducts']['products'];
        $gtin = array();
        $admitad = array();
        $aw_items = array();
        $tiktok_data = array();
        $tiktok_items = array();
        $linkwise = array();
        $linkwise_items = array();
        $linkwise_tax = (isset($tagmanger['linkwise_tax']) ? $tagmanger['linkwise_tax'] : 1);
        $customer_data = array();
        $pixel_customer_data = array();
        $g_review = array();
        $fb_data = false;
        $useremail = '';
        $i = 1;

        foreach ($ec_orderProducts as $product)
        {

            $optext = '';

            foreach ($product['option'] as $option)
            {
                if (isset($option['type']) && $option['type'] != 'file')
                {
                    $value = (isset($option['value']) ? $option['value'] : '');
                }
                else
                {
                    $value = '';
                }
                $optext .= $option['name'] . ': ' . (utf8_strlen($value) > 50 ? utf8_substr($value, 0, 50) . '..' : $value) . ' ';
            }
            $optext = utf8_substr($optext, 0, 499);

            $ecom_prodid[] = $product['pid'];

            if (isset($product['gtin']) && !empty($product['gtin']))
            {
                $gtin[] = array(
                    'gtin' => $product['gtin']
                );
            }

            $remarketing_ids[] = array(
                'id' => $product['pid'],
                'google_business_vertical' => 'retail'
            );
            $product_total = $product['price'] * $product['quantity'];
            $ecom_totalvalue += number_format((float)$product_total, 2, '.', '');

            $orderProducts[] = array(
                'id' => (string)$product['pid'],
                'name' => $product['title'],
                'category' => $product['category'],
                'brand' => $product['brand'],
                'variant' => $optext,
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'currency' => $data['ec_currency']
            );

            $aw_items[] = array(
                'id' => (string)$product['pid'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
            );

            if (!isset($order_desc))
            {
                $order_desc = '';
            }
            if ($i == 1)
            {
                $order_desc .= $product['title'];
            }
            else
            {
                $order_desc .= '+' . $product['title'];
            }

            $affiliate_gateway[] = array(
                'id' => (string)$product['pid'],
                'name' => $product['title'],
                'category' => $product['category'],
                'brand' => $product['brand'],
                'cat' => $this->getProductCatID($product['pid']) ,
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'currency' => $data['ec_currency']
            );

            $admitad[] = array(
                'product_id' => (string)$product['pid'],
                'category' => (isset($tagmanager['admitad_category']) ? $tagmanager['admitad_category'] : '1') ,
                'price' => $product['price'],
                'currency' => $data['ec_currency'],
                'quantity' => $product['quantity'],
                'type' => (isset($tagmanager['admitad_additional_type']) ? $tagmanager['admitad_additional_type'] : 'sale') ,
            );

            $skroutz_items[] = array(
                'order_id' => $data['ec_orderDetails']['order_id'],
                'product_id' => (string)$product['pid'],
                'name' => $product['title'],
                'price' => $product['price'],
                'quantity' => $product['quantity']
            );

            $linkwise_items[] = array(
                'product_id' => (string)$product['pid'],
                'name' => $product['title'],
                'price' => $product['price'] / $linkwise_tax,
                'quantity' => $product['quantity']
            );

            $tiktok_items[] = array(
                'content_id' => (string)$product['pid'],
                'content_type' => 'product',
                'content_name' => $product['title'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
            );

            if ($i == 1)
            {
                $dynx_itemid = (string)$product['pid'];
            }
            elseif ($i == 2)
            {
                $dynx_itemid2 = (string)$product['pid'];
            }
            $i++;
        }
        $actionField = array(
            'id' => $data['ec_orderDetails']['order_id'],
            'affiliation' => (isset($data['ec_affiliate_code']) ? $data['ec_affiliate_code'] : '') ,
            'revenue' => $data['ec_orderValue'],
            'tax' => $data['ec_orderTax'],
            'shipping' => $data['ec_orderShipping'],
            'coupon' => (isset($data['ec_orderCoupon']) ? $data['ec_orderCoupon'] : '') ,
            'currency' => $data['ec_currency']
        );

        $purchase = array(
            'actionField' => $actionField,
            'products' => $orderProducts
        );

        $ecommerce = array(
            'purchase' => $purchase
        );

        $ec_ecommerce = array(
            'ecommerce' => $ecommerce
        );

        $result = array(
            'event' => 'ecommerceComplete',
            'eventAction' => 'New Order',
            'eventLabel' => 'purchase',
            'ec_ecommerce' => $ec_ecommerce
        );

        if (isset($ec_orderProducts))
        {
            $fb_items = 0;
            foreach ($ec_orderProducts as $product)
            {
                $price = $product['price'];
                if ($tagmanager['alt_currency_status'])
                {
                    $price = $this
                        ->currency
                        ->format($product['price'], $tagmanager['alt_currency'], '', false);
                }
                $fb_contents[] = array(
                    'id' => (string)$product['pid'],
                    'quantity' => $product['quantity'],
                    'item_price' => number_format($price, 2, '.', '')
                );
                $fb_items = $fb_items + $product['quantity'];
            }
        }

        if ($tagmanager['alt_currency_status'] && $tagmanager['alt_currency'] != $tagmanager['currency'])
        {
            $ftotal = number_format($this
                ->currency
                ->format($data['ec_orderValue'], $tagmanager['alt_currency'], '', false) , 2, '.', '');
            $fcurrency = $tagmanager['alt_currency'];
        }
        else
        {
            $ftotal = $data['ec_orderValue'];
            $fcurrency = $data['ec_currency'];
        }

        $skroutz_order_tax = $data['ec_orderTax'];
        $skroutz_order_shipping = $data['ec_orderShipping'];
        $skroutz_revenue = $data['ec_orderValue'];

        if (isset($data['adjustment']['plus']))
        {
            $skroutz_revenue = $skroutz_revenue - $data['adjustment']['plus'];
        }

        if (isset($tagmanager['override_tax']) && $tagmanager['override_tax'])
        {
            $skroutz_order_tax = $skroutz_revenue - ($skroutz_revenue / $tagmanager['tax']);
            $skroutz_order_shipping_tax = $skroutz_order_shipping - ($skroutz_order_shipping / $tagmanager['tax']);
        }

        $skroutz_order = array(
            'order_id' => $data['ec_orderDetails']['order_id'],
            'revenue' => $skroutz_revenue,
            'shipping' => number_format((float)$skroutz_order_shipping, 2, '.', '') ,
            'tax' => number_format((float)$skroutz_order_tax, 2, '.', '')
        );

        if ($tagmanager['linkwise_status'])
        {

            $linkwise = array(
                'items' => $linkwise_items,
                'order' => $skroutz_order,
            );
        }

        $order_product_value = (int)$ecom_totalvalue - (int)$data['ec_orderTax'];

        if (isset($tagmanager['performant_tax_override']) && $tagmanager['performant_tax_override'] && $tagmanager['performant_tax'] > 0)
        {

            $ptotal = $data['ec_orderValue'] - $data['ec_orderShipping'] + $data['adjustment']['minus'] - $data['adjustment']['plus'];

            $ptax = $ptotal - ($ptotal / $tagmanager['performant_tax']);

            $permonat_value = $data['ec_orderValue'] - $data['ec_orderShipping'] - $ptax + $data['adjustment']['minus'] - $data['adjustment']['plus'];

            $permonat_value = number_format((float)$permonat_value, 2, '.', '');

        }
        else
        {

            $permonat_value = $data['ec_orderValue'] - $data['ec_orderShipping'] - $data['ec_orderTax'] + $data['adjustment']['minus'] - $data['adjustment']['plus'];
            $permonat_value = number_format((float)$permonat_value, 2, '.', '');

        }

        if ($tagmanager['alt_currency_status'] && $tagmanager['alt_currency'] != $tagmanager['currency'])
        {
            $permonat_value = number_format($this
                ->currency
                ->format($permonat_value, $tagmanager['alt_currency'], '', false) , 2, '.', '');
        }

        $estimate = $this->DeliveryEstimate('15:00:00', 5, $data['ec_orderDetails']['shipping_code']);

        if (isset($estimate) && !empty($estimate))
        {
            $estimate = date('Y-m-d', $estimate);
        }

        if (isset($tagmanager['greview']) && $tagmanager['greview'])
        {

            $g_review = array(
                'order_id' => $data['ec_orderDetails']['order_id'],
                'email' => $data['ec_orderDetails']['email'],
                'country' => $data['ec_orderDetails']['shipping_iso_code_2'],
                'estimate' => $estimate
            );
        }

        $order_calc = array(
            'order_total' => $data['ec_orderValue'],
            'shipping' => $data['ec_orderShipping'],
            'tax' => $data['ec_orderTax'],
            'adjustment_plus' => $data['adjustment']['plus'],
            'adjustment_minus' => $data['adjustment']['minus'],
            'sub_total' => $data['adjustment']['sub_total'],
            'order_totals' => $data['adjustment']['order_totals'],
        );

        $order_subtotal = number_format((float)$ecom_totalvalue, 2, '.', '') - $data['ec_orderTax'];

        if ($tagmanager['tiktok_status'])
        {

            $y = 0;
            $tcontentid = '';
            if ($this->check_array($ecom_prodid))
            {
                foreach ($ecom_prodid as $tids)
                {
                    if ($y > 0)
                    {
                        $tcontentid .= ',';
                    }
                    $tcontentid .= $tids;
                    $y++;
                }
            }

            $tiktok_data = array(
                'contents' => $tiktok_items,
                'content_name' => 'Purchase',
                'currency' => $tagmanager['currency'],
                'value' => number_format((float)$ecom_totalvalue, 2, '.', '') ,
            );
        }

        if (isset($tagmanager['customer_data']) && $tagmanager['customer_data'])
        {

            $useremail = str_replace(' ', '', $data['ec_orderDetails']['email']);

            $customer_data = array(
                'userid' => $this
                    ->customer
                    ->getId() ,
                'customer_id' => $this
                    ->customer
                    ->getId() ,
                'email' => $useremail,
                'firstname' => $data['ec_orderDetails']['firstname'],
                'lastname' => $data['ec_orderDetails']['lastname'],
                'telephone' => $data['ec_orderDetails']['telephone'],
                'city' => $data['ec_orderDetails']['payment_city'],
                'postcode' => $data['ec_orderDetails']['payment_postcode'],
                'zone' => $data['ec_orderDetails']['payment_zone'],
                'country' => $data['ec_orderDetails']['payment_country'],
                'country_code' => $data['ec_orderDetails']['payment_iso_code_2'],
                'em' => $this->getHash($useremail) ,
                'fn' => $this->getHash($data['ec_orderDetails']['firstname']) ,
                'ln' => $this->getHash($data['ec_orderDetails']['lastname']) ,
                'ph' => $this->getHash($data['ec_orderDetails']['telephone']) ,
                'ad' => $this->getHash($data['ec_orderDetails']['payment_address_1']) ,
                'ct' => $this->getHash($data['ec_orderDetails']['payment_city']) ,
                'pc' => $this->getHash($data['ec_orderDetails']['payment_postcode']) ,
                'st' => $this->getHash($data['ec_orderDetails']['payment_zone']) ,
                'c' => $this->getHash($data['ec_orderDetails']['payment_country']) ,
                'cc' => $this->getHash($data['ec_orderDetails']['payment_iso_code_2']) ,
                'newsletter' => '',
            );

            $useremail = $this->getHash($useremail);

            if (isset($tagmanager['adword_ec']) && $tagmanager['adword_ec'])
            {

                $ec_customer_data = array(
                    'awec_fn' => $customer_data['fn'],
                    'awec_ln' => $customer_data['ln'],
                    'awec_em' => $customer_data['em'],
                    'awec_ph' => $customer_data['ph'],
                    'awec_ad' => $customer_data['ad'],
                    'awec_ct' => $customer_data['ct'],
                    'awec_pc' => $customer_data['pc'],
                    'awec_st' => $customer_data['st'],
                    'awec_c' => $customer_data['c'],
                    'awec_cc' => $customer_data['cc'],
                );

            }

            if ($tagmanager['pixel'])
            {
                $pixel_customer_data = array(
                    'fn' => $customer_data['fn'],
                    'ln' => $customer_data['ln'],
                    'em' => $customer_data['em'],
                    'ph' => $customer_data['ph'],
                    'ad' => $customer_data['ad'],
                    'ct' => $customer_data['ct'],
                    'pc' => $customer_data['pc'],
                    'st' => $customer_data['st'],
                    'c' => $customer_data['c'],
                    'cc' => $customer_data['cc'],
                );
            }

            $tagmanager['em'] = (isset($customer_data['em']) ? $customer_data['em'] : '');
            $tagmanager['fn'] = (isset($customer_data['fn']) ? $customer_data['fn'] : '');
            $tagmanager['ln'] = (isset($customer_data['ln']) ? $customer_data['ln'] : '');
            $tagmanager['ph'] = (isset($customer_data['ph']) ? $customer_data['ph'] : '');
            $tagmanager['ct'] = (isset($customer_data['ct']) ? $customer_data['ct'] : '');
            $tagmanager['st'] = (isset($customer_data['st']) ? $customer_data['st'] : '');
            $tagmanager['pc'] = (isset($customer_data['pc']) ? $customer_data['pc'] : '');
            $tagmanager['cc'] = (isset($customer_data['cc']) ? $customer_data['cc'] : '');

            $this->saveCustomerData($customer_data);
        }

        if (isset($tagmanager['sendinblue_status']) && $tagmanager['sendinblue_status'])
        {
            $sendinblue = array(
                'email' => $customer_data['email'],
                'event' => 'order_completed',
                'cuid' => $this->getCuid() ,
                'properties' => array(
                    'FIRSTNAME' => $data['ec_orderDetails']['firstname'],
                    'LASTNAME' => $data['ec_orderDetails']['lastname'],
                    'LOCATION' => $data['ec_orderDetails']['payment_city'],
                    'COUNTRY' => $data['ec_orderDetails']['payment_country'],
                    'TELEPHONE' => $data['ec_orderDetails']['telephone'],
                ) ,
                'eventdata' => array(
                    'id' => $this->GUID() ,
                    'cuid' => $this->getCuid() ,
                    'data' => array()
                )
            );

            $sendinblue['eventdata']['data']['Billing_Details'] = array(
                'billing_FIRST_NAME' => $data['ec_orderDetails']['payment_firstname'],
                'billing_LAST_NAME' => $data['ec_orderDetails']['payment_lastname'],
                'billing_COMPANY ' => $data['ec_orderDetails']['payment_company'],
                'billing_ADDRESS_1' => $data['ec_orderDetails']['payment_address_1'],
                'billing_ADDRESS_2' => $data['ec_orderDetails']['payment_address_2'],
                'billing_CITY' => $data['ec_orderDetails']['payment_city'],
                'billing_STATE' => $data['ec_orderDetails']['payment_zone'],
                'billing_POSTCODE' => $data['ec_orderDetails']['payment_postcode'],
                'billing_COUNTRY' => $data['ec_orderDetails']['payment_country'],
                'billing_PHONE' => $data['ec_orderDetails']['telephone'],
                'billing_EMAIL' => $data['ec_orderDetails']['email']
            );

            $sendinblue['eventdata']['data']['Shipping_Details'] = array(
                'shipping_FIRST_NAME' => $data['ec_orderDetails']['shipping_firstname'],
                'shipping_LAST_NAME' => $data['ec_orderDetails']['shipping_lastname'],
                'shipping_COMPANY ' => $data['ec_orderDetails']['shipping_company'],
                'shipping_ADDRESS_1' => $data['ec_orderDetails']['shipping_address_1'],
                'shipping_ADDRESS_2' => $data['ec_orderDetails']['shipping_address_2'],
                'shipping_CITY' => $data['ec_orderDetails']['shipping_city'],
                'shipping_STATE' => $data['ec_orderDetails']['shipping_zone'],
                'shipping_POSTCODE' => $data['ec_orderDetails']['shipping_postcode'],
                'shipping_COUNTRY' => $data['ec_orderDetails']['shipping_country'],
                'shipping_METHOD_TITLE' => $data['ec_orderDetails']['shipping_method']
            );

            $sendinblue['eventdata']['data']['Order_Details'] = array(
                'order_ID' => $data['ec_orderDetails']['order_id'],
                'order_KEY' => $data['ec_orderDetails']['order_id'],
                'order_TAX' => $order_calc['tax'],
                'order_SHIPPING_TAX' => 0,
                'order_SHIPPING' => $order_calc['shipping'],
                'order_PRICE' => number_format((float)$data['ec_orderValue'], 2, '.', '') ,
                'order_DATE' => $data['ec_orderDetails']['date_added'],
                'order_SUBTOTAL' => $order_calc['sub_total'],
                'order_DOWNLOAD_LINK' => ''
            );

            $sendinblue['eventdata']['data']['Miscalleneous'] = array(
                'cart_DISCOUNT' => '0',
                'cart_DISCOUNT_TAX' => '0',
                'customer_USER ' => $data['ec_orderDetails']['customer_id'],
                'payment_METHOD' => $data['ec_orderDetails']['payment_code'],
                'payment_METHOD_TITLE' => $data['ec_orderDetails']['payment_method'],
                'customer_IP_ADDRESS' => $data['ec_orderDetails']['ip'],
                'customer_USER_AGENT' => $data['ec_orderDetails']['user_agent'],
            );
        }
		
		
		$new_customer_query = $this->db->query('SELECT email FROM '.DB_PREFIX.'order WHERE email = "'.$data['ec_orderDetails']['email'].'"  LIMIT 1');
		
		if($new_customer_query->row) {
			$new_customer_data = false;
		} else {
			$new_customer_data = true;
		}
		
		
        $dataLayer = array(
            'event' => 'new_order',
            'eventAction' => 'new_order',
            'eventLabel' => 'Order Completed',
            'event_id' => $event_id,
            'content_name' => 'Purchase',
            'content_category' => 'Confirm',
            'ga4_items' => $ga4_products,
            'aw_items' => ($tagmanager['aw_optional'] ? $aw_items : '') ,
            'aw_merchant_id' => ($tagmanager['aw_optional'] ? $tagmanager['aw_merchant_id'] : '') ,
            'aw_feed_country' => ($tagmanager['aw_optional'] ? $tagmanager['aw_feed_country'] : '') ,
            'aw_feed_language' => ($tagmanager['aw_optional'] ? $tagmanager['aw_feed_language'] : '') ,
            'content_ids' => $ecom_prodid,
            'contents' => $fb_contents,
            'number_items' => $fb_items,
            'gtins' => (isset($gtin) ? $gtin : null) ,
            'content_type' => 'product',
            'pixel_value' => number_format((float)$ftotal, 2, '.', '') ,
            'fb_currency' => $fcurrency,
            'remarketing_ids' => $remarketing_ids,
            'dynx_itemid' => (string)$dynx_itemid,
            'dynx_itemid2' => (string)$dynx_itemid2,
            'dynx_pagetype' => 'purchse',
            'dynx_totalvalue' => number_format((float)$ecom_totalvalue, 2, '.', '') ,
            'ecomm_totalvalue' => number_format((float)$ecom_totalvalue, 2, '.', '') ,
            'ecomm_pagetype' => 'purchase',
            'ecomm_prodid' => $ecom_prodid,
            'currency' => $data['ec_currency'],
            'value' => number_format((float)$ecom_totalvalue, 2, '.', '') ,
            'order_total' => number_format((float)$data['ec_orderValue'], 2, '.', '') ,
            'order_id' => $data['ec_orderDetails']['order_id'],
            'transaction_id' => $data['ec_orderDetails']['order_id'],
            'tax' => $data['ec_orderTax'],
            'shipping' => $data['ec_orderShipping'],
            'estimated_delivery' => $estimate,
            'country_code' => $data['ec_orderDetails']['shipping_iso_code_2'],
            'order_email' => $data['ec_orderDetails']['email'], 
            'order_telephone' => $data['ec_orderDetails']['telephone'], 
            'order_shipping_postcode' => $data['ec_orderDetails']['shipping_postcode'],
            'new_customer_data' => $new_customer_data,
            'email_hash' => $useremail,
            'permonat_value' => $permonat_value,
            'skroutz_order' => $skroutz_order,
            'coupon' => (isset($data['ec_orderCoupon']) ? $data['ec_orderCoupon'] : '') ,
            'affiliation' => (isset($data['ec_affiliate_code']) ? $data['ec_affiliate_code'] : '') ,
            'oder_raw' => $order_calc,
        );

        if ($tagmanager['pixel'])
        {

            $fb_data = array(
                'content_category' => 'Confirm',
                'content_ids' => $ecom_prodid,
                'contents' => $fb_contents,
                'currency' => $fcurrency,
                'num_items' => $fb_items,
                'value' => number_format((float)$ftotal, 2, '.', '') ,
                'content_name' => 'Purchase',
                'content_type' => 'product',

            );
            if (isset($tagmanager['fb_catalog_id']) && !empty($tagmanager['fb_catalog_id']))
            {
                $fb_data['product_catalog_id'] = $tagmanager['fb_catalog_id'];
            }
        }

        $ecdata = array(
            'tmerror' => 'false',
            'tagmanager' => $tagmanager,
            'datalayer' => $dataLayer,
            'gadata' => $result,
            'fb_data' => $fb_data,
            'tagmanager' => $tagmanager,
            'aw_ec_data' => $ec_customer_data,
            'pixel_customer_data' => $pixel_customer_data,
            'tiktok' => $tiktok_data,
            'affiliate_gateway' => $affiliate_gateway,
            'linkwise' => $linkwise,
            'skroutz_items' => $skroutz_items,
            'admitad_items' => ($tagmanager['admitad_status'] ? $admitad : '') ,
            'sendinblue' => $sendinblue,
            'currency' => $data['ec_currency'],
            'revenue' => $data['ec_orderValue'],
            'product_value' => number_format((float)$order_product_value, 2, '.', '') ,
            'tax' => $data['ec_orderTax'],
            'shipping' => $data['ec_orderShipping'],
            'order_id' => $data['ec_orderDetails']['order_id'],
            'customer' => $customer_data,
            'discount' => '',
            'hit' => $data['hit']
        );

        return $ecdata;
    }

    public function getCartProducts()
    {
        $products = $this
            ->cart
            ->getProducts();
        $this
            ->load
            ->model('catalog/product');
        $tagmanager = $this->settings;
        $data = array();

        $data['ec_shipping_total'] = isset($this
            ->session
            ->data['shipping_method']['cost']) ? $this
            ->session
            ->data['shipping_method']['cost'] : 0;
        $data['ec_coupon'] = isset($this
            ->session
            ->data['coupon']) ? $this
            ->session
            ->data['coupon'] : false;

        $data['ecom_prodid'] = array();
        $data['fb_contents'] = array();
        $data['remarketing_ids'] = array();
        $data['sendinblue_products'] = array();
        $data['ecom_pagetype'] = 'purchase';
        $data['ecom_totalvalue'] = 0;
        $data['dynx_itemid'] = '';
        $data['dynx_itemid2'] = '';
        $data['ftotal'] = 0;
        $data['fb_items'] = 0;
        $i = 1;

        $orderProducts = array();

        foreach ($products as $product)
        {

            $optext = '';

            foreach ($product['option'] as $option)
            {
                if (isset($option['type']) && $option['type'] != 'file')
                {
                    $value = (isset($option['value']) ? $option['value'] : '');
                }
                else
                {
                    $value = '';
                }
                $optext .= $option['name'] . ': ' . (utf8_strlen($value) > 50 ? utf8_substr($value, 0, 50) . '..' : $value) . ' ';
            }

            $optext = utf8_substr($optext, 0, 499);

            $model = $product['model'];
            $sku = (isset($product['sku']) ? $product['sku'] : false);

            $pid = $this->tagmangerPmap($model, $sku, $product['product_id'], $tagmanager);
            $brand = $this->getProductBrandName($product['product_id']);
            $cat_data = $this->getProductCatName($product['product_id']);

            if (isset($cat_data))
            {
                $category_name = $cat_data['category'];
                $item_list_id = $cat_data['item_list_id'];
                $item_list_name = $cat_data['item_list_name'];
                $item_category = $cat_data['item_category'];
                $item_category2 = $cat_data['item_category2'];
                $item_category3 = $cat_data['item_category3'];
                $item_category4 = $cat_data['item_category4'];
                $item_category5 = $cat_data['item_category5'];
            }
            $title = $this->tagmangerPtitle($product['name'], $brand, $model, $product['product_id']);
            $unit_price = $this
                ->tax
                ->calculate($product['price'], $product['tax_class_id'], $this
                ->config
                ->get('config_tax'));
            $total_price = $unit_price * $product['quantity'];
            $total_price = $this
                ->currency
                ->format($total_price, $this
                ->session
                ->data['currency'], '', false);
            $fprice = $this
                ->currency
                ->format($total_price, $tagmanager['alt_currency'], '', false);
            $data['ftotal'] = $data['ftotal'] + $fprice;

            $data['ecom_prodid'][] = $pid;
            $data['remarketing_ids'][] = array(
                'id' => $pid,
                'google_business_vertical' => 'retail'
            );
            $data['ecom_totalvalue'] += number_format((float)$total_price, 2, '.', '');
            $data['fb_contents'][] = array(
                'id' => $pid,
                'quantity' => $product['quantity']
            );
            $data['fb_items'] = $data['fb_items'] + $product['quantity'];

            if ($i == 1)
            {
                $data['dynx_itemid'] = $pid;
            }
            elseif ($i == 2)
            {
                $data['dynx_itemid2'] = $pid;
            }

            $data['ec_cartproducts'][] = array(
                'id' => (string)$pid,
                'product_id' => $product['product_id'],
                'name' => $title,
                'category' => $category_name,
                'brand' => $brand,
                'variant' => $optext,
                'quantity' => $product['quantity'],
                'price' => number_format((float)$this
                    ->currency
                    ->format($this
                    ->tax
                    ->calculate($product['price'], $product['tax_class_id'], $this
                    ->config
                    ->get('config_tax')) , $this
                    ->session
                    ->data['currency'], '', false) , 2, '.', '') ,
                'ex_price' => number_format((float)$product['price'], 2, '.', '') ,
                'currency' => $this
                    ->session
                    ->data['currency']
            );

            if (isset($tagmanager['sendinblue_status']) && $tagmanager['sendinblue_status'])
            {

                $data['sendinblue_products'][] = array(
                    'id' => (string)$pid,
                    'name' => $title,
                    'quantity' => $product['quantity'],
                    'price' => number_format((float)$product['price'], 2, '.', '') ,
                    'url' => str_replace('&amp;', '&', $this
                        ->url
                        ->link('product/product', 'product_id=' . $product['product_id']))
                );
            }

            $data['ga4_data'][] = array(
                'item_id' => (isset($pid) ? (string)$pid : '') ,
                'item_name' => (isset($title) ? $title : '') ,
                'item_brand' => (isset($brand) ? $brand : '') ,
                'item_category' => (isset($item_category) ? $item_category : '') ,
                'item_category2' => (isset($item_category2) ? $item_category2 : '') ,
                'item_category3' => (isset($item_category3) ? $item_category3 : '') ,
                'item_category4' => (isset($item_category4) ? $item_category4 : '') ,
                'item_category5' => (isset($item_category5) ? $item_category5 : '') ,
                'item_list_id' => (isset($item_list_id) ? $item_list_id : '') ,
                'item_list_name' => (isset($item_list_name) ? $item_list_name : '') ,
                'item_variant' => $optext,
                'affiliation' => '',
                'discount' => 0,
                'coupon' => $data['ec_coupon'],
                'price' => number_format((float)$this
                    ->currency
                    ->format($this
                    ->tax
                    ->calculate($product['price'], $product['tax_class_id'], $this
                    ->config
                    ->get('config_tax')) , $this
                    ->session
                    ->data['currency'], '', false) , 2, '.', '') ,
                'curency' => $tagmanager['currency'],
                'quantity' => $product['quantity']
            );
            $i++;

        }
        return $data;
    }

    public function getOrder($order_id)
    {

        $this
            ->load
            ->model('checkout/order');
        $this
            ->load
            ->model('account/customer');
        $tagmanager = $this->config();

        if (!isset($order_id) || empty($order_id))
        {
            return false;
        }

        $data['ec_orderDetails'] = $this
            ->model_checkout_order
            ->getOrder($order_id);

        if (!$data['ec_orderDetails'])
        {
            if (isset($tagmanager['debug']) && $tagmanager['debug'])
            {
                $this->tmerror('Tagmanager: Order Id Not Found in gerOrder');
            }
            return false;
        }
        $data['ec_language'] = $this
            ->config
            ->get('config_language');
        $data['ec_orderCoupon'] = $this->getOrderCoupon($order_id);

        $data['ec_currency'] = $data['ec_orderDetails']['currency_code'];

        $data['ec_orderShipping'] = $this->getOrderShipping($order_id) * $data['ec_orderDetails']['currency_value'];
        $data['ec_orderValue'] = $data['ec_orderDetails']['total'] * $data['ec_orderDetails']['currency_value'];
        $data['ec_orderValue'] = number_format((float)$data['ec_orderValue'], 2, '.', '');
        $data['ec_orderTax'] = $this->getOrderTax($order_id) * $data['ec_orderDetails']['currency_value'];
        $data['adjustment'] = $this->getOrderTotalAdjustment($order_id, $data['ec_orderDetails']['currency_value']);
        $data['ec_affiliate_code'] = '';

        if (isset($data['ec_orderDetails']['tracking']) && !empty($data['ec_orderDetails']['tracking']))
        {
            $this
                ->load
                ->model('checkout/marketing');
            $marketing_info = $this
                ->model_checkout_marketing
                ->getMarketingByCode($data['ec_orderDetails']['tracking']);
            if ($marketing_info)
            {
                $data['ec_affiliate_code'] = $marketing_info['name'];
            }
        }

        $data['ec_orderProducts'] = $this->getOrderProducts($order_id, $data['ec_orderDetails'], $data['ec_orderCoupon'], $data['ec_affiliate_code']);
        $data['ec_orderDetails']['coupon'] = $this->getOrderCoupon($order_id);

        $data['ec_orderTax'] = number_format($data['ec_orderTax'], 2, '.', '');
        $data['ec_orderShipping'] = number_format((float)$data['ec_orderShipping'], 2, '.', '');

        $query = $this
            ->db
            ->query("SELECT * FROM " . DB_PREFIX . "analytics_tracking WHERE order_id = '" . (int)$order_id . "'");

        $data['hit'] = 0;

        if ($query->num_rows)
        {
            $data['hit'] = $query->row['hit'];
        }
        else
        {
            $data['hit'] = 0;
        }

        return $data;

    }

    public function getOrderProducts($order_id, $order_info, $coupon, $affiliation)
    {
        $order_product_query = $this
            ->db
            ->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

        $data = array();

        $tagmanager = $this->settings;

        foreach ($order_product_query->rows as $product)
        {

            $product_id = $product['product_id'];
            $option_data = array();
            $options = $this->getOrderOptions($order_id, $product['order_product_id']);

            foreach ($options as $option)
            {
                $option_data[] = array(
                    'name' => $option['name'] . " " . (utf8_strlen($option['value']) > 100 ? utf8_substr($option['value'], 0, 100) . '..' : $option['value'])
                );
            }

            $optext = '';

            foreach ($options as $option)
            {
                if (isset($option['type']) && $option['type'] != 'file')
                {
                    $value = (isset($option['value']) ? $option['value'] : '');
                }
                else
                {
                    $value = '';
                }
                $optext .= $option['name'] . ': ' . (utf8_strlen($value) > 50 ? utf8_substr($value, 0, 50) . '..' : $value) . ' ';
            }

            $optext = utf8_substr($optext, 0, 499);

            $brand = $this->getProductBrandName($product['product_id']);
            $cat_data = $this->getProductCatName($product['product_id']);
            $gtin = $this->getProductGTIN($product['product_id']);
            $sku = $this->getProductSKU($product['product_id']);

            if (isset($cat_data))
            {
                $category_name = $cat_data['category'];
                $item_list_id = $cat_data['item_list_id'];
                $item_list_name = $cat_data['item_list_name'];
                $item_category = $cat_data['item_category'];
                $item_category2 = $cat_data['item_category2'];
                $item_category3 = $cat_data['item_category3'];
                $item_category4 = $cat_data['item_category4'];
                $item_category5 = $cat_data['item_category5'];
            }

            $price = $this
                ->currency
                ->format($product['price'] + ($this
                ->config
                ->get('config_tax') ? $product['tax'] : 0) , $order_info['currency_code'], $order_info['currency_value'], false);
            $fprice = $this
                ->currency
                ->format($product['price'] + ($this
                ->config
                ->get('config_tax') ? $product['tax'] : 0) , $tagmanager['alt_currency'], '', false);
            $total = $this
                ->currency
                ->format($product['total'] + ($this
                ->config
                ->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0) , $order_info['currency_code'], $order_info['currency_value'], false);
            $ftotal = $this
                ->currency
                ->format($product['total'] + ($this
                ->config
                ->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0) , $tagmanager['alt_currency'], '', false);

            $pid = $this->tagmangerPmap($product['model'], $product['model'], $product['product_id']);

            $title = $this->tagmangerPtitle($product['name'], $brand, $product['model'], $product['product_id']);

            $data['products'][] = array(
                'name' => $product['name'],
                'title' => $title,
                'model' => $product['model'],
                'pid' => $pid,
                'gtin' => (isset($gtin) ? $gtin : '') ,
                'sku' => (isset($sku) ? $sku : '') ,
                'product_id' => $product['product_id'],
                'category' => (isset($category_name) ? $category_name : '') ,
                'category_id' => (isset($item_list_id) ? $item_list_id : '') ,
                'brand' => (isset($brand) ? $brand : '') ,
                'option' => $option_data,
                'quantity' => $product['quantity'],
                'price' => number_format((float)$price, 2, '.', '') ,
                'fprice' => number_format((float)$fprice, 2, '.', '') ,
                'ftotal' => number_format((float)$ftotal, 2, '.', '') ,
                'total' => number_format((float)$total, 2, '.', '')
            );

            $data['ga4_data'][] = array(
                'item_id' => (isset($pid) ? (string)$pid : '') ,
                'item_name' => (isset($title) ? $title : '') ,
                'item_brand' => (isset($brand) ? $brand : '') ,
                'item_category' => (isset($item_category) ? $item_category : '') ,
                'item_category2' => (isset($item_category2) ? $item_category2 : '') ,
                'item_category3' => (isset($item_category3) ? $item_category3 : '') ,
                'item_category4' => (isset($item_category4) ? $item_category4 : '') ,
                'item_category5' => (isset($item_category5) ? $item_category5 : '') ,
                'item_list_id' => (isset($item_list_id) ? $item_list_id : '') ,
                'item_list_name' => (isset($item_list_name) ? $item_list_name : '') ,
                'item_variant' => $optext,
                'affiliation' => (isset($affiliation) ? $affiliation : '') ,
                'discount' => 0,
                'coupon' => (isset($coupon) ? $coupon : '') ,
                'price' => number_format((float)$price, 2, '.', '') ,
                'curency' => $tagmanager['currency'],
                'quantity' => $product['quantity']
            );
        }

        return $data;

    }

    public function getOrderOptions($order_id, $order_product_id)
    {
        $query = $this
            ->db
            ->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

        return $query->rows;
    }

    public function getOrderTax($order_id)
    {
        $tax_query = $this
            ->db
            ->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'tax'");
        $order_tax = '0.00';
        if ($tax_query->num_rows)
        {
            $order_tax = $tax_query->row['value'];
        }
        return $order_tax;
    }

    public function getOrderShipping($order_id)
    {
        $shipping_query = $this
            ->db
            ->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'shipping'");
        $order_shipping = '0.00';
        if ($shipping_query->num_rows)
        {
            $order_shipping = $shipping_query->row['value'];
        }
        return $order_shipping;

    }

    public function getOrderCoupon($order_id)
    {
        $coupon_query = $this
            ->db
            ->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'coupon'");
        $order_coupon = '';
        if ($coupon_query->num_rows)
        {
            $order_coupon = $coupon_query->row['title'];
        }
        return $order_coupon;
    }

    private function getOrderTotalAdjustment($order_id, $value)
    {
        $tagmanager = $this->config();
        $plus_value = 0;
        $minus_value = 0;
        $sub_total = 0;
        $order_totals = array();

        if (!isset($tagmanager['total_plus']) || !isset($tagmanager['total_minus']))
        {
            $order_total_plus = array(
                'cod_fee',
                'handling',
                'klarna_fee',
                'low_order_fee'
            );
            $order_total_minus = array(
                'credit',
                'reward',
                'voucher'
            );
        }
        else
        {
            $order_total_plus = $tagmanager['total_plus'];
            $order_total_minus = $tagmanager['total_minus'];
        }

        foreach ($order_total_plus as $code)
        {

            if (!empty($code))
            {
                $query = $this
                    ->db
                    ->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = '" . $this
                    ->db
                    ->escape($code) . "'");
                if ($query->num_rows)
                {
                    $plus_value = $plus_value + $query->row['value'];
                }
            }
        }

        foreach ($order_total_minus as $code)
        {
            if (!empty($code))
            {
                $query = $this
                    ->db
                    ->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = '" . $this
                    ->db
                    ->escape($code) . "'");
                if ($query->num_rows)
                {
                    $minus_value = $minus_value + $query->row['value'];
                }
            }
        }

        $query = $this
            ->db
            ->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'sub_total'");
        if ($query->num_rows)
        {
            $sub_total = $query->row['value'];
        }

        $query = $this
            ->db
            ->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");
        if ($query->num_rows)
        {
            $order_totals = $query;
        }

        $data = array(
            'plus' => $plus_value * $value,
            'minus' => $minus_value * $value,
            'sub_total' => $sub_total * $value,
            'order_totals' => $order_totals,

        );

        return $data;
    }

    public function GAorderAdd($order_id, $data)
    {
        $cid = '';
        $tagmanager = $this->config();
        if (isset($order_id) && isset($data) && isset($data['currency_code']))
        {

            $query = $this
                ->db
                ->query("SELECT * FROM " . DB_PREFIX . "analytics_tracking WHERE order_id = '" . (int)$order_id . "'");

            if (!$query->num_rows)
            {
                $this
                    ->db
                    ->query("INSERT INTO `" . DB_PREFIX . "analytics_tracking` SET order_id = '" . (int)$order_id . "', cid = '" . $this
                    ->db
                    ->escape($tagmanager['cid']) . "', currency_code = '" . $this
                    ->db
                    ->escape($data['currency_code']) . "', currency_id = '" . $this
                    ->db
                    ->escape($data['currency_id']) . "', uid = '" . $this
                    ->db
                    ->escape($tagmanager['userid']) . "', ul = '" . $this
                    ->db
                    ->escape($tagmanager['language']) . "', ip = '" . $this
                    ->db
                    ->escape($data['ip']) . "', user_agent = '" . $this
                    ->db
                    ->escape($data['user_agent']) . "', tid = '" . $this
                    ->db
                    ->escape($tagmanager['gid']) . "'");
            }
        }
        else
        {
            if (isset($tagmanager['debug']) && $tagmanager['debug'])
            {
                $this->tmerror('Tagmanager Debug log: Error GAorderAdd empty data OrderID: ' . $order_id);
            }
        }
    }

    public function GAupdateorder($order_id)
    {
        if (isset($order_id) && !empty($order_id))
        {
            $this
                ->db
                ->query("UPDATE `" . DB_PREFIX . "analytics_tracking` SET hit = '1' WHERE order_id = '" . (int)$order_id . "'");
        }
        return 'Order hit updated ';
    }

    public function OrderStatusCheck($order_id)
    {
        if (isset($order_id) && !empty($order_id))
        {
            $query = $this
                ->db
                ->query("SELECT order_id, order_status_id from `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");

            $order_status_id = 0;

            if ($query->num_rows)
            {
                $order_status_id = $query->row['order_status_id'];
            }
        }
        return $order_status_id;
    }

    private function DeliveryEstimate($cutoftime, $days = 7, $shipping_code = null)
    {

        date_default_timezone_set("Europe/London");

        $dayofweek = date("N", time()); /* get today day */

        if ($dayofweek < 5)
        { /* IS THIS WEEKDAYS (MON-THU) */
            if (time() <= strtotime($cutoftime))
            {
                $dispathtoday = true;
                $addday = 0;
            }
            else
            {
                $dispathtoday = false;
                $addday = 1;
            }
        }
        else if ($dayofweek == 5)
        { /* Friday */
            if (time() <= strtotime($cutoftime))
            {
                $dispathtoday = true;
                $addday = 0;
            }
            else
            {
                $dispathtoday = false;
                $addday = 3;
            }
        }
        else if ($dayofweek == 6)
        { /* SAT */
            $dispathtoday = false;
            $addday = 2;
        }
        else if ($dayofweek == 7)
        { /* SUN */
            $dispathtoday = false;
            $addday = 1;
        }

        $dispathdate = time() + ($addday * 24 * 60 * 60);

        if (isset($shipping_code) && $shipping_code)
        { /* Custom Shipping Code */
            if ($shipping_code == 'customshipping.customshipping0')
            {
                $scode = '3-5 days';
                $estdelivery = $dispathdate + (7 * 24 * 60 * 60);
            }
            else if ($shipping_code == 'customshipping.customshipping1')
            {
                $scode = '2 days';
                $estdelivery = $dispathdate + (3 * 24 * 60 * 60);
            }
            else if ($shipping_code == 'customshipping.customshipping2')
            {
                $scode = '1 day';
                $estdelivery = $dispathdate + (2 * 24 * 60 * 60);
            }
            else if ($shipping_code == 'customshipping.customshipping3')
            {
                $scode = '1 days';
                $estdelivery = $dispathdate + (2 * 24 * 60 * 60);
            }
            else if ($shipping_code == 'customshipping.customshipping4')
            {
                $scode = '1 days';
                $estdelivery = $dispathdate + (2 * 24 * 60 * 60);
            }
            else
            {
                $scode = '5 days';
                $estdelivery = $dispathdate + (7 * 24 * 60 * 60);
            }
        }
        else
        {

            $estdelivery = $dispathdate + ($days * 24 * 60 * 60);
        }

        return $estdelivery;
    }

    private function getSizeAndColorOptionMap($product_id, $store_id)
    {
        $color_id = $this->getOptionId($product_id, $store_id, 'color');
        $size_id = $this->getOptionId($product_id, $store_id, 'size');

        $groups = $this
            ->googleshopping
            ->getGroups($product_id, $this
            ->config
            ->get('config_language_id') , $color_id, $size_id);

        $colors = $this
            ->googleshopping
            ->getProductOptionValueNames($product_id, $this
            ->config
            ->get('config_language_id') , $color_id);
        $sizes = $this
            ->googleshopping
            ->getProductOptionValueNames($product_id, $this
            ->config
            ->get('config_language_id') , $size_id);

        $map = array(
            'groups' => $groups,
            'colors' => count($colors) > 1 ? $colors : null,
            'sizes' => count($sizes) > 1 ? $sizes : null,
        );

        return $map;
    }

    private function getCountry($country_id)
    {
        $tagmanager = $this->settings;
        $data = false;
        if (isset($tagmanager['cache']) && $tagmanager['cache'])
        {
            $data = $this
                ->cache
                ->get('tagmanager.country.' . $country_id);
        }
        if ($data)
        {
            return $data;
        }
        $data = array();

        $query = $this
            ->db
            ->query("SELECT * FROM " . DB_PREFIX . "country WHERE country_id = '" . (int)$country_id . "'");

        $data = $query->row;

        if (isset($tagmanager['cache']) && $tagmanager['cache'] == '1')
        {
            $this
                ->cache
                ->set('tagmanager.country.' . $country_id, $data);
        }
        return $data;
    }

    private function getSettings($code, $key = 'date_modified', $value = false, $clean = false)
    {
        if ($clean)
        {
            $this
                ->db
                ->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `key` = '" . $this
                ->db
                ->escape($code) . "'");
        }
        else
        {
            if (substr(VERSION, 0, 1) == '1')
            {
                $this
                    ->db
                    ->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `group` = '" . $this
                    ->db
                    ->escape($code) . "'");
                $this
                    ->db
                    ->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `group` = '" . $this
                    ->db
                    ->escape($code) . "', `key` = '" . $this
                    ->db
                    ->escape($key) . "', `value` = '" . $this
                    ->db
                    ->escape($value) . "'");

            }
            else
            {
                $this
                    ->db
                    ->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = '" . $this
                    ->db
                    ->escape($code) . "'");
                $this
                    ->db
                    ->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `code` = '" . $this
                    ->db
                    ->escape($code) . "', `key` = '" . $this
                    ->db
                    ->escape($key) . "', `value` = '" . $this
                    ->db
                    ->escape($value) . "'");
            }
        }
    }

    private function getSettingValue($key, $store_id = 0)
    {
        $data = false;
        $data = $this
            ->cache
            ->get('tagmanager.settings.' . $key . '.' . $store_id);

        if (!$data)
        {

            $query = $this
                ->db
                ->query("SELECT value FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $this
                ->db
                ->escape($key) . "'");

            if ($query->num_rows)
            {
                $data = $query->row['value'];
                $this
                    ->cache
                    ->set('tagmanager.settings.' . $key . '.' . $store_id, $data);
            }
        }
        return $data;
    }

    /* apis */

    public function apiOrderSend($order_id)
    {

        $tagmanager = $this->config();
        $order_id = (int)$order_id;
        $json['error'] = true;
        $json['message'] = 'error in apiOrderSend()';
        $response_ua['error'] = true;
        $response_ga4['error'] = true;
        $response_pixel['error'] = true;

        if ($order_id == 0)
        {
            return 'Invalid Order Id';
        }

        $this
            ->load
            ->model('checkout/order');
        $query = $this
            ->db
            ->query("SELECT * FROM " . DB_PREFIX . "analytics_tracking WHERE order_id = '" . (int)$order_id . "' AND hit = '0'");
        $data = array();

        $ua_api = $this->checkapiStatus('ua');
        $ga4_api = $this->checkapiStatus('ga4');

        if ($query->num_rows)
        {
            $data['cid'] = $query->row['cid'];
            $data['currency_code'] = $query->row['currency_code'];
            $data['ip'] = $query->row['ip'];
            $data['user_agent'] = $query->row['user_agent'];
        }
        else
        {
            $message = 'Tagmanager Debug Log: Measurement Protocol call [ Order: ' . $order_id . ' ] Result: Order not found';
            $newquery = $this
                ->db
                ->query("SELECT * FROM " . DB_PREFIX . "analytics_tracking WHERE order_id = '" . (int)$order_id . "'");
            if ($newquery->num_rows)
            {
                $hit = (isset($newquery->row['hit']) ? $newquery->row['hit'] : 0);
                if ($hit == 1)
                {
                    $message = 'Tagmanager Debug Log: Measurement Protocol call [ Order: ' . $order_id . ' ] Result: Order already hit';
                }
            }
            $this->tmerror($message);
            $json['message'] = 'error order not found or already hit';
            return $json;
        }

        $order_status_id = $this->OrderStatusCheck($order_id);

        if ($order_status_id == '0')
        {
            $this->tmerror('Tagmanager Debug Log: Measurement Protocol call [ Order: ' . $order_id . ' ] Result: Order Status Id is 0 / Missing');
            $json['message'] = 'Incomplete or Missing Order';
            return $json;
        }

        $result = $this->getOrder($order_id);

        $data = array_merge($data, $result);

        if ($ua_api)
        {

            $para = '';
            $para .= "v=1";
            $para .= "&tid=" . $tagmanager['gid'];
            $para .= "&cid=" . $data['cid'];
            $para .= "&t=event&ec=Purchase&ea=sale";
            $para .= "&dh=" . $tagmanager['host'];
            $para .= "&dp=checkout/success";
            $para .= "&dt=Order%20Complete";
            $para .= "&ti=" . $order_id;
            $para .= "&ta=";
            $para .= "&cu=" . $data['currency_code'];
            $para .= "&tr=" . $data['ec_orderValue'];
            $para .= "&tt=" . $data['ec_orderTax'];
            $para .= "&ts=" . $data['ec_orderShipping'];
            $para .= (!empty($data['ec_orderCoupon']) ? "&tc=" . $data['ec_orderCoupon'] : '');
            $para .= "&aip=1&ds=web&uip=" . $data['ip'];
            $para .= "&pa=purchase";

            $i = 1;

            foreach ($data['ec_orderProducts']['products'] as $product)
            {
                if (!isset($product['pid']))
                {
                    $this->tmerror('UA Measurement Protocol call [ Order: ' . $order_id . ' ] Product data missing not sent.');
                    $json['message'] = 'error product data not found';
                    return $json;
                }
                $product['category'] = str_replace(">", "/", $product['category']);
                $product['category'] = str_replace("&", "and", $product['category']);
                $product['category'] = str_replace("amp;", "", $product['category']);

                $para .= "&pr" . $i . "id=" . $product['pid'] . "&pr" . $i . "nm=" . $product['title'] . "&pr" . $i . "ca=" . $product['category'] . "&pr" . $i . "br=" . $product['brand'];
                $para .= "&pr" . $i . "qt=" . $product['quantity'] . "&pr" . $i . "pr=" . $product['price'];
                if (isset($product['option']))
                {
                    $optext = '';
                    foreach ($product['option'] as $op)
                    {
                        $optext .= $op['name'];
                    }
                    $para .= "&pr" . $i . "va=" . utf8_substr($optext, 0, 499);
                }

                $i++;
            }
            parse_str($para, $orderdata);
            $response_ua = $this->post_UA($orderdata, false);
            if (isset($response_ua['error']) & !$response_ua['error'])
            {
                $json['message'] = 'UA Order Hit sent';
            }
            else
            {
                $json['message'] = 'UA Order Hit Failed';
                $this->tmerror('GA-UA api Failed Sending Order id: ' . $order_id . ' Result: ' . $response_ua['message']);
            }
        }

        if ($ga4_api)
        {

            $items = array();

            foreach ($data['ec_orderProducts']['ga4_data'] as $product)
            {
                if (!isset($product['item_id']))
                {
                    $this->tmerror('GA4 API Call:  [ Order: ' . $order_id . ' ] Product data missing not sent.');
                    $json['message'] = 'error product data not found';
                    return $json;
                }

                $items[] = array(
                    'item_id' => $product['item_id'],
                    'item_name' => $product['item_name'],
                    'item_brand' => $product['item_brand'],
                    'item_category' => $product['item_category'],
                    'item_category2' => $product['item_category2'],
                    'item_category3' => $product['item_category3'],
                    'item_category4' => $product['item_category4'],
                    'item_category5' => $product['item_category5'],
                    'item_list_id' => $product['item_list_id'],
                    'item_list_name' => $product['item_list_name'],
                    'item_variant' => $product['item_variant'],
                    'affiliation' => $data['ec_affiliate_code'],
                    'discount' => $product['discount'],
                    'coupon' => $data['ec_orderCoupon'],
                    'price' => $product['price'],
                    'curency' => $product['curency'],
                    'quantity' => $product['quantity'],
                );
            }
            $params = array(
                'affiliation' => '',
                'coupon' => $data['ec_orderCoupon'],
                'currency' => $data['currency_code'],
                'items' => $items,
                'transaction_id' => $order_id,
                'shipping' => $data['ec_orderShipping'],
                'value' => $data['ec_orderValue'],
                'tax' => $data['ec_orderTax'],
            );

            $events[] = array(
                'name' => 'purchase',
                'params' => $params,
            );

            $ga4_payload = array(
                'client_id' => $data['cid'],
                'events' => $events
            );

            $ga4_payload = json_encode($ga4_payload);
            $response_ga4 = $this->post_GA4($ga4_payload, false);

        }

        if (!$response_ua['error'] || !$response_ga4['error'])
        {
            $json['error'] = false;
            $this->tmerror('Google API [ Order: ' . $order_id . ' ] Result: success order data posted');
            $this->GAupdateorder($order_id);
        }

        return $json;
    }

    public function apiOrderRefund($order_id)
    {

        $this
            ->load
            ->model('checkout/order');
        $query = $this
            ->db
            ->query("SELECT * FROM " . DB_PREFIX . "analytics_tracking WHERE order_id = '" . (int)$order_id . "' AND hit = '1'");
        $data = array();
        $json['error'] = true;
        $json['message'] = 'error in apiOrderRefund';

        $tagmanager = $this->config();

        $ua_api = $this->checkapiStatus('ua');
        $ga4_api = $this->checkapiStatus('ga4');

        if ($query->num_rows)
        {
            $data['cid'] = $query->row['cid'];
            $data['currency_code'] = $query->row['currency_code'];
            $data['ip'] = $query->row['ip'];
            $data['user_agent'] = $query->row['user_agent'];
        }
        else
        {
            $this->tmerror('Tagmanager Debug Log: Measurement Protocol Refund Order id: ' . $order_id . '  Result: error order not found or not hit');
            $json['message'] = 'Refund: error order not found or alrady refunded';
            return $json;
        }

        $order_status_id = $this->OrderStatusCheck($order_id);

        if ($order_status_id == '0')
        {
            $this->tmerror('Tagmanager Debug Log: Measurement Protocol Refund Order id: ' . $order_id . ' Result: Incomplete or Missing Order');
            $json['message'] = 'Error: The order do not have valid status code 0';
            return $json;
        }

        $result = $this->getOrder($order_id);

        $data = array_merge($data, $result);

        $para = '';
        $para .= "v=1";
        $para .= "&tid=" . $tagmanager['gid'];
        $para .= "&cid=" . $data['cid'];
        $para .= "&t=event&ec=Purchase&ea=sale";
        $para .= "&dh=" . $tagmanager['host'];
        $para .= "&dp=refund";
        $para .= "&dt=Refund";
        $para .= "&ti=" . $order_id;
        $para .= "&cu=" . $data['currency_code'];
        $para .= "&ta=";
        $para .= "&ni=1";
        $para .= "&tr=-" . $data['ec_orderValue'];
        $para .= "&tt=-" . $data['ec_orderTax'];
        $para .= "&ts=-" . $data['ec_orderShipping'];
        $para .= "&aip=1&ds=web&uip=" . $data['ip'];
        $para .= "&pa=purchase";

        $i = 1;

        foreach ($data['ec_orderProducts']['products'] as $product)
        {
            $product['category'] = str_replace(">", "/", $product['category']);
            $product['category'] = str_replace("&", "and", $product['category']);
            $product['category'] = str_replace("amp;", "", $product['category']);

            $para .= "&pr" . $i . "id=" . $product['pid'] . "&pr" . $i . "nm=" . $product['title'] . "&pr" . $i . "ca=" . $product['category'] . "&pr" . $i . "br=" . $product['brand'];
            $para .= "&pr" . $i . "qt=-" . $product['quantity'] . "&pr" . $i . "pr=" . $product['price'];
            if (isset($product['option']))
            {
                $optext = '';
                foreach ($product['option'] as $op)
                {
                    $optext .= $op['name'];
                }
                $para .= "&pr" . $i . "va=" . utf8_substr($optext, 0, 499);
            }

            $i++;
        }

        parse_str($para, $orderdata);

        $json = $this->post_UA($orderdata, false);
        if (isset($json['error']) & !$json['error'])
        {
            $json['message'] = 'Order Refund sent successfully to Analytics';
            $this
                ->db
                ->query("UPDATE `" . DB_PREFIX . "analytics_tracking` SET hit = '2' WHERE order_id = '" . (int)$order_id . "'");
            $this->tmerror('Tagmanager Debug Log: MP Refund Order id: ' . $order_id . ' Result: Success');
        }
        else
        {
            $json['message'] = 'Unable to send refund hit, curl returned error';
            $this->tmerror('Tagmanager Debug Log: MP Failed Refund Order id: ' . $order_id . ' Result: ' . $json['message']);
        }

        return $json;
    }

    public function apiOrderChecker($url, $post)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        $content = curl_exec($curl);
        curl_close($curl);
        $content = (isset($content) ? json_decode($content, true) : false);
        return $content;
    }

    public function GAContact()
    {

        $tagmanager = $this->config();

        $data = array(
            'v' => '1',
            'tid' => $tagmanager['gid'],
            'cid' => $tagmanager['cid'],
            't' => 'event',
            'ec' => 'contact',
            'ea' => 'contact',
            'el' => 'contact',
            'ni' => '0',
            'dp' => '/conact',
            'dt' => 'Contact Form'
        );

        $curl = curl_init('https://www.google-analytics.com/collect');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);

        curl_close($curl);

        $json = json_decode($response, true);

        if (isset($tagmanager['debug_api']) && $tagmanager['debug_api'])
        {
            $this->tmerror('Tagmanager Debug Log: Measurement Protocol Contact Event. Result: Success');
        }

        return $json;
    }

    public function GAcheckoutSteps($data)
    {

        $tagmanager = $this->config();

        if (!isset($tagmanager['gid']) || $tagmanager['mp'] == '0')
        {
            if (isset($tagmanager['debug']) && $tagmanager['debug'])
            {
                $this->tmerror('Tagmanager Debug Log: Measurement Protocol Checkout Steps: Result: error analytics id or mp not set');
            }
            return 'error analytics id or mp not set';
        }

        if (isset($data['gadata_goals']) && isset($data['gadata_ec']) && isset($data['gadata_ec']['ecommerce']['step']))
        {

            $data = array(
                'v' => '1',
                'tid' => $tagmanager['gid'],
                'cid' => $tagmanager['cid'],
                't' => 'event',
                'ec' => (isset($data['gadata_ec']['event']) ? $data['gadata_ec']['event'] : '') ,
                'ea' => (isset($data['gadata_ec']['eventAction']) ? $data['gadata_ec']['eventAction'] : '') ,
                'el' => (isset($data['gadata_ec']['eventLabel']) ? $data['gadata_ec']['eventLabel'] : '') ,
                't' => 'event',
                'dp' => $data['gadata_goals']['goalPageUrl'],
                'dt' => $data['gadata_goals']['goalPageTitle'],
                'cos' => (isset($data['gadata_ec']['ecommerce']['step']) ? $data['gadata_ec']['ecommerce']['step'] : 0) ,
                'col' => (isset($data['gadata_ec']['ecommerce']['option']) ? $data['gadata_ec']['ecommerce']['option'] : '')
            );

            $curl = curl_init('https://www.google-analytics.com/collect');
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);

            curl_close($curl);

            $json = json_decode($response, true);

            if (isset($tagmanager['debug_api']) && $tagmanager['debug_api'])
            {
                $this->tmerror('Tagmanager Debug Log: Measurement Protocol Checkout Step Event. Result: Success');
            }

            return $json;
        }
        if (isset($tagmanager['debug_api']) && $tagmanager['debug_api'])
        {
            $this->tmerror('Tagmanager Debug Log: Measurement Protocol Checkout Step Failed');
        }
    }

    public function post_UA($data, $debug = false)
    {

        if (!isset($data))
        {
            return;
        }

        $tagmanager = $this->config();

        if (!$debug)
        {
            $curl = curl_init('https://www.google-analytics.com/collect');
        }
        else
        {
            $curl = curl_init('https://www.google-analytics.com/debug/collect');
        }

        $curl = curl_init('https://www.google-analytics.com/collect');

        $json['error'] = true;
        $json['message'] = 'error in post_ua';

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        $result_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $response_text = json_decode($response, true);

        if ($result_code != '200')
        {
            $json['error'] = true;
            $json['message'] = $response_text;
        }
        else
        {
            $json['error'] = false;
            $json['message'] = $response_text;
        }

        if (isset($tagmanager['debug_api']) && $tagmanager['debug_api'])
        {
            $this->tmerror('GA UA API Callback: ' . $result_code . ' -- ' . ' Order Data Posted ');
        }

        return $json;

    }

    public function post_GA4($data, $debug = false)
    {

        if (!isset($data))
        {
            return;
        }

        $tagmanager = $this->config();

        $curl = curl_init('https://www.google-analytics.com/mp/collect?measurement_id=' . $tagmanager['ga4_mid'] . '&api_secret=' . $tagmanager['ga4_api']);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        $result_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $response_text = json_decode($response, true);

        $json['code'] = $result_code;

        if ($result_code != '204' || $result_code != '200')
        {
            $json['error'] = true;
            $json['message'] = $response_text;
        }
        else
        {
            $json['error'] = false;
            $json['message'] = $response_text;
        }

        if (isset($tagmanager['debug_api']) && $tagmanager['debug_api'])
        {
            $this->tmerror('GA4 API Callback : ' . $result_code . ' Order Data Posted ');
        }

        return $json;

    }

    public function pixelView($value, $event_id, $tagmanager = false)
    {
        if (!$tagmanager)
        {
            $this->tmerror('Missing Tagmanager config');
            return false;
        }
        $fb_data = array(
            'content_type' => $value,
            'event_id' => $event_id,
        );
        $event = 'ViewContent';

        if ($value == 'contact')
        {
            $event_id = '3-' . $this->eventid();
            $event = 'Contact';
        }

        if ($value == 'signup')
        {
            $event_id = '12-' . $this->eventid();
            $event = 'CompleteRegistration';
        }

        if ($value == 'pageview')
        {
            $event = 'PageView';
        }

        if ($value == 'cart')
        {
            $event_id = '13-' . $this->eventid();
            $event = 'ViewCart';
        }

        $pixel_post = $this->pixelSetup($tagmanager, $event, $fb_data);

    }

    public function pixelSetup($tagmanager, $event, $data)
    {
        $fb_data = array();
        $pixel_data = array();

        if (!$tagmanager)
        {
            $this->tmerror('Missing Tagmanager Config in API Call');
            return false;

        }

        if (isset($tagmanager['debug_api']) && $tagmanager['debug_api'])
        {
            $debug = true;
        }
        else
        {
            $debug = false;
        }
        if (!$tagmanager['fb_api'])
        {
            return false;
        }

        if (empty($tagmanager['fb_token']))
        {
            return false;
        }

        if ($tagmanager['bot'])
        {
            return false;
        }
        if ($event == 'ViewContent')
        {
            $event_name = 'View content';
        }
        elseif ($event == 'Search')
        {
            $event_name = 'Search';
        }
        elseif ($event == 'ViewCategory')
        {
            $event_name = 'ViewCategory';
        }
        elseif ($event == 'Contact')
        {
            $event_name = 'Contact';
        }
        elseif ($event == 'AddToWishlist')
        {
            $event_name = 'Add to wishlist';
        }
        elseif ($event == 'AddToCart')
        {
            $event_name = 'Add to cart';
        }
        elseif ($event == 'ViewCart')
        {
            $event_name = 'ViewCart';
        }
        elseif ($event == 'InitiateCheckout')
        {
            $event_name = 'Initiate checkout';
        }
        elseif ($event == 'AddPaymentInfo')
        {
            $event_name = 'Add payment info';
        }
        elseif ($event == 'Purchase')
        {
            $event_name = 'Purchase';
        }
        elseif ($event == 'PageView')
        {
            $event_name = 'PageView';
        }
        else
        {
            $event_name = '';
        }

        $event_id = (isset($data['event_id']) ? $data['event_id'] : false);
        $event_time = time();
        $api_status = $tagmanager['fb_api'];
        $access_token = $tagmanager['fb_token'];
        $action_source = 'website';
        $event_source_url = $tagmanager['url'];
        $external_id = $this->readGTMCookie('OCSESSID');

        if (empty($external_id))
        {
            $external_id = $this
                ->session
                ->getId();
            $external_id = $external_id;
        }

        $user_data = array(
            'client_ip_address' => $tagmanager['ip_address'],
            'client_user_agent' => $tagmanager['user_agent'],
            'fbc' => $tagmanager['fbc'],
            'fbp' => $tagmanager['fbp'],
            'external_id' => $external_id
        );

        if (isset($tagmanager['em']) && !empty($tagmanager['em']))
        {
            $user_data['em'] = $tagmanager['em'];
        }
        if (isset($tagmanager['ph']) && !empty($tagmanager['ph']))
        {
            $user_data['ph'] = $tagmanager['ph'];
        }
        if (isset($tagmanager['fn']) && !empty($tagmanager['fn']))
        {
            $user_data['fn'] = $tagmanager['fn'];
        }
        if (isset($tagmanager['ln']) && !empty($tagmanager['ln']))
        {
            $user_data['ln'] = $tagmanager['ln'];
        }
        if (isset($tagmanager['ct']) && !empty($tagmanager['ct']))
        {
            $user_data['ct'] = $tagmanager['ct'];
        }
        if (isset($tagmanager['st']) && !empty($tagmanager['st']))
        {
            $user_data['st'] = $tagmanager['st'];
        }
        if (isset($tagmanager['pc']) && !empty($tagmanager['pc']))
        {
            $user_data['zp'] = $tagmanager['pc'];
        }
        if (isset($tagmanager['cc']) && !empty($tagmanager['c']))
        {
            $user_data['country'] = $tagmanager['cc'];
        }

        $custom_data = $data;

        $fb_data[] = array(
            'event_name' => $event,
            'event_id' => $event_id,
            'event_time' => $event_time,
            'action_source' => $action_source,
            'event_source_url' => $event_source_url,
            'user_data' => $user_data,
            'custom_data' => $custom_data,

        );

        if (!empty($event_id))
        {
            $result = $this->post_Pixel($fb_data, $access_token, $tagmanager['pixelcode'], $tagmanager, $debug);
        }
        else
        {
            if ($debug)
            {
                $this->tmerror('Pixel API Post Data Invalid: ' . json_encode($fb_data));
            }
            return false;
        }
    }

    public function post_Pixel($data, $access_token, $pixel_id, $tagmanager = false, $debug = false)
    {

        if (!isset($data))
        {
            return;
        }
        $post_data = json_encode($data);

        $fields = array();
        $fields['access_token'] = $access_token;
        $fields['data'] = $post_data;
        if (isset($tagmanager['pixel_test_code']) && !empty($tagmanager['pixel_test_code']))
        {
            $fields['test_event_code'] = $tagmanager['pixel_test_code'];
        }

        if (!$tagmanager)
        {
            $this->tmerror('Missing Tagmanger Config in API Call');
            return;
        }

        $api_version = 'v13.0';

        $curl = curl_init('https://graph.facebook.com/' . $api_version . '/' . $pixel_id . '/events');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        $result_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $response_text = json_decode($response, true);

        if ($result_code != '200')
        {
            $json['error'] = true;
            $json['message'] = $response_text . "\n" . $post_data;
        }
        else
        {
            $json['error'] = false;
            $json['message'] = '';
        }

        if ($debug)
        {
            if ($json['error'])
            {
                $this->tmerror('Pixel API Post Data: ' . $response . "\n" . $json['message']);
            }
            else
            {
                $this->tmerror('Pixel API Response Code: ' . $result_code . "\n" . $post_data);
            }
        }

        return $json;

    }

    public function sendinbluePost($data, $method = 'identify')
    {

        $tagmanager = $this->config();

        if (isset($tagmanager['debug_api']) && $tagmanager['debug_api'])
        {
            $debug = true;
        }
        else
        {
            $debug = false;
        }

        if (!isset($data))
        {
            if ($debug)
            {
                $this->tmerror('Sendinblue error Code: Empty body api call cancelled');
            }
            return false;
        }

        $url = "https://in-automate.sendinblue.com/api/v2/$method";

        $headers = array(
            'Content-Type: application/json',
            'ma-key: ' . $tagmanager['sendinblue_code']
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data) ,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err)
        {
            if ($debug)
            {
                $this->tmerror('Sendinblue CURL error: ' . $err);
                $this->tmerror('Sendinblue CURL response: ' . $response);
            }
        }
    }

    /* helpers */

    public function tmerror($line)
    {
        $key = 'tmcrom_date';
        $code = 'tmcron';
        $date = date("d/m/Y");
        $value = date('d/m/Y', strtotime('+1 days'));
        $cron = $this
            ->config
            ->get($key);
        $logfile = DIR_LOGS . "tagmanager.log";
        if (!isset($cron) || empty($cron) || $date >= $cron)
        {
            if (file_exists($logfile))
            {
                $fsize = filesize($logfile);
                $this->getSettings($code, $key, $value, false);
                if ($fsize > 2400000)
                {
                    unlink($logfile);
                }
            }
        }

        $this
            ->registry
            ->set('tmlog', new Log('tagmanager.log'));

        $tagmanager = $this->config();

        if (isset($tagmanager['debug']) && $tagmanager['debug'])
        {
            $this
                ->tmlog
                ->write($line);
        }
    }

    public function tmprint($data = array() , $killme = true)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if (isset($this
            ->request
            ->get['killme']))
        {
            $killme = true;
        }
        if ($killme)
        {
            die;
        }
    }

    private function checkapiStatus($platform)
    {

        $tagmanager = $this->config();

        if (!isset($platform))
        {
            return false;
        }
        if ($platform == 'ua')
        {
            if (!isset($tagmanager['gid']) || $tagmanager['mp'] == '0')
            {
                if (isset($tagmanager['debug']) && $tagmanager['debug'])
                {
                    $this->tmerror('Tagmanager Debug Log: API Check failed for UA, error analytics id or mp not set');
                }
                return false;
            }
            else
            {
                return true;
            }
        }

        if ($platform == 'ga4')
        {
            if (!isset($tagmanager['ga4_mid']) && empty($tagmanager['ga4_api']))
            {
                if (isset($tagmanager['debug']) && $tagmanager['debug'])
                {
                    $this->tmerror('Tagmanager Debug Log: API Check failed for GA4, error GA4 API secret missing');
                }
            }
            else
            {
                return true;
            }
        }

        return false;
    }

    public function check_array($var)
    {
        return is_array($var) || $var instanceof \Countable || $var instanceof \SimpleXMLElement || $var instanceof \ResourceBundle;
    }

    public function escapeJsonString($value)
    {
        $escapers = array(
            "\\",
            "/",
            "\"",
            "\n",
            "\r",
            "\t",
            "\x08",
            "\x0c"
        );
        $replacements = array(
            "\\\\",
            "\\/",
            "\\\"",
            "\\n",
            "\\r",
            "\\t",
            "\\f",
            "\\b"
        );
        $result = str_replace($escapers, $replacements, $value);
        return $result;
    }

    private function getNewURL()
    {
        $url = false;
        $temp = $this
            ->request
            ->server['SERVER_NAME'];
        $explode = explode(".", $temp);
        $counter = $this->check_array($explode);
        if ($counter)
        {
            $i = count($explode);
            if ($i == 2)
            {
                $url = $explode[0] . '.' . $explode[1];
            }
            elseif ($i == 3)
            {
                if (strtolower($explode[0]) != 'www')
                {
                    $url = $explode[0] . '.' . $explode[1] . '.' . $explode[2];
                }
                else
                {
                    $url = $explode[1] . '.' . $explode[2];
                }
            }
            elseif ($i == 4)
            {
                $url = $explode[1] . '.' . $explode[2] . '.' . $explode[3];
            }
        }
        return $url;
    }

    public function cleanStr($data)
    {
        $data = str_replace('"', "", $data);
        $data = str_replace("'", "", $data);
        $data = str_replace("&#039;", "", $data);
        $data = str_replace("quot;", "", $data);
        $data = str_replace("&amp;", "&", $data);
        $data = str_replace("&", "&amp;", $data);
        $data = str_replace("&amp;", "", $data);
        $data = utf8_substr(trim(strip_tags(html_entity_decode($data, ENT_QUOTES, 'UTF-8'))) , 0, 50);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        $data = str_replace("&amp;", "", $data);
        $data = str_replace("&gt;", ">", $data);
        $data = str_replace("  ", " ", $data);
        return $data;
    }

    private function getEmailHash($data)
    {

        if (!isset($data) || empty($data))
        {
            return '';
        }

        $data = trim($data);
        $data = strtolower($data);

        if ($this->isHashed($data))
        {
            return $data;
        }
        return hash('sha256', $data, false);
    }

    private function getPhoneHash($data, $country = false)
    {

        if (!isset($data) || empty($data))
        {
            return '';
        }

        $data = trim($data);
        $data = strtolower($data);

        if ($this->isHashed($data))
        {
            return $data;
        }
        return hash('sha256', $data, false);
    }

    private function getHash($data)
    {

        if (!isset($data) || empty($data))
        {
            return '';
        }

        $data = trim($data);
        $data = strtolower($data);

        if ($this->isHashed($data))
        {
            return $data;
        }
        return hash('sha256', $data, false);
    }

    private function getEncrypt($data)
    {

        if (!isset($data))
        {
            return false;
        }
        try
        {
            $ciphering = "AES-128-CTR";
            $iv_length = openssl_cipher_iv_length($ciphering);
            $options = 0;
            $encryption_iv = '1234567891011121';
            $encryption_key = "GTMEXTENSIONBYAITS";
            $encryption = openssl_encrypt($data, $ciphering, $encryption_key, $options, $encryption_iv);

            return $encryption;
        }
        catch(Exception $e)
        {
            $this->tmerror('OpenSSL encrypt failer');
        }
        return false;
    }

    private function getDecrypt($data)
    {

        if (!isset($data))
        {
            return false;
        }
        try
        {
            $ciphering = "AES-128-CTR";
            $iv_length = openssl_cipher_iv_length($ciphering);
            $options = 0;
            $encryption_iv = '1234567891011121';
            $encryption_key = "GTMEXTENSIONBYAITS";
            $encryption = openssl_decrypt($data, $ciphering, $encryption_key, $options, $encryption_iv);

            return $encryption;
        }
        catch(Exception $e)
        {
            $this->tmerror('OpenSSL decrypt failer');
        }
        return false;
    }

    private function isHashed($data)
    {

        return preg_match('/^[A-Fa-f0-9]{64}$/', $data) || preg_match('/^[a-f0-9]{32}$/', $data);
    }

    public function getIpAddress()
    {
        $ip_address = '0.0.0.0';

        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && !empty($_SERVER['HTTP_CF_CONNECTING_IP']))
        {
            $ip_address = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $temp_ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if ($this->strFind($temp_ip_address, ','))
            {
                $temp_ip_address = explode(",", $temp_ip_address);
                $ip_address = array_pop($temp_ip_address);
            }
            else
            {
                $ip_address = $temp_ip_address;
            }
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED']) && !empty($_SERVER['HTTP_X_FORWARDED']))
        {
            $ip_address = $_SERVER['HTTP_X_FORWARDED'];
        }
        elseif (isset($_SERVER['HTTP_FORWARDED_FOR']) && !empty($_SERVER['HTTP_FORWARDED_FOR']))
        {
            $ip_address = $_SERVER['HTTP_FORWARDED_FOR'];
        }
        elseif (isset($_SERVER['HTTP_FORWARDED']) && !empty($_SERVER['HTTP_FORWARDED']))
        {
            $ip_address = $_SERVER['HTTP_FORWARDED'];
        }
        elseif (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']))
        {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }

        return $ip_address;
    }

    public function getHttpUserAgent()
    {
        $user_agent = null;

        if (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT']))
        {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
        }
        elseif (isset($this
            ->request
            ->server['HTTP_USER_AGENT']))
        {
            $user_agent = $this
                ->request
                ->server['HTTP_USER_AGENT'];
        }

        return $user_agent;
    }

    public function getRequestUri()
    {

        $url = "http://";
        if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        {
            $url = "https://";
        }

        if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST']))
        {
            $url .= $_SERVER['HTTP_HOST'];
        }

        if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI']))
        {
            $url .= $_SERVER['REQUEST_URI'];
        }

        return $url;
    }

    private function getFbp()
    {
        $fbp = null;

        if (isset($_COOKIE['_fbp']) && !empty($_COOKIE['_fbp']))
        {
            $fbp = $_COOKIE['_fbp'];
        }

        return $fbp;
    }

    private function getFbc()
    {
        $fbc = null;

        if (isset($_COOKIE['_fbc']) && !empty($_COOKIE['_fbc']))
        {
            $fbc = $_COOKIE['_fbc'];
        }
        else
        {
            if (isset($_GET['fbclid']))
            {
                $fbc = 'fb.1.' . time() . '.' . $_GET['fbclid'];

            }
        }

        return $fbc;
    }

    private function getCuid()
    {
        $cuid = null;

        if (isset($_COOKIE['sib_cuid']) && !empty($_COOKIE['sib_cuid']))
        {
            $cuid = $_COOKIE['sib_cuid'];
        }

        return $cuid;
    }

    private function GUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid() , '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535) , mt_rand(0, 65535) , mt_rand(0, 65535) , mt_rand(16384, 20479) , mt_rand(32768, 49151) , mt_rand(0, 65535) , mt_rand(0, 65535) , mt_rand(0, 65535));
    }

    private function checkbot($agent)
    {

        if (!isset($agent) || empty($agent))
        {
            return true;
        }
        if (stripos($agent, "bot") !== false)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private function getCurrentURL()
    {
        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']) , 'https') === false ? 'http' : 'https';
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        $params = $_SERVER['QUERY_STRING'] == '' ? '' : '?' . $_SERVER['QUERY_STRING'];

        return $protocol . '://' . $host . $script . $params;
    }

    private function botDetect()
    {
        $agent = $this->getHttpUserAgent();
        if (isset($agent) && preg_match('/bot|crawl|slurp|spider|mediapartners/i', $agent))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function redirect($url, $status = 302)
    {
        $this
            ->response
            ->redirect($url);
    }

    public function unserialize($data = array())
    {
        return json_decode($data, true);
    }

    public function get_numeric($val)
    {
        if (is_numeric($val))
        {
            return $val + 0;
        }
        return 0;
    }

    public function getHost()
    {
        if ($this
            ->request
            ->server['HTTPS'])
        {
            $host_server = 'https://' . (isset($this
                ->request
                ->server['SERVER_NAME']) ? $this
                ->request
                ->server['SERVER_NAME'] : '');
        }
        else
        {
            $host_server = 'http://' . (isset($this
                ->request
                ->server['SERVER_NAME']) ? $this
                ->request
                ->server['SERVER_NAME'] : '');
        }
        return $host_server;
    }

    public function strFind($haystack, $needle)
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }

}

