<?php

chdir(dirname(__DIR__));

// Fail fast with a clear message if APP_KEY is missing.
if (getenv('APP_KEY') === false || getenv('APP_KEY') === '') {
    http_response_code(500);
    header('Content-Type: text/plain');
    exit('Config error: APP_KEY is not set. Add it in Vercel Project → Settings → Environment Variables.');
}

// Ensure vendor/ was built by the vercel-php runtime.
if (!file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    http_response_code(500);
    header('Content-Type: text/plain');
    exit('Config error: vendor/autoload.php not found. Composer dependencies were not installed.');
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

// Wrap bootstrap in a try/catch so we see the real error.
// Set APP_DEBUG=true in Vercel dashboard to expose the message.
try {
    $app = require_once dirname(__DIR__) . '/bootstrap/app.php';
    $app->handleRequest(Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain');
    if (getenv('APP_DEBUG') === 'true') {
        exit('[DEBUG] ' . get_class($e) . ': ' . $e->getMessage()
            . "\nFile: " . $e->getFile() . ':' . $e->getLine()
            . "\n\n" . $e->getTraceAsString());
    }
    exit('Server error. Set APP_DEBUG=true in Vercel environment variables to see details.');
}
