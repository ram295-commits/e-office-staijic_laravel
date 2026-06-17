<?php

/**
 * Vercel Serverless Entry Point for Laravel
 *
 * This file is the single entrypoint for all HTTP traffic on Vercel.
 * The `vercel-php` runtime executes this file for every request.
 *
 * It resolves the correct paths back to the Laravel project root
 * (one level up from the /api directory) and delegates completely
 * to the standard Laravel public/index.php bootstrapper.
 *
 * IMPORTANT: Do NOT put application logic here. This file only handles
 * path resolution so Laravel can bootstrap from its expected root.
 */

// ─── 1. Establish the Laravel root path ──────────────────────────────────────
// __DIR__ resolves to the /api directory on Vercel.
// The Laravel project root is one level up.
$laravelRoot = dirname(__DIR__);

// ─── 2. Vercel /tmp path configuration ───────────────────────────────────────
// Vercel's filesystem is read-only except for /tmp.
// We set these BEFORE requiring public/index.php so that the
// Application instance picks up the overridden paths immediately.
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {

    // Force LOG_CHANNEL to stderr so logs appear in the Vercel Dashboard.
    $_ENV['LOG_CHANNEL']  = 'stderr';
    $_SERVER['LOG_CHANNEL'] = 'stderr';

    // Override storage paths to /tmp which is writable on Vercel.
    // These are read by config/filesystems.php and config/session.php
    // BEFORE the Laravel service container boots.
    $tmpStorage = '/tmp/laravel-storage';

    // Ensure the required runtime directories exist in /tmp.
    // Laravel will fail silently if they don't exist on first boot.
    $runtimeDirs = [
        $tmpStorage . '/framework/cache/data',
        $tmpStorage . '/framework/sessions',
        $tmpStorage . '/framework/views',
        $tmpStorage . '/logs',
        $tmpStorage . '/app',
    ];

    foreach ($runtimeDirs as $dir) {
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    // Point Laravel's storage_path() to /tmp via LARAVEL_STORAGE_PATH.
    // Laravel 11 checks this env var natively in Application::storagePath().
    // bootstrap/app.php also reads it for the Vercel path redirect.
    $_ENV['LARAVEL_STORAGE_PATH']    = $tmpStorage;
    $_SERVER['LARAVEL_STORAGE_PATH'] = $tmpStorage;

    // Keep APP_STORAGE_PATH for backward compat with bootstrap/app.php fallback.
    $_ENV['APP_STORAGE_PATH']    = $tmpStorage;
    $_SERVER['APP_STORAGE_PATH'] = $tmpStorage;

    // Override bootstrap cache paths so Laravel doesn't try to write to read-only bootstrap/cache
    $_ENV['APP_SERVICES_CACHE'] = '/tmp/services.php';
    $_SERVER['APP_SERVICES_CACHE'] = '/tmp/services.php';
    $_ENV['APP_PACKAGES_CACHE'] = '/tmp/packages.php';
    $_SERVER['APP_PACKAGES_CACHE'] = '/tmp/packages.php';
    $_ENV['APP_CONFIG_CACHE'] = '/tmp/config.php';
    $_SERVER['APP_CONFIG_CACHE'] = '/tmp/config.php';
    $_ENV['APP_ROUTES_CACHE'] = '/tmp/routes-v7.php';
    $_SERVER['APP_ROUTES_CACHE'] = '/tmp/routes-v7.php';
    $_ENV['APP_EVENTS_CACHE'] = '/tmp/events.php';
    $_SERVER['APP_EVENTS_CACHE'] = '/tmp/events.php';
}

// ─── 3. Fix the working directory ────────────────────────────────────────────
// Vercel may set cwd to the /api subdirectory. Laravel expects cwd to be
// the project root so that relative paths in configs resolve correctly.
chdir($laravelRoot);

// ─── 4. Delegate to Laravel's public/index.php ───────────────────────────────
// This is functionally identical to what a standard web server (Nginx/Apache)
// does: it points the document root at /public and invokes index.php.
require $laravelRoot . '/public/index.php';
