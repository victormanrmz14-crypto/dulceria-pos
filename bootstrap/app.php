<?php

use App\Console\Commands\CreatePlatformAdmin;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SoloAdmin;
use App\Http\Middleware\SoloSuperAdmin;
use App\Http\Middleware\UsuarioActivo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        CreatePlatformAdmin::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // En producción la app corre detrás de nginx (y un terminador TLS por
        // delante, ya que APP_URL es https). Sin confiar en los proxies, Laravel
        // no detecta HTTPS: genera URLs http://, no marca las cookies como secure
        // y no envía HSTS. Solo el proxy de borde alcanza a la app en esta red.
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            HandleInertiaRequests::class,
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'solo.admin'       => SoloAdmin::class,
            'solo.superadmin'  => SoloSuperAdmin::class,
            'usuario.activo'   => UsuarioActivo::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
