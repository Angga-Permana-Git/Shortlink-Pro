<?php

/*
|--------------------------------------------------------------------------
| Fix Git Bash Windows SESSION_PATH issue
|--------------------------------------------------------------------------
|
| Git Bash on Windows converts the SESSION_PATH env var '/' to
| 'C:/Program Files/Git/' which breaks cookie-based session handling
| and causes 419 CSRF errors in tests.
|
*/

if (isset($_SERVER['SESSION_PATH']) && str_starts_with($_SERVER['SESSION_PATH'], 'C:')) {
    $_SERVER['SESSION_PATH'] = '/';
    $_ENV['SESSION_PATH'] = '/';
    putenv('SESSION_PATH=/');
}
