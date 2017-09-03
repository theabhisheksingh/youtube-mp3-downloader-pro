<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get template color
list($template, $flavor, $color) = array(uniqueX\Backend::getValue('template'), uniqueX\Backend::getFlavor(), '#4DBDEB');
if (isset($template['flavors'][$flavor]) && substr($template['flavors'][$flavor][0], 0, 1) == '#') {
    $color = $template['flavors'][$flavor][0];
}


?>
<!doctype html>
<html class="flavor-<?php echo $flavor; ?>">
<head>
    <title>Privacy Policy - <?php echo @$config['site-name']; ?></title>
    <meta charset="UTF-8"/>
    <meta name="description" content="Privacy Policy - <?php echo @$config['site-name']; ?>">
    <meta name="theme-color" content="<?php echo $color; ?>">
    <!-- Extra scripts -->
    <?php require_once __DIR__ . '/../layers/html_head.php'; ?>
</head>
<body>
<?php
// Import page header
require_once __DIR__ . '/../layers/site_header.php';
?>
<div id="videosList">
    <ul id="videosMenu">
        <li><a href="<?php echo uniqueX\General::siteLink('terms');
            ?>" title="Terms of Service">Terms of Service</a></li>
        <li class="active"><a href="<?php echo uniqueX\General::siteLink('privacy');
            ?>" title="Privacy Policy">Privacy Policy</a></li>
        <li><a href="<?php echo uniqueX\General::siteLink('contact');
            ?>" title="Contact Us">Contact Us</a></li>
    </ul>
    <div id="videosArea" class="videosArea">
        <?php echo isset($config['advt-above-results'])
            ? "<div class='area-728'>{$config['advt-above-results']}</div>" : null; ?>
        <h1 class="pageHead">Privacy Policy - <?php echo @$config['site-name']; ?></h1>

        <h3>What is Lorem Ipsum?</h3>

        <p class="pageContent">
            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the
            industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and
            scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into
            electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of
            Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like
            Aldus PageMaker including versions of Lorem Ipsum.
        </p>

        <h3>What is Lorem Ipsum?</h3>

        <p class="pageContent">
            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the
            industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and
            scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into
            electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of
            Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like
            Aldus PageMaker including versions of Lorem Ipsum.
        </p>
    </div>
    <div class="sidePanel">
        <?php require_once __DIR__ . '/../layers/side_panel.php'; ?>
    </div>
    <div style="position:relative;clear:both"></div>
</div>
<div id="videoPlayer"></div>
<?php require_once __DIR__ . '/../layers/site_footer.php'; ?>
</body>
</html>