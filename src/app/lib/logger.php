<?php
function log_msg($msg, $file)
{
    error_log("[" . date('Y-m-d H:m:s:u') . "]" . $msg . "\n", 3, $file);
}

function log_info($msg)
{
    log_msg($msg, "/var/log/async_php_access.log");
}

function log_error($msg)
{
    log_msg($msg, "/var/log/async_php_error.log");
}

function log_debug($msg)
{
    if ($GLOBALS['debug_enabled'])
        log_msg($msg, "/var/log/async_php_debug.log");
}
