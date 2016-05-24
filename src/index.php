<?php
require 'app/bootstrap.php';
try_authorize_from_cookie();
$user = get_authorized_user();
$verification_popup = $_GET['verification_popup'];
$page_title = "TaskBoards";
ob_start();
require 'app/view/templates/layout.php';
ob_end_flush();