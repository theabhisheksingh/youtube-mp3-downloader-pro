<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get current directory name
$myDir = basename(__DIR__);

// Register plugin
uniqueX\Backend::registerPlugin(array(
    'id' => 'dmca-notice',
    'dir' => $myDir,
    'name' => 'DMCA Notice',
    'description' => 'Don\'t allow users to download video audio files of specific video or channel',
    'process' => __DIR__ . '/process.php',
    'version' => array(
        'current' => '1.1',
        'latest' => 'http://files.uniquex.co/plugin-dmca-notice.version'
    ),
    'author' => array(
        'name' => 'uniqueX',
        'site' => 'http://uniquex.co/',
    ),
));
