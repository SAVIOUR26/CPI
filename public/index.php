<?php

// When running under `php -S` (local/dev testing only), let the built-in
// server serve real static files directly instead of routing them through
// the app. Apache/.htaccess already does this in production via
// "RewriteCond %{REQUEST_FILENAME} !-f", so this has no effect there.
if (PHP_SAPI === 'cli-server') {
    $requestedFile = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($requestedFile !== __DIR__ . '/' && is_file($requestedFile)) {
        return false;
    }
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Request;
use App\Core\Router;

$router = new Router();
require dirname(__DIR__) . '/routes/web.php'; // populates $router

$router->dispatch(new Request());
