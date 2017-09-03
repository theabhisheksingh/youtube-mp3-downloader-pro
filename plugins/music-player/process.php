<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get this plugin directory name
$myDir = basename(__DIR__);

// Import music player processer
uniqueX\Backend::importJavaScripts(array(uniqueX\General::siteLink("plugins/{$myDir}/musicPlayer.js")));

// Attach music player HTML code to page
uniqueX\Backend::attachBody(file_get_contents(__DIR__ . '/musicPlayer.html'));
