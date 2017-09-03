<!doctype html>
<html>
<head>
    <title>ADVT</title>
    <meta charset="UTF-8"/>
    <style type="text/css">
        html {
            overflow: hidden;
        }

        body {
            margin: 0;
        }
    </style>
</head>
<body>
<?php
echo isset($_GET['adCode'])
    ? base64_decode(str_replace(array('.', '-', '_'), array('+', '/', '='), $_GET['adCode']))
    : null;
?>
</body>
</html>