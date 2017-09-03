<?php
if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}
?>
<!doctype html>
<html>
<head>
    <!-- Page details -->
    <title>Admin Panel - Login</title>
    <meta charset="UTF-8"/>
    <!-- Import CSS style sheets -->
    <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="<?php
    echo uniqueX\General::siteLink('assets/scripts/admin.css'); ?>">
    <!-- Import javascript files -->
    <script type="text/javascript" src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script type="text/javascript" src="<?php
    echo uniqueX\General::siteLink('assets/scripts/admin.js'); ?>"></script>
</head>
<body class="login-form">
<h1 id="logo"><a href="<?php echo uniqueX\General::siteLink('$admin'); ?>" title="Admin Panel">Admin Panel</a></h1>

<form action="<?php echo uniqueX\General::siteLink('$admin'); ?>" method="post">
    <label>
        admin user<br>
        <input name="user" type="text" autocomplete="off" spellcheck="false" class="loginField">
    </label>
    <br>
    <label>
        admin password<br>
        <input name="pass" type="password" autocomplete="off" spellcheck="false" class="loginField">
    </label>
    <br>
    <input value="Login" type="submit" id="loginTS">
</form>
</body>
</html>
