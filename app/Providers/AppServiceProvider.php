<?php

namespace App\Providers;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
     * Enregistre les 24 permissions du système comme Gates Laravel.
     *
     * - Les Admins contournent toutes les vérifications (Gate::before).
     * - Chaque permission est vérifiée via User::hasPermission() qui consulte
     *   à la fois les permissions directes (permission_user) et celles héritées
     *   des rôles (role_user → roles_has_permissions).
     */
    private function registerPermissionGates(): void
    {
        // Les administrateurs ont accès à tout sans vérification.
        Gate::before(fn (User $user, string $ability) => $user->isAdmin() ? true : null);

        $permissions = [
            // Personnel / Agents
            'agents.voir', 'agents.creer', 'agents.modifier', 'agents.supprimer', 'agents.affecter',
            // Structures
            'structures.voir', 'structures.creer', 'structures.modifier',
            // Évaluations
            'evaluations.creer', 'evaluations.soumettre', 'evaluations.accepter',
            'evaluations.voir-propres', 'evaluations.voir-equipe', 'evaluations.exporter-pdf',
            // Objectifs
            'objectifs.assigner', 'objectifs.accepter', 'objectifs.avancement',
            'objectifs.voir-propres', 'objectifs.voir-equipe',
            // Administration
            'admin.roles', 'admin.users', 'admin.annees', 'admin.activites', 'admin.alertes',
        ];

        foreach ($permissions as $perm) {
            Gate::define($perm, fn (User $user) => $user->hasPermission($perm));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPermissionGates();

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
