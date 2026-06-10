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

/**
 * AppServiceProvider — Point de démarrage de l'application.
 *
 * ═══════════════════════════════════════════════════════════════════════
 * COMMENT FONCTIONNE LE SYSTÈME DE PERMISSIONS (Spatie Laravel-Permission)
 * ═══════════════════════════════════════════════════════════════════════
 *
 * L'application utilise DEUX mécanismes complémentaires pour le contrôle d'accès :
 *
 * 1. ESPACE (qui peut aller OÙ) — géré par les middlewares
 *    ─────────────────────────────────────────────────────
 *    Chaque rôle a son propre espace applicatif (routes séparées) :
 *      - Admin   → routes admin.*    → middleware EnsureIsAdmin
 *      - DG      → routes dg.*       → middleware EnsureIsDg
 *      - DGA     → routes dga.*      → middleware EnsureIsDga
 *      - Directeur → routes directeur.* → middleware EnsureIsDirecteur
 *      - etc.
 *    Le champ `users.role` (ex: 'DG', 'Directeur_Direction') est utilisé
 *    pour identifier l'espace de l'utilisateur.
 *
 * 2. ACTION (qui peut faire QUOI) — géré par les permissions Spatie
 *    ──────────────────────────────────────────────────────────────
 *    Dans chaque contrôleur, `$this->authorize('permission.nom')` vérifie
 *    si l'utilisateur a le droit d'effectuer une action précise.
 *    Exemples : 'evaluations.creer', 'objectifs.assigner', 'admin.alertes'.
 *
 * ARCHITECTURE DU PONT (Bridge Pattern) :
 * ─────────────────────────────────────────
 *    users.role = 'DG'
 *         ↓  (registerRolePermissionBridge lit users.role)
 *    Spatie Role 'DG'
 *         ↓  (hasPermissionTo)
 *    Permission 'evaluations.creer' → true/false
 *         ↓
 *    $this->authorize('evaluations.creer') → passe ou lance 403
 *
 *    Note : on utilise `users.role` comme nom de rôle Spatie directement,
 *    sans appeler $user->assignRole() — le pont fait le lien à la volée.
 *
 * POUR MODIFIER LES PERMISSIONS D'UN RÔLE :
 * ─────────────────────────────────────────
 *    Modifier RolesAndPermissionsSeeder::ROLE_PERMISSIONS puis relancer :
 *    php artisan db:seed --class=RolesAndPermissionsSeeder
 *    OU utiliser l'interface admin → Paramètres → Permissions.
 */
class AppServiceProvider extends ServiceProvider
{
    // Le nom du rôle Spatie correspond EXACTEMENT à la valeur de users.role.
    // Ex : users.role = 'Directeur_Direction' → rôle Spatie 'Directeur_Direction'.
    // Le seeder RolesAndPermissionsSeeder crée un rôle pour chaque valeur possible de users.role.

    public function register(): void {}

    /**
     * Gate::before #1 — Bypass automatique pour l'administrateur système.
     *
     * L'admin (role = 'Admin') a accès à tout sauf les évaluations et objectifs.
     * On l'exclut intentionnellement des évaluations/objectifs car il n'est pas
     * dans la hiérarchie fonctionnelle — il gère le système, pas les RH.
     *
     * Gate::before est exécuté AVANT toute autre vérification de permission.
     * Retourner `true`  → accès accordé immédiatement, aucune autre vérification.
     * Retourner `null`  → passer au handler suivant (Gate::before #2 ou les policies).
     */
    private function registerAdminBypass(): void
    {
        Gate::before(function (User $user, string $ability) {
            // Si l'utilisateur n'est pas admin, ce handler ne le concerne pas.
            if (! $user->isAdmin()) {
                return null; // → Gate::before #2 (pont rôle-permissions)
            }

            // L'admin ne peut pas évaluer ni assigner des objectifs (pas dans la hiérarchie).
            // admin.archives est aussi exclu : la permission doit être vérifiée explicitement.
            if (str_starts_with($ability, 'evaluations.')
                || str_starts_with($ability, 'objectifs.')
                || $ability === 'admin.archives') {
                return null; // → Gate::before #2 → refus si le rôle Admin n'a pas la permission
            }

            return true; // Admin → accès accordé pour tout le reste (agents, structures, alertes…)
        });
    }

    /**
     * Gate::before #2 — Pont users.role → permissions du rôle Spatie.
     *
     * POURQUOI CE PONT EXISTE :
     *   Spatie gère normalement les rôles via une table pivot (model_has_roles).
     *   Mais notre app utilise un champ texte `users.role` pour identifier
     *   l'espace applicatif (routing middleware). Plutôt que de maintenir deux
     *   systèmes en parallèle, ce pont lit `users.role`, trouve le rôle Spatie
     *   correspondant, et délègue la vérification à Spatie.
     *
     * FLUX :
     *   $this->authorize('evaluations.creer')
     *        → Gate appelle ce handler
     *        → on lit $user->role (ex: 'DGA')
     *        → on cherche le rôle Spatie 'DGA' dans la base
     *        → on vérifie si ce rôle a la permission 'evaluations.creer'
     *        → true  : accès accordé
     *        → null  : pas de permission → Gate continue → refus (403)
     *
     * VALEURS DE RETOUR :
     *   true  → permission accordée, Gate s'arrête là.
     *   null  → ce handler ne se prononce pas, Gate continue (permissions directes, policies).
     *   (jamais false : on ne refuse pas explicitement, on laisse le refus par défaut)
     */
    private function registerRolePermissionBridge(): void
    {
        Gate::before(function (User $user, string $ability) {
            // Récupère la valeur du champ users.role (ex: 'DG', 'DGA', 'Chef_Service').
            $spatieRoleName = $user->role;

            // Si l'utilisateur n'a pas de rôle défini, on ne peut pas décider.
            if (! $spatieRoleName) {
                return null; // → Gate continue → refus par défaut
            }

            try {
                // Cherche le rôle Spatie dont le nom = valeur de users.role.
                // Lance RoleDoesNotExist si le seeder n'a pas encore été joué.
                $role = Role::findByName($spatieRoleName, 'web');
            } catch (RoleDoesNotExist) {
                // Le rôle n'existe pas encore en base (seeder non joué) → fallthrough.
                // L'application fonctionnera sans permissions jusqu'au prochain seeder.
                return null;
            }

            // Demande à Spatie si ce rôle possède la permission demandée.
            // true → accordé | null → pas de permission, Gate continue → 403
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
                        'evaluationsEnabled'         => Setting::featureEnabled('evaluations'),
                        'objectifsEnabled'           => Setting::featureEnabled('objectifs'),
                        'evaluationsDisabledMessage' => Setting::featureMessage('evaluations'),
                        'objectifsDisabledMessage'   => Setting::featureMessage('objectifs'),
                    ];
                } catch (\Throwable) {
                    // Table pas encore créée (ex: première migration)
                    $featuresCache = [
                        'evaluationsEnabled'         => true,
                        'objectifsEnabled'           => true,
                        'evaluationsDisabledMessage' => '',
                        'objectifsDisabledMessage'   => '',
                    ];
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
