<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}


// Get template color
list($template, $flavor, $color) = array(uniqueX\Backend::getValue('template'), uniqueX\Backend::getFlavor(), '#4DBDEB');
if (isset($template['flavors'][$flavor]) && substr($template['flavors'][$flavor][0], 0, 1) == '#') {
    $color = $template['flavors'][$flavor][0];
}


if (!(isset($config['site-contact']) && is_string($config['site-contact']) &&
    !filter_var($config['site-contact'], FILTER_VALIDATE_EMAIL) === false)
) {
    header('HTTP/1.0 404 Not Found');
    echo 'Invalid contact us email address';
    exit;
}

// Detect contact form data
$contactName = isset($_POST['name']) ? trim($_POST['name']) : null;
$contactMail = isset($_POST['mail']) ? trim($_POST['mail']) : null;
$contactHead = isset($_POST['head']) ? trim($_POST['head']) : null;
$contactBody = isset($_POST['body']) ? trim($_POST['body']) : null;

// Detect contact form errors
$contactErrors = array();
$contactFormStatus = 'fresh';

// Validate contact form data
if ($contactName != null || $contactMail != null || $contactHead != null || $contactBody != null) {
    if ($contactName == null) {
        $contactErrors[] = 'Enter your full name';
    }
    if (filter_var($contactMail, FILTER_VALIDATE_EMAIL) === false) {
        $contactErrors[] = 'Enter valid email address';
    }
    if ($contactHead == null) {
        $contactErrors[] = 'Provide subject for this message';
    }
    if ($contactBody == null) {
        $contactErrors[] = 'Write your complete message';
    }
    // Check form status
    if (count($contactErrors) > 0) {
        $contactFormStatus = 'invalid';
    } else {
        // Prepare headers for email
        $headers = array(
            "MIME-Version: 1.0",
            "Content-type: text/html; charset=UTF-8",
            "From: {$contactName} <{$contactMail}>",
            "Reply-To: {$contactName} <{$contactMail}>"
        );
        // Send email
        $contactFormStatus = mail(
            $config['site-contact'],
            $contactHead,
            $contactBody,
            implode("\r\n", $headers) . "\r\n"
        ) ? 'success' : 'failed';
    }
}

?>
<!doctype html>
<html class="flavor-<?php echo $flavor; ?>">
<head>
    <title>Contact Us - <?php echo @$config['site-name']; ?></title>
    <meta charset="UTF-8"/>
    <meta name="description" content="Contact Us - <?php echo @$config['site-name']; ?>">
    <meta name="theme-color" content="<?php echo $color; ?>">
    <!-- Extra scripts -->
    <?php require_once __DIR__ . '/../layers/html_head.php'; ?>
</head>
<body>
<?php
// Import page header
require_once __DIR__ . '/../layers/site_header.php';
?>
<div id="videosList">
    <ul id="videosMenu">
        <li><a href="<?php echo uniqueX\General::siteLink('terms');
            ?>" title="Terms of Service">Terms of Service</a></li>
        <li><a href="<?php echo uniqueX\General::siteLink('privacy');
            ?>" title="Privacy Policy">Privacy Policy</a></li>
        <li class="active"><a href="<?php echo uniqueX\General::siteLink('contact');
            ?>" title="Contact Us">Contact Us</a></li>
    </ul>
    <div id="videosArea" class="videosArea">
        <?php echo isset($config['advt-above-results'])
            ? "<div class='area-728'>{$config['advt-above-results']}</div>" : null; ?>
        <h1 class="pageHead">Contact Us - <?php echo @$config['site-name']; ?></h1>
        <?php
        if ($contactFormStatus == 'success') {
            echo '<div class="contactForm-alert contactForm-success">';
            echo '<div class="cf-banner">Your message sent successfully<br>';
            echo '<span class="cf-mail">Thanks for contacting us</span></div>';
            echo '</div>';
        } else {
            switch ($contactFormStatus) {
                case 'invalid':
                    echo '<div class="contactForm-alert contactForm-invalid">';
                    echo '<div class="cf-head">Please checkout this below errors</div>';
                    echo '<ul class="cf-list">';
                    foreach ($contactErrors as $cfError) {
                        echo "<li>{$cfError}</li>";
                    }
                    echo '</ul>';
                    echo '</div>';
                    break;
                case 'failed':
                    echo '<div class="contactForm-alert contactForm-failed">';
                    echo '<div class="cf-banner">Failed to send email address<br>';
                    echo '<span class="cf-mail">You can also contact us from <span class="mail">';
                    echo $config['site-contact'] . '</span></span></div>';
                    echo '</div>';
                    break;
            }
            ?>
            <form method="post" class="contactForm">
                <label>
                    Your name:<br>
                    <input type="text" name="name" spellcheck="false" placeholder="Enter your full name"
                           value="<?php echo $contactName; ?>">
                </label>
                <br>
                <label>
                    Your e-mail:<br>
                    <input type="text" name="mail" spellcheck="false" placeholder="Enter your valid email address"
                           value="<?php echo $contactMail; ?>">
                </label>
                <br>
                <label>
                    Subject:<br>
                    <input type="text" name="head" placeholder="Provide good subject for this message"
                           value="<?php echo $contactHead; ?>">
                </label>
                <br>
                <label>
                    Message:<br>
                    <textarea name="body"
                              placeholder="Write your complete message here..."><?php echo $contactBody; ?></textarea>
                </label>
                <br>
                <input type="submit" value="Send Message">
            </form>
            <?php
        }
        ?>
    </div>
    <div class="sidePanel">
        <?php
        require_once __DIR__ . '/../layers/side_panel.php'; ?>
    </div>
    <div style="position:relative;clear:both"></div>
</div>
<div id="videoPlayer"></div>
<?php require_once __DIR__ . '/../layers/site_footer.php'; ?>
</body>
</html>
