<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Current directory name
$myDir = basename(__DIR__);

// Import library
require_once __DIR__ . '/inc/dmca.php';

// Admin panel for DMCA
uniqueX\Backend::registerAdmin('plugins', 'copyright', 'dmca', 'DMCA Notice', __DIR__ . '/inc/index.php');

// Register ajax
uniqueX\Backend::registerPage('$ajax-dmca', __DIR__ . '/inc/ajax.php');

// Import styles and javascripts
uniqueX\Backend::importStyleSheets(array(
    array(uniqueX\General::siteLink("plugins/{$myDir}/inc/style.css"), true)
));
uniqueX\Backend::importJavaScripts(array(
    array(uniqueX\General::siteLink("plugins/{$myDir}/inc/dmca.js"), true)
));
