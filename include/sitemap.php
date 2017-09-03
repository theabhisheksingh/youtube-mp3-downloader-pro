<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get sitemap value
$siteMap = uniqueX\Backend::getValue('siteMap');
$siteMap = is_string($siteMap) ? $siteMap : null;

// Optimize sitemap request
$siteMap = ltrim($siteMap, '-');
$siteMapLC = strtolower($siteMap);

// Create time value
$timeUTC = date('Y-m-d') . 'T' . date('H:i:s') . '+00:00';

// Prepare sitemap links (init)
$sitemapLinks = array();

// Sitemap index
$siteMapIndex = false;

// Start process on HTTP request demand
if ($siteMap == null) {
    header("HTTP/1.1 301 Moved Permanently");
    header('Location: ./sitemap_index.xml');
    exit;
} elseif ($siteMapLC == '_index') {
    $siteMapIndex = true;
    // Main sitemap
    $sitemapLinks[] = array('url' => 'main', 'pri' => 100, 'frq' => 'hourly');
    // Latest searches
    $sitemapLinks[] = array('url' => 'latest-searches', 'pri' => 90, 'frq' => 'hourly');
    // Get charts
    $charts = uniqueX\YouTube::charts();
    if (is_array($charts)) {
        // Get last modification
        $chartsMod = is_file(__DIR__ . '/../store/.charts') ? @filemtime(__DIR__ . '/../store/.charts') : false;
        $chartsMod = is_numeric($chartsMod) ? (date('Y-m-d') . 'T' . date('H:i:s') . '+00:00') : $timeUTC;
        // Get charts countries
        $chartCountries = array_keys($charts['list']);
        foreach ($charts['countries'] as $group) {
            foreach ($group as $code => $name) {
                if (in_array($code = strtolower($code), $chartCountries)) {
                    $sitemapLinks[] = array(
                        'url' => "chart-{$code}-" . strtolower(uniqueX\General::safeString($name)),
                        'mod' => $chartsMod,
                        'pri' => 40
                    );
                }
            }
        }
    }
    // Check recent videos file
    $recentFile = __DIR__ . '/../store/.recent';
    if (is_file($recentFile)) {
        $rvCount = ceil(intval(filesize($recentFile) / 11) / 50);
        $rvLength = strlen("{$rvCount}");
        for ($i = 1; $i <= $rvCount; $i++) {
            $sitemapLinks[] = array(
                'url' => "recent-videos-page-" . str_pad($i, $rvLength, '0', STR_PAD_LEFT),
                'pri' => 90,
                'frq' => 'hourly'
            );
        }
    }

} elseif ($siteMapLC == 'main') {
    $siteMapIndex = false;
    // Home page
    $sitemapLinks[] = array('url' => '', 'pri' => 100, 'frq' => 'hourly');
} elseif ($siteMapLC == 'latest-searches') {
    $siteMapIndex = false;
    // Get latest searches
    $latestSearches = uniqueX\General::latestSearches(null, true);
    // Check latest searches
    if (is_array($latestSearches)) {
        foreach ($latestSearches as $lS) {
            $sitemapLinks[] = strtolower(uniqueX\General::safeString($lS));
        }
    }
} elseif (preg_match('/^chart\-(?P<country>[a-z0-9]{2})/i', $siteMapLC, $matches)) {
    $siteMapIndex = false;
    // Get charts
    $charts = uniqueX\YouTube::charts();
    if (is_array($charts) && isset($charts['list'], $charts['list'][$matches['country']])) {
        // Get videos information
        $vInfo = uniqueX\YouTube::search($charts['list'][$matches['country']]);
        // Prepare links
        if (is_array($vInfo)) {
            foreach ($vInfo['videos'] as $video) {
                $sitemapLinks[] = array(
                    'url' => strtolower(uniqueX\General::safeString($video['title'])),
                    'frq' => 'monthly',
                    'pri' => 20
                );
            }
        }
    }
} elseif (preg_match('/^recent\-videos\-page\-(?P<page>[0-9]+)?/i', $siteMapLC, $matches)) {
    $siteMapIndex = false;
    // Get page number
    $page = intval($matches['page']);
    // Get video IDs
    list($videos, $recentFile) = array(null, __DIR__ . '/../store/.recent');
    if (is_file($recentFile)) {
        // File offset
        $offset = ($page - 1) * 50 * 11;
        // Recent file data
        if (is_string($recentData = file_get_contents($recentFile)) && strlen($recentData) >= $offset) {
            $videos = substr($recentData, $offset, 50 * 11);
            // Check video IDs
            if (strlen($videos) >= 11) {
                // Get videos information
                $vInfo = uniqueX\YouTube::search($videos);
                // Prepare links
                if (is_array($vInfo)) {
                    foreach ($vInfo['videos'] as $video) {
                        $sitemapLinks[] = array(
                            'url' => strtolower(uniqueX\General::safeString($video['title'])),
                            'frq' => 'monthly',
                            'pri' => 20
                        );
                    }
                }
            }
        }
    }
} else {
    $siteMapIndex = false;
    // Search videos
    $videos = uniqueX\YouTube::search(str_replace('-', ' ', $siteMap), 50);
    // Prepare links
    if (is_array($videos)) {
        foreach ($videos['videos'] as $video) {
            $sitemapLinks[] = array(
                'url' => strtolower(uniqueX\General::safeString($video['title'])),
                'frq' => 'monthly',
                'pri' => 20
            );
        }
    }
}

// Set header for XML
header('Content-Type: application/xml; charset=UTF-8');

// Print XML header
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n";
echo '<?xml-stylesheet type="text/xsl" href="' . uniqueX\General::siteLink('assets/others/sitemap.xsl') . '"?>';
echo "\n\n";

if ($siteMapIndex) {
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
} else {
    echo '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
        'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ' .
        'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 ' .
        'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" ' .
        'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
}
echo "\n";

// Print sitemap links
if (count($sitemapLinks) > 0) {
    foreach ($sitemapLinks as $sLink) {
        // Prepare sitemap row
        list($url, $xml, $mod, $frq, $pri) = array(null, null, null, null, null);
        if (is_array($sLink)) {
            if (isset($sLink['url'])) {
                $url = $sLink['url'];
            }
            $mod = isset($sLink['mod']) ? $sLink['mod'] : $timeUTC;
            $frq = isset($sLink['frq']) ? $sLink['frq'] : 'weekly';
            $pri = isset($sLink['pri']) ? ($sLink['pri'] / 100) : (rand(30, 60) / 100);
        } else {
            $url = $sLink;
            $mod = $timeUTC;
            $frq = 'weekly';
            $pri = rand(30, 60) / 100;
        }
        // Print sitemap row
        if (is_string($url)) {
            if ($siteMapIndex) {
                echo "    <sitemap>\n";
                echo "        <loc>" . uniqueX\General::siteLink("sitemap-{$url}.xml") . "</loc>\n";
                echo "        <lastmod>{$mod}</lastmod>\n";
                echo "    </sitemap>\n";
            } else {
                echo "    <url>\n";
                echo "        <loc>" . uniqueX\General::siteLink($url) . "</loc>\n";
                echo "        <lastmod>{$mod}</lastmod>\n";
                echo "        <changefreq>{$frq}</changefreq>\n";
                echo "        <priority>{$pri}</priority>\n";
                echo "    </url>\n";
            }
        }
    }
}

echo $siteMapIndex ? "</sitemapindex>" : "</urlset>";
echo "\n";
