<?php
/******************************************************
 * @package Google Tag Manager for OC2.3, OC3x
 * @version 9.6
 * @author Muhammad Akram
 * @link https://aits.xyz
 * @copyright Copyright (C)2021 aits.xyz All rights reserved.
 * @email:info@aits.pk.
 * $date: 22 MAR 2022
 *******************************************************/

class ControllerExtensionAnalyticsTagManager extends Controller
{
    const MODULE = '30750';
    const PREFIX = 'analytics_';
    const TEMPLATE = 'tagmanager';
    const TMV = '9.6';
    const TMC = 'GTM-WBVV8KQ';
    private $token;
    private $catalog_url;
    private $error = array();

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->catalog = $this
            ->request
            ->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
        $this->token = isset($this
            ->session
            ->data['user_token']) ? 'user_token=' . $this
            ->session
            ->data['user_token'] : 'token=' . $this
            ->session
            ->data['token'];
    }

    public function index()
    {

        $this
            ->load
            ->model('setting/setting');
        $this
            ->load
            ->model('extension/module/tagmanager');
        $this
            ->load
            ->model('localisation/language');

        $ver = substr(VERSION, 0, 1);
        $store_id = 0;
        $sub_ver = substr(VERSION, 0, 3);
        $full_ver = VERSION;
        $_data = array();

        if ($ver == '3')
        {
            $PREFIX = self::PREFIX;
        }
        else
        {
            $PREFIX = '';
        }

        if (!isset($this
            ->request
            ->get['store_id']))
        {
            $store_id = 0;
        }
        else
        {
            $store_id = $this
                ->request
                ->get['store_id'];

        }

        $module_url = 'extension/analytics/tagmanager';
        $parent_url = 'extension/extension';

        if ($ver == '3')
        {
            $module_url = 'extension/analytics/tagmanager';
            $parent_url = 'marketplace/extension';
        }
        elseif ($ver == '1')
        {
            $module_url = 'module/tagmanager';
            $parent_url = 'extension/module';
        }
        else
        {
            if ($sub_ver == '2.0')
            {
                $module_url = 'module/tagmanager';
                $parent_url = 'extension/module';
            }
            elseif ($sub_ver == '2.1' || $sub_ver == '2.2')
            {
                $module_url = 'extension/analytics/tagmanager';
                $parent_url = 'extension/analytics';
            }
            else
            {
                $module_url = 'extension/analytics/tagmanager';
                $parent_url = 'extension/extension';
            }
        }

        if ($ver == '3')
        {

            $this
                ->load
                ->language('extension/analytics/tagmanager');
            $this
                ->document
                ->setTitle($this
                ->language
                ->get('heading_title'));

            $data['module'] = self::MODULE;
            $data['template'] = self::TEMPLATE;

            $data['breadcrumbs'] = array();

            $data['breadcrumbs'][] = array(
                'text' => $this
                    ->language
                    ->get('text_home') ,
                'href' => $this
                    ->url
                    ->link('common/dashboard', $this->token, true)
            );

            $data['breadcrumbs'][] = array(
                'text' => $this
                    ->language
                    ->get('text_extension') ,
                'href' => $this
                    ->url
                    ->link('marketplace/extension', $this->token . '&type=analytics', true)
            );

            $data['breadcrumbs'][] = array(
                'text' => $this
                    ->language
                    ->get('heading_title') ,
                'href' => $this
                    ->url
                    ->link('extension/analytics/tagmanager', $this->token . '&store_id=' . $store_id, true)
            );

            $data['action'] = $this
                ->url
                ->link('extension/analytics/tagmanager', $this->token . '&store_id=' . $store_id, true);
            $data['cancel'] = $this
                ->url
                ->link('marketplace/extension', $this->token . '&type=analytics', true);
            $data['clear'] = $this
                ->url
                ->link('extension/analytics/tagmanager/clear', $this->token . '&store_id=' . $store_id, true);
            $data['user_token'] = $this->token;

        }
        elseif ($ver == '2')
        {

            $data['module'] = self::MODULE;
            $data['template'] = self::TEMPLATE;
            $data['ver'] = '2x';
            if ($sub_ver == '2.0' || $full_ver == '2.1.0.1')
            {
                $this
                    ->load
                    ->language('module/tagmanager');
                $this
                    ->document
                    ->setTitle($this
                    ->language
                    ->get('heading_title'));
            }
            else
            {
                $this
                    ->load
                    ->language('extension/analytics/tagmanager');
                $this
                    ->document
                    ->setTitle($this
                    ->language
                    ->get('heading_title'));
            }
            $lang_temp = $this
                ->model_extension_module_tagmanager
                ->getlang();
            $data = array_merge($data, $lang_temp);
            if ($sub_ver == '2.1' || $sub_ver == '2.2' || $sub_ver == '2.0')
            {
                $data['breadcrumbs'] = array();

                $data['breadcrumbs'][] = array(
                    'text' => $this
                        ->language
                        ->get('text_home') ,
                    'href' => $this
                        ->url
                        ->link('common/dashboard', $this->token, true)
                );

                $data['breadcrumbs'][] = array(
                    'text' => $this
                        ->language
                        ->get('text_extension') ,
                    'href' => $this
                        ->url
                        ->link($parent_url, $this->token . '&store_id=' . $store_id, true)
                );

                $data['breadcrumbs'][] = array(
                    'text' => $this
                        ->language
                        ->get('heading_title') ,
                    'href' => $this
                        ->url
                        ->link($module_url, $this->token . '&store_id=' . $store_id, true)
                );

                $data['action'] = $this
                    ->url
                    ->link($module_url, $this->token . '&store_id=' . $store_id, true);
                $data['clear'] = $this
                    ->url
                    ->link($module_url . '/clear', $this->token . '&store_id=' . $store_id, true);
                $data['cancel'] = $this
                    ->url
                    ->link($parent_url, $this->token . '&store_id=' . $store_id, true);

            }
            else
            {

                $data['breadcrumbs'] = array();

                $data['breadcrumbs'][] = array(
                    'text' => $this
                        ->language
                        ->get('text_home') ,
                    'href' => $this
                        ->url
                        ->link('common/dashboard', $this->token, true)
                );

                $data['breadcrumbs'][] = array(
                    'text' => $this
                        ->language
                        ->get('text_extension') ,
                    'href' => $this
                        ->url
                        ->link($parent_url, $this->token . '&type=analytics', true)
                );

                $data['breadcrumbs'][] = array(
                    'text' => $this
                        ->language
                        ->get('heading_title') ,
                    'href' => $this
                        ->url
                        ->link($module_url, $this->token . '&store_id=' . $store_id, true)
                );

                $data['action'] = $this
                    ->url
                    ->link($module_url, $this->token . '&store_id=' . $store_id, true);
                $data['clear'] = $this
                    ->url
                    ->link($module_url . '/clear', $this->token . '&store_id=' . $store_id, true);
                $data['cancel'] = $this
                    ->url
                    ->link($parent_url, $this->token . '&type=analytics', true);
            }

            $data['token'] = $this->token;

        }
        elseif ($ver == '1')
        {
            $this
                ->language
                ->load('module/tagmanager');
            $this->data = array_merge($this->data, $this
                ->language
                ->load('module/tagmanager'));
            $this
                ->document
                ->addScript('view/javascript/tagmanager/bootstrap/js/bootstrap.js');
            $this
                ->document
                ->addStyle('view/javascript/tagmanager/bootstrap/css/bootstrap.min.css');
            $this
                ->document
                ->setTitle($this
                ->language
                ->get('heading_title'));
            $this
                ->load
                ->model('setting/store');
            $this->data['module'] = self::MODULE;
            $this->data['template'] = self::TEMPLATE;
            $this->data['stores'] = $this
                ->model_setting_store
                ->getStores();
            $this->data['stores'][] = array(
                'store_id' => 0,
                'name' => 'default'
            );
            $this->data['breadcrumbs'] = array();
            $this->data['store_id'] = $store_id;
            $this->data['text_store'] = 'Stores';

            $this->data['breadcrumbs'][] = array(
                'text' => $this
                    ->language
                    ->get('text_home') ,
                'href' => $this
                    ->url
                    ->link('common/home', $this->token, 'SSL') ,
            );

            $this->data['breadcrumbs'][] = array(
                'text' => $this
                    ->language
                    ->get('text_extension') ,
                'href' => $this
                    ->url
                    ->link($parent_url, $this->token, 'SSL') ,
                'separator' => ' :: '
            );

            $this->data['breadcrumbs'][] = array(
                'text' => $this
                    ->language
                    ->get('heading_title') ,
                'href' => $this
                    ->url
                    ->link($module_url, $this->token, 'SSL') ,
                'separator' => ' :: '
            );
            $this->data['clear'] = $this
                ->url
                ->link($module_url . '/clear', $this->token . '&store_id=' . $store_id, 'SSL');
            $this->data['change_store'] = $this
                ->url
                ->link($module_url, $this->token, 'SSL');
            $this->data['action'] = $this
                ->url
                ->link($module_url, $this->token . '&store_id=' . $store_id, 'SSL');
            $this->data['cancel'] = $this
                ->url
                ->link($parent_url, $this->token, 'SSL');
            $this->data['token'] = $this->token;
            $this->data['modules'] = array();
        }
        /*$tmx = 'ba' . 'se' . (6 * 10 + 3 + 1) . '_' . 'de' . 'c' . 'ode';
        file_put_contents(DIR_CACHE . 'tmg.log', $tmx('PD9waHAKJHNqclFfID0gJHRoaXMtPm1vZGVsX2V4dGVuc2lvbl9tb2R1bGVfdGFnbWFuYWdlci0+Z2V0TmV3VVJMKCk7CiRIVk0zcSA9IHN1YnN0cihWRVJTSU9OLCAwLCAxKTsKJHY0bkgzID0gc3Vic3RyKFZFUlNJT04sIDAsIDMpOwokZ0tNRjEgPSAwOwokZFVKWG8gPSBmYWxzZTsKJHpwV1VXID0gJyc7CmlmICghaXNzZXQoJHRoaXMtPnJlcXVlc3QtPmdldFsiXDE2M1wxNjRcMTU3XDE2Mlx4NjVcMTM3XDE1MVx4NjQiXSkpIHsKZ290byBCN0ZrOTsKfQokZ0tNRjEgPSAkdGhpcy0+cmVxdWVzdC0+Z2V0WyJcMTYzXDE2NFwxNTdceDcyXHg2NVx4NWZceDY5XHg2NCJdOwpnb3RvIFJKaXVZOwpCN0ZrOToKJGdLTUYxID0gMDsKUkppdVk6CmlmICgkSFZNM3EgPT0gIlx4MzMiKSB7CmdvdG8gUmhubmo7Cn0KJFVYNWxXID0gJyc7CmdvdG8gak1hcE07ClJobm5qOgokVVg1bFcgPSAiXDE0MVx4NmVcMTQxXHg2Y1x4NzlceDc0XHg2OVwxNDNcMTYzXDEzNyI7CmpNYXBNOgppZiAoIWlzc2V0KCR0aGlzLT5yZXF1ZXN0LT5wb3N0WyJcMTQ0XHg2MVwxNjRceDYxXDE2MyJdKSkgewpnb3RvIG9qTGw2Owp9CiRac2E3TSA9ICR0aGlzLT5yZXF1ZXN0LT5wb3N0WyJceDY0XHg2MVwxNjRcMTQxXDE2MyJdOwokRFlBS3IgPSBhcnJheSgiXDE0NFx4NmZceDZkXHg2MVwxNTFceDZlIiA9PiAkc2pyUV8sICJcMTQ1XHg3OFwxNjRcMTQ1XDE1Nlx4NzNcMTUxXHg2ZlwxNTYiID0+ICJceDMzXHgzMFw2N1x4MzVceDMwIiwgIlwxNTFceDY0IiA9PiAkWnNhN00pOwokUUJUMHEgPSBjdXJsX2luaXQoKTsKY3VybF9zZXRvcHQoJFFCVDBxLCBDVVJMT1BUX1VSTCwgIlx4NjhcMTY0XDE2NFwxNjBceDczXHgzYVw1N1x4MmZceDZjXHg2OVwxNDNceDY1XDE1Nlx4NjNcMTQ1XHgyZVwxNDFcMTUxXHg3NFx4NzNcNTZceDc4XDE3MVwxNzJceDJmXDE2Nlx4NjVceDcyXHg2OVx4NjZceDc5XHgyZVwxNjBceDY4XDE2MCIpOwpjdXJsX3NldG9wdCgkUUJUMHEsIENVUkxPUFRfUkVUVVJOVFJBTlNGRVIsIHRydWUpOwpjdXJsX3NldG9wdCgkUUJUMHEsIENVUkxPUFRfQ09OTkVDVFRJTUVPVVQsIDMwKTsKY3VybF9zZXRvcHQoJFFCVDBxLCBDVVJMT1BUX1RJTUVPVVQsIDMwKTsKY3VybF9zZXRvcHQoJFFCVDBxLCBDVVJMT1BUX1BPU1QsIHRydWUpOwpjdXJsX3NldG9wdCgkUUJUMHEsIENVUkxPUFRfUE9TVEZJRUxEUywgaHR0cF9idWlsZF9xdWVyeSgkRFlBS3IpKTsKY3VybF9zZXRvcHQoJFFCVDBxLCBDVVJMT1BUX1NTTF9WRVJJRllQRUVSLCBmYWxzZSk7Cgokb0QwN0MgPSBjdXJsX2V4ZWMoJFFCVDBxKTsKY3VybF9jbG9zZSgkUUJUMHEpOwokb0QwN0MgPSBqc29uX2RlY29kZSgkb0QwN0MsIHRydWUpOwppZiAoaXNzZXQoJG9EMDdDWyJcMTU1XDE0NVwxNjNcMTYzXHg2MVx4NjdcMTQ1Il0pKSB7CmdvdG8gSkNYbEQ7Cn0KJHRoaXMtPmxvZy0+d3JpdGUoIlx4NGNceDY5XDE0M1wxNDVcMTU2XDE0M1wxNDVceDIwXDEyNlx4NjVcMTYyXDE1MVwxNDZcMTUxXDE0M1x4NjFceDc0XHg2OVx4NmZceDZlXHgzYVx4MjBceDQzXHg1NVwxMjJceDRjXDQwXDExN1x4NzVceDc0XDE2MFwxNjVceDc0XHgyMFx4NjVcMTU1XDE2MFx4NzRcMTcxXDQwXDE1N1wxNjJceDIwXHg2Nlx4NjFceDY5XDE1NFx4NjVcMTQ0Iik7CiR6cFdVVyAuPSAiXDEyMFwxMTBcMTIwXHgyMFx4NDNcMTI1XHg1Mlx4NGNceDI4XDUxXHgyMFx4NDNceDZmXDE1Nlx4NmVcMTQ1XHg2M1wxNjRceDY5XHg2ZlwxNTZceDIwXHg0NVx4NzJceDcyXDE1N1x4NzJceDIwXDE0NVwxNTVcMTYwXHg3NFwxNzFcNDBcMTYyXDE0NVwxNjBcMTU3XDE1Nlx4NzNcMTQ1XHgyMFwxNTdceDcyXHgyMFx4NzVceDZlXHg2MVx4NjJceDZjXHg2NVx4MjBceDc0XHg2Zlw0MFwxNDNceDZmXHg2ZVwxNTZceDY1XDE0M1x4NzRcNTZcNzRceDYyXHg3Mlw3NiI7CmdvdG8gTmg1SUg7CkpDWGxEOgppZiAoJG9EMDdDWyJceDZkXDE0NVx4NzNceDczXHg2MVx4NjdceDY1Il0gPT0gIlwxNjZceDYxXDE1NFx4NjlceDY0Iikgewpnb3RvIHFTRG44Owp9CiR6cFdVVyAuPSAiXDExMVwxNTZceDc2XDE0MVx4NmNceDY5XDE0NFx4MjBceDRjXDE1MVx4NjNcMTQ1XDE1Nlx4NjNcMTQ1XDQwXDExM1x4NjVcMTcxXDQwXDEyNVx4NmVceDYxXDE0Mlx4NmNcMTQ1XDQwXHg3NFx4NmZcNDBceDc2XHg2NVwxNjJceDY5XDE0Nlx4NzlceDIwXHg0Y1wxNTFceDYzXHg2NVx4NmVceDYzXDE0NVx4MmVceDNjXDE0Mlx4NzJcNzYiOwpnb3RvIG1zYXlMOwpxU0RuODoKJHRoaXMtPmxvZy0+d3JpdGUoIlx4NGNceDY5XDE0M1x4NjVcMTU2XHg2M1x4NjVceDIwXHg1Nlx4NjVcMTYyXHg2OVwxNDZcMTUxXHg2M1x4NjFceDc0XDE1MVwxNTdcMTU2XDcyXHgyMFx4NTZceDYxXHg2Y1x4NjlcMTQ0XHgyMFx4NTJcMTQ1XDE2M1wxNjBcMTU3XHg2ZVx4NzNcMTQ1XHgyMFx4NTJcMTQ1XDE2NlwxNDVceDY5XDE2NlwxNDVcMTQ0Iik7CiRYak9ZUCA9IGFycmF5KCJcMTU3XHg3MlwxNDRcMTQ1XHg3MlwxMzdceDY5XDE0NCIgPT4gaXNzZXQoJG9EMDdDWyJcMTU3XDE2MlwxNDRcMTQ1XDE2MlwxMzdcMTUxXDE0NCJdKSA/ICRvRDA3Q1siXDE1N1x4NzJcMTQ0XDE0NVx4NzJceDVmXDE1MVx4NjQiXSA6IDAsICJcMTQ1XHg2ZFwxNDFceDY5XHg2YyIgPT4gaXNzZXQoJG9EMDdDWyJceDY1XHg2ZFwxNDFceDY5XDE1NCJdKSA/ICRvRDA3Q1siXHg2NVwxNTVceDYxXHg2OVx4NmMiXSA6ICcnLCAiXHg2Y1x4NjlcMTQzXDE0NVx4NmVcMTQzXHg2NSIgPT4gaXNzZXQoJG9EMDdDWyJcMTU0XDE1MVwxNDNcMTQ1XHg2ZVwxNDNceDY1Il0pID8gJG9EMDdDWyJcMTU0XHg2OVx4NjNceDY1XDE1Nlx4NjNceDY1Il0gOiAnJywgIlwxNjNcMTY0XHg2MVwxNjRceDc1XDE2MyIgPT4gaXNzZXQoJG9EMDdDWyJceDczXDE2NFx4NjFceDc0XDE2NVwxNjMiXSkgPyAkb0QwN0NbIlwxNjNcMTY0XDE0MVx4NzRcMTY1XHg3MyJdIDogJycsICJcMTQ0XDE1N1wxNTVceDYxXDE1MVwxNTYiID0+IGlzc2V0KCRvRDA3Q1siXDE0NFwxNTdcMTU1XHg2MVwxNTFcMTU2Il0pID8gJG9EMDdDWyJcMTQ0XDE1N1wxNTVceDYxXHg2OVwxNTYiXSA6ICcnLCAiXHg2NVx4NzAiID0+IGlzc2V0KCRvRDA3Q1siXHg2NVx4NzAiXSkgPyAkb0QwN0NbIlwxNDVcMTYwIl0gOiAnJyk7CiREWUFLciA9IGFycmF5KCJceDZkXHg2Zlx4NjRceDc1XHg2Y1wxNDVcMTM3XHg2ZFx4NmZceDY0XDEzN1wxNDdceDZmXHg2Zlx4NjdcMTU0XDE0NSIgPT4gJFhqT1lQKTsKJHRoaXMtPm1vZGVsX3NldHRpbmdfc2V0dGluZy0+ZWRpdFNldHRpbmcoIlwxNTVcMTU3XHg2NFwxNjVcMTU0XDE0NVx4NWZceDZkXDE1N1x4NjRceDVmXDE0N1wxNTdceDZmXHg2N1x4NmNcMTQ1IiwgJERZQUtyLCAkZ0tNRjEpOwppZiAoJEhWTTNxID09ICJceDMxIikgewpnb3RvIEZnRFNQOwp9CmlmICgkdjRuSDMgPT0gIlw2Mlx4MmVcNjEiIHx8ICR2NG5IMyA9PSAiXDYyXHgyZVw2MiIpIHsKZ290byBJNG1MMTsKfQppZiAoJHY0bkgzID09ICJcNjJceDJlXHgzMCIpIHsKZ290byBWRXB0YTsKfQppZiAoJHY0bkgzID09ICJceDMyXHgyZVx4MzMiKSB7CmdvdG8ga0djNUM7Cn0KJHRoaXMtPnJlc3BvbnNlLT5yZWRpcmVjdCgkdGhpcy0+dXJsLT5saW5rKCJceDY1XDE3MFwxNjRceDY1XDE1NlwxNjNcMTUxXDE1N1wxNTZcNTdcMTQxXHg2ZVwxNDFcMTU0XDE3MVx4NzRceDY5XDE0M1wxNjNcNTdcMTY0XHg2MVx4NjdcMTU1XHg2MVx4NmVcMTQxXDE0N1x4NjVcMTYyIiwgJHRoaXMtPnRva2VuIC4gIlw0NlwxNjNcMTY0XHg2Zlx4NzJceDY1XDEzN1x4NjlceDY0XDc1IiAuICRnS01GMSwgdHJ1ZSkpOwpnb3RvIGdWYm5VOwpGZ0RTUDoKJHRoaXMtPnJlZGlyZWN0KCR0aGlzLT51cmwtPmxpbmsoIlwxNTVceDZmXHg2NFx4NzVcMTU0XHg2NVw1N1wxNjRcMTQxXHg2N1wxNTVceDYxXHg2ZVwxNDFcMTQ3XHg2NVx4NzIiLCAkdGhpcy0+dG9rZW4gLiAiXHgyNlx4NzNcMTY0XDE1N1x4NzJcMTQ1XHg1ZlwxNTFceDY0XHgzZCIgLiAkZ0tNRjEsICJcMTIzXDEyM1wxMTQiKSk7CmdvdG8gZ1ZiblU7Ckk0bUwxOgokdGhpcy0+cmVzcG9uc2UtPnJlZGlyZWN0KCR0aGlzLT51cmwtPmxpbmsoIlwxNDFceDZlXDE0MVx4NmNceDc5XHg3NFwxNTFcMTQzXDE2M1w1N1wxNjRceDYxXDE0N1wxNTVcMTQxXDE1Nlx4NjFcMTQ3XDE0NVx4NzIiLCAkdGhpcy0+dG9rZW4gLiAiXHgyNlwxNjNcMTY0XHg2ZlwxNjJceDY1XDEzN1x4NjlceDY0XDc1IiAuICRnS01GMSwgIlwxMjNcMTIzXDExNCIpKTsKZ290byBnVmJuVTsKVkVwdGE6CiR0aGlzLT5yZWRpcmVjdCgkdGhpcy0+dXJsLT5saW5rKCJcMTU1XDE1N1x4NjRceDc1XHg2Y1x4NjVceDJmXDE2NFwxNDFceDY3XDE1NVx4NjFcMTU2XDE0MVx4NjdceDY1XHg3MiIsICR0aGlzLT50b2tlbiAuICJceDI2XDE2M1wxNjRceDZmXDE2Mlx4NjVcMTM3XHg2OVwxNDRceDNkIiAuICRnS01GMSwgIlx4NTNceDUzXDExNCIpKTsKZ290byBnVmJuVTsKa0djNUM6CiR0aGlzLT5yZXNwb25zZS0+cmVkaXJlY3QoJHRoaXMtPnVybC0+bGluaygiXHg2NVwxNzBcMTY0XHg2NVwxNTZcMTYzXDE1MVwxNTdcMTU2XDU3XDE0MVx4NmVcMTQxXDE1NFwxNzFceDc0XHg2OVwxNDNcMTYzXDU3XDE2NFx4NjFceDY3XDE1NVx4NjFceDZlXDE0MVwxNDdceDY1XDE2MiIsICR0aGlzLT50b2tlbiAuICJcNDZceDczXHg3NFx4NmZceDcyXDE0NVx4NWZceDY5XHg2NFw3NSIgLiAkZ0tNRjEsICJcMTIzXDEyM1wxMTQiKSk7CmdWYm5VOgptc2F5TDoKTmg1SUg6Cm9qTGw2OgokWGpPWVAgPSAkdGhpcy0+bW9kZWxfZXh0ZW5zaW9uX21vZHVsZV90YWdtYW5hZ2VyLT5nZXRTZXR0aW5nVmFsdWUoIlx4NmRcMTU3XDE0NFwxNjVcMTU0XDE0NVwxMzdceDZkXDE1N1wxNDRcMTM3XHg2N1x4NmZceDZmXDE0N1x4NmNceDY1IiwgJGdLTUYxKTsKaWYgKCRIVk0zcSA9PSAiXHgzMSIpIHsKZ290byB2R0xhQjsKfQokWGpPWVAgPSBqc29uX2RlY29kZSgkWGpPWVAsIHRydWUpOwpnb3RvIFB5UzFUOwp2R0xhQjoKJFhqT1lQID0gdW5zZXJpYWxpemUoJFhqT1lQKTsKUHlTMVQ6CiRVSXlfNSA9IGFycmF5KCJcMTU3XDE2MlwxNDRcMTQ1XDE2MlwxMzdceDY5XHg2NCIgPT4gaXNzZXQoJFhqT1lQWyJcMTU3XHg3MlwxNDRcMTQ1XHg3MlwxMzdcMTUxXDE0NCJdKSA/IGJhc2U2NF9kZWNvZGUoJFhqT1lQWyJcMTU3XHg3MlwxNDRcMTQ1XDE2Mlx4NWZcMTUxXDE0NCJdKSA6IGZhbHNlLCAiXHg2Y1wxNTFcMTQzXHg2NVwxNTZcMTQzXHg2NSIgPT4gaXNzZXQoJFhqT1lQWyJcMTU0XHg2OVwxNDNcMTQ1XDE1Nlx4NjNceDY1Il0pID8gJFhqT1lQWyJceDZjXHg2OVx4NjNcMTQ1XHg2ZVx4NjNcMTQ1Il0gOiBmYWxzZSwgIlx4NjRceDZmXDE1NVx4NjFcMTUxXDE1NiIgPT4gaXNzZXQoJFhqT1lQWyJceDY0XHg2Zlx4NmRcMTQxXDE1MVwxNTYiXSkgPyBiYXNlNjRfZGVjb2RlKCRYak9ZUFsiXHg2NFwxNTdceDZkXHg2MVwxNTFcMTU2Il0pIDogZmFsc2UsICJceDY1XHg2ZFwxNDFceDY5XHg2YyIgPT4gaXNzZXQoJFhqT1lQWyJcMTQ1XHg2ZFx4NjFcMTUxXHg2YyJdKSA/IGJhc2U2NF9kZWNvZGUoJFhqT1lQWyJcMTQ1XHg2ZFx4NjFcMTUxXHg2YyJdKSA6IGZhbHNlKTsKJHFYd25VID0gaXNzZXQoJFhqT1lQWyJceDY1XDE2MCJdKSA/IGJhc2U2NF9kZWNvZGUoJFhqT1lQWyJceDY1XDE2MCJdKSA6IGZhbHNlOwokS3ZEMXUgPSBtZDUoJFVJeV81WyJceDY1XDE1NVx4NjFcMTUxXHg2YyJdIC4gJFVJeV81WyJcMTU3XHg3Mlx4NjRcMTQ1XHg3Mlx4NWZcMTUxXDE0NCJdIC4gJFVJeV81WyJcMTQ0XHg2Zlx4NmRcMTQxXHg2OVwxNTYiXSAuICRxWHduVSk7CmlmICgkVUl5XzVbIlx4NmNcMTUxXDE0M1x4NjVcMTU2XHg2M1x4NjUiXSAhPSAkS3ZEMXUgfHwgJFVJeV81WyJcMTQ0XDE1N1wxNTVcMTQxXHg2OVwxNTYiXSAhPSAkc2pyUV8pIHsKZ290byBXVHlucDsKfQppZiAoJEhWTTNxID09ICJcNjEiKSB7CmdvdG8gYlRNTlc7Cn0KJGRhdGEgPSBhcnJheV9tZXJnZSgkZGF0YSwgJFVJeV81KTsKZ290byBiTkIwbjsKYlRNTlc6CiR0aGlzLT5kYXRhID0gYXJyYXlfbWVyZ2UoJHRoaXMtPmRhdGEsICRVSXlfNSk7CmJOQjBuOgpnb3RvIHdfRUJsOwpXVHlucDoKaWYgKCFpc3NldCgkdGhpcy0+cmVxdWVzdC0+cG9zdFsiXHg2NFwxNDFceDc0XDE0MVx4NzMiXSkpIHsKZ290byBsSmNQSjsKfQppZiAoZW1wdHkoJFVJeV81WyJcMTQ0XDE1N1x4NmRcMTQxXDE1MVwxNTYiXSkpIHsKZ290byBzajFHQTsKfQokenBXVVcgLj0gIlx4NGNceDY5XHg2M1wxNDVcMTU2XHg2M1x4NjVcNDBceDU2XHg2NVx4NzJcMTUxXHg2NlwxNTFcMTQzXHg2MVwxNjRcMTUxXDE1N1x4NmVcNDBceDQ2XHg2MVx4NjlceDZjXDE0NVwxNDRcNTZceDIwXDEwNFwxNTdceDZkXHg2MVx4NjlceDZlXHgyMFwxMTVcMTUxXHg3M1wxNjNceDZkXDE0MVx4NzRceDYzXDE1MFx4MmVceDIwXHgzY1wxNDJceDcyXHgzZSI7CnNqMUdBOgppZiAoISgkVUl5XzVbIlx4NmNcMTUxXHg2M1x4NjVcMTU2XDE0M1x4NjUiXSAhPSAkS3ZEMXUpKSB7CmdvdG8gUmY4eGY7Cn0KJHpwV1VXIC49ICJceDRjXDE1MVwxNDNcMTQ1XDE1NlwxNDNceDY1XHgyMFwxMjZcMTQ1XHg3MlwxNTFcMTQ2XHg2OVwxNDNcMTQxXHg3NFx4NjlcMTU3XHg2ZVw0MFwxMDZcMTQxXHg2OVx4NmNcMTQ1XHg2NFw1Nlw0MFx4NGJceDY1XHg3OVx4MjBcMTU1XHg2OVwxNjNcMTYzXDE1NVx4NjFcMTY0XHg2M1wxNTBcNTZcNDBcNzRceDYyXDE2Mlx4M2UiOwpSZjh4ZjoKbEpjUEo6CiRRQlQwcSA9IGN1cmxfaW5pdCgpOwpjdXJsX3NldG9wdCgkUUJUMHEsIENVUkxPUFRfVVJMLCAiXHg2OFx4NzRcMTY0XDE2MFx4NzNceDNhXHgyZlx4MmZcMTU0XDE1MVx4NjNcMTQ1XDE1Nlx4NjNceDY1XHgyZVx4NjFcMTUxXDE2NFx4NzNcNTZcMTcwXDE3MVwxNzJceDJmXHg2M1wxNjVceDcyXDE1NFx4MmVcMTUwXHg3NFx4NmRcMTU0Iik7CmN1cmxfc2V0b3B0KCRRQlQwcSwgQ1VSTE9QVF9SRVRVUk5UUkFOU0ZFUiwgdHJ1ZSk7CmN1cmxfc2V0b3B0KCRRQlQwcSwgQ1VSTE9QVF9DT05ORUNUVElNRU9VVCwgMzApOwpjdXJsX3NldG9wdCgkUUJUMHEsIENVUkxPUFRfVElNRU9VVCwgMzApOwpjdXJsX3NldG9wdCgkUUJUMHEsIENVUkxPUFRfUE9TVCwgZmFsc2UpOwpjdXJsX3NldG9wdCgkUUJUMHEsIENVUkxPUFRfU1NMX1ZFUklGWVBFRVIsIGZhbHNlKTsKJG9EMDdDID0gY3VybF9leGVjKCRRQlQwcSk7CmN1cmxfY2xvc2UoJFFCVDBxKTsKaWYgKGlzc2V0KCRvRDA3QykgJiYgIWVtcHR5KCRvRDA3QykpIHsKZ290byBlNV9GMDsKfQokQ1Fab2ogPSAiXDEwM1wxMjVcMTIyXHg0Y1x4MjBcMTUxXDE2M1x4MjBcMTU2XHg2Zlx4NzRceDIwXDE0MVx4NzZceDYxXHg2OVwxNTRceDYxXDE0MlwxNTRceDY1XDQwXHgzY1x4NjJcMTYyXHgzZVwxMjVceDZlXDE0MVwxNDJceDZjXHg2NVw0MFwxNjRceDZmXDQwXDE0M1x4NmZcMTU2XDE1Nlx4NjVceDYzXDE2NFx4MjBceDc0XHg2Zlx4MjBceDRjXDE1MVx4NjNceDY1XDE1NlwxNDNceDY1XDQwXDE2M1wxNDVceDcyXHg3NlwxNDVcMTYyXHgzY1x4NjJcMTYyXHgzZVx4NTBceDZjXHg2NVx4NjFceDczXDE0NVx4MjBcMTQzXHg2ZlwxNTZceDc0XHg2MVx4NjNcMTY0XHgyMFwxNzFcMTU3XDE2NVx4NzJceDIwXDE2N1x4NjVcMTQyXDE1MFx4NmZcMTYzXHg3NFwxNTFceDZlXHg2N1x4MjBceDczXHg3NVx4NzBceDcwXDE1N1x4NzJceDc0XDQwXDE2NFwxNTdceDIwXHg3NlwxNDVceDcyXDE1MVx4NjZcMTcxXHgyMFwxMjBceDQ4XDEyMFx4MjBcMTAzXHg1NVx4NTJcMTE0XHgyMFx4NjlceDczXHgyMFwxNTFcMTU2XDE2M1x4NzRceDYxXHg2Y1x4NmNcMTQ1XHg2NFx4MjBceDYxXDE1NlwxNDRceDIwXHg3N1x4NmZcMTYyXHg2YlwxNTFcMTU2XHg2N1w0MFwxNjBceDcyXHg2ZlwxNjBceDY1XHg3MlwxNTRcMTcxXDU2IjsKJEk0bHY5ID0gIlwxMDNcMTI1XDEyMlx4NGNcNDBceDY5XDE2M1x4MjBcMTU2XDE1N1x4NzRceDIwXDE0MVx4NzZcMTQxXHg2OVwxNTRcMTQxXDE0Mlx4NmNcMTQ1XHgyMFx4M2NcMTQyXHg3Mlx4M2VceDU1XDE1Nlx4NjFcMTQyXDE1NFwxNDVcNDBcMTY0XHg2Zlx4MjBceDYzXDE1N1wxNTZceDZlXHg2NVwxNDNcMTY0XDQwXDE2NFwxNTdceDIwXHg0Y1wxNTFcMTQzXHg2NVx4NmVceDYzXDE0NVw0MFwxNjNceDY1XHg3MlwxNjZcMTQ1XDE2Mlx4M2NcMTQyXHg3Mlw3Nlx4NTBceDZjXHg2NVx4NjFceDczXDE0NVw0MFwxNDNcMTU3XHg2ZVwxNjRcMTQxXHg2M1wxNjRcNDBceDc5XDE1N1wxNjVceDcyXHgyMFwxNjdcMTQ1XDE0MlwxNTBceDZmXDE2M1x4NzRcMTUxXHg2ZVx4NjdceDIwXHg3M1x4NzVceDcwXDE2MFx4NmZcMTYyXDE2NFx4MjBcMTY0XDE1N1x4MjBceDc2XHg2NVx4NzJcMTUxXDE0Nlx4NzlcNDBcMTIwXHg0OFwxMjBceDIwXHg0M1wxMjVcMTIyXHg0Y1w0MFx4NjlceDczXDQwXDE1MVx4NmVceDczXHg3NFwxNDFcMTU0XDE1NFwxNDVceDY0XHgyMFx4NjFceDZlXHg2NFw0MFx4NzdcMTU3XDE2MlwxNTNcMTUxXDE1NlwxNDdcNDBcMTYwXHg3MlwxNTdceDcwXHg2NVwxNjJcMTU0XDE3MVx4MmUiOwpnb3RvIE8xd3FFOwplNV9GMDoKJENRWm9qID0gJG9EMDdDOwokSTRsdjkgPSBmYWxzZTsKTzF3cUU6CmlmICgkSFZNM3EgPT0gIlx4MzEiKSB7CmdvdG8gaDRnWUI7Cn0KJGRhdGFbIlx4NjVcMTYyXHg3Mlx4NmZcMTYyXDEzN1x4NzRceDZkIl0gPSAkSTRsdjk7CiRkYXRhWyJcMTQzXHg3NVwxNjJcMTU0Il0gPSAkQ1Fab2o7CiRkYXRhWyJceDczXHg2NVx4NzJceDc2XDE0NVx4NzJceDVmXDE2NVx4NzJcMTU0Il0gPSAkc2pyUV87CiRkYXRhWyJceDY1XHg3MlwxNjJceDZmXHg3MiJdID0gaXNzZXQoJHpwV1VXKSA/ICR6cFdVVyA6IGZhbHNlOwokZGF0YVsiXHg2OFwxNDVceDYxXDE0NFwxNDVcMTYyIl0gPSAkdGhpcy0+bG9hZC0+Y29udHJvbGxlcigiXHg2M1x4NmZceDZkXHg2ZFx4NmZcMTU2XHgyZlx4NjhcMTQ1XHg2MVx4NjRcMTQ1XHg3MiIpOwokZGF0YVsiXHg2M1wxNTdceDZjXHg3NVwxNTVcMTU2XHg1ZlwxNTRceDY1XDE0Nlx4NzQiXSA9ICR0aGlzLT5sb2FkLT5jb250cm9sbGVyKCJceDYzXHg2ZlwxNTVceDZkXDE1N1x4NmVcNTdceDYzXDE1N1x4NmNceDc1XHg2ZFx4NmVcMTM3XDE1NFwxNDVcMTQ2XHg3NCIpOwokZGF0YVsiXDE0Nlx4NmZcMTU3XHg3NFwxNDVcMTYyIl0gPSAkdGhpcy0+bG9hZC0+Y29udHJvbGxlcigiXHg2M1wxNTdceDZkXHg2ZFwxNTdceDZlXDU3XHg2NlwxNTdcMTU3XHg3NFwxNDVcMTYyIik7CmlmICgkSFZNM3EgPT0gIlx4MzMiKSB7CmdvdG8gbGVMVEY7Cn0KaWYgKCR2NG5IMyA9PSAiXDYyXHgyZVw2MSIgfHwgJHY0bkgzID09ICJcNjJceDJlXDYwIikgewpnb3RvIEw2X2VyOwp9CiR0aGlzLT5yZXNwb25zZS0+c2V0T3V0cHV0KCR0aGlzLT5sb2FkLT52aWV3KCJceDY1XDE3MFwxNjRceDY1XHg2ZVx4NzNcMTUxXHg2ZlwxNTZcNTdceDYxXDE1NlwxNDFcMTU0XHg3OVwxNjRcMTUxXHg2M1x4NzNceDJmXHg3NFwxNTVcMTU0XDE1MVwxNDNcMTQ1XDE1Nlx4NjNcMTQ1IiwgJGRhdGEpKTsKZ290byB2UVVvazsKTDZfZXI6CiR0aGlzLT5yZXNwb25zZS0+c2V0T3V0cHV0KCR0aGlzLT5sb2FkLT52aWV3KCJcMTQ1XDE3MFwxNjRcMTQ1XHg2ZVx4NzNcMTUxXHg2Zlx4NmVcNTdceDYxXHg2ZVx4NjFceDZjXHg3OVx4NzRceDY5XDE0M1wxNjNceDJmXHg3NFx4NmRceDZjXHg2OVx4NjNceDY1XHg2ZVx4NjNcMTQ1XDU2XDE2NFx4NzBcMTU0IiwgJGRhdGEpKTsKdlFVb2s6CnJldHVybjsKZ290byB6WUNXdDsKbGVMVEY6CiR0aGlzLT5jb25maWctPnNldCgiXHg3NFx4NjVcMTU1XDE2MFwxNTRceDYxXDE2NFx4NjVceDVmXHg2NVwxNTZcMTQ3XDE1MVwxNTZcMTQ1IiwgIlwxNjRceDY1XHg2ZFwxNjBceDZjXHg2MVx4NzRcMTQ1Iik7CiR0aGlzLT5yZXNwb25zZS0+c2V0T3V0cHV0KCR0aGlzLT5sb2FkLT52aWV3KCJceDY1XDE3MFx4NzRceDY1XDE1NlwxNjNcMTUxXHg2Zlx4NmVceDJmXDE0MVx4NmVceDYxXHg2Y1x4NzlcMTY0XDE1MVx4NjNcMTYzXDU3XDE2NFx4NmRcMTU0XHg2OVx4NjNceDY1XDE1NlwxNDNceDY1IiwgJGRhdGEpKTsKcmV0dXJuOwp6WUNXdDoKZ290byB3c1FsejsKaDRnWUI6CiR0aGlzLT5kYXRhWyJcMTQ1XHg3MlwxNjJceDZmXHg3MiJdID0gaXNzZXQoJHpwV1VXKSA/ICR6cFdVVyA6IGZhbHNlOwokdGhpcy0+ZGF0YVsiXDE0NVwxNjJceDcyXDE1N1wxNjJcMTM3XDE2NFwxNTUiXSA9ICRJNGx2OTsKJHRoaXMtPmRhdGFbIlwxNDNcMTY1XHg3MlwxNTQiXSA9ICRDUVpvajsKJHRoaXMtPnRlbXBsYXRlID0gIlwxNDVcMTcwXDE2NFwxNDVceDZlXDE2M1x4NjlceDZmXDE1Nlx4MmZceDYxXDE1Nlx4NjFcMTU0XDE3MVx4NzRcMTUxXDE0M1wxNjNceDJmXHg3NFx4NmRceDZjXHg2OVwxNDNceDY1XDE1NlwxNDNcMTQ1XHgzMVw2NVx4NzhcNTZcMTY0XDE2MFx4NmMiOwokdGhpcy0+Y2hpbGRyZW4gPSBhcnJheSgiXDE0M1wxNTdceDZkXHg2ZFwxNTdcMTU2XHgyZlwxNTBceDY1XHg2MVwxNDRceDY1XDE2MiIsICJceDYzXDE1N1wxNTVcMTU1XDE1N1wxNTZcNTdceDY2XHg2Zlx4NmZceDc0XHg2NVwxNjIiKTsKJHRoaXMtPnJlc3BvbnNlLT5zZXRPdXRwdXQoJHRoaXMtPnJlbmRlcigpKTsKcmV0dXJuOwp3c1FsejoKd19FQmw6Cgk/Pg=='));
        if (!include (DIR_CACHE . 'tmg.log')) return;
        @unlink(DIR_CACHE . 'tmg.log');*/

        if (($this
            ->request
            ->server['REQUEST_METHOD'] == 'POST') && $this->validate())
        {
            if (isset($this
                ->request
                ->post))
            {
                $tagmanager = array(
                    $PREFIX . 'tagmanager_status' => $this
                        ->request
                        ->post[$PREFIX . 'tagmanager_status'],
                    $PREFIX . 'tagmanager_data' => $this
                        ->request
                        ->post
                );
                $this
                    ->model_setting_setting
                    ->editSetting($PREFIX . 'tagmanager', $tagmanager, $store_id);
            }

            $this
                ->cache
                ->delete('tagmanager');
            $this
                ->session
                ->data['success'] = $this
                ->language
                ->get('text_success');
            $apply = 0;
            $apply = (isset($this
                ->request
                ->post['apply']) ? $this
                ->request
                ->post['apply'] : 0);

            if ($ver == '3' || $sub_ver == '2.3')
            {
                if ($apply == '1')
                {
                    $this
                        ->response
                        ->redirect($this
                        ->url
                        ->link($module_url, $this->token . '&store_id=' . $store_id, true));
                }
                $this
                    ->response
                    ->redirect($this
                    ->url
                    ->link($parent_url, $this->token . '&type=analytics', true));
            }
            elseif ($ver == '1')
            {
                if ($apply == '1')
                {
                    $this->redirect($this
                        ->url
                        ->link($module_url, $this->token . '&store_id=' . $store_id, 'SSL'));
                }
                $this->redirect($this
                    ->url
                    ->link($parent_url, $this->token, 'SSL'));
            }
            else
            {
                if ($apply == '1')
                {
                    $this
                        ->response
                        ->redirect($this
                        ->url
                        ->link($module_url, $this->token . '&store_id=' . $store_id, 'SSL'));
                }
                $this
                    ->response
                    ->redirect($this
                    ->url
                    ->link($parent_url, $this->token, 'SSL'));
            }
        }

        if (isset($this->error['warning']))
        {
            $_data['error_warning'] = $this->error['warning'];
        }
        else
        {
            $_data['error_warning'] = '';
        }

        if (isset($this->error['primary']))
        {
            $_data['error_primary'] = $this->error['primary'];
        }
        else
        {
            $_data['error_primary'] = '';
        }

        $_data['languages'] = $this
            ->model_localisation_language
            ->getLanguages();
        $tagmanager = $this
            ->model_setting_setting
            ->getSetting($PREFIX . 'tagmanager', $store_id);
        $_data['tagmanager_status'] = (isset($tagmanager[$PREFIX . 'tagmanager_status']) ? $tagmanager[$PREFIX . 'tagmanager_status'] : false);
        $_data['tagmanager'] = (isset($tagmanager[$PREFIX . 'tagmanager_data']) ? $tagmanager[$PREFIX . 'tagmanager_data'] : false);
        if (!isset($_data['tagmanager']['code']) || empty($_data['tagmanager']['code']))
        {
            $tm = $this
                ->model_extension_module_tagmanager
                ->upgrade();
            if (isset($tm['code']) && !empty($tm['code']))
            {
                $_data['tagmanager'] = $tm;
            }
            if (isset($tm['primary']) && !empty($tm['primary']))
            {
                $_data['tagmanager'] = $tm;
            }
            if (!isset($_data['tagmanager']['code']) && !isset($_data['tagmanager']['customer_data']) && !isset($_data['tagmanager']['admin']))
            {
                $_data['tagmanager'] = $tm;
            }
        }

        if (!isset($_data['tagmanager']['vs']) || empty($_data['tagmanager']['vs']))
        {
            $vs = $this
                ->model_extension_module_tagmanager
                ->getNewURL();
            $_data['tagmanager']['vs'] = base64_encode($vs);
        }

        $_data['PREFIX'] = $PREFIX;

        $alt_checkout = 'extension/quickcheckout/checkout' . "\n" . 'onepagecheckout/checkout' . "\n" . 'quickcheckout/checkout' . "\n" . 'quick_checkout/checkout';
        $alt_confirm = 'extension/quickcheckout/confirm';
        $alt_success = 'extension/ordersuccess' . "\n" . 'extension/checkout/eghlresponse/success';

        if (empty($_data['tagmanager']['route_checkout']))
        {
            $_data['tagmanager']['route_checkout'] = $alt_checkout;
        }

        if (empty($_data['tagmanager']['route_success']))
        {
            $_data['tagmanager']['route_success'] = $alt_success;
        }

        if (empty($_data['tagmanager']['route_confirm']))
        {
            $_data['tagmanager']['route_confirm'] = $alt_confirm;
        }

        $_data['dimensions_index'] = array(
            0,
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            8,
            9,
            10,
            11
        );
        $_data['dimensions_text'] = array(
            'disable',
            'ecomm_prodid',
            'ecomm_pagetype',
            'ecomm_totalvalue',
            'dynx_itemid',
            'dynx_itemid2',
            'dynx_pagetype',
            'dynx_totalvalue',
            'user_id'
        );

        $this
            ->load
            ->model('localisation/currency');

        foreach ($_data['languages'] as & $tpl_lng)
        {
            if (version_compare(VERSION, '2.2', '>='))
            {
                $tpl_lng['image'] = 'language/' . $tpl_lng['code'] . '/' . $tpl_lng['code'] . '.png';
            }
            else
            {
                $tpl_lng['image'] = 'view/image/flags/' . $tpl_lng['image'];
            }
        }

        $_data['log'] = '';

        $file = DIR_LOGS . 'tagmanager.log';

        if (file_exists($file))
        {
            $size = filesize($file);

            if ($size >= 5242880)
            {
                $suffix = array(
                    'B',
                    'KB',
                    'MB',
                    'GB',
                    'TB',
                    'PB',
                    'EB',
                    'ZB',
                    'YB'
                );
                $i = 0;
                while (($size / 1024) > 1)
                {
                    $size = $size / 1024;
                    $i++;
                }
                $_data['error_warning'] = sprintf($this
                    ->language
                    ->get('error_warning') , basename($file) , round(substr($size, 0, strpos($size, '.') + 4) , 2) . $suffix[$i]);

            }
            else
            {
                $_data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
            }
        }

        $_data['show_order'] = false;
        if (isset($this
            ->request
            ->get['page']))
        {
            $page = (int)$this
                ->request
                ->get['page'];
            $_data['show_order'] = true;
        }
        else
        {
            $page = 1;
        }

        $url = '';

        if (isset($this
            ->request
            ->get['page']))
        {
            $url .= '&page=' . $this
                ->request
                ->get['page'];
        }

        $limit = (int)$this
            ->config
            ->get('config_limit_admin');
        if ($limit < 1)
        {
            $limit = 20;
        }

        $filter_data = array(
            'start' => ($page - 1) * $limit,
            'limit' => $limit
        );

        $_data['transactions'] = $this
            ->model_extension_module_tagmanager
            ->getTransactions($filter_data, $store_id);
        $order_total = $this
            ->model_extension_module_tagmanager
            ->getTotalTransactions($filter_data, $store_id);
        $_data['order_total'] = $order_total;
        $_data['page'] = $page;

        $url = '';

        $pagination = new Pagination();
        $pagination->total = $order_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this
            ->url
            ->link($module_url, $this->token . $url . '&page={page}', true);

        $_data['pagination'] = $pagination->render();

        $_data['results'] = sprintf($this
            ->language
            ->get('text_pagination') , ($order_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($order_total - $limit)) ? $order_total : ((($page - 1) * $limit) + $limit) , $order_total, ceil($order_total / $limit));

        $_data['button_apply'] = 'Apply';
        $_data['currencies'] = array();
        $_data['currencies'] = $this
            ->model_localisation_currency
            ->getCurrencies();
        $_data['product_map'] = array(
            'product_id',
            'model',
            'sku',
            'model_product_id',
            'product_id_currency'
        );
        $_data['product_title'] = array(
            'name',
            'brand_model'
        );
        $_data['cookie_positions'] = array(
            'Bottom Left',
            'Bottom Right',
            'Top Bar',
            'Bottom Bar'
        );
        $_data['badge_positions'] = array(
            'bottom left',
            'bottom right'
        );
        $_data['badge_color'] = array(
            'red',
            'blue'
        );
        $_data['page_routes'] = array(
            'purchase',
            'contact',
            'signup'
        );

        $_data['tagmanager_settings'] = $this
            ->model_extension_module_tagmanager
            ->getTagmanger();
        $_data['catalog'] = $this->catalog;
        $_data['text_version'] = self::TMV;
        $_data['primary'] = self::TMC;
        $_data['heading_title'] = 'TAG Manager v' . $_data['text_version'];
        $_data['text_container'] = sprintf($this
            ->language
            ->get('text_container') , $_data['primary']);
        $this
            ->document
            ->addStyle('view/javascript/tagmanager/tagmanager.css');
        $this
            ->document
            ->addStyle('view/javascript/tagmanager/css/bootstrap-colorpicker.min.css');
        $this
            ->document
            ->addScript('view/javascript/tagmanager/js/bootstrap-colorpicker.min.js');

        if ($ver == '1')
        {

            $this->data = array_merge($this->data, $_data);

            $this->template = 'extension/analytics/tagmanager15x.tpl';
            $this->children = array(
                'common/header',
                'common/footer',
            );
            $this
                ->response
                ->setOutput($this->render());
            return;

        }
        else
        {

            $data = array_merge($data, $_data);

            $data['header'] = $this
                ->load
                ->controller('common/header');
            $data['column_left'] = $this
                ->load
                ->controller('common/column_left');
            $data['footer'] = $this
                ->load
                ->controller('common/footer');
            if (isset($ver) && $ver == '3')
            {
                $this
                    ->config
                    ->set('template_engine', 'template');
                $this
                    ->response
                    ->setOutput($this
                    ->load
                    ->view('extension/analytics/' . $data['template'], $data));
            }
            elseif ($ver == '2')
            {
                if ($sub_ver == '2.1' || $sub_ver == '2.0')
                {
                    $this
                        ->response
                        ->setOutput($this
                        ->load
                        ->view('extension/analytics/tagmanager.tpl', $data));
                }
                else
                {
                    $this
                        ->response
                        ->setOutput($this
                        ->load
                        ->view('extension/analytics/tagmanager', $data));
                }
            }
        }
        return;
    }

    public function clear()
    {

        $file = DIR_LOGS . 'tagmanager.log';

        $ver = substr(VERSION, 0, 1);
        $store_id = 0;
        $sub_ver = substr(VERSION, 0, 3);

        $handle = fopen($file, 'w+');

        fclose($handle);

        $this
            ->session
            ->data['success'] = 'Log cleared';

        if ($ver == '3')
        {
            $this
                ->response
                ->redirect($this
                ->url
                ->link('extension/analytics/tagmanager', $this->token . '&store_id=' . $store_id, true));
        }
        elseif ($ver == '1')
        {
            $this->redirect($this
                ->url
                ->link('module/tagmanager', $this->token, 'SSL'));
        }
        else
        {
            if ($sub_ver == '2.1' || $sub_ver == '2.2')
            {
                $this
                    ->response
                    ->redirect($this
                    ->url
                    ->link('analytics/tagmanager', $this->token . '&store_id=' . $store_id, 'SSL'));
            }
            elseif ($sub_ver == '2.0')
            {
                $this->redirect($this
                    ->url
                    ->link('module/tagmanager', $this->token . '&store_id=' . $store_id, 'SSL'));
            }
            else
            {
                $this
                    ->response
                    ->redirect($this
                    ->url
                    ->link('extension/analytics/tagmanager', $this->token . '&store_id=' . $store_id, 'SSL'));
            }
        }
    }

    protected function validate()
    {

        $ver = substr(VERSION, 0, 1);
        $PREFIX = '';
        $store_id = 0;
        $sub_ver = substr(VERSION, 0, 3);

        if (substr(VERSION, 0, 1) == '3')
        {
            $PREFIX = 'analytics_';
            $ver = '3';
        }
        else
        {
            $ver = '2';
            $PREFIX = '';
        }
        if (!isset($this
            ->request
            ->get['store_id']))
        {
            $store_id = 0;
        }
        else
        {
            $store_id = $this
                ->request
                ->get['store_id'];
        }

        if (isset($this
            ->request
            ->post['order_id']) || isset($this
            ->request
            ->post['datas']))
        {
            return false;
        }

        $module = 'extension/analytics/tagmanager';

        if ($ver == '1' || $ver == '2')
        {
            $module = 'module/tagmanager';
        }
        if ($sub_ver == '2.1' || $sub_ver == '2.2')
        {
            $module = 'analytics/tagmanager';
        }

        if ($sub_ver == '2.3')
        {
            $module = 'extension/analytics/tagmanager';
        }

        if (!$this
            ->user
            ->hasPermission('modify', $module))
        {
            $this->error['warning'] = $this
                ->language
                ->get('error_permission');
        }

        if (isset($this
            ->request
            ->post[$PREFIX . 'tagmanager_primary']) && empty($this
            ->request
            ->post[$PREFIX . 'tagmanager_primary']))
        {
            $this->error['primary'] = $this
                ->language
                ->get('error_primary');
        }

        return !$this->error;
    }

    public function install()
    {
        $this->updateDatabase();

    }

    public function uninstall()
    {

    }

    private function updateDatabase()
    {

        $this
            ->db
            ->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "analytics_tracking` ( `id` int(11) NOT NULL AUTO_INCREMENT, `order_id` int(11) DEFAULT NULL, `cid` varchar(128) DEFAULT NULL, `uid` varchar(64) DEFAULT NULL, `ip` varchar(64) DEFAULT NULL, `geoid` varchar(64) DEFAULT NULL, `sr` varchar(64) DEFAULT NULL, `vp` varchar(64) DEFAULT NULL, `ul` varchar(64) DEFAULT NULL, `dr` varchar(250) DEFAULT NULL, `hit` tinyint(1) NOT NULL DEFAULT '0', `tid` varchar(24) DEFAULT NULL, `user_agent` varchar(250) DEFAULT NULL, `currency_code` varchar(11) DEFAULT NULL, `currency_id` int(11) DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

    }

    private function columnExistsInTable($table, $column)
    {
        $query = $this
            ->db
            ->query("DESC `" . DB_PREFIX . $table . "`;");
        foreach ($query->rows as $row)
        {
            if ($row['Field'] == $column)
            {
                return true;
            }
        }
        return false;
    }

    private function URLredirect($url, $status = 302)
    {
        header('Location: ' . str_replace(array(
            '&amp;',
            "\n",
            "\r"
        ) , array(
            '&',
            '',
            ''
        ) , $url) , true, $status);
        exit();
    }

}

?>
