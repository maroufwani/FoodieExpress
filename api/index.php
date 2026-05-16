<?php

chdir(dirname(__DIR__));

// ── Guard: APP_KEY ────────────────────────────────────────────────────────────
if (getenv('APP_KEY') === false || getenv('APP_KEY') === '') {
    http_response_code(500);
    header('Content-Type: text/plain');
    exit('Config error: APP_KEY is not set. Add it in Vercel Project → Settings → Environment Variables.');
}

// ── Guard: vendor/ ────────────────────────────────────────────────────────────
if (!file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    http_response_code(500);
    header('Content-Type: text/plain');
    exit('Config error: vendor/autoload.php not found. Composer dependencies were not installed.');
}

// ── Writable /tmp directories for Blade, sessions, cache ─────────────────────
$tmpDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/logs',
];
foreach ($tmpDirs as $dir) {
    @mkdir($dir, 0755, true);
}

// ── Set env vars BEFORE bootstrap so Laravel config picks them up ─────────────
$env = [
    'LOG_CHANNEL'          => 'stderr',
    'LOG_STACK'            => 'stderr',
    'SESSION_DRIVER'       => 'cookie',
    'CACHE_STORE'          => 'array',
    'QUEUE_CONNECTION'     => 'sync',
    'BROADCAST_CONNECTION' => 'log',
    'VIEW_COMPILED_PATH'   => '/tmp/storage/framework/views',
    'APP_ENV'              => getenv('APP_ENV') ?: 'production',
    'APP_DEBUG'            => getenv('APP_DEBUG') ?: 'false',
];
foreach ($env as $k => $v) {
    putenv("$k=$v");
    $_ENV[$k]    = $v;
    $_SERVER[$k] = $v;
}

define('LARAVEL_START', microtime(true));

if (file_exists(dirname(__DIR__) . '/storage/framework/maintenance.php')) {
    require dirname(__DIR__) . '/storage/framework/maintenance.php';
}

require dirname(__DIR__) . '/vendor/autoload.php';

try {
    $app = require_once dirname(__DIR__) . '/bootstrap/app.php';

    // ── Override config directly on the app after bootstrap ──────────────────
    // This guarantees our values win regardless of putenv() SAPI behavior.
    $app->booted(function () use ($app) {
        $cfg = $app->make('config');
        $cfg->set('logging.default',       'stderr');
        $cfg->set('logging.deprecations',  null);
        $cfg->set('session.driver',        'cookie');
        $cfg->set('cache.default',         'array');
        $cfg->set('queue.default',         'sync');
        $cfg->set('view.compiled',         '/tmp/storage/framework/views');
    });

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

    exit('Server error. Set APP_DEBUG=true in Vercel environment variables to see details.');
}
