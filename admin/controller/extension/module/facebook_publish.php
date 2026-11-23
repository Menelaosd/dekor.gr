<?php
class ControllerExtensionModuleFacebookPublish extends Controller {
    // DEKOR SERVER STORE VALIDATION V1
    private $route = 'extension/module/facebook_publish';

    public function index() {
        if (($this->request->get['channel'] ?? '') === 'manual_social_image') {
            $this->manualSocialImage();
            return;
        }
        if (($this->request->get['channel'] ?? '') === 'instagram_apokrifa') {
            $this->apokrifaInstagramIndex();
            return;
        }
        if (($this->request->get['channel'] ?? '') === 'instagram_dekor') {
            $this->dekorInstagramIndex();
            return;
        }
        $this->document->setTitle('Δημοσίευση προϊόντος στο Facebook');
        $userToken = $this->session->data['user_token'] ?? '';

        $this->load->model('catalog/product');
        $productIdRaw = $this->request->get['product_id'] ?? '';
        $productId = ctype_digit((string)$productIdRaw) ? (int)$productIdRaw : 0;
        $productStores = $productId > 0
            ? array_map('intval', $this->model_catalog_product->getProductStores($productId))
            : [];
        $hasDekor = in_array(0, $productStores, true);
        $hasApokrifa = in_array(2, $productStores, true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['action'] = $this->url->link($this->route, 'user_token=' . $userToken, true);
        $data['error'] = '';
        $data['success'] = '';
        $instagramProductId = isset($this->request->post['product_id'])
    ? (int)$this->request->post['product_id']
    : $productId;
$data['dekor_instagram_action'] = $this->url->link($this->route, 'user_token=' . $userToken . '&channel=instagram_dekor&product_id=' . $instagramProductId, true);
        $data['apokrifa_instagram_action'] = $this->url->link($this->route, 'user_token=' . $userToken . '&channel=instagram_apokrifa&product_id=' . $instagramProductId, true);
        // APOKRIFA INSTAGRAM FORM V1
        $data['instagram_retry'] = false;
        $data['publish_completed'] = false;
        $keys = $this->session->data['apokrifa_publish_keys'] ?? [];
        $isPost = ($this->request->server['REQUEST_METHOD'] ?? '') === 'POST';
        $publishKey = $isPost ? (string)($this->request->post['publish_key'] ?? '') : bin2hex(random_bytes(16));
        if (!$isPost) {
            $keys[$publishKey] = time();
            $keys = array_slice($keys, -40, null, true);
            $this->session->data['apokrifa_publish_keys'] = $keys;
        }
        $data['publish_key'] = $publishKey;
        $page = $this->request->get['page'] ?? 'dekor';
        if (!in_array($page, ['dekor', 'apokrifa'], true)) {
            $page = 'dekor';
        }
        if ($hasDekor && !$hasApokrifa) {
            $page = 'dekor';
        } elseif ($hasApokrifa && !$hasDekor) {
            $page = 'apokrifa';
        }

        $data['page'] = $page;
        $data['product_id'] = $productId;
        $data['manual_social_image_base'] = $productId > 0
            ? $this->url->link($this->route, 'user_token=' . $userToken . '&channel=manual_social_image&product_id=' . $productId, true)
            : '';
        $data['product_url'] = '';
        if (!empty($this->request->get['product_id']) && ctype_digit((string)$this->request->get['product_id'])) {
            $domain = $page === 'dekor' ? 'dekor.gr' : 'apokrifa.gr';
            $data['product_url'] = 'https://' . $domain . '/index.php?route=product/product&product_id=' . (int)$this->request->get['product_id'];
        }
        $data['message'] = '';
        // PHOTO DEFAULT: όλα τα προϊόντα DEKOR και APOKRIFA
        $data['publish_mode'] = 'photo';

        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            $data['product_url'] = trim($this->request->post['product_url'] ?? '');
            $data['message'] = trim($this->request->post['message'] ?? '');
            $postedProductId = $this->request->post['product_id'] ?? '';
            $data['product_id'] = ctype_digit((string)$postedProductId)
                ? (int)$postedProductId : 0;
            $productStores = $data['product_id'] > 0
                ? array_map('intval', $this->model_catalog_product->getProductStores($data['product_id']))
                : [];
            $hasDekor = in_array(0, $productStores, true);
            $hasApokrifa = in_array(2, $productStores, true);
            $data['page'] = $this->request->post['page'] ?? 'dekor';
            $data['publish_mode'] = $this->request->post['publish_mode'] ?? 'link';

            if (!$this->user->hasPermission('modify', $this->route)) {
                $data['error'] = 'Δεν έχεις δικαίωμα δημοσίευσης.';
            } elseif ($data['product_id'] < 1) {
                $data['error'] = 'Δεν αναγνωρίστηκε το προϊόν. Άνοιξέ το από την επεξεργασία προϊόντος.';
            } elseif (!$hasDekor && !$hasApokrifa) {
                $data['error'] = 'Το προϊόν δεν είναι συνδεδεμένο με το DEKOR ή το APOKRIFA.';
            } elseif (($data['page'] === 'dekor' && !$hasDekor) ||
                      ($data['page'] === 'apokrifa' && !$hasApokrifa)) {
                $data['error'] = 'Η επιλεγμένη σελίδα Facebook δεν αντιστοιχεί στα καταστήματα του προϊόντος.';
            } elseif (!in_array($data['page'], ['dekor', 'apokrifa'], true)) {
                $data['error'] = 'Επίλεξε μία από τις δύο Σελίδες.';
            } elseif ($data['page'] === 'apokrifa' && $data['publish_mode'] === 'photo' &&
                      (!preg_match('/^[a-f0-9]{32}$/D', $publishKey) || !isset($keys[$publishKey]))) {
                $data['error'] = 'Άνοιξε ξανά τη φόρμα από την επεξεργασία προϊόντος.';
            } elseif (!in_array($data['publish_mode'], ['link', 'photo'], true)) {
                $data['error'] = 'Επίλεξε έγκυρο τρόπο δημοσίευσης.';
            } else {
                $parts = parse_url($data['product_url']);
                $host = strtolower($parts['host'] ?? '');
                $allowedHosts = $data['page'] === 'dekor'
                    ? ['dekor.gr', 'www.dekor.gr']
                    : ['apokrifa.gr', 'www.apokrifa.gr'];

                if ($data['publish_mode'] === 'link' && (($parts['scheme'] ?? '') !== 'https' || !in_array($host, $allowedHosts, true))) {
                    $data['error'] = 'Βάλε τον σύνδεσμο προϊόντος από το σωστό κατάστημα, με https://.';
                } elseif (strlen($data['message']) > 2000) {
                    $data['error'] = 'Το κείμενο είναι πολύ μεγάλο.';
                } else {
                    $tokenPath = '/home/chshost/storage/facebook-publish/' . $data['page'] . '-page-token.txt';
                    $pageToken = is_readable($tokenPath) ? trim(file_get_contents($tokenPath)) : '';

                    if ($pageToken === '') {
                        $data['error'] = 'Δεν βρέθηκε το ιδιωτικό token της Σελίδας.';
                    } else {
                        $pageInfo = $this->graphRequest('GET', '/me', [
                            'fields' => 'id,name',
                            'access_token' => $pageToken
                        ]);

                        if (empty($pageInfo['id'])) {
                            $data['error'] = 'Η Meta δεν επιβεβαίωσε τη Σελίδα. Meta code: '
    . (int)($pageInfo['meta_error_code'] ?? 0)
    . ' — subcode: '
    . (int)($pageInfo['meta_error_subcode'] ?? 0);
                        } elseif ($data['page'] === 'apokrifa' && (string)$pageInfo['id'] !== '1382238784969763') {
                            $data['error'] = 'Το token Facebook δεν αντιστοιχεί στη Σελίδα Apokrifa.gr.';
                        } else {
                            if ($data['publish_mode'] === 'link') {
                            // Ανάκτηση canonical SEO URL για Facebook.
                            $url = $data['product_url'];
                            $ch = curl_init($url);
                            curl_setopt_array($ch, [
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_FOLLOWLOCATION => false,
                                CURLOPT_TIMEOUT => 15,
                                CURLOPT_SSL_VERIFYPEER => true
                            ]);
                            $html = curl_exec($ch);
                            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);

                            if ($http == 200 && is_string($html)) {
                                $dom = new DOMDocument();
                                $previous = libxml_use_internal_errors(true);
                                $loaded = $dom->loadHTML($html);
                                libxml_clear_errors();
                                libxml_use_internal_errors($previous);

                                if ($loaded) {
                                    foreach ($dom->getElementsByTagName('link') as $link) {
                                        if (strtolower($link->getAttribute('rel')) === 'canonical') {
                                            $canonical = $link->getAttribute('href');
                                            $parts = parse_url($canonical);

                                            if (is_array($parts) &&
                                                ($parts['scheme'] ?? '') === 'https' &&
                                                in_array(strtolower($parts['host'] ?? ''), $allowedHosts, true) &&
                                                !isset($parts['port']) &&
                                                !isset($parts['user']) &&
                                                !isset($parts['pass'])) {
                                                $data['product_url'] = $canonical;
                                            }
                                            break;
                                        }
                                    }
                                }
                            }

                            }

                            // DYNAMIC PRODUCT PHOTO PUBLISH V1
                            if ($data['publish_mode'] === 'photo') {
                                $productId = (int)($data['product_id'] ?? 0);
                                $product = $productId > 0
                                    ? $this->model_catalog_product->getProduct($productId)
                                    : [];

                                $domain = $data['page'] === 'dekor'
                                    ? 'dekor.gr' : 'apokrifa.gr';

                                $imageRelative = str_replace(
                                    '\\',
                                    '/',
                                    trim((string)($product['image'] ?? ''))
                                );

                                $imageRoot = realpath(DIR_IMAGE);
                                $realImage = $imageRelative !== ''
                                    ? realpath(DIR_IMAGE . ltrim($imageRelative, '/'))
                                    : false;

                                if (empty($product) || $productId < 1) {
                                    $data['error'] = 'Δεν βρέθηκαν τα στοιχεία του προϊόντος.';
                                } elseif (!$imageRoot || !$realImage ||
                                    strpos($realImage, $imageRoot . DIRECTORY_SEPARATOR) !== 0) {
                                    $data['error'] = 'Δεν βρέθηκε ασφαλής διαδρομή εικόνας για το προϊόν.';
                                } else {
                                    $info = @getimagesize($realImage);
                                    $cacheRelative = 'cache/facebook-photo/' .
                                        $data['page'] . '-product-' .
                                        $productId . '-1000x1000.jpg';
                                    $destination = DIR_IMAGE . $cacheRelative;
                                    $photoDir = dirname($destination);

                                    if (!$info || $info[0] < 1 || $info[1] < 1) {
                                        $data['error'] = 'Η κύρια εικόνα του προϊόντος δεν είναι έγκυρη.';
                                    } elseif (!is_dir($photoDir) &&
                                        !@mkdir($photoDir, 0755, true) &&
                                        !is_dir($photoDir)) {
                                        $data['error'] = 'Δεν δημιουργήθηκε ο φάκελος εικόνων Facebook.';
                                    } else {
                                        $needsUpdate = !is_file($destination) ||
                                            filemtime($realImage) > filemtime($destination);

                                        if ($needsUpdate) {
                                            switch ($info[2]) {
                                                case IMAGETYPE_JPEG:
                                                    $src = @imagecreatefromjpeg($realImage);
                                                    break;
                                                case IMAGETYPE_PNG:
                                                    $src = @imagecreatefrompng($realImage);
                                                    break;
                                                case IMAGETYPE_WEBP:
                                                    $src = @imagecreatefromwebp($realImage);
                                                    break;
                                                case IMAGETYPE_GIF:
                                                    $src = @imagecreatefromgif($realImage);
                                                    break;
                                                default:
                                                    $src = false;
                                                    break;
                                            }

                                            if (!$src) {
                                                $data['error'] = 'Δεν υποστηρίζεται ο τύπος της κύριας εικόνας.';
                                            } else {
                                                $width = imagesx($src);
                                                $height = imagesy($src);
                                                $canvas = imagecreatetruecolor(1000, 1000);
                                                $white = imagecolorallocate($canvas, 255, 255, 255);
                                                imagefill($canvas, 0, 0, $white);

                                                $scale = min(1000 / $width, 1000 / $height);
                                                $newWidth = max(1, (int)round($width * $scale));
                                                $newHeight = max(1, (int)round($height * $scale));
                                                $x = (int)( (1000 - $newWidth) / 2 );
                                                $y = (int)( (1000 - $newHeight) / 2 );

                                                imagecopyresampled(
                                                    $canvas, $src, $x, $y, 0, 0,
                                                    $newWidth, $newHeight, $width, $height
                                                );

                                                if (!imagejpeg($canvas, $destination, 90)) {
                                                    $data['error'] = 'Δεν δημιουργήθηκε η φωτογραφία JPEG.';
                                                }

                                                imagedestroy($src);
                                                imagedestroy($canvas);
                                            }
                                        }

                                        if ($data['error'] === '') {
                                            $photoUrl = 'https://' . $domain . '/image/' .
                                                $cacheRelative;

                                            $caption = trim($data['message']);
                                            $cta = "Δείτε το προϊόν:\n" .
                                                'https://' . $domain . '/go/?id=' . $productId;

                                            $caption = ($caption === ''
                                                ? '' : $caption . "\n\n") . $cta;

                                            $publishFacebook = function () use ($pageInfo, $photoUrl, $caption, $pageToken) {
                                                return $this->graphRequest('POST', '/' . rawurlencode($pageInfo['id']) . '/photos', [
                                                    'url' => $photoUrl, 'caption' => $caption, 'access_token' => $pageToken
                                                ]);
                                            };
                                            $posted = $data['page'] === 'apokrifa'
                                                ? $this->apokrifaPublishPhoto($publishKey, $photoUrl, $caption, hash_file('sha256', $destination), $publishFacebook, true)
                                                : $publishFacebook();
                                        }
                                    }
                                }
                            } elseif ($data['publish_mode'] === 'link') {
                            $postData = [
                                'link' => $data['product_url'],
                                'access_token' => $pageToken
                            ];
                            if ($data['message'] !== '') {
                                $postData['message'] = $data['message'];
                            }

                            // Προετοιμασία Facebook link preview πριν από τη δημοσίευση.
                            $scrape = $this->graphRequest('POST', '/', [
                                'id' => $data['product_url'],
                                'scrape' => 'true',
                                'access_token' => $pageToken
                            ]);

                            if (!empty($scrape['_error'])) {
                                $data['error'] = 'Δεν ολοκληρώθηκε η ανάκτηση της προεπισκόπησης από τη Meta: ' .
                                    htmlspecialchars($scrape['_error'], ENT_QUOTES, 'UTF-8');
                            } elseif (empty($scrape['image']) && empty($scrape['og_object']['id']) && empty($scrape['id'])) {
                                $data['error'] = 'Η Meta δεν επιβεβαίωσε την ανάκτηση της προεπισκόπησης. Η δημοσίευση δεν πραγματοποιήθηκε.';
                            } else {
                                $posted = $this->graphRequest('POST', '/' . rawurlencode($pageInfo['id']) . '/feed', $postData);
                            }

                            } else {
                                $data['error'] = 'Μη έγκυρος τρόπος δημοσίευσης.';
                            }

                            if (!isset($posted)) {
                                // Διατηρούμε το σφάλμα προεπισκόπησης και δεν επιχειρούμε δημοσίευση.
                            } elseif (!empty($posted['id'])) {
                                if (!empty($posted['_ig_ok'])) {
                                    $data['success'] = 'Facebook Apokrifa.gr: επιτυχής δημοσίευση. Instagram @apokrifa.gr: επιτυχής δημοσίευση.';
                                    $data['publish_completed'] = true;
                                } elseif (!empty($posted['_ig_error'])) {
                                    $data['success'] = 'Facebook Apokrifa.gr: επιτυχής δημοσίευση.';
                                    $data['error'] = 'Instagram: ' . htmlspecialchars($posted['_ig_error'], ENT_QUOTES, 'UTF-8');
                                    $data['instagram_retry'] = true;
                                } else {
                                    $data['success'] = 'Η δημοσίευση ολοκληρώθηκε στη Σελίδα ' . htmlspecialchars($pageInfo['name'], ENT_QUOTES, 'UTF-8') . '.';
                                    $data['publish_completed'] = true;
                                }
                                if ($data['publish_completed']) {
                                    $data['product_url'] = '';
                                    $data['message'] = '';
                                }
                            } else {
                                $data['error'] = 'Η Meta δεν ολοκλήρωσε τη δημοσίευση. ' .
                                    htmlspecialchars($posted['_error'] ?? 'Έλεγξε τα δικαιώματα της εφαρμογής.', ENT_QUOTES, 'UTF-8');
                            }
                        }
                    }
                }
            }
        }

        $this->response->setOutput($this->load->view('extension/module/facebook_publish', $data));
    }


    // APOKRIFA INSTAGRAM PUBLISH V1
    private function apokrifaInstagramRequest($method, $path, $params, $token) {
        $url = 'https://graph.instagram.com/v26.0' . $path;
        if ($method === 'GET') {
            $url .= '?' . http_build_query($params);
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token]
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        $body = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = is_string($body) ? json_decode($body, true) : null;
        if (is_array($result) && !empty($result['error'])) {
            return [
                '_error' => 'Instagram: HTTP ' . $http . ', Meta code ' .
                    (int)($result['error']['code'] ?? 0) . ', subcode ' .
                    (int)($result['error']['error_subcode'] ?? 0),
                '_definite' => $http >= 400 && $http < 500
            ];
        }
        if ($http < 200 || $http >= 300 || !is_array($result)) {
            return ['_error' => 'Δεν επιβεβαιώθηκε η απάντηση του Instagram (HTTP ' . $http . ').'];
        }
        return $result;
    }

    private function apokrifaPublishPhoto($key, $photoUrl, $caption, $imageHash, $publishFacebook, $facebookOnly = false) {
        $dir = '/home/chshost/storage/facebook-publish';
        $lock = false;
        $sharedIgLock = false;
        $job = [];
        try {
            if (!preg_match('/^[a-f0-9]{32}$/D', $key) || !$imageHash) {
                throw new RuntimeException('Άνοιξε ξανά τη φόρμα από το προϊόν.');
            }
            $jobs = $dir . '/instagram-jobs';
            if (!is_dir($jobs) && !@mkdir($jobs, 0700) && !is_dir($jobs)) {
                throw new RuntimeException('Δεν δημιουργήθηκε ο φάκελος καταγραφής δημοσιεύσεων.');
            }
            $path = $jobs . '/' . $key . '.json';
            $lock = @fopen($path . '.lock', 'c');
            if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException('Η ίδια δημοσίευση εκτελείται ήδη. Περίμενε πριν ξαναπατήσεις.');
            }
            $fingerprint = hash('sha256', $photoUrl . "\n" . $caption . "\n" . $imageHash);
            if (is_file($path)) {
                $job = json_decode((string)file_get_contents($path), true);
                if (!is_array($job) || ($job['fingerprint'] ?? '') !== $fingerprint) {
                    throw new RuntimeException('Η προηγούμενη προσπάθεια έχει διαφορετικό κείμενο ή εικόνα. Άνοιξε νέα φόρμα από το προϊόν.');
                }
            } else {
                $job = ['fingerprint' => $fingerprint, 'created_at' => gmdate('c')];
            }
            $save = function () use (&$job, $path, $jobs) {
                $json = json_encode($job, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $tmp = tempnam($jobs, '.job-');
                if ($json === false || $tmp === false) {
                    throw new RuntimeException('Αποτυχία καταγραφής κατάστασης.');
                }
                if (!chmod($tmp, 0600) || file_put_contents($tmp, $json, LOCK_EX) !== strlen($json) || !rename($tmp, $path)) {
                    @unlink($tmp);
                    throw new RuntimeException('Αποτυχία αποθήκευσης κατάστασης.');
                }
            };
            if ($facebookOnly) {
                if (!empty($job['fb_id'])) {
                    return ['id' => (string)$job['fb_id']];
                }
                if (!empty($job['fb_attempted'])) {
                    throw new RuntimeException('Η προηγούμενη προσπάθεια Facebook δεν επιβεβαιώθηκε. Έλεγξε πρώτα τη Σελίδα.');
                }
                $job['fb_attempted'] = true;
                $save();
                $fb = $publishFacebook();
                if (empty($fb['id'])) {
                    throw new RuntimeException($fb['_error'] ?? 'Δεν επιβεβαιώθηκε η δημοσίευση Facebook.');
                }
                $job['fb_id'] = (string)$fb['id'];
                $save();
                return ['id' => $job['fb_id']];
            }
            if (!empty($job['ig_published'])) {
                return ['id' => $job['fb_id'], '_ig_ok' => true];
            }
            $sharedIgLock = $this->apokrifaSharedIgGuard($fingerprint, $path, $dir);
            $config = is_readable($dir . '/apokrifa-instagram.json')
                ? json_decode((string)file_get_contents($dir . '/apokrifa-instagram.json'), true) : [];
            if (($config['username'] ?? '') !== 'apokrifa.gr' ||
                (string)($config['user_id'] ?? '') !== '17841458393808877' ||
                empty($config['access_token'])) {
                throw new RuntimeException('Δεν βρέθηκε η επιβεβαιωμένη σύνδεση Instagram APOKRIFA.');
            }
            $token = $config['access_token'];
            $igId = (string)$config['user_id'];
            if (empty($job['fb_id'])) {
                if (!empty($job['fb_attempted'])) {
                    throw new RuntimeException('Η προηγούμενη απάντηση Facebook δεν επιβεβαιώθηκε. Έλεγξε τη Σελίδα πριν από νέα δημοσίευση. Δεν έγινε επανάληψη.');
                }
                $account = $this->apokrifaInstagramRequest('GET', '/me', ['fields' => 'user_id,username'], $token);
                $account = $account['data'][0] ?? $account;
                if (($account['username'] ?? '') !== 'apokrifa.gr' || (string)($account['user_id'] ?? '') !== $igId) {
                    throw new RuntimeException($account['_error'] ?? 'Δεν επιβεβαιώθηκε ο λογαριασμός Instagram APOKRIFA.');
                }
                $job['fb_attempted'] = true;
                $save();
                $fb = $publishFacebook();
                if (empty($fb['id'])) {
                    throw new RuntimeException('Δεν επιβεβαιώθηκε η δημοσίευση Facebook. Meta code ' .
                        (int)($fb['meta_error_code'] ?? 0) . '. Έλεγξε τη Σελίδα πριν επιχειρήσεις νέα δημοσίευση.');
                }
                $job['fb_id'] = (string)$fb['id'];
                $save();
            }
            if (empty($job['ig_container'])) {
                $container = $this->apokrifaInstagramRequest('POST', '/' . $igId . '/media', [
                    'image_url' => $photoUrl, 'caption' => $caption
                ], $token);
                if (empty($container['id'])) {
                    throw new RuntimeException($container['_error'] ?? 'Δεν δημιουργήθηκε η προετοιμασία εικόνας Instagram.');
                }
                $job['ig_container'] = (string)$container['id'];
                $save();
            }
            for ($attempt = 0; $attempt < 3; $attempt++) {
                $status = $this->apokrifaInstagramRequest('GET', '/' . rawurlencode($job['ig_container']), ['fields' => 'status_code'], $token);
                $state = $status['status_code'] ?? '';
                if ($state !== 'IN_PROGRESS') break;
                if ($attempt < 2) sleep(2);
            }
            if ($state === 'PUBLISHED') {
                $job['ig_published'] = true;
                $save();
                return ['id' => $job['fb_id'], '_ig_ok' => true];
            }
            if ($state === 'ERROR' || $state === 'EXPIRED') {
                unset($job['ig_container'], $job['ig_publish_attempted']);
                $save();
                throw new RuntimeException('Η προετοιμασία Instagram απέτυχε ή έληξε (' . $state . '). Μπορείς να επαναλάβεις μόνο το Instagram.');
            }
            if ($state !== 'FINISHED') {
                throw new RuntimeException($status['_error'] ?? 'Το Instagram επεξεργάζεται την εικόνα. Περίμενε ένα λεπτό και πάτησε επανάληψη Instagram.');
            }
            if (!empty($job['ig_publish_attempted'])) {
                throw new RuntimeException('Η προηγούμενη απάντηση Instagram δεν επιβεβαιώθηκε. Έλεγξε το προφίλ και ξαναδοκίμασε αργότερα για έλεγχο κατάστασης. Δεν έγινε δεύτερη αποστολή.');
            }
            $job['ig_publish_attempted'] = true;
            $save();
            $ig = $this->apokrifaInstagramRequest('POST', '/' . $igId . '/media_publish', ['creation_id' => $job['ig_container']], $token);
            if (empty($ig['id'])) {
                if (!empty($ig['_definite'])) {
                    unset($job['ig_publish_attempted']);
                    $save();
                }
                throw new RuntimeException($ig['_error'] ?? 'Δεν επιβεβαιώθηκε η δημοσίευση Instagram.');
            }
            $job['ig_id'] = (string)$ig['id'];
            $job['ig_published'] = true;
            $save();
            return ['id' => $job['fb_id'], '_ig_ok' => true];
        } catch (Throwable $e) {
            if (!empty($job['fb_id'])) {
                return ['id' => $job['fb_id'], '_ig_error' => $e->getMessage()];
            }
            return ['_error' => $e->getMessage()];
        } finally {
            if (is_resource($sharedIgLock)) {
                flock($sharedIgLock, LOCK_UN);
                fclose($sharedIgLock);
            }
            if (is_resource($lock)) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
    }

    // DEKOR INSTAGRAM ONLY V1
    private function dekorInstagramIndex() {
        $this->document->setTitle('Δημοσίευση στο Instagram DEKOR');
        $this->load->model('catalog/product');
        $isPost = ($this->request->server['REQUEST_METHOD'] ?? '') === 'POST';
        $rawId = $isPost ? ($this->request->post['product_id'] ?? '') : ($this->request->get['product_id'] ?? '');
        $id = ctype_digit((string)$rawId) ? (int)$rawId : 0;
        $keys = $this->session->data['dekor_instagram_keys'] ?? [];
        $key = $isPost ? (string)($this->request->post['publish_key'] ?? '') : bin2hex(random_bytes(16));
        if (!$isPost) {
            $keys[$key] = time();
            $this->session->data['dekor_instagram_keys'] = array_slice($keys, -40, null, true);
        }
        $data = [
            'product_id' => $id, 'publish_key' => $key, 'page' => 'dekor',
            'message' => $isPost ? trim((string)($this->request->post['message'] ?? '')) : '',
            'error' => '', 'success' => '', 'publish_completed' => false
        ];
        $data['action'] = $this->url->link($this->route, 'user_token=' . ($this->session->data['user_token'] ?? '') . '&channel=instagram_dekor', true);
        if ($isPost) {
            $stores = $id > 0 ? array_map('intval', $this->model_catalog_product->getProductStores($id)) : [];
            if (!$this->user->hasPermission('modify', $this->route)) {
                $data['error'] = 'Δεν έχεις δικαίωμα δημοσίευσης.';
            } elseif ($id < 1 || !in_array(0, $stores, true)) {
                $data['error'] = 'Το προϊόν δεν ανήκει στο κατάστημα DEKOR.';
            } elseif (!preg_match('/^[a-f0-9]{32}$/D', $key) || !isset($keys[$key])) {
                $data['error'] = 'Άνοιξε ξανά τη φόρμα από το προϊόν.';
            } elseif (strlen($data['message']) > 2000) {
                $data['error'] = 'Το κείμενο είναι πολύ μεγάλο.';
            } else {
                                $productId = (int)($data['product_id'] ?? 0);
                                $product = $productId > 0
                                    ? $this->model_catalog_product->getProduct($productId)
                                    : [];

                                $domain = $data['page'] === 'dekor'
                                    ? 'dekor.gr' : 'apokrifa.gr';

                                $imageRelative = str_replace(
                                    '\\',
                                    '/',
                                    trim((string)($product['image'] ?? ''))
                                );

                                $imageRoot = realpath(DIR_IMAGE);
                                $realImage = $imageRelative !== ''
                                    ? realpath(DIR_IMAGE . ltrim($imageRelative, '/'))
                                    : false;

                                if (empty($product) || $productId < 1) {
                                    $data['error'] = 'Δεν βρέθηκαν τα στοιχεία του προϊόντος.';
                                } elseif (!$imageRoot || !$realImage ||
                                    strpos($realImage, $imageRoot . DIRECTORY_SEPARATOR) !== 0) {
                                    $data['error'] = 'Δεν βρέθηκε ασφαλής διαδρομή εικόνας για το προϊόν.';
                                } else {
                                    $info = @getimagesize($realImage);
                                    $cacheRelative = 'cache/facebook-photo/' .
                                        $data['page'] . '-product-' .
                                        $productId . '-1000x1000.jpg';
                                    $destination = DIR_IMAGE . $cacheRelative;
                                    $photoDir = dirname($destination);

                                    if (!$info || $info[0] < 1 || $info[1] < 1) {
                                        $data['error'] = 'Η κύρια εικόνα του προϊόντος δεν είναι έγκυρη.';
                                    } elseif (!is_dir($photoDir) &&
                                        !@mkdir($photoDir, 0755, true) &&
                                        !is_dir($photoDir)) {
                                        $data['error'] = 'Δεν δημιουργήθηκε ο φάκελος εικόνων Facebook.';
                                    } else {
                                        $needsUpdate = !is_file($destination) ||
                                            filemtime($realImage) > filemtime($destination);

                                        if ($needsUpdate) {
                                            switch ($info[2]) {
                                                case IMAGETYPE_JPEG:
                                                    $src = @imagecreatefromjpeg($realImage);
                                                    break;
                                                case IMAGETYPE_PNG:
                                                    $src = @imagecreatefrompng($realImage);
                                                    break;
                                                case IMAGETYPE_WEBP:
                                                    $src = @imagecreatefromwebp($realImage);
                                                    break;
                                                case IMAGETYPE_GIF:
                                                    $src = @imagecreatefromgif($realImage);
                                                    break;
                                                default:
                                                    $src = false;
                                                    break;
                                            }

                                            if (!$src) {
                                                $data['error'] = 'Δεν υποστηρίζεται ο τύπος της κύριας εικόνας.';
                                            } else {
                                                $width = imagesx($src);
                                                $height = imagesy($src);
                                                $canvas = imagecreatetruecolor(1000, 1000);
                                                $white = imagecolorallocate($canvas, 255, 255, 255);
                                                imagefill($canvas, 0, 0, $white);

                                                $scale = min(1000 / $width, 1000 / $height);
                                                $newWidth = max(1, (int)round($width * $scale));
                                                $newHeight = max(1, (int)round($height * $scale));
                                                $x = (int)( (1000 - $newWidth) / 2 );
                                                $y = (int)( (1000 - $newHeight) / 2 );

                                                imagecopyresampled(
                                                    $canvas, $src, $x, $y, 0, 0,
                                                    $newWidth, $newHeight, $width, $height
                                                );

                                                if (!imagejpeg($canvas, $destination, 90)) {
                                                    $data['error'] = 'Δεν δημιουργήθηκε η φωτογραφία JPEG.';
                                                }

                                                imagedestroy($src);
                                                imagedestroy($canvas);
                                            }
                                        }

                                        if ($data['error'] === '') {
                                            $photoUrl = 'https://' . $domain . '/image/' .
                                                $cacheRelative;

                                            $caption = trim($data['message']);
                                            $cta = "Ανακαλύψτε το προϊόν μας.\n\n" .
                                                "Δείτε περισσότερα πατώντας τον σύνδεσμο στο προφίλ μας.";

                                            $caption = ($caption === ''
                                                ? '' : $caption . "\n\n") . $cta;

                                            $posted = $this->dekorPublishPhoto($key, $photoUrl, $caption, hash_file('sha256', $destination));
                                        }
                                    }
                                }

                if (!empty($posted['_ig_ok'])) {
                    $data['success'] = 'Instagram @dekor_candles: επιτυχής δημοσίευση. Δεν έγινε δημοσίευση στο Facebook.';
                    $data['publish_completed'] = true;
                } elseif (isset($posted)) {
                    $data['error'] = htmlspecialchars($posted['_error'] ?? 'Δεν επιβεβαιώθηκε η δημοσίευση Instagram.', ENT_QUOTES, 'UTF-8');
                }
            }
        }
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/module/dekor_instagram_publish', $data));
    }

    private function dekorPublishPhoto($key, $photoUrl, $caption, $imageHash) {
        $dir = '/home/chshost/storage/facebook-publish';
        $lock = false;
        $dekorSharedLock = false;
        $job = [];
        try {
            if (!preg_match('/^[a-f0-9]{32}$/D', $key) || !$imageHash) {
                throw new RuntimeException('Άνοιξε ξανά τη φόρμα από το προϊόν.');
            }
            $jobs = $dir . '/dekor-instagram-jobs';
            if (!is_dir($jobs) && !@mkdir($jobs, 0700) && !is_dir($jobs)) {
                throw new RuntimeException('Δεν δημιουργήθηκε ο φάκελος καταγραφής δημοσιεύσεων.');
            }
            $path = $jobs . '/' . $key . '.json';
            $lock = @fopen($path . '.lock', 'c');
            if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException('Η ίδια δημοσίευση εκτελείται ήδη. Περίμενε πριν ξαναπατήσεις.');
            }
            $fingerprint = hash('sha256', $photoUrl . "\n" . $caption . "\n" . $imageHash);
            if (is_file($path)) {
                $job = json_decode((string)file_get_contents($path), true);
                if (!is_array($job) || ($job['fingerprint'] ?? '') !== $fingerprint) {
                    throw new RuntimeException('Η προηγούμενη προσπάθεια έχει διαφορετικό κείμενο ή εικόνα. Άνοιξε νέα φόρμα από το προϊόν.');
                }
            } else {
                $job = ['fingerprint' => $fingerprint, 'created_at' => gmdate('c')];
            }
            $save = function () use (&$job, $path, $jobs) {
                $json = json_encode($job, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $tmp = tempnam($jobs, '.job-');
                if ($json === false || $tmp === false) {
                    throw new RuntimeException('Αποτυχία καταγραφής κατάστασης.');
                }
                if (!chmod($tmp, 0600) || file_put_contents($tmp, $json, LOCK_EX) !== strlen($json) || !rename($tmp, $path)) {
                    @unlink($tmp);
                    throw new RuntimeException('Αποτυχία αποθήκευσης κατάστασης.');
                }
            };
            if (!empty($job['ig_published'])) {
                return ['id' => ($job['ig_id'] ?? $job['ig_container']), '_ig_ok' => true];
            }
            $dekorSharedLock = $this->dekorSharedIgGuard($fingerprint, $path, $dir);
            $config = is_readable($dir . '/dekor-instagram.json')
                ? json_decode((string)file_get_contents($dir . '/dekor-instagram.json'), true) : [];
            if (($config['username'] ?? '') !== 'dekor_candles' ||
                (string)($config['user_id'] ?? '') !== '17841400680965034' ||
                empty($config['access_token'])) {
                throw new RuntimeException('Δεν βρέθηκε η επιβεβαιωμένη σύνδεση Instagram DEKOR.');
            }
            $token = $config['access_token'];
            $igId = (string)$config['user_id'];
            if (empty($job['ig_container'])) {
                $account = $this->apokrifaInstagramRequest('GET', '/me', ['fields' => 'user_id,username'], $token);
                $account = $account['data'][0] ?? $account;
                if (($account['username'] ?? '') !== 'dekor_candles' || (string)($account['user_id'] ?? '') !== $igId) {
                    throw new RuntimeException($account['_error'] ?? 'Δεν επιβεβαιώθηκε ο λογαριασμός Instagram DEKOR.');
                }
            }
            if (empty($job['ig_container'])) {
                // Retry only the confirmed Meta image-fetch error 9004/2207052.
                for ($mediaAttempt = 0; $mediaAttempt < 3; $mediaAttempt++) {
                    $container = $this->apokrifaInstagramRequest('POST', '/' . $igId . '/media', [
                        'image_url' => $photoUrl, 'caption' => $caption
                    ], $token);

                    if (!empty($container['id'])) {
                        break;
                    }

                    $retryable = !empty($container['_definite'])
                        && strpos((string)($container['_error'] ?? ''), 'Meta code 9004, subcode 2207052') !== false;

                    if (!$retryable || $mediaAttempt === 2) {
                        break;
                    }

                    sleep(($mediaAttempt + 1) * 3);
                }

                if (empty($container['id'])) {
                    throw new RuntimeException($container['_error'] ?? 'Δεν δημιουργήθηκε η προετοιμασία εικόνας Instagram.');
                }
                $job['ig_container'] = (string)$container['id'];
                $save();
            }
            for ($attempt = 0; $attempt < 7; $attempt++) {
                $status = $this->apokrifaInstagramRequest('GET', '/' . rawurlencode($job['ig_container']), ['fields' => 'status_code'], $token);
                $state = $status['status_code'] ?? '';
                if ($state !== 'IN_PROGRESS') break;
                if ($attempt < 6) sleep(3);
            }
            if ($state === 'PUBLISHED') {
                $job['ig_published'] = true;
                $save();
                return ['id' => ($job['ig_id'] ?? $job['ig_container']), '_ig_ok' => true];
            }
            if ($state === 'ERROR' || $state === 'EXPIRED') {
                unset($job['ig_container'], $job['ig_publish_attempted']);
                $save();
                throw new RuntimeException('Η προετοιμασία Instagram απέτυχε ή έληξε (' . $state . '). Μπορείς να επαναλάβεις μόνο το Instagram.');
            }
            if ($state !== 'FINISHED') {
                throw new RuntimeException($status['_error'] ?? 'Το Instagram επεξεργάζεται την εικόνα. Περίμενε ένα λεπτό και πάτησε επανάληψη Instagram.');
            }
            if (!empty($job['ig_publish_attempted'])) {
                throw new RuntimeException('Η προηγούμενη απάντηση Instagram δεν επιβεβαιώθηκε. Έλεγξε το προφίλ και ξαναδοκίμασε αργότερα για έλεγχο κατάστασης. Δεν έγινε δεύτερη αποστολή.');
            }
            $job['ig_publish_attempted'] = true;
            $save();
            $ig = $this->apokrifaInstagramRequest('POST', '/' . $igId . '/media_publish', ['creation_id' => $job['ig_container']], $token);
            if (empty($ig['id'])) {
                if (!empty($ig['_definite'])) {
                    unset($job['ig_publish_attempted']);
                    $save();
                }
                throw new RuntimeException($ig['_error'] ?? 'Δεν επιβεβαιώθηκε η δημοσίευση Instagram.');
            }
            $job['ig_id'] = (string)$ig['id'];
            $job['ig_published'] = true;
            $save();
            return ['id' => ($job['ig_id'] ?? $job['ig_container']), '_ig_ok' => true];
        } catch (Throwable $e) {
            return ['_error' => $e->getMessage()];
        } finally {
            if (is_resource($dekorSharedLock)) {
                flock($dekorSharedLock, LOCK_UN);
                fclose($dekorSharedLock);
            }
            if (is_resource($lock)) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
    }

    // APOKRIFA INSTAGRAM ONLY V1
    private function apokrifaInstagramIndex() {
        $this->document->setTitle('Δημοσίευση στο Instagram APOKRIFA');
        $this->load->model('catalog/product');
        $isPost = ($this->request->server['REQUEST_METHOD'] ?? '') === 'POST';
        $rawId = $isPost ? ($this->request->post['product_id'] ?? '') : ($this->request->get['product_id'] ?? '');
        $id = ctype_digit((string)$rawId) ? (int)$rawId : 0;
        $keys = $this->session->data['apokrifa_solo_instagram_keys'] ?? [];
        $key = $isPost ? (string)($this->request->post['publish_key'] ?? '') : bin2hex(random_bytes(16));
        if (!$isPost) {
            $keys[$key] = time();
            $this->session->data['apokrifa_solo_instagram_keys'] = array_slice($keys, -40, null, true);
        }
        $data = [
            'product_id' => $id, 'publish_key' => $key, 'page' => 'apokrifa',
            'message' => $isPost ? trim((string)($this->request->post['message'] ?? '')) : '',
            'error' => '', 'success' => '', 'publish_completed' => false
        ];
        $data['action'] = $this->url->link($this->route, 'user_token=' . ($this->session->data['user_token'] ?? '') . '&channel=instagram_apokrifa', true);
        if ($isPost) {
            $stores = $id > 0 ? array_map('intval', $this->model_catalog_product->getProductStores($id)) : [];
            if (!$this->user->hasPermission('modify', $this->route)) {
                $data['error'] = 'Δεν έχεις δικαίωμα δημοσίευσης.';
            } elseif ($id < 1 || !in_array(2, $stores, true)) {
                $data['error'] = 'Το προϊόν δεν ανήκει στο κατάστημα APOKRIFA.';
            } elseif (!preg_match('/^[a-f0-9]{32}$/D', $key) || !isset($keys[$key])) {
                $data['error'] = 'Άνοιξε ξανά τη φόρμα από το προϊόν.';
            } elseif (strlen($data['message']) > 2000) {
                $data['error'] = 'Το κείμενο είναι πολύ μεγάλο.';
            } else {
                                $productId = (int)($data['product_id'] ?? 0);
                                $product = $productId > 0
                                    ? $this->model_catalog_product->getProduct($productId)
                                    : [];

                                $domain = $data['page'] === 'dekor'
                                    ? 'dekor.gr' : 'apokrifa.gr';

                                $imageRelative = str_replace(
                                    '\\',
                                    '/',
                                    trim((string)($product['image'] ?? ''))
                                );

                                $imageRoot = realpath(DIR_IMAGE);
                                $realImage = $imageRelative !== ''
                                    ? realpath(DIR_IMAGE . ltrim($imageRelative, '/'))
                                    : false;

                                if (empty($product) || $productId < 1) {
                                    $data['error'] = 'Δεν βρέθηκαν τα στοιχεία του προϊόντος.';
                                } elseif (!$imageRoot || !$realImage ||
                                    strpos($realImage, $imageRoot . DIRECTORY_SEPARATOR) !== 0) {
                                    $data['error'] = 'Δεν βρέθηκε ασφαλής διαδρομή εικόνας για το προϊόν.';
                                } else {
                                    $info = @getimagesize($realImage);
                                    $cacheRelative = 'cache/facebook-photo/' .
                                        $data['page'] . '-product-' .
                                        $productId . '-1000x1000.jpg';
                                    $destination = DIR_IMAGE . $cacheRelative;
                                    $photoDir = dirname($destination);

                                    if (!$info || $info[0] < 1 || $info[1] < 1) {
                                        $data['error'] = 'Η κύρια εικόνα του προϊόντος δεν είναι έγκυρη.';
                                    } elseif (!is_dir($photoDir) &&
                                        !@mkdir($photoDir, 0755, true) &&
                                        !is_dir($photoDir)) {
                                        $data['error'] = 'Δεν δημιουργήθηκε ο φάκελος εικόνων Facebook.';
                                    } else {
                                        $needsUpdate = !is_file($destination) ||
                                            filemtime($realImage) > filemtime($destination);

                                        if ($needsUpdate) {
                                            switch ($info[2]) {
                                                case IMAGETYPE_JPEG:
                                                    $src = @imagecreatefromjpeg($realImage);
                                                    break;
                                                case IMAGETYPE_PNG:
                                                    $src = @imagecreatefrompng($realImage);
                                                    break;
                                                case IMAGETYPE_WEBP:
                                                    $src = @imagecreatefromwebp($realImage);
                                                    break;
                                                case IMAGETYPE_GIF:
                                                    $src = @imagecreatefromgif($realImage);
                                                    break;
                                                default:
                                                    $src = false;
                                                    break;
                                            }

                                            if (!$src) {
                                                $data['error'] = 'Δεν υποστηρίζεται ο τύπος της κύριας εικόνας.';
                                            } else {
                                                $width = imagesx($src);
                                                $height = imagesy($src);
                                                $canvas = imagecreatetruecolor(1000, 1000);
                                                $white = imagecolorallocate($canvas, 255, 255, 255);
                                                imagefill($canvas, 0, 0, $white);

                                                $scale = min(1000 / $width, 1000 / $height);
                                                $newWidth = max(1, (int)round($width * $scale));
                                                $newHeight = max(1, (int)round($height * $scale));
                                                $x = (int)( (1000 - $newWidth) / 2 );
                                                $y = (int)( (1000 - $newHeight) / 2 );

                                                imagecopyresampled(
                                                    $canvas, $src, $x, $y, 0, 0,
                                                    $newWidth, $newHeight, $width, $height
                                                );

                                                if (!imagejpeg($canvas, $destination, 90)) {
                                                    $data['error'] = 'Δεν δημιουργήθηκε η φωτογραφία JPEG.';
                                                }

                                                imagedestroy($src);
                                                imagedestroy($canvas);
                                            }
                                        }

                                        if ($data['error'] === '') {
                                            $photoUrl = 'https://' . $domain . '/image/' .
                                                $cacheRelative;

                                            $caption = trim($data['message']);
                                            $cta = "Ανακαλύψτε το προϊόν μας.\n\n" .
                                                "Δείτε περισσότερα πατώντας τον σύνδεσμο στο προφίλ μας.";

                                            $caption = ($caption === ''
                                                ? '' : $caption . "\n\n") . $cta;

                                            $posted = $this->apokrifaSoloPublishPhoto($key, $photoUrl, $caption, hash_file('sha256', $destination));
                                        }
                                    }
                                }

                if (!empty($posted['_ig_ok'])) {
                    $data['success'] = 'Instagram @apokrifa.gr: επιτυχής δημοσίευση. Δεν έγινε δημοσίευση στο Facebook.';
                    $data['publish_completed'] = true;
                } elseif (isset($posted)) {
                    $data['error'] = htmlspecialchars($posted['_error'] ?? 'Δεν επιβεβαιώθηκε η δημοσίευση Instagram.', ENT_QUOTES, 'UTF-8');
                }
            }
        }
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/module/apokrifa_instagram_publish', $data));
    }

    private function apokrifaSoloPublishPhoto($key, $photoUrl, $caption, $imageHash) {
        $dir = '/home/chshost/storage/facebook-publish';
        $lock = false;
        $sharedIgLock = false;
        $job = [];
        try {
            if (!preg_match('/^[a-f0-9]{32}$/D', $key) || !$imageHash) {
                throw new RuntimeException('Άνοιξε ξανά τη φόρμα από το προϊόν.');
            }
            $jobs = $dir . '/apokrifa-solo-instagram-jobs';
            if (!is_dir($jobs) && !@mkdir($jobs, 0700) && !is_dir($jobs)) {
                throw new RuntimeException('Δεν δημιουργήθηκε ο φάκελος καταγραφής δημοσιεύσεων.');
            }
            $path = $jobs . '/' . $key . '.json';
            $lock = @fopen($path . '.lock', 'c');
            if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException('Η ίδια δημοσίευση εκτελείται ήδη. Περίμενε πριν ξαναπατήσεις.');
            }
            $fingerprint = hash('sha256', $photoUrl . "\n" . $caption . "\n" . $imageHash);
            if (is_file($path)) {
                $job = json_decode((string)file_get_contents($path), true);
                if (!is_array($job) || ($job['fingerprint'] ?? '') !== $fingerprint) {
                    throw new RuntimeException('Η προηγούμενη προσπάθεια έχει διαφορετικό κείμενο ή εικόνα. Άνοιξε νέα φόρμα από το προϊόν.');
                }
            } else {
                $job = ['fingerprint' => $fingerprint, 'created_at' => gmdate('c')];
            }
            $save = function () use (&$job, $path, $jobs) {
                $json = json_encode($job, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $tmp = tempnam($jobs, '.job-');
                if ($json === false || $tmp === false) {
                    throw new RuntimeException('Αποτυχία καταγραφής κατάστασης.');
                }
                if (!chmod($tmp, 0600) || file_put_contents($tmp, $json, LOCK_EX) !== strlen($json) || !rename($tmp, $path)) {
                    @unlink($tmp);
                    throw new RuntimeException('Αποτυχία αποθήκευσης κατάστασης.');
                }
            };
            if (!empty($job['ig_published'])) {
                return ['id' => ($job['ig_id'] ?? $job['ig_container']), '_ig_ok' => true];
            }
            $sharedIgLock = $this->apokrifaSharedIgGuard($fingerprint, $path, $dir);
            $config = is_readable($dir . '/apokrifa-instagram.json')
                ? json_decode((string)file_get_contents($dir . '/apokrifa-instagram.json'), true) : [];
            if (($config['username'] ?? '') !== 'apokrifa.gr' ||
                (string)($config['user_id'] ?? '') !== '17841458393808877' ||
                empty($config['access_token'])) {
                throw new RuntimeException('Δεν βρέθηκε η επιβεβαιωμένη σύνδεση Instagram APOKRIFA.');
            }
            $token = $config['access_token'];
            $igId = (string)$config['user_id'];
            if (empty($job['ig_container'])) {
                $account = $this->apokrifaInstagramRequest('GET', '/me', ['fields' => 'user_id,username'], $token);
                $account = $account['data'][0] ?? $account;
                if (($account['username'] ?? '') !== 'apokrifa.gr' || (string)($account['user_id'] ?? '') !== $igId) {
                    throw new RuntimeException($account['_error'] ?? 'Δεν επιβεβαιώθηκε ο λογαριασμός Instagram APOKRIFA.');
                }
            }
            if (empty($job['ig_container'])) {
                // Retry only the confirmed Meta image-fetch error 9004/2207052.
                for ($mediaAttempt = 0; $mediaAttempt < 3; $mediaAttempt++) {
                    $container = $this->apokrifaInstagramRequest('POST', '/' . $igId . '/media', [
                        'image_url' => $photoUrl, 'caption' => $caption
                    ], $token);

                    if (!empty($container['id'])) {
                        break;
                    }

                    $retryable = !empty($container['_definite'])
                        && strpos((string)($container['_error'] ?? ''), 'Meta code 9004, subcode 2207052') !== false;

                    if (!$retryable || $mediaAttempt === 2) {
                        break;
                    }

                    sleep(($mediaAttempt + 1) * 3);
                }

                if (empty($container['id'])) {
                    throw new RuntimeException($container['_error'] ?? 'Δεν δημιουργήθηκε η προετοιμασία εικόνας Instagram.');
                }
                $job['ig_container'] = (string)$container['id'];
                $save();
            }
            for ($attempt = 0; $attempt < 7; $attempt++) {
                $status = $this->apokrifaInstagramRequest('GET', '/' . rawurlencode($job['ig_container']), ['fields' => 'status_code'], $token);
                $state = $status['status_code'] ?? '';
                if ($state !== 'IN_PROGRESS') break;
                if ($attempt < 6) sleep(3);
            }
            if ($state === 'PUBLISHED') {
                $job['ig_published'] = true;
                $save();
                return ['id' => ($job['ig_id'] ?? $job['ig_container']), '_ig_ok' => true];
            }
            if ($state === 'ERROR' || $state === 'EXPIRED') {
                unset($job['ig_container'], $job['ig_publish_attempted']);
                $save();
                throw new RuntimeException('Η προετοιμασία Instagram απέτυχε ή έληξε (' . $state . '). Μπορείς να επαναλάβεις μόνο το Instagram.');
            }
            if ($state !== 'FINISHED') {
                throw new RuntimeException($status['_error'] ?? 'Το Instagram επεξεργάζεται την εικόνα. Περίμενε ένα λεπτό και πάτησε επανάληψη Instagram.');
            }
            if (!empty($job['ig_publish_attempted'])) {
                throw new RuntimeException('Η προηγούμενη απάντηση Instagram δεν επιβεβαιώθηκε. Έλεγξε το προφίλ και ξαναδοκίμασε αργότερα για έλεγχο κατάστασης. Δεν έγινε δεύτερη αποστολή.');
            }
            $job['ig_publish_attempted'] = true;
            $save();
            $ig = $this->apokrifaInstagramRequest('POST', '/' . $igId . '/media_publish', ['creation_id' => $job['ig_container']], $token);
            if (empty($ig['id'])) {
                if (!empty($ig['_definite'])) {
                    unset($job['ig_publish_attempted']);
                    $save();
                }
                throw new RuntimeException($ig['_error'] ?? 'Δεν επιβεβαιώθηκε η δημοσίευση Instagram.');
            }
            $job['ig_id'] = (string)$ig['id'];
            $job['ig_published'] = true;
            $save();
            return ['id' => ($job['ig_id'] ?? $job['ig_container']), '_ig_ok' => true];
        } catch (Throwable $e) {
            return ['_error' => $e->getMessage()];
        } finally {
            if (is_resource($sharedIgLock)) {
                flock($sharedIgLock, LOCK_UN);
                fclose($sharedIgLock);
            }
            if (is_resource($lock)) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
    }


    // DEKOR_SHARED_IG_GUARD_V1
    private function dekorSharedIgGuard($fingerprint, $ownPath, $dir) {
        $lockPath = $dir . '/dekor-instagram-shared.lock';
        $lock = @fopen($lockPath, 'c');

        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) fclose($lock);
            throw new RuntimeException(
                'Υπάρχει ήδη δημοσίευση Instagram DEKOR σε εξέλιξη.'
            );
        }

        @chmod($lockPath, 0600);

        try {
            $folder = $dir . '/dekor-instagram-jobs';

            foreach (glob($folder . '/*.json') ?: [] as $file) {
                if ($file === $ownPath) continue;

                $job = json_decode(
                    (string)@file_get_contents($file), true
                );
                if (!is_array($job)) continue;

                $created = strtotime($job['created_at'] ?? '');
                if (!$created || $created < time() - 600) continue;

                if (empty($job['fingerprint']) ||
                    !hash_equals((string)$job['fingerprint'], $fingerprint)) {
                    continue;
                }

                if (!empty($job['ig_published'])) {
                    throw new RuntimeException(
                        'Το ίδιο περιεχόμενο δημοσιεύτηκε στο Instagram DEKOR τα τελευταία 10 λεπτά.'
                    );
                }

                if (!empty($job['ig_publish_attempted'])) {
                    throw new RuntimeException(
                        'Υπάρχει προηγούμενη απόπειρα για το ίδιο περιεχόμενο. Έλεγξε πρώτα το Instagram DEKOR.'
                    );
                }
            }

            return $lock;
        } catch (Throwable $e) {
            flock($lock, LOCK_UN);
            fclose($lock);
            throw $e;
        }
    }

    // APOKRIFA_SHARED_IG_GUARD_V1
    private function apokrifaSharedIgGuard($fingerprint, $ownPath, $dir) {
        $lockPath = $dir . '/apokrifa-instagram-shared.lock';
        $lock = @fopen($lockPath, 'c');

        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) fclose($lock);
            throw new RuntimeException('Υπάρχει ήδη δημοσίευση Instagram APOKRIFA σε εξέλιξη.');
        }

        @chmod($lockPath, 0600);

        try {
            foreach (['instagram-jobs', 'apokrifa-solo-instagram-jobs'] as $folder) {
                foreach (glob($dir . '/' . $folder . '/*.json') ?: [] as $file) {
                    if ($file === $ownPath) continue;
                    if (!is_file($file)) continue;

                    $job = json_decode((string)@file_get_contents($file), true);
                    if (!is_array($job)) continue;

                    $created = strtotime($job['created_at'] ?? '');
                    if (!$created || $created < time() - 600) continue;

                    if (empty($job['fingerprint']) ||
                        !hash_equals((string)$job['fingerprint'], $fingerprint)) {
                        continue;
                    }

                    if (!empty($job['ig_published'])) {
                        throw new RuntimeException(
                            'Το ίδιο περιεχόμενο δημοσιεύτηκε στο Instagram APOKRIFA τα τελευταία 10 λεπτά.'
                        );
                    }

                    if (!empty($job['ig_publish_attempted'])) {
                        throw new RuntimeException(
                            'Υπάρχει προηγούμενη απόπειρα για το ίδιο περιεχόμενο. Έλεγξε πρώτα το Instagram.'
                        );
                    }
                }
            }

            return $lock;
        } catch (Throwable $e) {
            flock($lock, LOCK_UN);
            fclose($lock);
            throw $e;
        }
    }
    private function graphRequest($method, $path, $params) {
        $url = 'https://graph.facebook.com/v26.0' . $path;
        $ch = curl_init();

        if ($method === 'GET') {
            $url .= '?' . http_build_query($params);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        $body = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        $result = is_string($body) ? json_decode($body, true) : null;

        if (!is_array($result)) {
            return ['_error' => $curlError ?: 'Δεν υπήρξε έγκυρη απάντηση από τη Meta.'];
        }
        if (!empty($result['error'])) {
            return ['_error' => $result['error']['message'] ?? 'Σφάλμα Meta.'] + [
                'meta_error_code' => (int)($result['error']['code'] ?? 0),
                'meta_error_subcode' => (int)($result['error']['error_subcode'] ?? 0)
            ];
        }
        return $result;
    }
    private function manualSocialImage() {
        $fail = function ($status, $message) {
            $this->response->addHeader('HTTP/1.1 ' . $status);
            $this->response->addHeader('Content-Type: text/plain; charset=utf-8');
            $this->response->setOutput($message);
        };

        if (!$this->user->hasPermission('modify', $this->route)) {
            $fail('403 Forbidden', 'Δεν έχεις δικαίωμα πρόσβασης.');
            return;
        }

        $idRaw = $this->request->get['product_id'] ?? '';
        $productId = ctype_digit((string)$idRaw) ? (int)$idRaw : 0;
        $page = $this->request->get['page'] ?? '';

        if ($productId < 1 || !in_array($page, ['dekor', 'apokrifa'], true)) {
            $fail('400 Bad Request', 'Μη έγκυρο προϊόν ή κατάστημα.');
            return;
        }

        $this->load->model('catalog/product');
        $stores = array_map('intval', $this->model_catalog_product->getProductStores($productId));
        $storeId = $page === 'dekor' ? 0 : 2;

        if (!in_array($storeId, $stores, true)) {
            $fail('403 Forbidden', 'Το προϊόν δεν ανήκει στο επιλεγμένο κατάστημα.');
            return;
        }

        $product = $this->model_catalog_product->getProduct($productId);
        $imageRelative = str_replace('\\', '/', trim((string)($product['image'] ?? '')));
        $imageRoot = realpath(DIR_IMAGE);
        $realImage = $imageRelative !== ''
            ? realpath(DIR_IMAGE . ltrim($imageRelative, '/'))
            : false;

        if (!$imageRoot || !$realImage ||
            strpos($realImage, $imageRoot . DIRECTORY_SEPARATOR) !== 0) {
            $fail('404 Not Found', 'Δεν βρέθηκε ασφαλής εικόνα για το προϊόν.');
            return;
        }

        $info = @getimagesize($realImage);
        if (!$info || empty($info[0]) || empty($info[1])) {
            $fail('415 Unsupported Media Type', 'Η εικόνα του προϊόντος δεν είναι έγκυρη.');
            return;
        }

        $cacheRelative = 'cache/facebook-photo/' . $page . '-product-' . $productId . '-1000x1000.jpg';
        $destination = DIR_IMAGE . $cacheRelative;
        $photoDir = dirname($destination);

        if (!is_dir($photoDir) && !@mkdir($photoDir, 0755, true) && !is_dir($photoDir)) {
            $fail('500 Internal Server Error', 'Δεν δημιουργήθηκε ο φάκελος εικόνων.');
            return;
        }

        $needsUpdate = !is_file($destination) || filemtime($realImage) > filemtime($destination);

        if ($needsUpdate) {
            switch ($info[2]) {
                case IMAGETYPE_JPEG:
                    $src = @imagecreatefromjpeg($realImage);
                    break;
                case IMAGETYPE_PNG:
                    $src = @imagecreatefrompng($realImage);
                    break;
                case IMAGETYPE_WEBP:
                    $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($realImage) : false;
                    break;
                case IMAGETYPE_GIF:
                    $src = @imagecreatefromgif($realImage);
                    break;
                default:
                    $src = false;
                    break;
            }

            if (!$src) {
                $fail('415 Unsupported Media Type', 'Δεν υποστηρίζεται ο τύπος της εικόνας.');
                return;
            }

            $width = imagesx($src);
            $height = imagesy($src);
            $canvas = imagecreatetruecolor(1000, 1000);

            if (!$canvas || $width < 1 || $height < 1) {
                imagedestroy($src);
                if ($canvas) {
                    imagedestroy($canvas);
                }
                $fail('500 Internal Server Error', 'Δεν δημιουργήθηκε η εικόνα.');
                return;
            }

            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);

            $scale = min(1000 / $width, 1000 / $height);
            $newWidth = max(1, (int)round($width * $scale));
            $newHeight = max(1, (int)round($height * $scale));
            $x = (int)( (1000 - $newWidth) / 2 );
            $y = (int)( (1000 - $newHeight) / 2 );

            imagecopyresampled(
                $canvas, $src, $x, $y, 0, 0,
                $newWidth, $newHeight, $width, $height
            );

            $saved = @imagejpeg($canvas, $destination, 90);
            imagedestroy($src);
            imagedestroy($canvas);

            if (!$saved) {
                $fail('500 Internal Server Error', 'Δεν αποθηκεύτηκε η εικόνα JPEG.');
                return;
            }
        }

        $bytes = @file_get_contents($destination);
        if ($bytes === false) {
            $fail('500 Internal Server Error', 'Δεν ήταν δυνατή η ανάγνωση της εικόνας.');
            return;
        }

        $filename = $page . '-product-' . $productId . '-1000x1000.jpg';
        $this->response->addHeader('Content-Type: image/jpeg');
        $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
        $this->response->addHeader('Content-Length: ' . strlen($bytes));
        $this->response->addHeader('Cache-Control: private, no-store');
        $this->response->addHeader('X-Content-Type-Options: nosniff');
        $this->response->setOutput($bytes);
    }

}
