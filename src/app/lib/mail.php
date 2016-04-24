<?php

function send_confirmation_email($email, $signup_token)
{
    $to = $email;
    $subject = 'Confirm registration on taskboards.top';
    $message = 'Follow this link to confirm signup request: https://taskboard.dev/api/v1/auth/verify?confirmation_token=' . $signup_token;
    $headers = 'From: signup@taskboards.top';
    mail($to, $subject, $message, $headers);
}