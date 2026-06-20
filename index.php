<?php

if ($_SERVER['REQUEST_URI'] === '/%CE%B1%CE%BB%CE%BB%CE%B1%20-%CF%80%CF%81%CE%BF%CF%8A%CE%BF%CE%BD%CF%84%CE%B1') {
    header("Location: https://dekor.gr/aggelies-exoplismos-ergastirion", true, 301);
    exit();
}


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Version
define('VERSION', '3.0.2.0');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: install/index.php');
	exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('catalog');