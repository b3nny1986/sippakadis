<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'user.active' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // Di belakang proxy (Vercel/nginx) agar HTTPS & IP terdeteksi benar.
        $middleware->trustProxies(at: '*');

        // Endpoint internal /cron/daily dipanggil GitHub Actions dengan token Bearer (bukan form).
        $middleware->validateCsrfTokens(except: ['cron/daily']);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
