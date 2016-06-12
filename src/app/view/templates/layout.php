<?php
?>
<!DOCTYPE html>
<!--[if lt IE 7]>
<html class="lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>
<html class="lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>
<html class="lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!-->
<html lang="en"><!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width,height=device-height,initial-scale=1,maximum-scale=1,user-scalable=no">
    <title><?php echo $page_title ?></title>
    <link rel="apple-touch-icon-precomposed" sizes="57x57" href="/icons/apple-touch-icon-57x57.png"/>
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/icons/apple-touch-icon-114x114.png"/>
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/icons/apple-touch-icon-72x72.png"/>
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/icons/apple-touch-icon-144x144.png"/>
    <link rel="apple-touch-icon-precomposed" sizes="60x60" href="/icons/apple-touch-icon-60x60.png"/>
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="/icons/apple-touch-icon-120x120.png"/>
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="/icons/apple-touch-icon-76x76.png"/>
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="/icons/apple-touch-icon-152x152.png"/>
    <link rel="icon" type="image/png" href="/icons/favicon-196x196.png" sizes="196x196"/>
    <link rel="icon" type="image/png" href="/icons/favicon-96x96.png" sizes="96x96"/>
    <link rel="icon" type="image/png" href="/icons/favicon-32x32.png" sizes="32x32"/>
    <link rel="icon" type="image/png" href="/icons/favicon-16x16.png" sizes="16x16"/>
    <link rel="icon" type="image/png" href="/icons/favicon-128.png" sizes="128x128"/>
    <meta name="application-name" content="&nbsp;"/>
    <meta name="msapplication-TileColor" content="#FFFFFF"/>
    <meta name="msapplication-TileImage" content="/icons/mstile-144x144.png"/>
    <meta name="msapplication-square70x70logo" content="/icons/mstile-70x70.png"/>
    <meta name="msapplication-square150x150logo" content="/icons/mstile-150x150.png"/>
    <meta name="msapplication-wide310x150logo" content="/icons/mstile-310x150.png"/>
    <meta name="msapplication-square310x310logo" content="/icons/mstile-310x310.png"/>
    <!--[if lt IE 9]>
    <script type="application/javascript" src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script type="application/javascript" src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
          integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css"/>
    <link rel="stylesheet" href="/css/style.css?__nocache=<?php echo rand(0,100000); ?>"/>
    <script type="application/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script type="application/javascript" src="/js/eventstream.js"></script>
    <script type="application/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
            integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
            crossorigin="anonymous"></script>
    <script type="application/javascript" src="/js/localization.js?__nocache=<?php echo rand(0,100000); ?>"></script>
    <script type="application/javascript" src="/js/app.js?__nocache=<?php echo rand(0,100000); ?>"></script>
    <script type="application/javascript"
            src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
</head>
<body>
<?php
$role = is_authorized() ? get_authorized_user()[ROLE] : 0;
$payload = get_random_payload(get_authorized_user());
$commission = get_system_commission();
echo "<div id=\"user-data\" data-role=\"$role\" data-payloadsid=\"$payload\" data-commission=\"$commission\"></div>";
?>
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <a href="#" class="pull-left navbar-brand logo fa fa-check-circle-o fa-2x"></a>
            <a class="navbar-brand" href="#">TaskBoards</a>
        </div>
        <div>
            <?php
            require 'view/templates/right_menu.html.php';
            ?>
        </div>
    </div>
</nav>
<div id="success-popup" class="alert alert-success hidden fade in">
    <a href="#" class="close alert-close">&times;</a>
    <span id="success-popup-text"></span>
</div>
<div id="error-popup" class="alert alert-danger hidden fade in">
    <a href="#" class="close alert-close">&times;</a>
    <span id="error-popup-text"></span>
</div>
<div class="container">
    <div class="row">
        <?php
        if (is_authorized()) {
            require 'view/templates/feed.html.php';
        } else {
            require 'view/templates/unauthorized.html.php';
        }
        ?>
    </div>
</div>
</body>
</html>