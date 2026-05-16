<?php

chdir(dirname(__DIR__));

// Serverless-safe defaults — only applied if the variable isn't already
// set in the Vercel environment dashboard.
$defaults = [
    'APP_ENV'              => 'production',
    'APP_DEBUG'            => 'false',
    'LOG_CHANNEL'          => 'stderr',      // no filesystem writes
    'SESSION_DRIVER'       => 'cookie',      // no filesystem writes
    'CACHE_STORE'          => 'array',       // in-memory, no filesystem writes
    'QUEUE_CONNECTION'     => 'sync',
    'BROADCAST_CONNECTION' => 'log',
    'FILESYSTEM_DISK'      => 'local',
];

foreach ($defaults as $key => $value) {
    if (getenv($key) === false && empty($_ENV[$key])) {
        putenv("$key=$value");
        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
    }
}

// Blade compiles views to disk — point it to /tmp which is writable on Vercel.
if (getenv('VIEW_COMPILED_PATH') === false) {
    $compiledPath = '/tmp/storage/framework/views';
    @mkdir($compiledPath, 0755, true);
    putenv("VIEW_COMPILED_PATH=$compiledPath");
    $_ENV['VIEW_COMPILED_PATH']    = $compiledPath;
    $_SERVER['VIEW_COMPILED_PATH'] = $compiledPath;
}

define('LARAVEL_START', microtime(true));

if (file_exists(dirname(__DIR__) . '/storage/framework/maintenance.php')) {
    require dirname(__DIR__) . '/storage/framework/maintenance.php';
}

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';

$app->handleRequest(Illuminate\Http\Request::capture());
