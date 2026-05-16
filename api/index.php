<?php

// ── Create writable /tmp directories Blade, sessions, and logs need on Vercel ─
foreach ([
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/logs',
] as $dir) {
    @mkdir($dir, 0755, true);
}

// ── Force serverless-safe config before Laravel bootstraps ───────────────────
// All three superglobals + putenv() are set so every env-reader in Laravel
// (phpdotenv adapters, getenv(), $_ENV, $_SERVER) sees our values, even when
// the Vercel dashboard has conflicting entries (e.g. LOG_CHANNEL=stack).
foreach ([
    'LOG_CHANNEL'        => 'stderr',
    'SESSION_DRIVER'     => 'cookie',
    'CACHE_STORE'        => 'array',
    'QUEUE_CONNECTION'   => 'sync',
    'VIEW_COMPILED_PATH' => '/tmp/storage/framework/views',
    'APP_CONFIG_CACHE'   => '/tmp/config.php',
    'APP_EVENTS_CACHE'   => '/tmp/events.php',
    'APP_PACKAGES_CACHE' => '/tmp/packages.php',
    'APP_ROUTES_CACHE'   => '/tmp/routes.php',
    'APP_SERVICES_CACHE' => '/tmp/services.php',
] as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $_SERVER[$key] = $value;
}

require __DIR__ . '/../public/index.php';
