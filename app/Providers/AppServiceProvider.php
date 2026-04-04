<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.app', 'layouts.pca'], function ($view) {
            $user = auth()->user();
            if ($user) {
                $alertesNonLues = $user->alertesNonLues()
                    ->latest('alertes.created_at')
                    ->take(8)
                    ->get();
                $alertesNonLuesCount = $user->alertesNonLues()->count();
            } else {
                $alertesNonLues = collect();
                $alertesNonLuesCount = 0;
            }
            $view->with('alertesNonLues', $alertesNonLues);
            $view->with('alertesNonLuesCount', $alertesNonLuesCount);
        });
    }
}
