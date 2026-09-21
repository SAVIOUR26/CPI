<?php

/**
 * Application bootstrap — no Composer required. Registers a PSR-4-ish
 * autoloader for the App\ namespace, loads .env, starts the session, and
 * wires up error handling.
 */

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use App\Core\Env;
use App\Core\Session;

require BASE_PATH . '/app/Support/helpers.php';

Env::load(BASE_PATH . '/.env');

$debug = Env::get('APP_DEBUG', false);
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

date_default_timezone_set(Env::get('APP_TIMEZONE', 'Africa/Kampala'));

set_exception_handler(function (Throwable $e) use ($debug) {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    if ($debug) {
        echo '<pre style="padding:20px;font-family:monospace;white-space:pre-wrap;">';
        echo htmlspecialchars($e->getMessage() . "\n\n" . $e->getTraceAsString());
        echo '</pre>';
    } else {
        echo 'Something went wrong. Please try again shortly.';
    }
});

Session::start();
