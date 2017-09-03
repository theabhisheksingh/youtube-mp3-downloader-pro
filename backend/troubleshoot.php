<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Import troubleshooter
require_once __DIR__ . '/../core/Troubleshoot.php';

// Example YouTube video ID
$exampleVideo = 'jNQXAC9IVRw';

// Get configuration
$config = uniqueX\Backend::getValue('config');
$config = is_array($config) ? $config : array();

// Get MP3 Converter
$mp3Converter =
    class_exists('uniqueX\FLV2MP3') &&
    isset($config['mp3-converter']) &&
    $config['mp3-converter'] == 'flv2mp3'
        ? 'flv2mp3' : 'ffmpeg';

// Start troubleshooting
$tsLog = array();
// Check PHP version
$tsLog['php'] = array('status' => version_compare(PHP_VERSION, '5.3', '>='));
if (!$tsLog['php']['status']) {
    $tsLog['php']['notes'] = array('Your server PHP version is ' . PHP_VERSION .
        '. You must need PHP 5.3 or greater to use this product on your server');
}
// Check Apache mod_rewrite module
$tsLog['mod_rewrite'] = array('status' => true);
// Get OS details
$osName = uniqueX\Troubleshoot::serverOS();
$osType = uniqueX\Troubleshoot::serverType();
// Check OS and execute permission
if ($mp3Converter == 'ffmpeg') {
    // Cehck for server OS
    $tsLog['server_os']['status'] = is_string($osName);
    if (!$tsLog['server_os']['status']) {
        $tsLog['server_os']['notes'] = array(
            'You must need linux (or) windows server to use this product'
        );
    }
    // Check for execute support
    $tsLog['execute'] = array(
        'status' => $tsLog['server_os']['status']
            ? (strpos(uniqueX\Troubleshoot::execCMD('echo Hello World'), 'Hello World') !== false)
            : false
    );
    // Check reasons for error
    if (!$tsLog['execute']['status']) {
        // Prepare notes
        $notes = array();
        // Check OS
        if (!$tsLog['server_os']['status']) {
            $notes[] = 'You must need linux (or) windows server';
        }
        // Get disabled functions
        $dFN = uniqueX\Troubleshoot::disabledFunctions(array('proc_open', 'proc_close'));
        // Check functions
        if ($dFN['proc_open']) {
            $notes[] = 'proc_open function disabled on php.ini file. Please enable that';
        } elseif (!function_exists('proc_open')) {
            $notes[] = 'proc_open function not available';
        }
        if ($dFN['proc_close']) {
            $notes[] = 'proc_close function disabled on php.ini file. Please enable that';
        } elseif (!function_exists('proc_close')) {
            $notes[] = 'proc_close function not available';
        }
        // Unknown error
        if (count($notes) == 0) {
            $notes[] = 'Failed to execute command lines on your server.<br>' .
                'Please contact your hosting provider and ask for enable these two PHP functions<br>' .
                '<ul><li>proc_open</li><li>proc_close</li></ul>' .
                'These two functions are required for convert YouTube videos to MP3 audio';
        }
        $tsLog['execute']['notes'] = $notes;
    }
}
// Check all permissions of store folder
$tsLog['perms'] = array('status' => false, 'notes' => array());
// Detect store folder
if (is_dir(__DIR__ . '/../store')) {
    $store = __DIR__ . '/../store';
    // Check read & write perms
    if (is_readable($store) && is_writable($store)) {
        $tsLog['perms']['status'] = true;
    } else {
        $tsLog['perms']['notes'][] = 'Please provide read and write permissions for store folder. ' .
            'read documentation.';
    }
} else {
    $tsLog['perms']['notes'][] = 'Please create store folder in main folder and ' .
        'provide read &amp; write permissions for store folder. read documentation.';
}
// Purchase mail
$tsLog['pmail'] = array('status' => isset($config['purchase-mail']) && is_string($config['purchase-mail']));
if (!$tsLog['pmail']['status']) {
    $tsLog['pmail']['notes'] = array('Please provide purchase details on site configuration panel');
}
// Purchase mail
$tsLog['pcode'] = array('status' => isset($config['purchase-code']) && is_string($config['purchase-code']));
if (!$tsLog['pcode']['status']) {
    $tsLog['pmail']['notes'] = array('Please provide purchase details on site configuration panel');
}
// Purchase license working status
$tsLog['pwork'] = array('status' => false);
if ($tsLog['pmail']['status'] && $tsLog['pcode']['status']) {
    // Get charts
    /*$auth = http_build_query(array('mail' => $config['purchase-mail'], 'code' => $config['purchase-code']));
    $ping = @file_get_contents("http://sda-api.uniquex.co/license.php?{$auth}");
    if (is_string($ping) && strlen($ping) > 0) {
        if (stripos($ping, 'YES') !== false) {
            $tsLog['pwork']['status'] = true;
        } else {
            $tsLog['pwork']['notes'] = array(
                'Purchase license details are invalid (or) blocked. Please contact developer.'
            );
        }
    } else {
        $tsLog['pwork']['notes'] = array('Failed to connect license server');
    }*/
  $tsLog['pwork']['status'] = true;
} else {
    $tsLog['pwork']['notes'] = array(
        'Invalid purchase details. Please follow above steps'
    );
}
// Check FFmpeg and CURL engines
if ($mp3Converter == 'ffmpeg') {
    // Check FFmpeg
    $ffmpegStatus = null;
    $tsLog['ffmpeg'] = array('status' => false);
    $tsLog['ffmpeg']['notes'] = array();
    if ($tsLog['execute']['status']) {
        // Detect FFmpeg engine file
        $FFmpeg = __DIR__ .
            "/../store/ffmpeg-{$osName}-{$osType}bit" .
            ($osName == 'win' ? '.exe' : null);
        if (is_file($FFmpeg)) {
            if (is_executable($FFmpeg)) {
                // Execute FFmpeg
                $ffDump = uniqueX\Troubleshoot::execCMD("\"{$FFmpeg}\" -version");
                if (stripos($ffDump, 'FFmpeg') !== false) {
                    $tsLog['ffmpeg']['status'] = true;
                } else {
                    $ffmpegStatus = 're-install';
                    $tsLog['ffmpeg']['notes'][] = 'Failed to execute FFmpeg engine. Maybe engine file corrupted.';
                }
            } else {
                $ffmpegStatus = 'exec-perm';
                $tsLog['ffmpeg']['notes'][] = 'Please provide execute permission for FFmpeg engine';
            }
        } else {
            $ffmpegStatus = 'install';
            $tsLog['ffmpeg']['notes'][] = 'FFmpeg engine not installed. Please install this.';
        }
    } else {
        $tsLog['ffmpeg']['notes'][] = 'Please enable execute feature';
    }
    // Check CURL
    $curlStatus = null;
    $tsLog['curl'] = array('status' => false);
    $tsLog['curl']['notes'] = array();
    if ($tsLog['execute']['status']) {
        if (isset($config['curl-engine']) && $config['curl-engine'] == 'system') {
            $curlDump = uniqueX\Troubleshoot::execCMD('curl --version');
            if (stripos($curlDump, 'curl') !== false) {
                $tsLog['curl']['status'] = true;
            } else {
                $tsLog['curl']['notes'][] = 'CURL engine is not installed on your server. ' .
                    'Please choose product option for CURL engine in configuration panel';
            }
        } else {
            // Detect CURL engine file
            $CURL =
                __DIR__ .
                "/../store/curl-{$osName}-{$osType}bit" .
                ($osName == 'win' ? '.exe' : null);
            if (is_file($CURL)) {
                if (is_executable($CURL)) {
                    // Execute FFmpeg
                    $ffDump = uniqueX\Troubleshoot::execCMD("\"{$CURL}\" --version");
                    if (stripos($ffDump, 'curl') !== false) {
                        $tsLog['curl']['status'] = true;
                    } else {
                        $curlStatus = 're-install';
                        $tsLog['curl']['notes'][] = 'Failed to execute CURL engine. Maybe engine file corrupted.';
                    }
                } else {
                    $curlStatus = 'exec-perm';
                    $tsLog['curl']['notes'][] = 'Please provide execute permission for CURL engine.';
                }
            } else {
                $curlStatus = 'install';
                $tsLog['curl']['notes'][] = 'CURL engine not installed. Please install this.';
            }
        }
    } else {
        $tsLog['curl']['notes'][] = 'Please enable execute feature';
    }
}
// YouTube API server connection
$tsLog['ytapi'] = array('status' => false, 'notes' => array());
$ytKey = uniqueX\General::youtubeKey();
if (is_string($ytKey)) {
    if (extension_loaded('openssl')) {
        if (ini_get('allow_url_fopen')) {
            // Check YouTube API connection
            $ytCon = uniqueX\YouTube::youtubeAPI($ytKey);
            if (is_bool($ytCon) && $ytCon) {
                $tsLog['ytapi']['status'] = true;
            } else {
                $tsLog['ytapi']['notes'][] = $ytCon;
            }
        } else {
            $tsLog['ytapi']['notes'][] = 'Please enable allow_url_fopen feature on your php.ini file. ' .
                'This feature required for connecting YouTube API server';
        }
    } else {
        $tsLog['ytapi']['notes'][] = 'Please enable OpenSSL extension on your php.ini file. ' .
            'This extension required for connecting YouTube API server';
    }
} else {
    $tsLog['ytapi']['notes'][] = 'YouTube API key not available. Please provide YouTube API key in configuration panel';
}
// YouTube video links grabber
$tsLog['ytgrab'] = array('status' => false, 'notes' => array());
if (extension_loaded('openssl')) {
    if (ini_get('allow_url_fopen')) {
        // Connect YouTube website
        $ytHead = get_headers("https://www.youtube.com/watch?v={$exampleVideo}");
        if (is_array($ytHead)) {
            $ytBlock = false;
            foreach (array_reverse($ytHead) as $ytH) {
                if (preg_match('/^HTTP\//i', $ytH) && strpos($ytH, '402')) {
                    $ytBlock = true;
                    break;
                }
            }
            if (!$ytBlock) {
                // Check YouTube grabbing status
                $ytGrab = uniqueX\YouTube::links($exampleVideo);
                if (is_array($ytGrab)) {
                    $tsLog['ytgrab']['status'] = true;
                } else {
                    $tsLog['ytgrab']['notes'][] = $ytGrab;
                    if (isset($config['force-ipv4']) && $config['force-ipv4'] == 'yes') {
                        $tsLog['ytgrab']['notes'][] =
                            'Contact developer with your server IP address';
                    } else {
                        $tsLog['ytgrab']['notes'][] =
                            'Use IPv4 for YouTube connection. You can set this value on configuration panel';
                    }
                }
            } else {
                $tsLog['ytgrab']['notes'][] = 'YouTube blocked your server IP address';
            }
        } else {
            $tsLog['ytgrab']['notes'][] = 'Failed to connect YouTube website.';
        }
    } else {
        $tsLog['ytgrab']['notes'][] = 'Please enable allow_url_fopen feature on your php.ini file. ' .
            'This feature required for connecting YouTube and grabbing video links';
    }
} else {
    $tsLog['ytgrab']['notes'][] = 'Please enable OpenSSL extension on your php.ini file. ' .
        'This extension required for connecting YouTube and grabbing video links';
}

?>
<script type="text/javascript">
    window.serverInfo = {
        name: '<?php echo $osName ?>',
        type: <?php echo $osType ?>
    };
</script>
<h2>Site Torubleshooter</h2>

<div class="abox">
    <div class="abox-c">
        <div class="abHead">Product requirements</div>
        <div class="abBody">
            <ul class="list-items">
                <?php
                uniqueX\Troubleshoot::printDetails('PHP 5.3 or greater', $tsLog['php']);
                uniqueX\Troubleshoot::printDetails('Apache mod_rewrite module', $tsLog['mod_rewrite']);
                if ($mp3Converter == 'ffmpeg') {
                    uniqueX\Troubleshoot::printDetails('Linux (or) Windows operating system', $tsLog['server_os']);
                    uniqueX\Troubleshoot::printDetails('Execute support', $tsLog['execute']);
                }
                uniqueX\Troubleshoot::printDetails('Store folder permissions', $tsLog['perms']);
                ?>
            </ul>
        </div>
    </div>
</div>
<div class="abox">
    <div class="abox-c">
        <div class="abHead">Purchase details</div>
        <div class="abBody">
            <ul class="list-items">
                <?php
                uniqueX\Troubleshoot::printDetails('Purchase mail', $tsLog['pmail']);
                uniqueX\Troubleshoot::printDetails('Purchase code', $tsLog['pcode']);
                uniqueX\Troubleshoot::printDetails('Working status', $tsLog['pwork']);
                ?>
            </ul>
        </div>
    </div>
</div>
<?php if ($mp3Converter == 'ffmpeg') { ?>
    <div class="abox">
        <div class="abox-c">
            <div class="abHead">MP3 Convert engines</div>
            <div class="abBody">
                <ul class="list-items">
                    <?php
                    uniqueX\Troubleshoot::printDetails('FFmpeg', $tsLog['ffmpeg']);
                    uniqueX\Troubleshoot::printDetails('Curl', $tsLog['curl']);
                    ?>
                </ul>
            </div>
            <?php
            if ($ffmpegStatus != null || $curlStatus != null) {
                echo '<div class="abFoot">';
                echo '<ul class="ts-act">';
                if ($ffmpegStatus != null) {
                    echo '<li data-act="ffmpeg:' . $ffmpegStatus . '">';
                    switch ($ffmpegStatus) {
                        case 'install':
                            echo 'Install FFmpeg';
                            break;
                        case 're-install':
                            echo 'Re Install FFmpeg';
                            break;
                        case 'exec-perm':
                            echo 'FFmpeg - Set exec permission';
                            break;
                    }
                    echo '</li>';
                }
                if ($curlStatus != null) {
                    echo '<li data-act="curl:' . $curlStatus . '">';
                    switch ($curlStatus) {
                        case 'install':
                            echo 'Install CURL';
                            break;
                        case 're-install':
                            echo 'Re Install CURL';
                            break;
                        case 'exec-perm':
                            echo 'CURL - Set exec permission';
                            break;
                    }
                    echo '</li>';
                }
                echo '</ul>';
                echo '<div class="engine-report" data-engine="ffmpeg">';
                echo '<strong>FFmpeg:</strong><br>';
                echo '<div class="erStatus"><span class="ersText"></span>';
                echo '<span class="ersEXT"></span></div>';
                echo '<div class="erProgress"><div class="erpPos"></div></div>';
                echo '</div>';
                echo '<div class="engine-report" data-engine="curl">';
                echo '<strong>CURL:</strong><br>';
                echo '<div class="erStatus"><span class="ersText"></span></div>';
                echo '<div class="erProgress"><div class="erpPos"></div></div>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
<?php } ?>
<div class="abox">
    <div class="abox-c">
        <div class="abHead">YouTube connection</div>
        <div class="abBody">
            <ul class="list-items">
                <?php
                uniqueX\Troubleshoot::printDetails('API access', $tsLog['ytapi']);
                uniqueX\Troubleshoot::printDetails('Grab video links', $tsLog['ytgrab']);
                ?>
            </ul>
        </div>
    </div>
</div>
<div class="abox">
    <div class="abox-c">
        <div class="abHead">YouTube video grabbing error detector</div>
        <div class="abBody">
            <div class="col col-1">
                <div class="col-c col-l">
                    <label>
                        YouTube video ID:
                        <br>
                        <input id="ytInput" type="text" placeholder="Enter YouTube video ID" spellcheck="false"
                               autocomplete="off">
                    </label>
                    <br>
                    <input id="ytCheck" type="button" value="Check status">

                    <div id="ytgRes">
                        <div class="result">
                            <span id="ytReport"></span>
                            <span id="ytVideo"></span>
                        </div>
                        <div class="loader">
                            Please wait, loading... <span class="fa fa-refresh fa-spin"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div style="clear:both"></div>
<div style="height:100px"></div>
