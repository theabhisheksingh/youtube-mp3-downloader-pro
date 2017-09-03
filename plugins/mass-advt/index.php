<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get current directory name
$myDir = basename(__DIR__);

// Register plugin
uniqueX\Backend::registerPlugin(array(
    'id' => 'mass-advt',
    'dir' => $myDir,
    'name' => 'Mass advertising',
    'description' => 'Display ads on above and below mp3/mp4 download buttons',
    'process' => __DIR__ . '/process.php',
    'version' => array(
        'current' => '1.0',
        'latest' => 'http://files.uniquex.co/plugin-mass-advt.version'
    ),
    'author' => array(
        'name' => 'uniqueX',
        'site' => 'http://uniquex.co/',
    ),
));
