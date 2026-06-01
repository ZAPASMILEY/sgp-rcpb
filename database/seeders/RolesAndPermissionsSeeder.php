<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * RolesAndPermissionsSeeder
 *
 * Crée les permissions du système SGP-RCPB et assigne les permissions
 * par défaut à chaque rôle.
 *
 * Règle de nommage : le nom du rôle Spatie = valeur exacte de users.role.
 * Ex : users.role = 'Directeur_Direction' → rôle Spatie 'Directeur_Direction'.
 *
 * Idempotent : peut être rejoué sans créer de doublons (firstOrCreate + syncPermissions).
 *
 * Lancer avec : php artisan db:seed --class=RolesAndPermissionsSeeder
 */
class RolesAndPermissionsSeeder extends Seeder
{
    // ── Catalogue des permissions ──────────────────────────────────────────
    //
    // Format : 'module.action'  — utilisé dans $this->authorize('xxx')
    // et $user->can('xxx') dans les contrôleurs.
    //
    private const PERMISSIONS = [

        // ── Personnel / Agents ─────────────────────────────────────────────
        'agents.voir',       // Consulter la liste du personnel (menu latéral "Gestion")

        // ── Structures organisationnelles ──────────────────────────────────
        'structures.voir',   // Consulter les structures (menu latéral "Gestion")

        // ── Évaluations ────────────────────────────────────────────────────
        'evaluations.creer',        // Rédiger une fiche d'évaluation
        'evaluations.soumettre',    // Soumettre une évaluation à la personne évaluée
        'evaluations.accepter',     // Accepter ou refuser une évaluation reçue
        'evaluations.voir-equipe',  // Consulter les évaluations de son équipe
        'evaluations.voir-reseau',  // Consulter toutes les évaluations du réseau (DG)
        'evaluations.exporter-pdf', // Télécharger une évaluation en PDF

        // ── Fiches d'objectifs ─────────────────────────────────────────────
        'objectifs.assigner',    // Créer et assigner une fiche d'objectifs
        'objectifs.accepter',    // Accepter ou refuser une fiche d'objectifs reçue
        'objectifs.contester',   // Contester un objectif individuel d'une fiche reçue
        'objectifs.avancement',  // Mettre à jour le % d'avancement
        'objectifs.voir-equipe', // Consulter les fiches de son équipe

        // ── Formations ────────────────────────────────────────────────────
        'formations.assigner', // Créer, modifier et supprimer des formations

        // ── Rapports & Statistiques ────────────────────────────────────────
        'statistiques.voir', // Consulter la page statistiques du personnel (notes)
        'tableaux.voir',     // Consulter et exporter les tableaux Excel personnalisés

        // ── Administration ─────────────────────────────────────────────────
        'admin.activites', // Consulter les logs d'activité
        'admin.alertes',   // Créer et diffuser des alertes
    ];

    // ── Matrice rôle → permissions ─────────────────────────────────────────
    //
    // Clé = valeur exacte de users.role (sensible à la casse).
    // La Gate::before 'admin bypass' dans AppServiceProvider court-circuite
    // évaluations.* et objectifs.* pour les admins → pas besoin de les lister.
    //
    private const ROLE_PERMISSIONS = [

        // ── Administrateur système ─────────────────────────────────────────
        'Admin' => [
            'agents.voir',
            'structures.voir',
            'evaluations.exporter-pdf',
            'admin.activites', 'admin.alertes',
        ],

        // ── PCA — évalue le DG, lui assigne des objectifs ─────────────────
        'PCA' => [
            'agents.voir',
            'structures.voir',
            'evaluations.creer', 'evaluations.soumettre', 'evaluations.exporter-pdf',
            'evaluations.voir-equipe',
            'objectifs.assigner', 'objectifs.voir-equipe',
        ],

        // ── Directeur Général ──────────────────────────────────────────────
        'DG' => [
            'agents.voir',
            'structures.voir',
            'evaluations.creer', 'evaluations.soumettre', 'evaluations.accepter', 'evaluations.exporter-pdf',
            'evaluations.voir-equipe', 'evaluations.voir-reseau',
            'objectifs.assigner', 'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement',
            'objectifs.voir-equipe',
            'admin.activites', 'admin.alertes',
        ],

        // ── Directeur Général Adjoint ──────────────────────────────────────
        'DGA' => [
            'agents.voir',
            'structures.voir',
            'evaluations.creer', 'evaluations.soumettre', 'evaluations.accepter', 'evaluations.exporter-pdf',
            'evaluations.voir-equipe',
            'objectifs.assigner', 'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement',
            'objectifs.voir-equipe',
            'admin.alertes',
        ],

        // ── Directeur de Direction ─────────────────────────────────────────
        'Directeur_Direction' => [
            'agents.voir',
            'structures.voir',
            'evaluations.creer', 'evaluations.soumettre', 'evaluations.accepter', 'evaluations.exporter-pdf',
            'evaluations.voir-equipe',
            'objectifs.assigner', 'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement',
            'objectifs.voir-equipe',
        ],

        // ── Directeur Technique ────────────────────────────────────────────
        'Directeur_Technique' => [
            'agents.voir',
            'structures.voir',
            'evaluations.creer', 'evaluations.soumettre', 'evaluations.accepter', 'evaluations.exporter-pdf',
            'evaluations.voir-equipe',
            'objectifs.assigner', 'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement',
            'objectifs.voir-equipe',
        ],

        // ── Directeur de Caisse ────────────────────────────────────────────
        'Directeur_Caisse' => [
            'agents.voir',
            'structures.voir',
            'evaluations.creer', 'evaluations.soumettre', 'evaluations.accepter', 'evaluations.exporter-pdf',
            'evaluations.voir-equipe',
            'objectifs.assigner', 'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement',
            'objectifs.voir-equipe',
        ],

        // ── Chef de Service ────────────────────────────────────────────────
        'Chef_Service' => [
            'agents.voir',
            'structures.voir',
            'evaluations.creer', 'evaluations.soumettre', 'evaluations.accepter', 'evaluations.exporter-pdf',
            'evaluations.voir-equipe',
            'objectifs.assigner', 'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement',
            'objectifs.voir-equipe',
        ],

        // ── Chef d'Agence ──────────────────────────────────────────────────
        'Chef_Agence' => [
            'agents.voir',
            'structures.voir',
            'evaluations.creer', 'evaluations.soumettre', 'evaluations.accepter', 'evaluations.exporter-pdf',
            'evaluations.voir-equipe',
            'objectifs.assigner', 'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement',
            'objectifs.voir-equipe',
        ],

        // ── Chef de Guichet ────────────────────────────────────────────────
        'Chef_Guichet' => [
            'agents.voir',
            'structures.voir',
            'evaluations.accepter', 'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement',
        ],

        // ── Assistante DG ──────────────────────────────────────────────────
        'Assistante_Dg' => [
            'agents.voir',
            'structures.voir',
            'evaluations.creer', 'evaluations.soumettre', 'evaluations.accepter', 'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement',
        ],

        // ── Conseiller DG ──────────────────────────────────────────────────
        'Conseillers_Dg' => [
            'agents.voir',
            'structures.voir',
            'evaluations.accepter', 'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.contester',
        ],

        // ── Secrétaire Assistante DG ───────────────────────────────────────
        'Secretaire_Assistante' => [
            'agents.voir',
            'structures.voir',
            'evaluations.accepter', 'evaluations.voir-equipe', 'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement', 'objectifs.voir-equipe',
        ],

        // ── Secrétaire de Direction ────────────────────────────────────────
        'Secretaire_Direction' => [
            'agents.voir',
            'structures.voir',
            'evaluations.accepter', 'evaluations.voir-equipe', 'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement', 'objectifs.voir-equipe',
        ],

        // ── Secrétaire Technique ───────────────────────────────────────────
        'Secretaire_Technique' => [
            'agents.voir',
            'structures.voir',
            'evaluations.accepter', 'evaluations.voir-equipe', 'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement', 'objectifs.voir-equipe',
        ],

        // ── Secrétaire de Caisse ───────────────────────────────────────────
        'Secretaire_Caisse' => [
            'agents.voir',
            'structures.voir',
            'evaluations.accepter', 'evaluations.voir-equipe', 'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement', 'objectifs.voir-equipe',
        ],

        // ── Secrétaire d'Agence ────────────────────────────────────────────
        'Secretaire_Agence' => [
            'agents.voir',
            'structures.voir',
            'evaluations.accepter', 'evaluations.voir-equipe', 'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement', 'objectifs.voir-equipe',
        ],

        // ── Responsable RH ─────────────────────────────────────────────────
        'RH' => [
            'agents.voir',
            'structures.voir',
            'evaluations.voir-equipe', 'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement',
            'formations.assigner',
        ],

        // ── Agent de base ──────────────────────────────────────────────────
        'Agent' => [
            'evaluations.accepter', 'evaluations.exporter-pdf',
            'objectifs.accepter', 'objectifs.contester', 'objectifs.avancement',
        ],
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 1. Supprimer les anciens rôles groupés EN PREMIER ─────────────
        // IMPORTANT : cette étape doit précéder la création, car MySQL est
        // insensible à la casse (collation ci) — 'dg' et 'DG' sont le même
        // enregistrement. Si on crée d'abord, firstOrCreate('DG') retourne
        // l'ancien 'dg', et le nettoyage le supprime ensuite.
        $legacyRoles = ['admin', 'pca', 'dg', 'dga', 'directeur', 'chef', 'conseiller', 'secretaire', 'agent'];
        foreach ($legacyRoles as $legacyName) {
            // Utilise une comparaison binaire (BINARY) pour être sensible à la casse
            // et ne supprimer QUE l'ancien nom minuscule, pas le nouveau.
            $legacy = Role::whereRaw('BINARY name = ?', [$legacyName])->where('guard_name', 'web')->first();
            if ($legacy) {
                $legacy->syncPermissions([]);
                $legacy->delete();
                $this->command->warn("  ✗ Ancien rôle '{$legacyName}' supprimé");
            }
        }

        // ── 2. Créer toutes les permissions ───────────────────────────────
        $this->command->info('Création des permissions...');
        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        $this->command->info('  → '.count(self::PERMISSIONS).' permissions OK');

        // ── 3. Créer les rôles et assigner leurs permissions ──────────────
        $this->command->info('Création des rôles et assignation des permissions...');
        foreach (self::ROLE_PERMISSIONS as $roleName => $permissionNames) {
            // Recherche sensible à la casse pour éviter les collisions MySQL ci
            $role = Role::whereRaw('BINARY name = ?', [$roleName])->where('guard_name', 'web')->first()
                ?? Role::create(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissionNames);
            $this->command->line("  → {$roleName} : ".count($permissionNames).' permissions');
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->command->info('✓ Rôles et permissions créés avec succès.');
    }
}
