<?php

// Init process
$_INIT_ = true;

// No timelimit for process
set_time_limit(0);

// Get runtime error (init)
$runtimeError = null;

// Set default timezone
date_default_timezone_set('America/Los_Angeles');

// Import configuration and libraries
require_once __DIR__ . '/../core/Cache.php';
require_once __DIR__ . '/../core/FFmpeg.php';
require_once __DIR__ . '/../core/YouTube.php';
require_once __DIR__ . '/../core/General.php';
require_once __DIR__ . '/../core/Backend.php';
require_once __DIR__ . '/../include/version.php';

// Get site config
uniqueX\Backend::setValue('config', uniqueX\Backend::getConfig());
$config = uniqueX\Backend::getValue('config');
$config = is_array($config) ? $config : array();

// Import plugins
require_once __DIR__ . '/../include/import.php';

// Get download query
$query = preg_match('/([a-f0-9\-]{71}\/.+)/i', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $matches)
    ? $matches[1] : null;

// Validate query
if (is_string($query) && preg_match('/^(?P<hash>[0-9a-f\-]{71})\/(?P<name>.+)/i', $query, $matches)) {
    // Get initial information
    $pattern = '/^(?P<tokenKEY>.{32})(?P<videoID>.{22})(?P<specialCMD>.{6})(?P<iTAG>.{4})$/i';
    if (preg_match($pattern, strtoupper(str_replace('-', '', $matches['hash'])), $hash)) {
        $name = urldecode($matches['name']);
        // Decode information
        list($videoID, $special, $iTag, $token) = array(
            pack('H*', $hash['videoID']),
            pack('H*', $hash['specialCMD']),
            hexdec($hash['iTAG']),
            $hash['tokenKEY']
        );
        // Validate token
        if (strtoupper(md5(md5($videoID . uniqueX\Backend::installKey()))) == $token) {
            // Get video links
            $videoLinks = uniqueX\Cache::getLinks($videoID);
            // Start process on demand
            if (is_array($videoLinks)) {
                if ($special == 'mp3') {
                    if (class_exists('uniqueX\FLV2MP3') &&
                        isset($config['mp3-converter']) && $config['mp3-converter'] == 'flv2mp3'
                    ) {
                        // Detect FLV link
                        $flvLink = null;
                        foreach ($videoLinks as $vLink) {
                            if ($vLink['extension'] == 'flv') {
                                $flvLink = $vLink['link'];
                                break;
                            }
                        }
                        // Check FLV link
                        if (is_string($flvLink)) {
                            // Convert FLV to MP3
                            if (is_string($convert = uniqueX\FLV2MP3::convert($flvLink, 'stdout:_auto_'))) {
                                $runtimeError = 'File not found. [ERR: 0x0A]';
                            }
                        } else {
                            $runtimeError = 'File not found. [ERR: 0x09]';
                        }
                    } else {
                        // Get input source
                        $inputSource = false;
                        $mp3Order = array(22, 141, 140, 18, 37, 38, 139);
                        foreach ($mp3Order as $_iTag) {
                            foreach ($videoLinks as $Dat) {
                                if ($Dat['itag'] == $_iTag) {
                                    $inputSource = $Dat['link'];
                                    break;
                                }
                            }
                            if (is_string($inputSource)) {
                                break;
                            }
                        }
                        // Detect input source
                        if (is_string($inputSource)) {
                            // Convert to MP3
                            $downloadName = uniqueX\General::downloadAs($name);
                            if (is_string($convert = uniqueX\FFmpeg::convert($inputSource, $downloadName, $iTag))) {
                                $runtimeError = 'File not found. [ERR: 0x08]';
                            }
                        } else {
                            $runtimeError = 'File not found. [ERR: 0x07]';
                        }
                    }
                } else {
                    // Detect iTag link on media links
                    $itagLink = null;
                    foreach ($videoLinks as $Dat) {
                        if ($Dat['itag'] == $iTag) {
                            $itagLink = $Dat['link'];
                            break;
                        }
                    }
                    // Check iTag link
                    if (is_string($itagLink)) {
                        // Direct download
                        if (isset($_GET['highSpeed']) && $_GET['highSpeed'] == 'yes') {
                            header("HTTP/1.1 301 Moved Permanently");
                            header("Location: {$itagLink}");
                            exit;
                        }
                        // Check flush support
                        $FlushSupport = function_exists('ob_flush') && function_exists('flush');
                        // Prepare UserAgent string
                        $userAgent = array(
                            'Mozilla/5.0', '(Windows NT 6.3; WOW64)',
                            'AppleWebKit/537.36', '(KHTML, like Gecko)',
                            'Chrome/40.0.2214.115', 'Safari/537.36');
                        // Prepare headers for HTTP request
                        $Headers = array(
                            'Referer' => $itagLink,
                            'Accept-Language' => 'en-US,en;q=0.8,te;q=0.6',
                            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                            'User-Agent' => implode(' ', $userAgent),
                        );
                        // Set download content range
                        if (isset($_SERVER['HTTP_RANGE'])) {
                            $Headers['Range'] = $_SERVER['HTTP_RANGE'];
                        }
                        $Headers['Connection'] = 'Close';
                        // Pack all headers
                        $HeadPack = '';
                        foreach ($Headers as $K => $V) {
                            $HeadPack .= "{$K}: {$V}\r\n";
                        }
                        // Create a stream
                        $options = array(
                            'http' => array(
                                'method' => "GET",
                                'header' => $HeadPack,
                                'timeout' => 10,
                                'max_redirects' => 10,
                                'ignore_errors' => true,
                                'follow_location' => 1,
                                'protocol_version' => '1.0',
                            )
                        );
                        $context = stream_context_create($options);
                        // Open HTTP connection
                        if (is_resource($http = fopen($itagLink, 'r', null, $context))) {
                            // Get headers
                            $meta = stream_get_meta_data($http);
                            $headers = array();
                            if (is_array($meta) && isset($meta['wrapper_data']) && is_array($meta['wrapper_data'])) {
                                $headers = $meta['wrapper_data'];
                                if (isset($headers['headers']) && is_array($headers['headers'])) {
                                    $headers = $headers['headers'];
                                }
                            }
                            // Get headers from CURL
                            if (count($headers) == 0 && function_exists('curl_version')) {
                                // Prepare CURL connection
                                $curl = curl_init();
                                curl_setopt($curl, CURLOPT_URL, $itagLink);
                                curl_setopt($curl, CURLOPT_HEADER, true);
                                curl_setopt($curl, CURLOPT_NOBODY, true);
                                if (isset($_SERVER['HTTP_RANGE'])) {
                                    $range = str_replace('bytes=', '', $_SERVER['HTTP_RANGE']);
                                    curl_setopt($curl, CURLOPT_RANGE, $range);
                                }
                                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($curl, CURLOPT_HTTPHEADER, $Headers);
                                @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                                // Get headers
                                $headers = explode("\n", str_replace("\r\n", "\n", curl_exec($curl)));
                            }
                            // Print headers
                            if (count($headers) > 0) {
                                // Devide response headers
                                $AllHeads = explode("\r\nHTTP/", implode("\r\n", $headers));
                                $LastHead = explode("\r\n", "HTTP/" . end($AllHeads));
                                // Eliminate some headers
                                $Eliminate = array('set-cookie', 'content-type', 'content-disposition', 'connection');
                                // Print headers
                                foreach ($LastHead as $I => $H) {
                                    if ($I == 0) {
                                        header($H);
                                    } elseif (preg_match('/^([^\:]+)/i', $H, $M)) {
                                        $K = strtolower($M[1]);
                                        if (!in_array($K, $Eliminate)) {
                                            header($H);
                                        }
                                    }
                                }
                                // Prepare download name
                                $downloadName = str_replace('"', '', uniqueX\General::downloadAs($name));
                                // Set headers
                                header('Content-Type: application/octet-stream');
                                header('Content-Disposition: attachment; filename="' . $downloadName . '"');
                                header('Connection: Close');
                            }
                            // Enable realtime data transfer
                            if (function_exists('apache_setenv')) {
                                apache_setenv('no-gzip', 1);
                            }
                            if (function_exists('ini_set')) {
                                ini_set('zlib.output_compression', false);
                                ini_set('implicit_flush', true);
                            }
                            if (function_exists('ob_implicit_flush') && function_exists('ob_end_flush')) {
                                ob_implicit_flush(true);
                                ob_end_flush();
                            }
                            // Transfer content
                            while (!feof($http)) {
                                echo fread($http, 1024 * 8);
                                if ($FlushSupport) {
                                    @ob_flush();
                                    @flush();
                                }
                            }
                            // Close HTTP connection
                            fclose($http);
                        } else {
                            $runtimeError = 'File not found. [ERR: 0x06]';
                        }
                    } else {
                        $runtimeError = 'File not found. [ERR: 0x05]';
                    }
                }
            } else {
                $runtimeError = 'File not found. [ERR: 0x04]';
            }
        } else {
            $runtimeError = 'File not found. [ERR: 0x03]';
        }
    } else {
        $runtimeError = 'File not found. [ERR: 0x02]';
    }
} else {
    $runtimeError = 'File not found. [ERR: 0x01]';
}

// Print runtime error
if (is_string($runtimeError)) {
    header('HTTP/1.0 404 Not Found');
    echo $runtimeError;
}