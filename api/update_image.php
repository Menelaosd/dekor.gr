<?php
//ERROR REPORTING
exit;
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors" , 1);
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('max_input_time', 30000);
ini_set('max_execution_time', 30000);
header( 'Content-type: text/html; charset=utf-8' );
set_time_limit(0);

// OPENCART FRAMEWORK
$root = $_SERVER['DOCUMENT_ROOT'].'/';    
if (file_exists($root . 'config.php')) {require_once($root . 'config.php');};
if (file_exists($root . 'system/startup.php')) {require_once($root . 'system/startup.php');};
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

$dir = DIR_IMAGE . 'catalog';

$it = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
	RecursiveIteratorIterator::CHILD_FIRST
);
foreach ($it as $path) {
	if ($path->isDir() && !(new FilesystemIterator($path->getPathname()))->valid()) {
		@rmdir($path->getPathname());
	}
}