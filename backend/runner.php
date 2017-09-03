<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Check admin status
if (!uniqueX\Backend::getValue('adminStatus')) {
    exit;
}

// Get PHP code
$phpCode = isset($_POST['phpCode']) ? trim($_POST['phpCode']) : null;

// Check PHP code
if ($phpCode != null) {
    // Save php code
    $phpFile = __DIR__ . '/../store/' . uniqueX\Backend::installKey() . '.php';
    file_put_contents($phpFile, $phpCode);
    // Run php code
    if (is_file($phpFile)) {
        require_once $phpFile;
    }
}
