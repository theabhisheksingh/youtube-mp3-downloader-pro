<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

if (!uniqueX\Backend::getValue('adminStatus')) {
    require_once __DIR__ . '/login.php';
    exit;
}

if (function_exists('ob_start')) {
    @ob_start();
}

// Magic quotes issue
if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() === 1) {
    foreach ($_POST as $key => $value) {
        if (is_string($key) && is_string($value)) {
            $_POST[$key] = stripslashes($value);
        }
    }
}

// Define default admin pages
uniqueX\Backend::registerAdmin('main', 'cogs', '#default', 'Configuration', __DIR__ . '/config.php');
uniqueX\Backend::registerAdmin('main', 'plug', 'plugins', 'Plugins', __DIR__ . '/plugins.php');
uniqueX\Backend::registerAdmin('main', 'paint-brush', 'templates', 'Templates', __DIR__ . '/templates.php');
uniqueX\Backend::registerAdmin('main', 'stethoscope', 'troubleshoot', 'Troubleshooter', __DIR__ . '/troubleshoot.php');

uniqueX\Backend::registerAdmin('others', 'terminal', 'developer', 'Developer', __DIR__ . '/developer.php');
uniqueX\Backend::registerAdmin('others', 'heartbeat', 'information', 'Information', __DIR__ . '/information.php');
uniqueX\Backend::registerAdmin('others', 'lock', 'settings', 'Settings', __DIR__ . '/settings.php');

// Get admin page
$adminPath = uniqueX\Backend::getValue('adminPath');
$adminPath = is_string($adminPath) && strlen($adminPath) > 0 ? $adminPath : '#default';

// Get admin pages
$adminPages = uniqueX\Backend::getValue('adminPages');

// Logout
if (isset($_GET['logout']) && $_GET['logout'] == strrev(md5($_SESSION['user'] . $_SESSION['pass']))) {
    unset($_SESSION['user']);
    unset($_SESSION['pass']);
    header('Location: ' . uniqueX\General::siteLink('$admin'));
    exit;
}

?>
<!doctype html>
<html data-token="<?php echo md5(uniqueX\Backend::installKey()); ?>">
<head>
    <!-- Page details -->
    <title>YouTube to MP3 Converter Pro - Admin Panel</title>
    <meta charset="UTF-8"/>
    <link rel="shortcut icon" href="<?php echo uniqueX\General::siteLink('assets/images/gear.png'); ?>"/>
    <!-- Import CSS style sheets -->
    <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="<?php
    echo uniqueX\General::siteLink('assets/scripts/admin.css?v=' . PRODUCT_VERSION); ?>">
    <?php
    // Get front end stylesheets
    $styleSheets = uniqueX\Backend::getValue('styleSheets');
    if (is_array($styleSheets)) {
        foreach ($styleSheets as $style) {
            if (is_array($style) && isset($style[1]) && $style[1]) {
                echo '<link rel="stylesheet" type="text/css" href="' . $style[0] . '">';
            }
        }
    }
    ?>
    <!-- Import javascript files -->
    <script type="text/javascript" src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script type="text/javascript" src="<?php
    echo uniqueX\General::siteLink('assets/plugins/cookie.js'); ?>"></script>
    <script type="text/javascript" src="<?php
    echo uniqueX\General::siteLink('assets/scripts/admin.js?v=' . PRODUCT_VERSION); ?>"></script>
    <?php
    // Get front end javascripts
    $javaScripts = uniqueX\Backend::getValue('javaScripts');
    if (is_array($javaScripts)) {
        foreach ($javaScripts as $javaScript) {
            if (is_array($javaScript) && isset($javaScript[1]) && $javaScript[1]) {
                echo '<script type="text/javascript" src="' . $javaScript[0] . '"></script>';
            }
        }
    }
    ?>
</head>
<body>
<div id="header">
    <a id="pageTitle" href="<?php echo uniqueX\General::siteLink('$admin'); ?>"
        >YouTube to MP3 Converter Pro - Admin Panel</a>
    <ul id="topMenu">
        <?php
        $siteName = isset($config['site-name']) && strlen($config['site-name']) > 0
            ? $config['site-name'] : 'View site';
        echo '<li><a href="." title="' . $siteName . '" target="_blank">' . $siteName . '</a></li>';
        ?>
        <li>
            <a href="#" id="logOut"
               data-logout="?logout=<?php echo strrev(md5($_SESSION['user'] . $_SESSION['pass'])) ?>">Logout</a>
        </li>
    </ul>
</div>
<div id="leftMenu">
    <ul>
        <?php
        list($mainMenu, $pluginMenu, $otherMenu) = array(null, null, null);
        if (is_array($adminPages)) {
            foreach ($adminPages as $aPage) {
                $adminGo = uniqueX\General::siteLink('$admin' .
                    ($aPage['id'] == '#default' ? null : "-{$aPage['id']}"));
                switch ($aPage['category']) {
                    case 'main':
                        $mainMenu .= '<li' . ($adminPath == $aPage['id'] ? ' class="active"' : null) .
                            ' data-admin-go="' . $adminGo . '"><span class="fa fa-' . $aPage['icon'] .
                            '"></span>' . $aPage['name'] . '</li>';
                        break;
                    case 'plugins':
                        $pluginMenu .= '<li' . ($adminPath == $aPage['id'] ? ' class="active"' : null) .
                            ' data-admin-go="' . $adminGo . '"><span class="fa fa-' . $aPage['icon'] .
                            '"></span>' . $aPage['name'] . '</li>';
                        break;
                    case 'others':
                        $otherMenu .= '<li' . ($adminPath == $aPage['id'] ? ' class="active"' : null) .
                            ' data-admin-go="' . $adminGo . '"><span class="fa fa-' . $aPage['icon'] .
                            '"></span>' . $aPage['name'] . '</li>';
                        break;
                }
            }
        }
        echo "{$mainMenu}{$pluginMenu}{$otherMenu}";
        ?>
    </ul>
    <div class="uniquexCR">
        <a href="http://uniquex.co/" title="uniqueX" target="_blank">
            uniqueX &copy; <?php echo date('Y'); ?>
        </a>
    </div>
</div>
<div id="contentPage">
    <?php
    if ($_SESSION['user'] == 'admin' && $_SESSION['pass'] == '12345') {
        echo '<div class="adminCredits">Hi, please update your admin panel login details. <a
            href="' . uniqueX\General::siteLink('$admin-settings') . '">click here</a></div>';
    }
    // Process admin page
    $importStatus = false;
    if (is_array($adminPages)) {
        foreach ($adminPages as $aPage) {
            if (!$importStatus && $aPage['id'] == $adminPath) {
                require_once $aPage['page'];
                $importStatus = true;
            }
        }
    }
    // Admin page not found
    if (!$importStatus) {
        echo '<h2>Admin Error</h2>';
        echo '<div class="adminPage">This page is not available in admin panel</div>';
    }
    ?>
    <div style="clear:both"></div>
</div>
</body>
</html>
