<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

if (isset($_GET['token'], $_GET['debug']) && $_GET['token'] == md5(uniqueX\Backend::installKey())) {
    // Update debug mode config
    $newStatus = $config['debug-mode'] == 'off' ? 'on' : 'off';
    uniqueX\Backend::setConfig(array('debug-mode' => $newStatus));
    // Redirect
    header('Location: ' . uniqueX\General::siteLink('$admin-developer'));
    exit;
}

?>
<h2>Developer panel<span id="debugMode"><?php
        echo $config['debug-mode'] == 'on' ? 'Disable' : 'Enable';
        ?> debug mode</span></h2>
<div class="adminPage">
    <div class="admin-dev-form">
        <form action="<?php echo uniqueX\General::siteLink('$runner'); ?>" method="post" target="phpResult">
            <label>
                Enter PHP code:<br>
                <textarea placeholder="Enter some PHP code here and press Run PHP button."
                          name="phpCode" spellcheck="false"></textarea>
            </label>
            <input type="submit" value="Run PHP">
        </form>
        <strong>Result of above code:</strong>
        <iframe src="<?php echo uniqueX\General::siteLink('$runner'); ?>" name="phpResult"></iframe>
    </div>
</div>
