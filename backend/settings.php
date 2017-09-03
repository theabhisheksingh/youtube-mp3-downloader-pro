<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get form data
$oldUser = uniqueX\General::requestParam('olduser');
$oldPass = uniqueX\General::requestParam('oldpass');
$newUser = uniqueX\General::requestParam('newuser');
$newPass = uniqueX\General::requestParam('newpass');

// Check for is request fresh
$formStatus = 'fresh';
if ($oldUser != null || $oldPass != null || $newUser != null || $newPass != null) {
    if ($_SESSION['user'] == $oldUser && $_SESSION['pass'] == $oldPass) {
        if ($oldUser == $newUser && $oldPass == $newPass) {
            $formStatus = 'same-details';
        } else {
            if (strlen($newUser) >= 5 && strlen($newPass) >= 5) {
                // Update admin settings
                uniqueX\Backend::setConfig(array(
                    'admin-user' => $newUser,
                    'admin-pass' => $newPass
                ));
                $_SESSION['user'] = $newUser;
                $_SESSION['pass'] = $newPass;
                // redirect
                header('Location: ' . uniqueX\General::siteLink('$admin-settings?update=' . md5($newUser . $newPass)));
                exit;
            } else {
                $formStatus = 'credit-length';
            }
        }
    } else {
        $formStatus = 'invalid-current';
    }
}

if (isset($_GET['update']) && $_GET['update'] == md5($_SESSION['user'] . $_SESSION['pass'])) {
    $formStatus = 'update';
}

switch ($formStatus) {
    case 'same-details':
        echo '<div class="adminCredits">Please enter new details for new user ID &amp; password</div>';
        break;
    case 'credit-length':
        echo '<div class="adminCredits">User ID &amp; password must contain at least 5 charactors</div>';
        break;
    case 'invalid-current':
        echo '<div class="adminCredits">Invalid current user ID and/or password</div>';
        break;
    case 'update':
        echo '<div class="adminCredits">Your admin login details are successfully updated</div>';
        break;
}

?>
<h2>Admin settings</h2>
<div class="adminPage">
    <div class="admin-form">
        <form action="<?php echo uniqueX\General::siteLink('$admin-settings'); ?>" method="post">
            <label>
                Current admin user ID:<br>
                <input type="text" placeholder="Enter your current admin user ID" autocomplete="off"
                       name="olduser" spellcheck="false">
            </label>
            <br>
            <label>
                Current admin password:<br>
                <input type="text" placeholder="Enter your current admin password" autocomplete="off"
                       name="oldpass" spellcheck="false">
            </label>
            <br><br>
            <label>
                New admin user ID:<br>
                <input type="text" placeholder="Enter new admin user ID" autocomplete="off"
                       name="newuser" spellcheck="false">
            </label>
            <br>
            <label>
                New admin password:<br>
                <input type="text" placeholder="Enter new admin password" autocomplete="off"
                       name="newpass" spellcheck="false">
            </label>

            <div style="height:10px"></div>
            <input type="submit" value="Update settings">
        </form>
    </div>
</div>
