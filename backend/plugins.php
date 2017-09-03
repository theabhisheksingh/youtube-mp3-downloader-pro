<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get configuration
$config = uniqueX\Backend::getValue('config');
$activePlugins = is_array($config) && isset($config['plugins']) && is_array($config['plugins'])
    ? $config['plugins'] : array();

// Get available plugins
$plugins = uniqueX\Backend::getValue('plugins');
$plugins = is_array($plugins) ? $plugins : array();

echo '<h2>Site plugins</h2>';

// Get install key
$installKey = md5(uniqueX\Backend::installKey());

// Plugin install
if (isset($_GET['install'], $_GET['plugin'], $_GET['action']) && $_GET['install'] == $installKey) {
    foreach ($plugins as $id => $data) {
        if (md5($id) == $_GET['plugin']) {
            // Enable/Disable plugin
            if ($_GET['action'] == 'enable') {
                $activePlugins[] = $id;
            } else {
                if (($pos = array_search($id, $activePlugins)) !== false) {
                    unset($activePlugins[$pos]);
                }
            }
            // Update plugins
            uniqueX\Backend::setConfig(array('plugins' => $activePlugins));
            // Redirect user
            header('Location: ' . uniqueX\General::siteLink('$admin-plugins'));
            exit;
        }
    }
}

// Get plugins from store
$pluginStore = @json_decode(file_get_contents('http://files.uniquex.co/plugins.json'));
if (is_object($pluginStore) || is_array($pluginStore)) {
    $pluginStore = uniqueX\General::object2Array($pluginStore);
    // Attch store plugins to main
    foreach ($pluginStore as $pS) {
        if (is_array($pS) && isset($pS['id']) && !isset($plugins[$pS['id']])) {
            $pS['store'] = true;
            $plugins[$pS['id']] = $pS;
        }
    }
}

if (count($plugins) > 0) {
    echo '<div class="plugins">';
    foreach ($plugins as $id => $plugin) {
        $pluginStatus = array_search($id, $activePlugins) !== false;
        $isStore = isset($plugin['store']) && $plugin['store'];
        $storePrice = null;
        if ($isStore && isset($plugin['purchase']['price']) && $plugin['purchase']['price'] > 0) {
            $storePrice = " : {$plugin['purchase']['price']} USD";
        }
        ?>
        <div class="plugin" data-plugin-id="<?php echo $id; ?>" data-plugin-status="<?php
        echo $pluginStatus ? 'true' : 'false'; ?>">
            <div class="piHead"><?php echo $plugin['name']; ?></div>
            <div class="piBody">
                <p><?php echo $plugin['description']; ?></p>
                <ul class="piTags">
                    <?php
                    if (!$isStore) {
                        echo '<li>Status:&nbsp;';
                        echo $pluginStatus
                            ? '<span class="pi-enable">Enable</span>'
                            : '<span class="pi-disable">Disable</span>';
                        echo '</li>';
                    }
                    ?>
                    <li>
                        Version: <?php echo $isStore ? $plugin['version'] : $plugin['version']['current']; ?></li>
                    <li>Autor:&nbsp;<a target="_blank" href="<?php echo $plugin['author']['site'];
                        ?>"><?php echo $plugin['author']['name']; ?></a></li>
                </ul>
                <?php
                if (!$isStore) {
                    $lVersion = @file_get_contents($plugin['version']['latest']);
                    if (is_string($lVersion) && version_compare($lVersion, $plugin['version']['current'], '>')) {
                        echo '<div class="version-note">Version ' . $lVersion .
                            ' is available. Please contact this plugin developer.</div>';
                    }
                    if ($pluginStatus) {
                        echo '<a href="?action=disbale&install=' . $installKey . '&plugin=' . md5($id) .
                            '" class="piAction pia-dis">Disable plugin</a>';
                    } else {
                        echo '<a href="?action=enable&install=' . $installKey . '&plugin=' . md5($id) .
                            '" class="piAction pia-en">Enable plugin</a>';
                    }
                } else {
                    echo '<a target="_blank" href="' . $plugin['purchase']['link'] .
                        '" class="piAction pia-en">Buy plugin' . $storePrice . '</a>';
                }
                ?>
            </div>
        </div>
        <?php
    }
    echo '</div>';
} else {
    echo '<div class="adminPage">Plugins are not available...</div>';
}
?>
