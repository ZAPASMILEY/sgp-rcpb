<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // ...existing code...
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            // ...existing code...
        ],

        'api' => [
            // ...existing code...
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        // ...autres middlewares...
        'dg' => \App\Http\Middleware\EnsureDg::class,
        'admin' => \App\Http\Middleware\EnsureAdmin::class,
        'pca' => \App\Http\Middleware\EnsurePca::class,
        'personnel' => \App\Http\Middleware\EnsurePersonnel::class,
    ];
}
