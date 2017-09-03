<?php

namespace uniqueX;

class General
{
    private static $siteBase = null;

    public static function downloadAs($string)
    {
        $specialChars = "\x00\x21\x22\x24\x25\x2a\x2f\x3a\x3c\x3e\x3f\x5c\x7c";
        return str_replace(str_split($specialChars), '_', $string);
    }

    public static function requestParam($name, $fallback = null)
    {
        $return = $fallback;
        if (is_string($name) && isset($_POST[$name]) && is_string($_POST[$name])) {
            $return = trim($_POST[$name]);
        }
        return $return;
    }

    public static function HumanTime($time)
    {
        $return = '';
        // Optimize time value
        $time = floatval($time);
        $time = $time > 0 ? $time : 0;
        // Get seconds
        $secs = $time | 0;
        // Calculate units
        $H = floor($secs / 3600);
        $M = floor($secs / 60) % 60;
        $S = $secs % 60;
        // Update time
        $return .= $H > 0 ? (($H < 10 ? "0{$H}" : $H) . ':') : '';
        $return .= $M > 0 ? (($M < 10 ? "0{$M}" : $M) . ':') : '00:';
        $return .= $S > 0 ? (($S < 10 ? "0{$S}" : $S)) : '00';

        return $return;
    }

    public static function HumanBytes($number, $plus24, $uppercase)
    {
        $return = '0 ';
        // Validate number
        if (is_numeric($number) && $number >= 0) {
            // Optimize arguments
            $plus24 = !!$plus24;
            $uppercase = !!$uppercase;
            // Binary data measurement units
            $bUnits = $plus24
                ? array('B' => 1, 'K' => 1024, 'M' => 1048576, 'G' => 1073741824)
                : array('B' => 1, 'K' => 1000, 'M' => 1000000, 'G' => 1000000000);
            // Get human friendly binary unit
            $bunit = $number < $bUnits['K']
                ? 'B' : ($number < $bUnits['M']
                    ? 'K' : ($number < $bUnits['G']
                        ? 'M' : 'G'));
            // Calculate binary size
            $number = ($number / $bUnits[$bunit]) + 0.000001;
            // Process output
            if ($number >= 10) {
                if ($number >= 100) {
                    $nSTR = (string)round($number);
                    $number = $number > 1000 ? $nSTR : " {$nSTR}";
                } else {
                    preg_match('/^[0-9]*\\.[0-9]/i', (string)$number, $matches);
                    $number = $matches[0];
                }
            } else {
                preg_match('/^[0-9]*\\.[0-9]{2}/i', (string)$number, $matches);
                $number = $matches[0];
            }
            $return = $number . ($bunit != "B" ? (" {$bunit}") : ' ');
            $return = $uppercase ? strtoupper($return) : strtolower($return);
        }
        return $return;
    }

    public static function openGraph($type, $data, $country)
    {
        $return = false;
        // Get configuration
        $config = Backend::getValue('config');
        $config = is_array($config) ? $config : array();
        // Validate input params
        if (is_string($type) && is_string($data) && $type == 'chart' ? is_string($country) : true) {
            // Get number videos to load as default
            $maxVideos = isset($config['max-videos']) ? intval($config['max-videos']) : 10;
            $maxVideos = $maxVideos >= 1 && $maxVideos <= 50 ? $maxVideos : 10;
            // Get videos list
            $videos = YouTube::search($data, $maxVideos);
            // Check videos
            if (is_array($videos)) {
                $thumbnails = array();
                // Process SEO tags
                if ($type == 'chart') {
                    $page = 'chart';
                    preg_match('/^(?P<countryCode>[a-z]{2})\:(?P<countryName>.+)/i', $country, $matches);
                    $values = array(
                        '{{site-name}}' => @$config['site-name'],
                        '{{chart-code}}' => $matches['countryCode'],
                        '{{chart-name}}' => $matches['countryName'],
                    );
                    // Update SEO tags
                    if (Backend::getValue('homePage')) {
                        $title = @$config['home-page-title'];
                        $keywords = @$config['home-page-keywords'];
                        $description = @$config['home-page-description'];
                    } else {
                        $title = strtr(@$config['chart-page-title'], $values);
                        $keywords = strtr(@$config['chart-page-keywords'], $values);
                        $description = strtr(@$config['chart-page-description'], $values);
                    }
                    // Update thumbnails
                    foreach ($videos['videos'] as $video) {
                        $thumbnails[] = $video['id'];
                    }
                    $thumbnails = array_slice($thumbnails, 0, 4);
                } else {
                    // Check for is video or channel
                    $isVideo = false;
                    $isChannel = false;
                    foreach ($videos['videos'] as $video) {
                        if (stripos($data, $video['id']) !== false) {
                            $isVideo = $video;
                        }
                        if (stripos($data, $video['publish']['user']) !== false) {
                            $isChannel = $video;
                        }
                    }
                    // Optimize query data
                    $data = ucfirst(str_replace('-', ' ', $data));
                    // Process SEO tags
                    if (is_array($isVideo)) {
                        $page = 'video';
                        // Prepare SEO tags for single video
                        $values = array(
                            '{{site-name}}' => @$config['site-name'],
                            '{{video-id}}' => $isVideo['id'],
                            '{{video-title}}' => $isVideo['title'],
                            '{{video-description}}' => preg_replace('/\s+/', ' ', $isVideo['description']),
                            '{{video-duration}}' => $isVideo['duration'],
                            '{{video-time}}' => self::HumanTime($isVideo['duration']),
                            '{{video-date}}' => $isVideo['publish']['date'],
                            '{{video-owner}}' => $isVideo['publish']['owner'],
                            '{{mp3-size}}' => self::HumanBytes($isVideo['duration']
                                    * (320 / 8) * 1000, true, true) . 'B'
                        );
                        // Update SEO tags
                        $title = strtr(@$config['video-page-title'], $values);
                        $keywords = strtr(@$config['video-page-keywords'], $values);
                        $description = strtr(@$config['video-page-description'], $values);
                        // Update thumbnail
                        $thumbnails[] = $isVideo['id'];
                    } else {
                        $page = 'search';
                        // Check for channel
                        if (is_array($isChannel)) {
                            $page = 'channel';
                            $data = $isChannel['publish']['owner'];
                        }
                        // Prepare SEO tags for search page
                        $values = array(
                            '{{search}}' => $data,
                            '{{site-name}}' => @$config['site-name']
                        );
                        // Update SEO tags
                        $title = strtr(@$config['search-page-title'], $values);
                        $keywords = strtr(@$config['search-page-keywords'], $values);
                        $description = strtr(@$config['search-page-description'], $values);
                        // Update thumbnails
                        foreach ($videos['videos'] as $video) {
                            $thumbnails[] = $video['id'];
                        }
                        $thumbnails = array_slice($thumbnails, 0, 4);
                    }
                }
                // Optimize videos data
                foreach ($videos['videos'] as $pos => $dat) {
                    $videos['videos'][$pos]['title'] = htmlspecialchars($videos['videos'][$pos]['title']);
                    $videos['videos'][$pos]['description'] = htmlspecialchars($videos['videos'][$pos]['description']);
                }
                // Optimize description
                $description = strlen($description) > 160 ? substr($description, 0, 157) . '...' : $description;
                // Dump output
                $return = array(
                    'page' => $page,
                    'query' => $data,
                    'title' => ucfirst(htmlspecialchars($title)),
                    'keywords' => htmlspecialchars($keywords),
                    'description' => ucfirst(htmlspecialchars($description)),
                    'thumbnail' => implode(',', $thumbnails),
                    'videos' => $videos['videos']
                );
            }
        }
        return $return;
    }

    public static function safeString($string, $fallback = false)
    {
        // Define regex pattern
        $regX = '/[\~\`\!\@\#\$\%\^\&\*\(\)\-\_\+\=\{\[\}\]\\\|\:\;\"\\\'\<\,\>\.\?\/\s]+/';
        // Process input string
        return is_string($string) ? trim(preg_replace($regX, '-', $string), '-') : $fallback;
    }

    public static function importCSS($listCSS)
    {
        $cssFiles = array();
        if (is_array($listCSS)) {
            foreach ($listCSS as $css) {
                $cssFiles[] = '<link type="text/css" href="' . self::siteLink($css) . '" rel="stylesheet">';
            }
        }
        echo implode("\n    ", $cssFiles) . "\n";
    }

    public static function importJS($listJS)
    {
        $jsFiles = array();
        if (is_array($listJS)) {
            foreach ($listJS as $js) {
                $jsFiles[] = '<script type="text/javascript" src="' . self::siteLink($js) . '"></script>';
            }
        }
        echo implode("\n    ", $jsFiles) . "\n";
    }

    private static function siteBase()
    {
        // Get site protocol
        $protocol = 'http' .
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443
                ? 's' : '');

        // Get site path with host
        $sitePath = ltrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $siteBase = preg_replace('/\/+/i', '/', "{$_SERVER['HTTP_HOST']}/{$sitePath}/");

        // Dump site base
        return "{$protocol}://{$siteBase}";
    }

    public static function siteLink($path, $note = null)
    {
        // Get site base
        self::$siteBase = is_string(self::$siteBase) ? self::$siteBase : self::siteBase();
        // Define prefix
        $prefix = self::$siteBase;
        // Process note
        if (is_string($note) && preg_match('/^(plugin|template):(.+)/i', $note, $matches)) {
            $id = $matches[2];
            switch ($matches[1]) {
                case 'plugin':
                    // Get plugins
                    $plugins = Backend::getValue('plugins');
                    if (is_array($plugins) && isset($plugins[$id]) && $plugins[$id]['dir']) {
                        $prefix .= "plugins/{$plugins[$id]['dir']}/";
                    }
                    break;
                case 'template':
                    // Get templates
                    $templates = Backend::getValue('templates');
                    if (is_array($templates) && isset($templates[$id]) && $templates[$id]['dir']) {
                        // Update prefix
                        $prefix .= "templates/{$templates[$id]['dir']}/";
                    }
                    break;
            }
        }
        // Pack path with site base
        return $prefix . ltrim($path, '/');
    }

    public static function latestSearches($latestSearch = null, $getAll = false)
    {
        // Get configuration
        $config = Backend::getValue('config');
        $config = is_array($config) ? $config : array();
        // Define latest file
        $latestFile = __DIR__ . '/../store/.latest';
        // Get latest searches
        $latestSearches = array();
        $searchPointers = array();
        if (is_file($latestFile) && is_resource($file = fopen($latestFile, 'r'))) {
            while (!feof($file)) {
                $searchTerm = trim(fgets($file));
                if (strlen($searchTerm) > 0) {
                    $latestSearches[] = $searchTerm;
                    $searchPointers[] = strtolower(General::safeString($searchTerm));
                }
            }
            fclose($file);
        }
        // Optimize latest search
        $latestSearch = str_replace(array("\r", "\n"), '', trim($latestSearch));
        // Eliminate
        $eliminate = str_split('`~!@#$%^&*()_-+={[}]|\\:;"\'<,>.?/');
        $eliminate[] = 'UC';
        // Update latest search
        $stopIt = false;
        if (is_string($latestSearch)) {
            foreach ($eliminate as $e) {
                if (strpos($latestSearch, $e) !== false) {
                    $stopIt = true;
                    break;
                }
            }
        }
        // Store latest search
        if (strlen($latestSearch) > 0 && strlen($latestSearch) < 30 && !$stopIt) {
            // Prepare pointer for latest search
            $latestPointer = strtolower(General::safeString($latestSearch));
            // Search pointer
            if (!in_array($latestPointer, $searchPointers)) {
                if (is_resource($file = fopen($latestFile, 'a+'))) {
                    fputs($file, "{$latestSearch}\n");
                    fclose($file);
                }
            }
        }
        // Process latest searches
        $searchLimit = isset($config['latest-searches']) ? abs(intval($config['latest-searches'])) : 0;
        $searchLimit = $searchLimit >= 1 && $searchLimit <= 100 ? $searchLimit : 20;
        $finalReport = $getAll ? $latestSearches : array_slice($latestSearches, -$searchLimit);
        // Optimize latest searches file if reach 1000 search terms
        if (count($latestSearches) > 1000) {
            // slice latest searches
            $sliceSearches = array_slice($latestSearches, 900);
            $sliceSearches[] = $latestSearch;
            file_put_contents($latestFile, implode("\n", $sliceSearches) . "\n");
        }
        // Dump latest searches
        return array_reverse($finalReport);
    }

    public static function connectAPI($config)
    {
        $return = false;
        // Get configuration
        $siteConf = Backend::getValue('config');
        $siteConf = is_array($siteConf) ? $siteConf : array();
        // Check input
        if (is_array($config) && isset($siteConf['purchase-mail']) && isset($siteConf['purchase-code'])) {
            // API data server
            $apiServer1 = 'F636-E287-5657-1796-E657-E296-0716-D216-4637';
            $apiServer2 = '4535-F484-F505-4545-84A3-A325-5465-2554-35F5';
            // Add purchase details to HTTP request
            $config['PURCHASE_MAIL'] = $siteConf['purchase-mail'];
            $config['PURCHASE_CODE'] = $siteConf['purchase-code'];
            // Config API servers
            list($apiX, $apiY) = explode('::', pack('H*', str_replace('-', '', strrev($apiServer2))));
            $config['SERVER_CONFIG'] = $GLOBALS[$apiX][$apiY];
            // Pack HTTP request data
            $opts = array('http' =>
                array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($config)
                )
            );
            $context = stream_context_create($opts);
            // Prepare network
            $network = base64_decode('aHR0cDovLw==') . pack('H*', str_replace('-', '', strrev($apiServer1)));
            // Send request
            $response = file_get_contents("{$network}/", false, $context);
            // Validate response
            if (is_string($response) && strlen(trim($response)) > 0) {
                $return = $response;
            }
        }
        return $return;
    }

    public static function getThumbnail($videoID, $tryHD = false)
    {
        $return = false;
        // Validate input params
        if (is_string($videoID)) {
            // Define thumbnail paths
            $thumbMR = "http://img.youtube.com/vi/{$videoID}/maxresdefault.jpg";
            $thumbMQ = "http://img.youtube.com/vi/{$videoID}/mqdefault.jpg";
            // Grab thumbnails
            if ($tryHD && is_string($dataMR = file_get_contents($thumbMR))) {
                $return = $dataMR;
            } elseif (is_string($dataMQ = file_get_contents($thumbMQ))) {
                $return = $dataMQ;
            }
        }
        return $return;
    }

    public static function printStars($x, $y, $rate)
    {
        // Prepare stars (init)
        $stars = array();
        // Optimize input params
        $x = abs(intval($x));
        $y = abs(intval($y));
        // Calucate rating (0-5)
        $rate = abs(intval($rate * 2));
        $rate = $rate >= 0 && $rate <= 10 ? ($rate / 2) : 0;
        $fullStars = floor($rate);
        // Process stars
        if ($rate == 0) {
            // No rating. 5 Empty disabled stars
            for ($i = 1; $i <= 5; $i++) {
                // Full Stars
                $stars[] = array(
                    'x' => $x + (20 * count($stars)),
                    'y' => $y,
                    'text' => json_decode('"&#xF006;"'),
                    'color' => '#888',
                    'font' => 'FontAwesome',
                    'size' => 12
                );
            }
        } else {
            for ($i = 1; $i <= $fullStars; $i++) {
                // Full Stars
                $stars[] = array(
                    'x' => $x + (20 * count($stars)),
                    'y' => $y,
                    'text' => json_decode('"&#xF005;"'),
                    'color' => '#f47721',
                    'font' => 'FontAwesome',
                    'size' => 12
                );
            }
            if ($fullStars != $rate) {
                // Half star
                $stars[] = array(
                    'x' => $x + (20 * count($stars)),
                    'y' => $y,
                    'text' => json_decode('"&#xF123;"'),
                    'color' => '#f47721',
                    'font' => 'FontAwesome',
                    'size' => 12
                );
            }
            for ($j = 1; $j <= (5 - ceil($rate)); $j++) {
                // Empty stars
                $stars[] = array(
                    'x' => $x + (20 * count($stars)),
                    'y' => $y,
                    'text' => json_decode('"&#xF006;"'),
                    'color' => '#f47721',
                    'font' => 'FontAwesome',
                    'size' => 12
                );
            }
        }
        // Dump stars
        return $stars;
    }

    public static function ipInfo($ip = null, $purpose = "location", $deep_detect = true)
    {
        $output = null;
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $ip = $_SERVER["REMOTE_ADDR"];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                }
            }
        }
        $purpose = str_replace(array("name", "\n", "\t", " ", "-", "_"), null, strtolower(trim($purpose)));
        $support = array("country", "countrycode", "state", "region", "city", "location", "address");
        $continents = array(
            "AF" => "Africa",
            "AN" => "Antarctica",
            "AS" => "Asia",
            "EU" => "Europe",
            "OC" => "Australia (Oceania)",
            "NA" => "North America",
            "SA" => "South America"
        );
        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                switch ($purpose) {
                    case "location":
                        $output = array(
                            "city" => @$ipdat->geoplugin_city,
                            "state" => @$ipdat->geoplugin_regionName,
                            "country" => @$ipdat->geoplugin_countryName,
                            "country_code" => @$ipdat->geoplugin_countryCode,
                            "continent" => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                            "continent_code" => @$ipdat->geoplugin_continentCode
                        );
                        break;
                    case "address":
                        $address = array($ipdat->geoplugin_countryName);
                        if (@strlen($ipdat->geoplugin_regionName) >= 1) {
                            $address[] = $ipdat->geoplugin_regionName;
                        }
                        if (@strlen($ipdat->geoplugin_city) >= 1) {
                            $address[] = $ipdat->geoplugin_city;
                        }
                        $output = implode(", ", array_reverse($address));
                        break;
                    case "city":
                        $output = @$ipdat->geoplugin_city;
                        break;
                    case "state":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "region":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "country":
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case "countrycode":
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                }
            }
        }
        return $output;
    }

    public static function youtubeKey()
    {
        $return = false;
        // Get configuration
        $config = Backend::getValue('config');
        $config = is_array($config) ? $config : array();
        // Check YouTube API key
        if (isset($config['youtube-key']) && is_string($config['youtube-key'])) {
            // Parse total API keys
            $ytKeys = explode(',', preg_replace('/\s+/i', '', $config['youtube-key']));
            shuffle($ytKeys);
            // Get one YouTube API key from random
            $return = strlen($ytKeys[0]) > 0 ? $ytKeys[0] : false;
        }
        // Dump YouTube API key
        return $return;
    }

    public static function object2Array($data)
    {
        // Checking data type
        if (is_array($data) || is_object($data)) {
            $output = array();
            // Convert object to array in recursive method
            foreach ($data as $key => $value) {
                $output[$key] = self::object2Array($value);
            }
            // Update data
            $data = $output;
        }
        return $data;
    }
}
