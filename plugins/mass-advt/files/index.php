<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Check form
if (is_array($_POST) && isset($_POST['token']) && $_POST['token'] == md5(uniqueX\Backend::installKey())) {
    // Update mass advt config
    uniqueX\Backend::setConfig(array(
        'mass-advt-mp3above' => @$_POST['mass-advt-mp3above'],
        'mass-advt-mp3below' => @$_POST['mass-advt-mp3below'],
        'mass-advt-mp4above' => @$_POST['mass-advt-mp4above'],
        'mass-advt-mp4below' => @$_POST['mass-advt-mp4below']
    ));
    // Redirect user
    header('Location: ' . uniqueX\General::siteLink('$admin-mass-advt?update=' . md5(uniqueX\Backend::installKey())));
    exit;
}

// Show update message
if (isset($_GET['update']) && $_GET['update'] == md5(uniqueX\Backend::installKey())) {
    echo '<div class="adminCredits">Mass Advertising codes are successfully updated.</div>';
}

?>
<h2>Mass Advertising</h2>
<div class="adminPage">
    <div class="admin-form">
        <form method="post" action="<?php echo uniqueX\General::siteLink('$admin-mass-advt'); ?>">
            <input type="hidden" name="token" value="<?php echo md5(uniqueX\Backend::installKey()); ?>">
            <label>
                Above MP3 buttons (468x90):<br>
            <textarea placeholder="Put advt code before MP3 download buttons" name="mass-advt-mp3above"
                      spellcheck="false" style="width:100%;height:70px"
                ><?php echo @$config['mass-advt-mp3above']; ?></textarea>
            </label>
            <br>
            <label>
                Below MP3 buttons (468x90):<br>
            <textarea placeholder="Put advt code after MP3 download buttons" name="mass-advt-mp3below"
                      spellcheck="false" style="width:100%;height:70px"
                ><?php echo @$config['mass-advt-mp3below']; ?></textarea>
            </label>
            <br><br>
            <label>
                Above MP4 buttons (468x90):<br>
            <textarea placeholder="Put advt code before MP4 download buttons" name="mass-advt-mp4above"
                      spellcheck="false" style="width:100%;height:70px"
                ><?php echo @$config['mass-advt-mp4above']; ?></textarea>
            </label>
            <br>
            <label>
                Below MP4 buttons (468x90):<br>
            <textarea placeholder="Put advt code after MP4 download buttons" name="mass-advt-mp4below"
                      spellcheck="false" style="width:100%;height:70px"
                ><?php echo @$config['mass-advt-mp4below']; ?></textarea>
            </label>
            <br><br>
            <input type="submit" value="Update Advertising Codes">
        </form>
    </div>
</div>
