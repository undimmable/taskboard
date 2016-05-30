<?php

require_once "../bootstrap.php";
require_once "dal_helper.php";

function get_login_connection()
{
    global $user_connection;
    if ($user_connection === null) {
        $user_connection = get_mysqli_connection(LOGIN_DB);
    }
    return $user_connection;
}

function dal_create_login()
{

}