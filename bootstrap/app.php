<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Vercel Serverless Environment Detection
|--------------------------------------------------------------------------
|
| Vercel's filesystem is read-only except for /tmp. When the VERCEL
| environment variable is detected, we redirect all runtime-generated
| files (views cache, sessions, framework cache, logs) to /tmp so
| Laravel can write them without hitting permission errors.
|
| This block has NO effect on local development (XAMPP, Artisan serve, etc.)
|
*/
$isVercel = isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']);

// Resolve the custom storage path injected by api/index.php, or fall back
// to the standard Laravel storage directory for local environments.
$storagePath = $isVercel
    ? ($_ENV['APP_STORAGE_PATH'] ?? '/tmp/laravel-storage')
    : dirname(__DIR__) . '/storage';

// Force LOG_CHANNEL to stderr on Vercel so all log output appears in
// the Vercel Function Logs dashboard. Has no effect locally.
if ($isVercel) {
    $_ENV['LOG_CHANNEL']    = $_ENV['LOG_CHANNEL']    ?? 'stderr';
    $_SERVER['LOG_CHANNEL'] = $_SERVER['LOG_CHANNEL'] ?? 'stderr';

    // Laravel 11 natively checks LARAVEL_STORAGE_PATH in Application::storagePath().
    // Setting it here is sufficient; no useStoragePath() call needed.
    $_ENV['LARAVEL_STORAGE_PATH']    = $storagePath;
    $_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.active' => \App\Http\Middleware\CheckUserActive::class,
        ]);

        // Trust all proxies — required for correct URL generation behind
        // Vercel's edge network (HTTPS detection, IP headers, etc.)
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
