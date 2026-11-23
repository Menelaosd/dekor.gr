<?php
declare(strict_types=1);

$host = strtolower(preg_replace(
    '/^www\./',
    '',
    (string)($_SERVER['HTTP_HOST'] ?? '')
));

$stores = [
    'dekor.gr' => 0,
    'apokrifa.gr' => 2
];

if (!isset($stores[$host])) {
    http_response_code(400);
    exit('Invalid store.');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid product.');
}

require dirname(__DIR__) . '/config.php';

$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($db->connect_errno) {
    http_response_code(503);
    exit('Temporarily unavailable.');
}

$db->set_charset('utf8mb4');
$storeId = $stores[$host];

$stmt = $db->prepare(
    "SELECT p.product_id
     FROM `" . DB_PREFIX . "product` p
     JOIN `" . DB_PREFIX . "product_to_store` p2s
       ON p2s.product_id = p.product_id
     WHERE p.product_id = ?
       AND p.status = 1
       AND p2s.store_id = ?
     LIMIT 1"
);

$stmt->bind_param('ii', $id, $storeId);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    $db->close();
    http_response_code(404);
    exit('Product not found in this store.');
}

$query = 'product_id=' . $id;
$stmt = $db->prepare(
    "SELECT keyword
     FROM `" . DB_PREFIX . "seo_url`
     WHERE `query` = ? AND store_id = ?
     ORDER BY language_id ASC
     LIMIT 1"
);

$stmt->bind_param('si', $query, $storeId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$db->close();

if (!empty($row['keyword'])) {
    $target = 'https://' . $host . '/' . ltrim($row['keyword'], '/');
} else {
    $target = 'https://' . $host .
        '/index.php?route=product/product&product_id=' . $id;
}

header('Cache-Control: no-store');
header('Location: ' . $target, true, 302);
exit;