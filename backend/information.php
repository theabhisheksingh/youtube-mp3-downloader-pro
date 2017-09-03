<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Import troubleshooter
require_once __DIR__ . '/../core/Troubleshoot.php';

// Get OS details
$osName = uniqueX\Troubleshoot::serverOS();
$osType = uniqueX\Troubleshoot::serverType();

?>
<h2>Site Information</h2>
<div class="adminPage">
    <div class="serverInfo">
        <table>
            <tbody>
            <tr>
                <td>Product version</td>
                <td><?php echo defined('PRODUCT_VERSION') ? PRODUCT_VERSION : 'Not available'; ?></td>
            </tr>
            <tr>
                <td>PHP version</td>
                <td><?php echo PHP_VERSION; ?></td>
            </tr>
            <tr>
                <td>Server OS</td>
                <td><?php
                    echo $osName == 'win' ? 'Windows' : ($osName == 'linux' ? 'Linux' : 'Others');
                    echo " - {$osType}bit";
                    ?></td>
            </tr>
            <tr>
                <td>Server IP address</td>
                <td id="serverIP">
                    <?php
                    $timeout = stream_context_create(array('http' => array('timeout' => 3)));
                    // Get IPV4 and IPv6 addresses of server
                    $addr = false;
                    $ipv4 = @trim(file_get_contents('http://ipv4.icanhazip.com/', false, $timeout));
                    $ipv6 = @trim(file_get_contents('http://ipv6.icanhazip.com/', false, $timeout));
                    // Show IPv4 details
                    if (is_string($ipv4) && strlen($ipv4) > 0) {
                        $addr = true;
                        echo "IPV4: {$ipv4}";
                        // Get location
                        if (is_string($ipv4Location = uniqueX\General::ipInfo($ipv4, 'address'))) {
                            echo "&nbsp;&nbsp;&nbsp;($ipv4Location)";
                        }
                    }
                    // Show IPv6 details
                    if (is_string($ipv6) && strlen($ipv6) > 0) {
                        $addr = true;
                        echo "<br><br>IPV6: {$ipv6}";
                        // Get location
                        if (is_string($ipv6Location = uniqueX\General::ipInfo($ipv6, 'address'))) {
                            echo "&nbsp;&nbsp;&nbsp;($ipv6Location)";
                        }
                    }
                    if (!$addr) {
                        echo 'Not available';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>PHP.ini path</td>
                <td><?php
                    echo function_exists('php_ini_loaded_file') ? php_ini_loaded_file() : 'Not available';
                    ?></td>
            </tr>
            <tr>
                <td>Product location</td>
                <td><?php echo realpath(__DIR__ . '/..'); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
