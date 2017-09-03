<?php

// Detect PHP version
if (!version_compare(PHP_VERSION, '5.3', '>=')) {
    echo 'You must need PHP 5.3 or greater to use this script. Your PHP version is: ' . PHP_VERSION . '. ';
    echo 'Please upgrade your PHP version. Contact your hosting provider.';
    exit;
}

// Try to create store folder
$store = __DIR__ . '/store';
if (!is_dir($store)) {
    @mkdir($store);
    @chmod($store, 0777);
}

// Detect store folder
clearstatcache();
if (!is_dir($store)) {
    echo 'Create store folder and provide read and write permissions.';
    exit;
}

// Check store folder permissions
if (!(is_readable($store) && is_writable($store))) {
    echo 'Please provide read and write permissions for store folder.';
    exit;
}

// Start process
$_INIT_ = true;
require_once __DIR__ . '/include/index.php';
