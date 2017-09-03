<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Set default timezone
date_default_timezone_set('America/Los_Angeles');

// Import configuration and libraries
require_once __DIR__ . '/../core/Cache.php';
require_once __DIR__ . '/../core/YouTube.php';
require_once __DIR__ . '/../core/General.php';
require_once __DIR__ . '/../core/Backend.php';
require_once __DIR__ . '/../include/version.php';

// Get HTTP request query
preg_match('/(?P<query>[^\/]+)$/i', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $mDynamic);
$requestQuery = is_array($mDynamic) && isset($mDynamic['query']) ? trim($mDynamic['query']) : null;

// Very special requests
if (preg_match('/^(curl|ffmpeg).+\.txt$/i', $requestQuery)) {
    exit;
}

// Get site config
uniqueX\Backend::setValue('config', uniqueX\Backend::getConfig());
$config = uniqueX\Backend::getValue('config');
$config = is_array($config) ? $config : array();

// Validate site name
$host = isset($_SERVER['HTTP_HOST']) && is_string($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
$domain = isset($config['site-addr']) && is_string($config['site-addr']) ? $config['site-addr'] : null;
$domain = strpos($domain, '://') !== false ? parse_url($domain, PHP_URL_HOST) : $domain;

// Check for host
if ($host == null) {
    ?>
    <!doctype html>
    <html>
    <head>
        <meta name="robots" content="noindex">
    </head>
    <body>
    Invalid host
    </body>
    </html>
    <?php
    exit;
}

if ($host != $domain && substr($requestQuery, 0, 1) != '$') {

    // Redirect to main domain
    if ($domain != null) {
        header('HTTP/1.1 301 Moved Permanently');
        header("Location: http://{$domain}{$_SERVER['REQUEST_URI']}");
        exit;
    }

    // Config domain
    ?>
    <!doctype html>
    <html>
    <head>
        <title>Invalid configuration</title>
        <meta charset="UTF-8">
        <meta name="robots" content="noindex">
        <style>
            body {
                margin-top: 100px;
                text-align: center;
            }

            * {
                color: #00bce4;
                font-family: monospace;
                font-size: 22px;
            }
        </style>
    </head>
    <body>
    Open <a href="./$admin" title="Admin panel" style="color: #00a4e4">admin panel</a>
    and config site address (domain name) in configuration panel to resolve this issue.
    </body>
    </html>
    <?php
    exit;
}

// Robots.txt
if (strtolower($requestQuery) == 'robots.txt') {
    header('Content-Type: text/plain; charset=UTF-8');
    echo isset($config['robots-txt']) ? $config['robots-txt'] : null;
    exit;
}

// Check for manual debug mode
$debugFile = __DIR__ . '/../debug.txt';
$debugNeed =
    is_file($debugFile) &&
    is_string($debugData = @file_get_contents($debugFile)) &&
    strtolower($debugData) == 'on';

// Enable or disable debug mode
if ($debugNeed || (isset($config['debug-mode']) && $config['debug-mode'] == 'on')) {
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 'Off');
    error_reporting(0);
}

// Detect admin login status
require_once __DIR__ . '/../backend/index.php';

// Import kernel
require_once __DIR__ . '/import.php';

// Process admin
if (preg_match('/^\$admin(?P<admin>.*)/i', $requestQuery, $matches)) {
    // Set admin path
    $adminPath = strtolower(ltrim(isset($matches['admin']) ? $matches['admin'] : null, '-'));
    uniqueX\Backend::setValue('adminPath', $adminPath);
    // Start process
    require_once __DIR__ . '/../backend/init.php';
    exit;
}

// Process sitemap
if (preg_match('/^sitemap(?P<sitemap>[^\.]*)\.xml$/i', $requestQuery, $matches)) {
    // Set sitemap path
    uniqueX\Backend::setValue('siteMap', isset($matches['sitemap']) ? $matches['sitemap'] : null);
    // Start process
    require_once __DIR__ . '/sitemap.php';
    exit;
}

// Process dynamic sharing thumbnail
if (preg_match('/^share\-(?P<share>[^\.]*)\.png/i', $requestQuery, $matches)) {
    // Set thumbnail ID
    uniqueX\Backend::setValue('shareThumb', isset($matches['share']) ? $matches['share'] : null);
    // Start process
    require_once __DIR__ . '/share.php';
    exit;
}

// Register some important files
uniqueX\Backend::registerPage('ajax', __DIR__ . '/ajax.php');
uniqueX\Backend::registerPage('$runner', __DIR__ . '/../backend/runner.php');
uniqueX\Backend::registerPage('$ajax-admin', __DIR__ . '/../backend/ajax.php');

// Process request to registered pages
$getPages = uniqueX\Backend::getValue('pages');
if (is_array($getPages)) {
    foreach ($getPages as $id => $file) {
        if ($requestQuery == $id && is_file($file)) {
            require_once $file;
            exit;
        }
    }
}

// DMCA process
list($dmcaExist, $dmcaFile, $dmcaData, $httpLink) = array(false, __DIR__ . '/../dmca.txt', null, null);

if (is_file($dmcaFile)) {
    $dmcaData = preg_replace('/\s+/i', '', file_get_contents($dmcaFile));
}

if (isset($_SERVER['REQUEST_URI'])) {
    $httpLink = explode('?', $_SERVER['REQUEST_URI']);
    $httpLink = $httpLink[0] == '/' ? null : $httpLink[0];
}

if ($dmcaData != null && $httpLink != null && stripos("{$dmcaData}http://", "{$httpLink}http://") !== false) {
    $dmcaExist = true;
}

if ($dmcaExist) {
    require_once $getPages['#404'];
    exit;
}

// Process request to default page
if (is_array($getPages) && isset($getPages['#default']) && is_file($getPages['#default'])) {
    require_once $getPages['#default'];
    exit;
}

// Process request to 404 page
if (is_array($getPages) && isset($getPages['#404']) && is_file($getPages['#404'])) {
    require_once $getPages['#404'];
    exit;
}

// Set not configured. So, redirect to admin
header('Location: ' . uniqueX\General::siteLink('$admin'));
