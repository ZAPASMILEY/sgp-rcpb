<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * RolesAndPermissionsSeeder
 *
 * Crée les 9 rôles et les 24 permissions du système SGP-RCPB,
 * puis assigne les permissions par défaut à chaque rôle.
 *
 * Idempotent : peut être rejoué sans créer de doublons (updateOrCreate).
 *
 * Lancer avec : php artisan db:seed --class=RolesAndPermissionsSeeder
 */
class RolesAndPermissionsSeeder extends Seeder
{
    // ── Définition des rôles ───────────────────────────────────────────────

    private const ROLES = [
        ['slug' => 'admin',      'name' => 'Administrateur',          'description' => 'Accès total au système'],
        ['slug' => 'pca',        'name' => 'PCA',                     'description' => 'Président(e) du Conseil d\'Administration'],
        ['slug' => 'dg',         'name' => 'Directeur Général',       'description' => 'Directeur(trice) Général(e) de la faîtière'],
        ['slug' => 'dga',        'name' => 'DGA',                     'description' => 'Directeur(trice) Général(e) Adjoint(e)'],
        ['slug' => 'directeur',  'name' => 'Directeur',               'description' => 'Directeur de Direction, de Caisse ou de Délégation Technique'],
        ['slug' => 'chef',       'name' => 'Chef',                    'description' => 'Chef de Service, d\'Agence, de Guichet ou Assistante DG'],
        ['slug' => 'conseiller', 'name' => 'Conseiller',              'description' => 'Conseiller DG — accès lecture niveau DG, sans subordonnés'],
        ['slug' => 'secretaire', 'name' => 'Secrétaire',              'description' => 'Secrétaire de Direction, de Caisse ou de DT'],
        ['slug' => 'agent',      'name' => 'Agent',                   'description' => 'Agent de base (caissier, conseiller, etc.)'],
    ];

    // ── Définition des permissions ─────────────────────────────────────────

    private const PERMISSIONS = [
        // Agents / Personnel
        ['name' => 'agents.voir',      'slug' => 'Voir le personnel'],
        ['name' => 'agents.creer',     'slug' => 'Créer un agent'],
        ['name' => 'agents.modifier',  'slug' => 'Modifier un agent'],
        ['name' => 'agents.supprimer', 'slug' => 'Supprimer un agent'],
        ['name' => 'agents.affecter',  'slug' => 'Affecter un agent à une structure'],

        // Structures organisationnelles
        ['name' => 'structures.voir',     'slug' => 'Voir les structures'],
        ['name' => 'structures.creer',    'slug' => 'Créer une structure'],
        ['name' => 'structures.modifier', 'slug' => 'Modifier une structure'],

        // Évaluations
        ['name' => 'evaluations.creer',       'slug' => 'Créer une évaluation'],
        ['name' => 'evaluations.soumettre',   'slug' => 'Soumettre une évaluation'],
        ['name' => 'evaluations.accepter',    'slug' => 'Accepter ou refuser une évaluation reçue'],
        ['name' => 'evaluations.voir-propres','slug' => 'Voir ses propres évaluations'],
        ['name' => 'evaluations.voir-equipe', 'slug' => 'Voir les évaluations de son équipe'],
        ['name' => 'evaluations.exporter-pdf','slug' => 'Exporter une évaluation en PDF'],

        // Objectifs / Fiches d'objectifs
        ['name' => 'objectifs.assigner',    'slug' => 'Assigner une fiche d\'objectifs'],
        ['name' => 'objectifs.accepter',    'slug' => 'Accepter ou refuser une fiche d\'objectifs reçue'],
        ['name' => 'objectifs.avancement',  'slug' => 'Mettre à jour l\'avancement d\'une fiche'],
        ['name' => 'objectifs.voir-propres','slug' => 'Voir ses propres fiches d\'objectifs'],
        ['name' => 'objectifs.voir-equipe', 'slug' => 'Voir les fiches d\'objectifs de son équipe'],

        // Administration
        ['name' => 'admin.roles',      'slug' => 'Gérer les rôles et permissions'],
        ['name' => 'admin.users',      'slug' => 'Gérer les comptes utilisateurs'],
        ['name' => 'admin.annees',     'slug' => 'Gérer les années d\'exercice'],
        ['name' => 'admin.activites',  'slug' => 'Voir les logs d\'activité'],
        ['name' => 'admin.alertes',    'slug' => 'Créer et gérer les alertes'],
    ];

    // ── Matrice rôle → permissions par défaut ─────────────────────────────
    //
    // Note : certaines permissions sont INTENTIONNELLEMENT absentes de certains
    // rôles pour être accordées manuellement (ex: evaluations.voir-equipe pour
    // conseiller, agents.voir pour secretaire selon contexte, etc.).

    private const ROLE_PERMISSIONS = [

        'admin' => [
            'agents.voir', 'agents.creer', 'agents.modifier', 'agents.supprimer', 'agents.affecter',
            'structures.voir', 'structures.creer', 'structures.modifier',
            'evaluations.creer', 'evaluations.soumettre', 'evaluations.voir-propres',
            'evaluations.voir-equipe', 'evaluations.exporter-pdf',
            'objectifs.assigner', 'objectifs.voir-propres', 'objectifs.voir-equipe',
            'admin.roles', 'admin.users', 'admin.annees', 'admin.activites', 'admin.alertes',
        ],

        'pca' => [
            'agents.voir',
            'structures.voir',
            'evaluations.creer', 'evaluations.soumettre',
            'evaluations.voir-equipe', 'evaluations.exporter-pdf',
            'objectifs.assigner', 'objectifs.voir-equipe',
        ],

        'dg' => [
            'agents.voir', 'agents.creer', 'agents.modifier', 'agents.affecter',
            'structures.voir', 'structures.creer', 'structures.modifier',
            'evaluations.creer', 'evaluations.soumettre',
            'evaluations.accepter', 'evaluations.voir-propres',
            'evaluations.voir-equipe', 'evaluations.exporter-pdf',
            'objectifs.assigner', 'objectifs.accepter', 'objectifs.avancement',
            'objectifs.voir-propres', 'objectifs.voir-equipe',
            'admin.annees', 'admin.activites', 'admin.alertes',
        ],

        'dga' => [
            'agents.voir', 'agents.affecter',
            'structures.voir', 'structures.modifier',
            'evaluations.creer', 'evaluations.soumettre',
            'evaluations.accepter', 'evaluations.voir-propres',
            'evaluations.voir-equipe', 'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.avancement',
            'objectifs.voir-propres', 'objectifs.voir-equipe',
            'admin.alertes',
        ],

        'directeur' => [
            'agents.voir', 'agents.affecter',
            'structures.voir', 'structures.modifier',
            'evaluations.creer', 'evaluations.soumettre',
            'evaluations.accepter', 'evaluations.voir-propres',
            'evaluations.voir-equipe', 'evaluations.exporter-pdf',
            'objectifs.assigner', 'objectifs.accepter', 'objectifs.avancement',
            'objectifs.voir-propres', 'objectifs.voir-equipe',
        ],

        'chef' => [
            'agents.voir',
            'structures.voir',
            'evaluations.creer', 'evaluations.soumettre',
            'evaluations.accepter', 'evaluations.voir-propres',
            'evaluations.voir-equipe', 'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.avancement',
            'objectifs.voir-propres', 'objectifs.voir-equipe',
        ],

        // Permissions accordées manuellement selon le conseiller :
        //   evaluations.voir-equipe → NON par défaut
        'conseiller' => [
            'agents.voir',
            'structures.voir',
            'evaluations.accepter', 'evaluations.voir-propres',
            'evaluations.exporter-pdf',
            'objectifs.voir-propres',
        ],

        'secretaire' => [
            'agents.voir',
            'structures.voir',
            'evaluations.accepter', 'evaluations.voir-propres',
            'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.avancement',
            'objectifs.voir-propres',
        ],

        'agent' => [
            'evaluations.accepter', 'evaluations.voir-propres',
            'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.avancement',
            'objectifs.voir-propres',
        ],
    ];

    // ── Exécution ──────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->command->info('Création des permissions...');
        $permissions = [];
        foreach (self::PERMISSIONS as $data) {
            $permissions[$data['name']] = Permission::updateOrCreate(
                ['name' => $data['name']],
                ['slug' => $data['slug']],
            );
        }

        $this->command->info('Création des rôles...');
        foreach (self::ROLES as $data) {
            $role = Role::updateOrCreate(
                ['slug' => $data['slug']],
                ['name' => $data['name'], 'description' => $data['description']],
            );

            // Associer les permissions par défaut (sync sans détacher les manuelles)
            $permissionIds = collect(self::ROLE_PERMISSIONS[$data['slug']] ?? [])
                ->map(fn (string $name) => $permissions[$name]->id)
                ->values()
                ->all();

            $role->permissions()->sync($permissionIds);

            $count = count($permissionIds);
            $this->command->line("  → {$data['slug']} : {$count} permissions");
        }

        $this->command->info('Rôles et permissions créés avec succès.');
    }
}
