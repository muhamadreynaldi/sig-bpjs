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
        // Ini adalah tempat untuk mendaftarkan middleware global, grup, dan alias.

        // Contoh middleware global (jika ada yang Anda tambahkan)
        // $middleware->append(SomeGlobalMiddleware::class);

        // Ini bagian penting untuk Anda: Mendaftarkan Alias Middleware
        $middleware->alias([
            // Tambahkan alias 'role' Anda di sini:
            'role' => \App\Http\Middleware\CheckRole::class,
            // Daftarkan alias lain jika ada (misal: 'verified', 'throttle', dll.)
            // 'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            // 'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            // 'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            // 'can' => \Illuminate\Auth\Middleware\Authorize::class,
            // 'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            // 'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        ]);

        // Anda juga bisa memodifikasi grup middleware di sini jika perlu, misalnya 'web'
        // $middleware->group('web', [
        //     \App\Http\Middleware\EncryptCookies::class,
        //     \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        //     // ... middleware lain dalam grup web
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Konfigurasi penanganan exception
    })->create();