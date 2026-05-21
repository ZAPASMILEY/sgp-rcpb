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
        // Exempter les routes de déconnexion du CSRF
        // (la session peut déjà être expirée quand l'utilisateur clique "déconnecter")
        $middleware->validateCsrfTokens(except: [
            '*/logout',
            'logout',
        ]);

        $middleware->alias([
            'admin'        => \App\Http\Middleware\EnsureAdmin::class,
            'pca'          => \App\Http\Middleware\EnsurePca::class,
            'personnel'    => \App\Http\Middleware\EnsurePersonnel::class,
            'dg'           => \App\Http\Middleware\EnsureDg::class,
            'subordonne'   => \App\Http\Middleware\EnsureIsSubordonne::class,
            'dga_espace'       => \App\Http\Middleware\EnsureIsDga::class,
            'directeur_espace' => \App\Http\Middleware\EnsureIsDirecteur::class,
            'rh'               => \App\Http\Middleware\EnsureRh::class,
            // Middleware pour les trois types de chefs (service, agence, guichet)
            'chef'             => \App\Http\Middleware\EnsureIsChef::class,
            'feature'          => \App\Http\Middleware\FeatureGate::class,
            'periode.ouverte'  => \App\Http\Middleware\PeriodeOuverte::class,
            'annee.ouverte'    => \App\Http\Middleware\AnneeOuverte::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
