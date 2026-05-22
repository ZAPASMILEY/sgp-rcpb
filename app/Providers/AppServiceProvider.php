<?php

namespace App\Providers;

use App\Models\Agent;
use App\Models\AuditLog;
use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\Setting;
use App\Models\User;
use App\Observers\AgentObserver;
use App\Observers\EvaluationObserver;
use App\Observers\FormationObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    // Plus de ROLE_MAP — le nom du rôle Spatie est exactement la valeur de users.role.
    // Ex : users.role = 'Directeur_Direction' → rôle Spatie 'Directeur_Direction'.
    // Le seeder RolesAndPermissionsSeeder crée un rôle par valeur users.role.

    public function register(): void {}

    /**
     * Gate::before #1 — Bypass Admin pour tout sauf evaluations.* et objectifs.*
     */
    private function registerAdminBypass(): void
    {
        Gate::before(function (User $user, string $ability) {
            if (! $user->isAdmin()) {
                return null; // pas admin → laisser les autres handlers décider
            }

            // L'admin gère le système mais n'évalue/n'assigne pas d'objectifs.
            if (str_starts_with($ability, 'evaluations.') || str_starts_with($ability, 'objectifs.')) {
                return null;
            }

            return true; // admin bypass pour tout le reste
        });
    }

    /**
     * Gate::before #2 — Pont users.role → permissions du rôle Spatie.
     *
     * Le nom du rôle Spatie est exactement la valeur de users.role.
     * Ex : users.role = 'Directeur_Direction' → rôle Spatie 'Directeur_Direction'.
     *
     * Retourne true  si le rôle a la permission (accordé).
     * Retourne null  sinon (fallthrough : permissions directes, autres handlers).
     */
    private function registerRolePermissionBridge(): void
    {
        Gate::before(function (User $user, string $ability) {
            $spatieRoleName = $user->role;

            if (! $spatieRoleName) {
                return null;
            }

            try {
                $role = Role::findByName($spatieRoleName, 'web');
            } catch (RoleDoesNotExist) {
                // Rôle pas encore créé dans Spatie (seeder non joué) → fallthrough.
                return null;
            }

            return $role->hasPermissionTo($ability) ? true : null;
        });
    }

    public function boot(): void
    {
        $this->registerAdminBypass();
        $this->registerRolePermissionBridge();

        // Journalisation des connexions / déconnexions
        Event::listen(Login::class, function (Login $event) {
            AuditLog::record(
                User::class,
                $event->user->id,
                'login',
                null,
                ['role' => $event->user->role],
                'Connexion — '.$event->user->name.' ('.$event->user->role.')',
            );
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                AuditLog::record(
                    User::class,
                    $event->user->id,
                    'logout',
                    null,
                    null,
                    'Déconnexion — '.$event->user->name,
                );
            }
        });

        // Observers pour l'historique d'audit
        Evaluation::observe(EvaluationObserver::class);
        User::observe(UserObserver::class);
        Agent::observe(AgentObserver::class);
        Formation::observe(FormationObserver::class);

        // Partage l'état des fonctionnalités (feature toggles) avec toutes les vues.
        // Le modèle Setting a un cache en mémoire, donc une seule requête DB par request.
        View::composer('*', function ($view): void {
            static $featuresCache = null;
            if ($featuresCache === null) {
                try {
                    $featuresCache = [
                        'evaluationsEnabled' => Setting::featureEnabled('evaluations'),
                        'objectifsEnabled'   => Setting::featureEnabled('objectifs'),
                    ];
                } catch (\Throwable) {
                    // Table pas encore créée (ex: première migration)
                    $featuresCache = ['evaluationsEnabled' => true, 'objectifsEnabled' => true];
                }
            }
            $view->with($featuresCache);
        });

        // Partage les alertes non lues dans TOUS les layouts qui incluent la cloche (_notif_bell).
        // Ajouter ici tout nouveau layout qui utilise @include('layouts._notif_bell').
        View::composer([
            'layouts.app',
            'layouts.pca',
            'layouts.dg',
            'layouts.dga',
            'layouts.directeur',
            'layouts.personnel',
            'layouts.subordonne',
            'layouts.chef',
            'layouts.rh',
        ], function ($view) {
            $user = auth()->user();
            if ($user) {
                $alertesNonLues      = $user->alertesNonLues()->latest('alertes.created_at')->take(8)->get();
                $alertesNonLuesCount = $user->alertesNonLues()->count();
            } else {
                $alertesNonLues      = collect();
                $alertesNonLuesCount = 0;
            }
            $view->with('alertesNonLues', $alertesNonLues);
            $view->with('alertesNonLuesCount', $alertesNonLuesCount);
        });
    }
}
