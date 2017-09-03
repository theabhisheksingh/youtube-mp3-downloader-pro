<?php
// Get configuration
$config = uniqueX\Backend::getValue('config');
$config = is_array($config) ? $config : array();

// Optimize social links
$socialLinks = array(
    'facebook' => @$config['facebook'],
    'twitter' => @$config['twitter'],
    'google+' => @$config['gplus']
);

?>
<!-- Page Configuration -->
<meta charset="UTF-8"/>
<link rel="shortcut icon" href="<?php echo uniqueX\General::siteLink('favicon.ico') ?>"/>
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
<!-- Import style sheets -->
<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<?php
uniqueX\General::importCSS(array(
    '/assets/scripts/flags.css',
    '/assets/plugins/jquery-ui.min.css',
    '/assets/plugins/slicknav.min.css',
    '/assets/plugins/qtip.min.css'
));
// Get front end stylesheets
$styleSheets = uniqueX\Backend::getValue('styleSheets');
if (is_array($styleSheets)) {
    foreach ($styleSheets as $style) {
        $sheet = is_string($style) ? $style : $style[0];
        if (!(is_array($style) && isset($style[1]) && $style[1])) {
            echo '<link rel="stylesheet" type="text/css" href="' . $sheet . '">';
        }
    }
}
echo '<!-- Import JavaScripts -->';
echo '<script type="text/javascript" src="//code.jquery.com/jquery-2.1.4.min.js"></script>';
uniqueX\General::importJS(array(
    '/assets/plugins/jquery-ui.min.js',
    '/assets/plugins/isotope.min.js',
    '/assets/plugins/slicknav.min.js',
    '/assets/plugins/bpopup.min.js',
    '/assets/plugins/qtip.min.js',
    '/assets/plugins/cookie.js'
));
// Get front end javascripts
$javaScripts = uniqueX\Backend::getValue('javaScripts');
if (is_array($javaScripts)) {
    foreach ($javaScripts as $javaScript) {
        $script = is_string($javaScript) ? $javaScript : $javaScript[0];
        if (!(is_array($javaScript) && isset($javaScript[1]) && $javaScript[1])) {
            echo '<script type="text/javascript" src="' . $script . '"></script>';
        }
    }
}
?>
<!-- AddThis share -->
<?php
$addThis = isset($config['addthis-key']) ? trim($config['addthis-key']) : null;
if (strlen($addThis) > 0) {
    echo '<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=';
    echo $config['addthis-key'] . '"></script>';
}
?>

<!-- Simple configuration -->
<script type="application/javascript">
    // Website config
    window.siteConf = {
        siteName: '<?php echo @$config['site-name']; ?>',
        useFFmpeg: <?php
        echo isset($config['mp3-converter']) && $config['mp3-converter'] == 'ffmpeg' ? 'true' : 'false'; ?>,
        siteLink: '<?php echo uniqueX\General::siteLink(''); ?>',
        loadMore: <?php $maxVideos = intval($config['max-videos']);
        echo $maxVideos > 1 && $maxVideos <= 50 ? $maxVideos : 10; ?>,
        musicPlayer: <?php echo uniqueX\Backend::checkPlugin('music-player') ? 'true' : 'false'; ?>,
        instantMode: <?php
        echo isset($config['instant-mode']) && $config['instant-mode'] == 'no' ? 'false' : 'true'; ?>,
        addthisShare: <?php echo strlen($addThis) > 0 ? 'true' : 'false'; ?>,
        detectCountry: <?php
        echo isset($config['detect-country']) && $config['detect-country'] == 'no' ? 'false' : 'true'; ?>,
        directDownload: <?php
        echo isset($config['direct-download']) && $config['direct-download'] == 'yes' ? 'true' : 'false'; ?>
    };
    // SEO config
    window.siteTitles = {
        charts: '<?php echo $config['chart-page-title']; ?>',
        search: '<?php echo $config['search-page-title']; ?>',
        video: '<?php echo $config['video-page-title'];  ?>'
    };
</script>
