<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Import templates
$_templates = __DIR__ . '/../templates';
if (is_dir($_templates)) {
    foreach (scandir($_templates) as $template) {
        if ($template != '.' && $template != '..' &&
            is_dir("{$_templates}/{$template}") &&
            is_file("{$_templates}/{$template}/index.php")
        ) {
            require_once "{$_templates}/{$template}/index.php";
        }
    }
}

// Import plugins
$_plugins = __DIR__ . '/../plugins';
if (is_dir($_plugins)) {
    foreach (scandir($_plugins) as $plugin) {
        if ($plugin != '.' && $plugin != '..' &&
            is_dir("{$_plugins}/{$plugin}") &&
            is_file("{$_plugins}/{$plugin}/index.php")
        ) {
            require_once "{$_plugins}/{$plugin}/index.php";
        }
    }
}

// Process template
uniqueX\Backend::initTemplate();

// Process plugins
if (is_array($config) && isset($config['plugins']) && is_array($config['plugins'])) {
    foreach ($config['plugins'] as $plugin) {
        uniqueX\Backend::processPlugin($plugin);
    }
}

// Import files
$importFiles = uniqueX\Backend::getValue('importFiles');
if (is_array($importFiles)) {
    foreach ($importFiles as $iFile) {
        require_once $iFile;
    }
}
