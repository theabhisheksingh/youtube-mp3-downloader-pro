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
    <title>Terms of Service - <?php echo @$config['site-name']; ?></title>
    <meta charset="UTF-8"/>
    <meta name="description" content="Terms of Service - <?php echo @$config['site-name']; ?>">
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
        <li class="active"><a href="<?php echo uniqueX\General::siteLink('terms');
            ?>" title="Terms of Service">Terms of Service</a></li>
        <li><a href="<?php echo uniqueX\General::siteLink('privacy');
            ?>" title="Privacy Policy">Privacy Policy</a></li>
        <li><a href="<?php echo uniqueX\General::siteLink('contact');
            ?>" title="Contact Us">Contact Us</a></li>
    </ul>
    <div id="videosArea" class="videosArea">
        <?php echo isset($config['advt-above-results'])
            ? "<div class='area-728'>{$config['advt-above-results']}</div>" : null; ?>
        <h1 class="pageHead">Terms of Services - <?php echo @$config['site-name']; ?></h1>

        <h3>By using the service provided by this site you acknowledge and agree to the following terms of
            service/use:</h3>

        <ul class="itemsList">
            <li>The user may not use this service to download copyrighted materials from the video sites that this
                service allows you to download from.
            </li>
            <li>The user is responsible and takes liability for the data sent
                to <?php echo @$config['site-name']; ?></li>
            <li>The user is aware of the legal risks he/she takes for sound storage and sharing. This is the subject to
                copyrights, which can force you to assume the legal responsibility.
            </li>
            <li>
                If you are the copyright owner of any material and would like to block the conversion of any
                videos to MP3, please contact us via the Contact Page. We will block the conversion of
                those videos.
            </li>
            <li>
                Anything you believe is copyright infringement, please contact us and let us know and
                we will block those videos from converting
            </li>
            <li>
                The user agrees to not use <?php echo @$config['site-name']; ?> services to upload any content that
                spreads messages of terror or depicts torture or death-gui; harm minors in any way, this includes
                any form of child pornography.
            </li>
            <li>
                The user accepts the condition not to share, copy or transmit any information or data
                which is the subject to copyright, brands, labels and other third-party ownership rights.
            </li>
            <li>
                The user is aware that any unlawful sharing of data and information, under protection
                of copyright laws, is illegal.
            </li>
            <li>
                This website does not endorse piracy of any sort, it is just a simple tool to help users convert
                videos into MP3/MP4/3GP/FLV/WEBM/M4A formats. We do not host any of the file types mentioned. For any
                questions, please contact us.
            </li>
            <li>
                The user is exclusively responsible for any damage occurred from using the website.
            </li>
            <li>The service supplier has the right to interrupt service at any time without prior notice.</li>
            <li><?php echo @$config['site-name']; ?> Online reserves the right to change or discontinue any of the
                Services at any time.
            </li>
            <li>The user may only use this tool for his/her own personal use.</li>
        </ul>
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
