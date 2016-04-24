<?php

require_once "../bootstrap.php";
require_once "helper.php";

function get_login_entity_name()
{
    return 'login';
}

$db_errors = [];

function initialize_db_errors()
{
    global $db_errors;
    $db_errors = [];
}

function get_db_errors()
{
    global $db_errors;
    return $db_errors;
}

function create_user($email, $role, $hashed_password, $confirmation_token)
{
    if (user_exists($email)) {
        global $db_errors;
        $db_errors[get_login_entity_name()] = "email already exists";
        return false;
    }
    initialize_db_errors();
    $mysqli = get_mysqli_login_connection();
    if (!$mysqli)
        add_error(mysqli_error($mysqli));
    $mysqli_stmt = mysqli_prepare($mysqli, "INSERT INTO db_login.login (user_id, user_email, role, password, confirmation_token) VALUES (?,?,?,?,?)");
    if (!$mysqli_stmt)
        add_error(mysqli_error($mysqli));
    $user_id = 3;
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    if (!mysqli_stmt_bind_param($mysqli_stmt, 'isiss', $user_id, $email, $role, $hashed_password, $confirmation_token))
        add_error(mysqli_error($mysqli));
    if (!mysqli_stmt_execute($mysqli_stmt))
        add_error(mysqli_error($mysqli));
    if (!mysqli_stmt_close($mysqli_stmt))
        add_error(mysqli_error($mysqli));
    if (mysqli_errno($mysqli) === 0)
        return [get_username_assoc_key() => $email, get_role_assoc_key() => $role, 'id' => mysqli_insert_id($mysqli)];
    else
        return false;
}

/**
 * @return mysqli
 */
function get_mysqli_login_connection()
{
    return get_mysqli_connection(get_login_entity_name());
}

function user_exists($email)
{
    initialize_db_errors();
    $mysqli = get_mysqli_connection(get_login_entity_name());
    if (!$mysqli)
        add_error(mysqli_error($mysqli));
    $mysqli_stmt = mysqli_prepare($mysqli, "SELECT count(*) AS count FROM db_login.login WHERE user_email = ?");
    if (!$mysqli_stmt)
        add_error(mysqli_error($mysqli));
    if (!mysqli_stmt_bind_param($mysqli_stmt, 's', $email))
        add_error(mysqli_error($mysqli));
    if (!mysqli_stmt_execute($mysqli_stmt))
        add_error(mysqli_error($mysqli));
    if (!mysqli_stmt_bind_result($mysqli_stmt, $count))
        add_error(mysqli_error($mysqli));
    if (!mysqli_stmt_fetch($mysqli_stmt))
        add_error(mysqli_error($mysqli));
    if (!mysqli_stmt_close($mysqli_stmt))
        add_error(mysqli_error($mysqli));
    return $count > 0;
}

function get_user($email)
{
    initialize_db_errors();
    $mysqli = get_mysqli_connection(get_login_entity_name());
    if (!$mysqli)
        add_error(mysqli_error($mysqli));
    $mysqli_stmt = mysqli_prepare($mysqli, "SELECT id, user_id, user_email, password, role FROM db_login.login WHERE user_email = ?");
    if (!$mysqli_stmt)
        add_error(mysqli_error($mysqli));
    if (!mysqli_stmt_bind_param($mysqli_stmt, 's', $email))
        add_error(mysqli_error($mysqli));
    if (!mysqli_stmt_execute($mysqli_stmt))
        add_error(mysqli_error($mysqli));
    if (!mysqli_stmt_bind_result($mysqli_stmt, $id, $user_id, $user_email, $password, $role))
        add_error(mysqli_error($mysqli));
    if (!mysqli_stmt_fetch($mysqli_stmt))
        add_error(mysqli_error($mysqli));
    if (!mysqli_stmt_close($mysqli_stmt))
        add_error(mysqli_error($mysqli));
    return ["id" => $id, "user_id" => $user_id, get_username_assoc_key() => $user_email, get_password_assoc_key() => $password, get_role_assoc_key() => $role];
}

function add_error($mysqli)
{
    global $db_errors;
    $error = mysqli_error($mysqli);
    array_push($db_errors, $error);
    error_log($error);
}