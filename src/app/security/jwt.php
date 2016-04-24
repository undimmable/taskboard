<?php

function JWT_decode($jwt, $key = null, $verify = true)
{
    $tks = explode('.', $jwt);
    if (count($tks) != 3) {
        return null;
    }
    list($headb64, $bodyb64, $cryptob64) = $tks;
    if (null === ($header = JWT_jsonDecode(JWT_urlsafeB64Decode($headb64)))) {
        return null;
    }
    if (null === $payload = JWT_jsonDecode(JWT_urlsafeB64Decode($bodyb64))) {
        return null;
    }
    $sig = JWT_urlsafeB64Decode($cryptob64);
    if ($verify) {
        if (!array_key_exists('alg', $header)) {
            return null;
        }
        if ($sig != JWT_sign("$headb64.$bodyb64", $key, $header['alg'])) {
            return null;
        }
    }
    return $payload;
}

function JWT_encode($payload, $key, $algo = 'HS256')
{
    $header = array('typ' => 'JWT', 'alg' => $algo);
    $segments = array();
    $segments[] = JWT_urlsafeB64Encode(JWT_jsonEncode($header));
    $segments[] = JWT_urlsafeB64Encode(JWT_jsonEncode($payload));
    $signing_input = implode('.', $segments);
    $signature = JWT_sign($signing_input, $key, $algo);
    $segments[] = JWT_urlsafeB64Encode($signature);
    return implode('.', $segments);
}

function JWT_sign($msg, $key, $method = "HS256")
{
    $methods = array(
        'HS256' => 'sha256',
        'HS384' => 'sha384',
        'HS512' => 'sha512',
    );
    if (empty($methods[$method])) {
        die('Algorithm not supported');
    }
    return hash_hmac($methods[$method], $msg, $key, true);
}

function JWT_jsonDecode($input)
{
    $decoded = json_decode($input, true);
    if ($errno = json_last_error()) {
        _JWT_handleJsonError($errno);
    } else if ($decoded === null && $input !== 'null') {
        die('Null result with non-null input');
    }
    return $decoded;
}

function JWT_jsonEncode($input)
{
    $json = json_encode($input);
    if ($errno = json_last_error()) {
        _JWT_handleJsonError($errno);
    } else if ($json === 'null' && $input !== null) {
        die('Null result with non-null input');
    }
    return $json;
}

function JWT_urlsafeB64Decode($input)
{
    $remainder = strlen($input) % 4;
    if ($remainder) {
        $padlen = 4 - $remainder;
        $input .= str_repeat('=', $padlen);
    }
    return base64_decode(strtr($input, '-_', '+/'));
}

function JWT_urlsafeB64Encode($input)
{
    return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
}

function _JWT_handleJsonError($errno)
{
    $messages = array(
        JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
        JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
        JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON'
    );
    die(
    isset($messages[$errno])
        ? $messages[$errno]
        : 'Unknown JSON error: ' . $errno
    );
}