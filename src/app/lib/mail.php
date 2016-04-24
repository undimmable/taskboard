<?php

function send_verification_confirmed_email($email)
{
    $to = $email;
    $subject = 'Thanks for the registration on taskboards.top';
    $message = 'Now you can use this link to access your profile https://taskboard.dev/profile';
    $headers = 'From: signup@taskboards.top';
    mail($to, $subject, $message, $headers);
}

function send_verification_request_email($email, $signup_token)
{
    $to = $email;
    $subject = 'Confirm registration on taskboards.top';
    $message = 'Follow this link to confirm signup request: https://taskboard.dev/api/v1/auth/verify?confirmation_token=' . $signup_token;
    $headers = 'From: signup@taskboards.top';
    mail($to, $subject, $message, $headers);
}