<?php

use App\Http\Middleware\RequiereCuentaActiva;
use App\Http\Middleware\RequiereNegocio;
use App\Http\Middleware\RequiereRol;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo(
            fn (Request $request): string => $request->user()?->esAdministrador()
                ? route('admin.dashboard')
                : route('dashboard')
        );

        $middleware->alias([
            'negocio' => RequiereNegocio::class,
            'rol' => RequiereRol::class,
            'cuenta.activa' => RequiereCuentaActiva::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
