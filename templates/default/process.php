<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Check YouTube API key
if (is_string(uniqueX\General::youtubeKey())) {
    // Get current directory name
    $myDir = basename(__DIR__);
    // Register pages
    uniqueX\Backend::registerPage('#default', __DIR__ . '/pages/index.php');
    uniqueX\Backend::registerPage('#404', __DIR__ . '/pages/404.php');
    uniqueX\Backend::registerPage('maintenance', __DIR__ . '/pages/maintenance.php');
    uniqueX\Backend::registerPage('contact', __DIR__ . '/pages/contact.php');
    uniqueX\Backend::registerPage('privacy', __DIR__ . '/pages/privacy.php');
    uniqueX\Backend::registerPage('terms', __DIR__ . '/pages/terms.php');
    // Import javascripts
    $jsFiles = array(
        "templates/{$myDir}/scripts/main.js?v=" . filemtime(__DIR__ . '/scripts/main.js')
    );
    foreach ($jsFiles as $pos => $js) {
        $jsFiles[$pos] = uniqueX\General::siteLink($js);
    }
    uniqueX\Backend::importJavaScripts($jsFiles);
    // Import stylesheet
    $cssFiles = array(
        "templates/{$myDir}/scripts/main.css?v=" . filemtime(__DIR__ . '/scripts/main.css'),
        "templates/{$myDir}/scripts/flavors.css?v=" . filemtime(__DIR__ . '/scripts/flavors.css')
    );
    foreach ($cssFiles as $pos => $css) {
        $cssFiles[$pos] = uniqueX\General::siteLink($css);
    }
    uniqueX\Backend::importStyleSheets($cssFiles);
}
