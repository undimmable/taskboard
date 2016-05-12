<?php

function send_verification_confirmed_email($email, $host)
{
    $to = $email;
    $subject = 'Thanks for the registration on taskboards.top';
    $message = '<html><body>';
    $message .= 'Now you can use <a href="https://' . $host . '/profile">this link</a> to access your profile ';
    $message .= '</body></html>';
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= 'From: Taskboards <signup@taskboards.top>' . "\r\n";
    $headers .= 'Reply-To: Taskboards <signup@taskboards.top>' . "\r\n";
    mail($to, $subject, $message, $headers);
}

function send_verification_request_email($email, $host, $signup_token)
{
    $to = $email;
    $subject = 'Confirm registration on taskboards.top';
    $message = '<html><body>';
    $message .= '<a href="https://' . $host . '/api/v1/auth/verify?confirmation_token=' . $signup_token . '">Follow this link to confirm signup request.</a>';
    $message .= '</body></html>';
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= 'From: Taskboards <signup@taskboards.top>' . "\r\n";
    $headers .= 'Reply-To: Taskboards <signup@taskboards.top>' . "\r\n";
    mail($to, $subject, $message, $headers);
}