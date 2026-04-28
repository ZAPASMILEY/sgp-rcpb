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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'        => \App\Http\Middleware\EnsureAdmin::class,
            'pca'          => \App\Http\Middleware\EnsurePca::class,
            'personnel'    => \App\Http\Middleware\EnsurePersonnel::class,
            'dg'           => \App\Http\Middleware\EnsureDg::class,
            'subordonne'   => \App\Http\Middleware\EnsureIsSubordonne::class,
            'dga_espace'       => \App\Http\Middleware\EnsureIsDga::class,
            'directeur_espace' => \App\Http\Middleware\EnsureIsDirecteur::class,
            'rh'               => \App\Http\Middleware\EnsureRh::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
