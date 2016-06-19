<?php
/**
 * Lib functions
 *
 * PHP version 5
 *
 * @category  LibFunctions
 * @package   Lib
 * @author    Dmitry Bogdanov <dimyriy.bogdanov@gmail.com>
 * @copyright 2016 Dmitry Bogdanov
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   GIT: $Id$ In development.
 * @link      https://taskboards.top
 * @since     1.0.0
 */

function send_password_changed_email($email, $host)
{
    $to = $email;
    $subject = "Password successfully changed.";
    $message = '<html><body>';
    $message .= 'Now you can use <a href="https://' . $host . '/">this link</a> to access the app.';
    $message .= '</body></html>';
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= 'From: Taskboards <signup@taskboards.top>' . "\r\n";
    $headers .= 'Reply-To: Taskboards <signup@taskboards.top>' . "\r\n";
    mail($to, $subject, $message, $headers);
}

function send_registration_email($email, $host)
{
    $to = $email;
    $subject = "Thanks for the registration on taskboards.top";
    $message = '<html><body>';
    $message .= '<a href="https://' . $host . '/">Follow this link to access taskboards.</a>';
    $message .= '</body></html>';
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= 'From: Taskboards <signup@taskboards.top>' . "\r\n";
    $headers .= 'Reply-To: Taskboards <signup@taskboards.top>' . "\r\n";
    mail($to, $subject, $message, $headers);
}

function send_reset_password_email($email, $host, $token)
{
    $to = $email;
    $subject = 'Reset password requested';
    $message = '<html><body>';
    $message .= '<a href="https://' . $host . '?verification_token=' . $token . '">Follow this link to reset your password.</a>';
    $message .= '</body></html>';
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= 'From: Taskboards <signup@taskboards.top>' . "\r\n";
    $headers .= 'Reply-To: Taskboards <signup@taskboards.top>' . "\r\n";
    mail($to, $subject, $message, $headers);
}
