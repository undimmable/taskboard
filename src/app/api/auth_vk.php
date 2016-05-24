<?php
$routes = [
    'POST' => [
    ],
    'GET' => [
        'signup' => 'auth_signup_action'
    ],
    'PUT' => [],
    'DELETE' => []
];

function auth_signup_vk_action()
{
    $code = $_GET[CODE];
    $token = fetch_vk_user($code);
    $email = $token['email'];
//    validate_input($email, $role, $password);
//    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
//    $confirmation_token = create_confirmation_token($email);
//    $user = create_user($email, $role, $hashed_password, $confirmation_token);
//    if (!$user) {
//        $db_errors = get_db_errors();
//        render_conflict($db_errors[get_login_entity_name()]);
//    }
//    $token = create_token($email, $role, $user['id']);
//    set_token_cookie($token);
//    send_verification_request_email($email, $confirmation_token);
}

function fetch_vk_user($code)
{
    $params = ['client_id' => get_config_vk_client_id(),
        CLIENT_SECRET => get_config_vk_secret(),
        CODE => $code,
        REDIRECT_URI => 'https://taskboard.dev/api/v1/auth_vk/signup'];

    $token = json_decode(file_get_contents('https://oauth.vk.com/access_token' . '?' . urldecode(http_build_query($params))), true);
    $params = [
        UIDS => $token[USER_ID],
        FIELDS => 'uid,email,mail,first_name,last_name,sex,screen_name',
        ACCESS_TOKEN => $token[ACCESS_TOKEN]
    ];
    $userInfo = json_decode(file_get_contents('https://api.vk.com/method/users.get' . '?' . urldecode(http_build_query($params))), true);
    if (isset($userInfo['response'][0][UID])) {
        $userInfo = $userInfo['response'][0];
        return $userInfo;
    }
    return null;
}