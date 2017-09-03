<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get current directory name
$myDir = basename(__DIR__);

// Register plugin
uniqueX\Backend::registerPlugin(array(
    'id' => 'flv2mp3',
    'dir' => $myDir,
    'name' => 'FLV to MP3 Converter (No FFmpeg)',
    'description' => 'Convert YouTube FLV video to MP3 audio file at 64kbps with pure PHP script without FFmpeg.' .
        ' Works on any server (Local host, Shared Host, VPS, Dedicated server, etc...)',
    'process' => __DIR__ . '/process.php',
    'version' => array(
        'current' => '1.1',
        'latest' => 'http://files.uniquex.co/plugin-flv2mp3.version'
    ),
    'author' => array(
        'name' => 'uniqueX',
        'site' => 'http://uniquex.co/',
    ),
));
