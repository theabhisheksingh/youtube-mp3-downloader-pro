<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// No time limit
set_time_limit(0);

// Start session
session_start();

// Get configuration
$config = uniqueX\Backend::getValue('config');

// Get admin user and password from registered config
$aUSER = isset($config['admin-user']) ? trim($config['admin-user']) : null;
$aPASS = isset($config['admin-pass']) ? trim($config['admin-pass']) : null;

// Default admin user and password
$aUSER = strlen($aUSER) > 0 ? $aUSER : 'admin';
$aPASS = strlen($aPASS) > 0 ? $aPASS : '12345';

// Get admin user and password from session
$sUSER = isset($_SESSION['user']) && is_string($_SESSION['user']) ? trim($_SESSION['user']) : null;
$sPASS = isset($_SESSION['pass']) && is_string($_SESSION['pass']) ? trim($_SESSION['pass']) : null;

// Get admin user and password from login form
$fUSER = isset($_POST['user']) && is_string($_POST['user']) ? trim($_POST['user']) : null;
$fPASS = isset($_POST['pass']) && is_string($_POST['pass']) ? trim($_POST['pass']) : null;

// Check admin login status
$adminStatus = false;
$loginFailed = $fUSER != null || $fPASS != null;

// Detect admin credits
if (($aUSER != null && $aPASS != null) &&
    ($aUSER == $sUSER || $aUSER == $fUSER) &&
    ($aPASS == $sPASS || $aPASS == $fPASS)
) {
    $loginStatus = true;
    $adminStatus = true;
    // Set login credits
    $_SESSION['user'] = $aUSER;
    $_SESSION['pass'] = $aPASS;
}

// Register admin login config
uniqueX\Backend::setValue('adminStatus', $adminStatus);
uniqueX\Backend::setValue('loginFailed', $loginFailed);
