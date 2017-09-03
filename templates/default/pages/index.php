<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get template color
list($template, $flavor, $color) = array(uniqueX\Backend::getValue('template'), uniqueX\Backend::getFlavor(), '#4DBDEB');
if (isset($template['flavors'][$flavor]) && substr($template['flavors'][$flavor][0], 0, 1) == '#') {
    $color = $template['flavors'][$flavor][0];
}

// Check for is homepage
uniqueX\Backend::setValue('homePage', $requestQuery == null);
uniqueX\Backend::setValue('favoriteVideos', stripos($requestQuery, '@Favorites') !== false);
$_favoriteVideos = uniqueX\Backend::getValue('favoriteVideos');

// Optimize query request
$requestQuery = $requestQuery == null ? '@Charts' : urldecode($requestQuery);

// Get search term
$searchTerm = isset($_COOKIE['searchTerm']) ? urldecode($_COOKIE['searchTerm']) : null;

// Optimize request query
if (uniqueX\General::safeString($searchTerm) == $requestQuery) {
    $requestQuery = $searchTerm;
}
$requestQuery = ucfirst($requestQuery);

// Process final query (init)
list($queryType, $queryData, $chartCountry) = array(null, null, null);

// Process query
if (preg_match('/^@Charts=?(?P<country>.*)/i', $requestQuery, $matches)) {
    // Get charts
    $charts = uniqueX\YouTube::charts();
    // Process charts request
    if (is_array($charts)) {
        // Get initial data of charts
        $ccList = array();
        $finalCountry = null;
        $queryCountry = strtoupper($matches['country']);
        $chartCountries = array_keys($charts['list']);
        // Search for country on charts
        foreach ($charts['countries'] as $countries) {
            foreach ($countries as $code => $name) {
                $ccList[$code] = $name;
            }
        }
        foreach ($ccList as $code => $name) {
            if ((
                    $code == $queryCountry ||
                    strtoupper(uniqueX\General::safeString($name)) == uniqueX\General::safeString($queryCountry)
                ) &&
                in_array(strtolower($code), $chartCountries)
            ) {
                $finalCountry = $code;
                break;
            }
        }
        // Get default chart
        $userChart = isset($_COOKIE['chartCountryCode']) ? $_COOKIE['chartCountryCode'] : null;
        $defaultChart = trim(strtoupper(@$config['default-chart']));
        // Optimize final country
        if ($finalCountry == null && in_array($userChart, array_keys($ccList))) {
            $finalCountry = $userChart;
        }
        if ($finalCountry == null && in_array($defaultChart, array_keys($ccList))) {
            $finalCountry = $defaultChart;
        }
        $finalCountry = $finalCountry != null ? $finalCountry : 'US';
        // Optimize final country
        $finalCountry = in_array(strtolower($finalCountry), array_keys($charts['list'])) ? $finalCountry : 'US';
        // Get top videos of country
        $queryType = 'chart';
        $queryData = $charts['list'][strtolower($finalCountry)];
        $chartCountry = "{$finalCountry}:{$ccList[$finalCountry]}";
    }
} elseif (strlen($requestQuery) > 0) {
    // Update query
    $queryType = 'search';
    $queryData = $requestQuery;
}

// Get Open Graph Tags (SEO)
$ogTags = uniqueX\General::openGraph($queryType, $_favoriteVideos ? 'Favorite' : $queryData, $chartCountry);

// Check OG tags
if (!$_favoriteVideos && !(is_array($ogTags) && is_array($ogTags['videos']) && count($ogTags['videos']) > 0)) {
    require_once __DIR__ . '/404.php';
    exit;
}

if (class_exists('uniqueX\DMCA')) {
    // DMCA gateway
    list($dmcaRecords, $dmcaReport, $dmcaVideo, $dmcaChannel) = array(uniqueX\DMCA::getDMCA(), false, null, null);
    // Get video and channel
    if ($ogTags['page'] == 'video' || $ogTags['page'] == 'channel') {
        $dmcaVideo = $ogTags['videos'][0]['id'];
        $dmcaChannel = $ogTags['videos'][0]['publish']['user'];
    }
    // Scan for DMCA
    if (is_array($dmcaRecords) && count($dmcaRecords) > 0) {
        $requestURI = uniqueX\General::safeString($_SERVER['REQUEST_URI']);
        foreach ($dmcaRecords as $record) {
            switch ($record[0]) {
                case 'word':
                    $dmcaReport = stripos($requestURI, uniqueX\General::safeString($record[1])) !== false;
                    break;
                case 'video':
                    $dmcaReport = $dmcaVideo == $record[1];
                    break;
                case 'channel':
                    $dmcaReport = $dmcaChannel == $record[1];
                    break;
            }
            if ($dmcaReport) {
                break;
            }
        }
    }

    if ($dmcaReport) {
        require_once __DIR__ . '/404.php';
        exit;
    }
}

?>
<!doctype html>
<html class="flavor-<?php echo $flavor; ?>">
<head>
    <title><?php echo $ogTags['title']; ?></title>
    <meta charset="UTF-8"/>
    <meta name="description" content="<?php echo $ogTags['description']; ?>">
    <meta name="keywords" content="<?php echo $ogTags['keywords']; ?>">
    <meta name="theme-color" content="<?php echo $color; ?>">
    <!-- OG tags -->
    <meta property="og:title" content="<?php echo $ogTags['title'] ?>"/>
    <meta property="og:description" content="<?php echo $ogTags['description'] ?>"/>
    <meta property="og:image" content="<?php
    echo uniqueX\General::siteLink("share-{$ogTags['thumbnail']}.png");
    ?>"/>
    <meta property="og:url" content="<?php echo uniqueX\General::siteLink($requestQuery); ?>"/>
    <meta property="og:site_name" content="<?php echo @$config['site-name']; ?>"/>
    <meta property="og:type" content="website"/>
    <!-- Extra scripts -->
    <?php require_once __DIR__ . '/../layers/html_head.php'; ?>
</head>
<body data-app="yes" data-default-chart="<?php
echo isset($config['default-chart']) ? $config['default-chart'] : 'US';
?>">
<?php
// Import page header
require_once __DIR__ . '/../layers/site_header.php';
// Print videos list
echo "\n<div class='videos-list'>\n";
foreach ($ogTags['videos'] as $video) {
    $videoLink = uniqueX\General::siteLink(uniqueX\General::safeString($video['title']) . "({$video['id']})");
    $videoUser = uniqueX\General::siteLink(
        uniqueX\General::safeString($video['publish']['owner']) . "({$video['publish']['user']})"
    );
    echo "<div class=\"video\">\n";
    echo "\t<h3><a href=\"{$videoLink}\" title=\"{$video['title']}\">{$video['title']}</a></h3>\n";
    echo "\t<a href=\"{$videoLink}\" title=\"{$video['title']}\">\n";
    echo "\t\t<img src=\"{$video['thumbnail']}\" title=\"{$video['title']}\" alt=\"{$video['title']}\"/>\n";
    echo "\t</a>\n";
    echo "\t<ul>\n";
    echo "\t\t<li>Video duration : " . uniqueX\General::HumanTime($video['duration']) . "</li>\n";
    echo "\t\t<li>Video uploaded by : \n";
    echo "\t\t\t<a href=\"{$videoUser}\" title=\"{$video['publish']['owner']} videos\">{$video['publish']['owner']}</a>\n";
    echo "\t\t</li>\n";
    echo "\t\t<li>Video release date : {$video['publish']['date']}</li>\n";
    echo "\t</ul>\n";
    echo "\t<ul>\n";
    echo "\t\t<li>Video views : " . number_format($video['stats']['views']) . "</li>\n";
    echo "\t\t<li>Video likes : " . number_format($video['stats']['likes']) . "</li>\n";
    echo "\t\t<li>Video dislikes : " . number_format($video['stats']['dislikes']) . "</li>\n";
    echo "\t</ul>\n";
    echo "</div>\n";
}
echo "</div>";

?>
<div id="videosList">
    <ul id="videosMenu">
        <li data-video-panel="charts"><a data-special-link="@Charts"
                                         href="<?php echo uniqueX\General::siteLink('@Charts'); ?>"
                                         title="Top Charts">Charts</a></li>
        <li data-video-panel="search"><a data-special-link="@Search"
                                         href="<?php echo uniqueX\General::siteLink('@Search'); ?>"
                                         title="Search">Search</a></li>
        <li data-video-panel="favorites"><a data-special-link="@Favorites"
                                            href="<?php echo uniqueX\General::siteLink('@Favorites'); ?>"
                                            title="Favorite videos">Favorites</a>
        </li>
    </ul>
    <div id="videosArea" class="videosArea">
        <?php echo isset($config['advt-above-results'])
            ? "<div class='area-728'>{$config['advt-above-results']}</div>" : null; ?>
        <div class="videosPanel" data-video-panel="charts">
            <div id="chooseCountry">
                Top Videos - &nbsp;<span class="countryName">loading...</span>
                <span class="chooseCountry">Change country</span>
            </div>
            <div id="countriesList"></div>
            <div id="videoCharts"></div>
        </div>
        <div class="videosPanel" data-video-panel="search"></div>
        <div class="videosPanel" data-video-panel="favorites"></div>
        <div id="videos"></div>
        <div id="load">
            <div class="load-icon" id="loadIcon"></div>
            <span></span>
        </div>
        <?php echo isset($config['advt-below-results'])
            ? "<div class='area-728'>{$config['advt-below-results']}</div>" : null; ?>
        <div id="loadMore"><span>Load <?php
                $maxVideos = intval($config['max-videos']);
                echo $maxVideos > 1 && $maxVideos <= 50 ? $maxVideos : 10;
                ?> more videos</span></div>
    </div>
    <div class="sidePanel">
        <?php require_once __DIR__ . '/../layers/side_panel.php'; ?>
    </div>
    <div style="position:relative;clear:both"></div>
</div>
<div id="videoPlayer"></div>
<div id="shareArea">
    <div class="addthis_toolbox addthis_default_style"><a class="addthis_button_more">&nbsp;</a></div>
</div>
<div id="gotoTop"><span class="fa fa-arrow-up"></span></div>
<?php if (!(isset($config['help-tour']) && $config['help-tour'] == 'no')) { ?>
    <div id="helpTour" style="display:none">
        <span class="fa fa-close"></span>

        <div class="htHead">Try these special features</div>
        <img src="<?php echo uniqueX\General::siteLink('images/help-tour.png', 'template:default') ?>"
             width="761" height="338" title="Help Tour" alt="Help Tour">
    </div>
    <?php
}
require_once __DIR__ . '/../layers/site_footer.php';
?>
</body>
</html>
