<?php
/**
 * Index page
 *
 * PHP version 5
 *
 * @category  View
 * @package   Root
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */
require 'app/bootstrap.php';
try_authenticate_from_cookie();
$user = get_authorized_user();
$page_title = "TaskBoards";
ob_start();
require 'view/templates/layout.php';
ob_end_flush();