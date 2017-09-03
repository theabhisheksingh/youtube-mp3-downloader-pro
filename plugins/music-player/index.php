<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get current directory name
$myDir = basename(__DIR__);

// Register plugin
uniqueX\Backend::registerPlugin(array(
    'id' => 'music-player',
    'dir' => $myDir,
    'name' => 'Music player (Advanced Options)',
    'description' => 'Allow your website users to play and listen music on your website instally.',
    'process' => __DIR__ . '/process.php',
    'version' => array(
        'current' => '1.2',
        'latest' => 'http://files.uniquex.co/plugin-music-player.version'
    ),
    'author' => array(
        'name' => 'uniqueX',
        'site' => 'http://uniquex.co/',
    ),
));
