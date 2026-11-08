<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('DB_PATH', APP_ROOT . '/database/voltmarket.sqlite');

require_once APP_ROOT . '/app/config.php';
require_once APP_ROOT . '/app/database.php';
require_once APP_ROOT . '/app/functions.php';

if (!is_dir(APP_ROOT . '/storage')) {
    mkdir(APP_ROOT . '/storage', 0775, true);
}

if (!is_dir(APP_ROOT . '/storage/sessions')) {
    mkdir(APP_ROOT . '/storage/sessions', 0775, true);
}
session_save_path(APP_ROOT . '/storage/sessions');

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', APP_ROOT . '/storage/php-error.log');
date_default_timezone_set('Europe/Moscow');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('voltmarket_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

db();
