<?php
require_once 'dal/dal_helper.php';

function check_logged_in($user_id, $user_agent, $ip)
{
    $result = false;
    $mysqli = get_mysqli_connection(LOGIN_DB);
    if ($mysqli) {
        $stmt = mysqli_prepare($mysqli, "SELECT * FROM db_login.login WHERE user_id=? AND user_client=? AND ip=?");
        if ($stmt) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            if (mysqli_stmt_bind_param($stmt, 'iss', $user_id, $user_agent, $ip)) {
                if (mysqli_stmt_execute($stmt)) {
                    if (mysqli_stmt_num_rows($stmt) > 0) {
                        $result = true;
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
    return $result;
}
