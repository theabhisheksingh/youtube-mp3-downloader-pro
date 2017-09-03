<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get this plugin directory name
$myDir = basename(__DIR__);

// Admin panel for MASS ADVT
uniqueX\Backend::registerAdmin('plugins', 'usd', 'mass-advt', 'Mass Advertising', __DIR__ . '/files/index.php');

// Register frame
uniqueX\Backend::registerPage('adframe', __DIR__ . '/files/frame.php');

// Register dynamic javascript page
uniqueX\Backend::registerPage('mass-advt.js', __DIR__ . '/files/mass-advt.php');

// Import mass advt processor
uniqueX\Backend::importStyleSheets(array(uniqueX\General::siteLink("plugins/{$myDir}/files/mass-advt.css")));
uniqueX\Backend::importJavaScripts(array(uniqueX\General::siteLink("mass-advt.js?rand=" . md5(microtime(true)))));
