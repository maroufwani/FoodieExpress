<?php

chdir(dirname(__DIR__));

// Fail fast with a clear message if APP_KEY is missing.
if (getenv('APP_KEY') === false || getenv('APP_KEY') === '') {
    http_response_code(500);
    header('Content-Type: text/plain');
    exit('Server configuration error: APP_KEY is not set. Add it in your Vercel Project → Settings → Environment Variables.');
}

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
