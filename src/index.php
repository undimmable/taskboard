<?php
require 'app/bootstrap.php';
try_authenticate_from_cookie();
$user = get_authorized_user();
$page_title = "TaskBoards";
ob_start();
require 'view/templates/layout.php';
ob_end_flush();