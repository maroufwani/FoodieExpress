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
$overrides = [
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
];

// Vercel terminates TLS at the edge; PHP sees plain HTTP.
// Force ASSET_URL to https:// so @vite() never emits http:// links.
// (trustProxies in bootstrap/app.php fixes this for runtime URL generation;
//  this covers the config-loading phase before middleware runs.)
if (!empty($_SERVER['HTTP_HOST'])) {
    $overrides['ASSET_URL'] = 'https://' . $_SERVER['HTTP_HOST'];
}

foreach ($overrides as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $_SERVER[$key] = $value;
}

// Fix Symfony base-URL detection.
// vercel-php sets SCRIPT_NAME=/api/index.php; Symfony then treats dirname (/api)
// as the base URL and strips it from every REQUEST_URI that starts with /api/…
// (e.g. /api/restaurants → /restaurants → no route → 404).
// Resetting to /index.php makes dirname resolve to '' so the full path is kept.
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF']    = '/index.php';

require __DIR__ . '/../public/index.php';
