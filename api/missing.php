<?php
// ERROR REPORTING
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('max_input_time', 30000);
ini_set('max_execution_time', 30000);
header('Content-type: text/html; charset=utf-8');
set_time_limit(0);

// OPENCART FRAMEWORK
$root = $_SERVER['DOCUMENT_ROOT'].'/';    
if (file_exists($root . 'config.php')) { require_once($root . 'config.php'); }
if (file_exists($root . 'system/startup.php')) { require_once($root . 'system/startup.php'); }

global $loader,$registry,$config;
$registry = new Registry();	
$loader = new Loader($registry);
$registry->set('load', $loader);
$config = new Config();
$registry->set('config', $config);
$cache = new Cache('file');
$registry->set('cache', $cache);
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

// ----------------------------
// MAIN PRODUCT IMAGE CHECK
// ----------------------------
$query = $db->query("SELECT product_id, image FROM " . DB_PREFIX . "product WHERE image IS NOT NULL AND image != ''");

$missing_main = [];

foreach ($query->rows as $row) {
    $image_path = DIR_IMAGE . $row['image'];
    if (!file_exists($image_path)) {
        $missing_main[] = $row;

        // Remove broken main image reference
        // $db->query("UPDATE " . DB_PREFIX . "product 
                    // SET image = '' 
                    // WHERE product_id = " . (int)$row['product_id']);
    }
}

// ----------------------------
// ADDITIONAL PRODUCT IMAGES CHECK
// ----------------------------
$query = $db->query("SELECT product_image_id, product_id, image FROM " . DB_PREFIX . "product_image");

$missing_additional = [];

foreach ($query->rows as $row) {
    $image_path = DIR_IMAGE . $row['image'];
    if (!file_exists($image_path)) {
        $missing_additional[] = $row;

        // Delete broken additional image row
        // $db->query("DELETE FROM " . DB_PREFIX . "product_image 
                    // WHERE product_image_id = " . (int)$row['product_image_id']);
    }
}

// ----------------------------
// OUTPUT RESULTS
// ----------------------------
if ($missing_main) {
    echo "<h3>Main product images missing (" . count($missing_main) . ")</h3>";
    echo "<table border='1' cellpadding='5'><tr><th>Product ID</th><th>Old Image</th></tr>";
    foreach ($missing_main as $row) {
        echo "<tr><td>{$row['product_id']}</td><td>{$row['image']}</td></tr>";
    }
    echo "</table><br>";
}

if ($missing_additional) {
    echo "<h3>Additional product images missing (" . count($missing_additional) . ")</h3>";
    echo "<table border='1' cellpadding='5'><tr><th>Product Image ID</th><th>Product ID</th><th>Old Image</th></tr>";
    foreach ($missing_additional as $row) {
        echo "<tr><td>{$row['product_image_id']}</td><td>{$row['product_id']}</td><td>{$row['image']}</td></tr>";
    }
    echo "</table><br>";
}

if (!$missing_main && !$missing_additional) {
    echo "✅ All product images exist.";
}
