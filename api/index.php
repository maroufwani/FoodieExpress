<?php

// Ensure the working directory is the project root so Laravel can
// resolve all paths (storage, bootstrap/cache, etc.) correctly.
chdir(dirname(__DIR__));

define('LARAVEL_START', microtime(true));

if (file_exists(dirname(__DIR__) . '/storage/framework/maintenance.php')) {
    require dirname(__DIR__) . '/storage/framework/maintenance.php';
}

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';

$app->handleRequest(Illuminate\Http\Request::capture());
