<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get current directory name
$myDir = basename(__DIR__);

// Register template
uniqueX\Backend::registerTemplate(array(
    'id' => 'default',
    'dir' => $myDir,
    'name' => 'Default template',
    'thumbnail' => uniqueX\General::siteLink("templates/{$myDir}/images/thumbnail.png"),
    'description' => 'Default template of YouTube to MP3 Converter Pro',
    'process' => __DIR__ . '/process.php',
    'features' => array(
        'responsive' => true,
        'desktops' => true,
        'tablets' => true,
        'mobiles' => true,
        'phones' => false,
    ),
    'flavors' => array(
        'random' => array('transparent', 'Random'),
        'default' => array('transparent', 'Default'),
        'blue' => array('#4DBDEB', 'Blue'),
        'dark' => array('#6a737b', 'Dark'),
        'green' => array('#34bf49', 'Green'),
        'smoke' => array('#eeeeee', 'Smoke'),
        'red' => array('#cd201f', 'Red'),
        'yellow' => array('#ffd900', 'Yellow'),
        'violet' => array('#9b59b6', 'Violet'),
        'orange' => array('#FF6600', 'Orange')
    ),
    'special' => array(
        'music-player' => array('Music Player', true),
        'multi-language' => array('Multi language', false),
    ),
    'version' => array(
        'current' => '1.2',
        'latest' => 'http://files.uniquex.co/template-default.version',
    ),
    'author' => array(
        'name' => 'uniqueX',
        'site' => 'http://uniquex.co/'
    ),
));
