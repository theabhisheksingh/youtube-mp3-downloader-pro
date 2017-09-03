<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get available templates
$templates = uniqueX\Backend::getValue('templates');
$templates = is_array($templates) ? $templates : array();

// Activate template
if (isset($_GET['token'], $_GET['template'], $_GET['flavor']) && isset($templates[$_GET['template']]) &&
    $_GET['token'] == md5(uniqueX\Backend::installKey())
) {
    // Check flavor
    list($flavor, $flavors) = array('default', @$templates[$_GET['template']]['flavors']);
    $flavor = is_array($flavors) && isset($flavors[$_GET['flavor']]) ? $_GET['flavor'] : 'default';
    // Update configuration
    uniqueX\Backend::setConfig(array('template' => $_GET['template'], 'template-flavor' => $_GET['flavor']));
    // Redirect
    header('Location: ' . uniqueX\General::siteLink('$admin-templates?confirm=' . md5(uniqueX\Backend::installKey())));
    exit;
}

if (isset($_GET['confirm']) && $_GET['confirm'] == md5(uniqueX\Backend::installKey())) {
    echo '<div class="adminCredits">Your site template successfully updated</div>';
}

// Get activated template
$config = uniqueX\Backend::getValue('config');

echo '<h2>Site templates<span id="clearCache">Clear template preview cache</span></h2>';

// Get templates from store
$templateStore = @json_decode(file_get_contents('http://files.uniquex.co/templates.json'));
if (is_object($templateStore) || is_array($templateStore)) {
    $templateStore = uniqueX\General::object2Array($templateStore);
    // Attch store templates to main
    foreach ($templateStore as $tS) {
        if (is_array($tS) && isset($tS['id']) && !isset($templates[$tS['id']])) {
            $tS['store'] = true;
            $templates[$tS['id']] = $tS;
        }
    }
}

// Display templates
if (count($templates) > 0) {
    // Final templates
    $finalTemplates = array();
    // Activated template
    $activeTemplate = isset($config['template']) && isset($templates[$config['template']])
        ? $config['template'] : null;
    if ($activeTemplate == null) {
        $templateKeys = array_keys($templates);
        $activeTemplate = $templateKeys[0];
    }
    // Get active template data
    $activeTemplateData = $templates[$activeTemplate];
    // Get template flavor
    $templateFlavor =
        isset($config['template-flavor']) &&
        isset($activeTemplateData['flavors']) &&
        isset($activeTemplateData['flavors'][$config['template-flavor']])
            ? $config['template-flavor'] : 'default';
    // Update templates order
    $finalTemplates[$activeTemplate] = $activeTemplateData;
    foreach ($templates as $id => $data) {
        if ($activeTemplate != $id) {
            $finalTemplates[$id] = $data;
        }
    }
    // Display templates
    echo '<div class="templates">';
    foreach ($finalTemplates as $id => $template) {
        $isStore = isset($template['store']) && $template['store'];
        $storePrice = null;
        if ($isStore && isset($template['purchase']['price']) && $template['purchase']['price'] > 0) {
            $storePrice = " : {$template['purchase']['price']} USD";
        }
        ?>
        <div class="template"
             data-template-id="<?php echo $id; ?>"
             data-template-store="<?php echo $isStore ? 'yes' : 'no'; ?>"
            <?php
            if ($isStore) {
                echo ' data-template-purchase-link="' . $template['purchase']['link'] . '"';
                echo ' data-template-purchase-view="' . $template['purchase']['view'] . '"';
            }
            ?>>
            <div class="tHead"><?php echo $template['name']; ?></div>
            <div class="tBody">
                <div class="tThumb" style="background-image:url('<?php echo $template['thumbnail']; ?>')"></div>
                <div class="tDetails">
                    <p><?php echo $template['description']; ?></p>
                    <strong>Features</strong>
                    <ul class="tFeatures">
                        <li><span class="fa fa-<?php
                            echo isset($template['features']['responsive']) && $template['features']['responsive']
                                ? 'check-' : null;
                            ?>square-o"></span>Resposive design (mobile friendly)
                        </li>
                        <li><span class="fa fa-<?php
                            echo isset($template['features']['desktops']) && $template['features']['desktops']
                                ? 'check-' : null;
                            ?>square-o"></span>Desktop/Laptops
                        </li>
                        <li><span class="fa fa-<?php
                            echo isset($template['features']['tablets']) && $template['features']['tablets']
                                ? 'check-' : null;
                            ?>square-o"></span>Tablets
                        </li>
                        <li><span class="fa fa-<?php
                            echo isset($template['features']['mobiles']) && $template['features']['mobiles']
                                ? 'check-' : null;
                            ?>square-o"></span>Smartphones
                        </li>
                        <li><span class="fa fa-<?php
                            echo isset($template['features']['phones']) && $template['features']['phones']
                                ? 'check-' : null;
                            ?>square-o"></span>Normal phones
                        </li>
                    </ul>
                    <?php
                    if (isset($template['flavors']) && is_array($template['flavors'])) {
                        echo '<strong>Flavors</strong>';
                        echo '<ul class="tFlavors">';
                        foreach ($template['flavors'] as $fID => $fDATA) {
                            echo '<li data-template-flavor="' . $fID . '" class="' .
                                ($activeTemplate == $id && $templateFlavor == $fID ? 'active' : null)
                                . '" style="background-color:' . $fDATA[0] .
                                '" title="' . $fDATA[1] . '"></li>';
                        }
                        echo '</ul>';
                    }
                    ?>
                </div>
                <ul class="tTags">
                    <?php
                    if (!$isStore) {
                        echo '<li>Status:&nbsp;';
                        echo $activeTemplate == $id
                            ? '<span class="tagGreen">Activated</span>'
                            : '<span class="tagRed">Not activate</span>';
                        echo '</li>';
                    }
                    ?>
                    <?php
                    if (isset($template['special']) && is_array($template['special'])) {
                        foreach ($template['special'] as $special) { ?>
                            <li><?php
                                echo "{$special[0]}:&nbsp;";
                                echo $special[1]
                                    ? '<span class="tagGreen">Yes</span>'
                                    : '<span class="tagRed">No</span>';
                                ?></li>
                            <?php
                        }
                    }
                    ?>
                    <li>Version: <?php echo $isStore ? $template['version'] : $template['version']['current']; ?></li>
                    <li>Autor:&nbsp;<a target="_blank" href="<?php echo $template['author']['site'];
                        ?>"><?php echo $template['author']['name']; ?></a></li>
                </ul>
                <?php
                if (!$isStore) {
                    $lVersion = @file_get_contents($template['version']['latest']);
                    if (is_string($lVersion) && version_compare($lVersion, $template['version']['current'], '>')) {
                        echo '<div class="version-note">Version ' . $lVersion .
                            ' is available. Please contact this template developer.</div>';
                    }
                }
                ?>
            </div>
            <div class="tFoot">
                <ul>
                    <?php
                    if ($isStore) {
                        echo '<li data-template-act="buy-now">Buy Now' . $storePrice . '</li>';
                    } else {
                        echo '<li data-template-act="active">' .
                            ($activeTemplate == $id ? 'Update' : 'Activate') . '</li>';
                    }
                    ?>
                    <li data-template-act="preview">Preview</li>
                </ul>
            </div>
        </div>
        <?php
    }
    echo '</div>';
} else {
    echo '<div class="adminPage">Templates are not available...</div>';
}
?>
