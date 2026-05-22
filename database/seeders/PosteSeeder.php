<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Remplit la table `postes` avec les intitulés de poste
 * prédéfinis par rôle/fonction d'agent.
 *
 * Idempotent : ignoré si des lignes existent déjà.
 */
class PosteSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('postes')->count() > 0) {
            return;
        }

        $now = now();

        $postes = [

            // ── Agents de guichet / caisse / agence ──────────────────────────
            ['fonction' => 'Agent', 'libelle' => 'Caissier(ère)'],
            ['fonction' => 'Agent', 'libelle' => 'Caissier principal'],
            ['fonction' => 'Agent', 'libelle' => 'Caissière principale'],
            ['fonction' => 'Agent', 'libelle' => 'Chargé(e) de crédit'],
            ['fonction' => 'Agent', 'libelle' => 'Chargé(e) de compte'],
            ['fonction' => 'Agent', 'libelle' => 'Chargé(e) de clientèle'],
            ['fonction' => 'Agent', 'libelle' => 'Chargé(e) de recouvrement'],
            ['fonction' => 'Agent', 'libelle' => 'Animateur(trice) de crédit'],
            ['fonction' => 'Agent', 'libelle' => 'Analyste de crédit'],
            ['fonction' => 'Agent', 'libelle' => 'Gestionnaire de portefeuille'],
            ['fonction' => 'Agent', 'libelle' => 'Guichetier(ère)'],
            ['fonction' => 'Agent', 'libelle' => 'Agent(e) de saisie'],
            ['fonction' => 'Agent', 'libelle' => 'Agent(e) de terrain'],
            ['fonction' => 'Agent', 'libelle' => 'Préposé(e) aux dépôts'],
            ['fonction' => 'Agent', 'libelle' => 'Agent(e) de sécurité'],
            ['fonction' => 'Agent', 'libelle' => 'Agent(e) d\'accueil'],
            ['fonction' => 'Agent', 'libelle' => 'Technicien(ne) de crédit'],
            ['fonction' => 'Agent', 'libelle' => 'Chargé(e) des opérations'],
            ['fonction' => 'Agent', 'libelle' => 'Comptable'],
            ['fonction' => 'Agent', 'libelle' => 'Aide-comptable'],
            ['fonction' => 'Agent', 'libelle' => 'Chargé(e) informatique'],
            ['fonction' => 'Agent', 'libelle' => 'Technicien(ne) de surface'],
            ['fonction' => 'Agent', 'libelle' => 'Chauffeur'],
            ['fonction' => 'Agent', 'libelle' => 'Coursier(ère)'],

            // ── Conseillers DG ───────────────────────────────────────────────
            ['fonction' => 'Conseiller DG', 'libelle' => 'Conseiller(ère) juridique'],
            ['fonction' => 'Conseiller DG', 'libelle' => 'Conseiller(ère) technique'],
            ['fonction' => 'Conseiller DG', 'libelle' => 'Conseiller(ère) en développement institutionnel'],
            ['fonction' => 'Conseiller DG', 'libelle' => 'Conseiller(ère) en stratégie'],
            ['fonction' => 'Conseiller DG', 'libelle' => 'Conseiller(ère) financier(ère)'],
            ['fonction' => 'Conseiller DG', 'libelle' => 'Conseiller(ère) en communication'],
            ['fonction' => 'Conseiller DG', 'libelle' => 'Conseiller(ère) en audit interne'],

            // ── Chefs de service ─────────────────────────────────────────────
            ['fonction' => 'Chef de Service', 'libelle' => 'Chef du Service Opérations'],
            ['fonction' => 'Chef de Service', 'libelle' => 'Chef du Service Crédit'],
            ['fonction' => 'Chef de Service', 'libelle' => 'Chef du Service Comptabilité'],
            ['fonction' => 'Chef de Service', 'libelle' => 'Chef du Service Informatique'],
            ['fonction' => 'Chef de Service', 'libelle' => 'Chef du Service RH'],
            ['fonction' => 'Chef de Service', 'libelle' => 'Chef du Service Contrôle Interne'],
            ['fonction' => 'Chef de Service', 'libelle' => 'Chef du Service Commercial'],

            // ── Chefs d'agence ───────────────────────────────────────────────
            ["fonction" => "Chef d'Agence", 'libelle' => "Chef d'Agence"],
            ["fonction" => "Chef d'Agence", 'libelle' => "Chef d'Agence Principal(e)"],
            ["fonction" => "Chef d'Agence", 'libelle' => "Directeur(trice) d'Agence"],

            // ── Chefs de guichet ─────────────────────────────────────────────
            ['fonction' => 'Chef de Guichet', 'libelle' => 'Chef de Guichet'],
            ['fonction' => 'Chef de Guichet', 'libelle' => 'Responsable de Guichet'],

            // ── Secrétaires (toutes catégories) ─────────────────────────────
            ['fonction' => 'Secrétaire de Direction', 'libelle' => 'Secrétaire de Direction'],
            ['fonction' => 'Secrétaire de Direction', 'libelle' => 'Secrétaire Exécutif(ve)'],
            ['fonction' => 'Secrétaire Technique',    'libelle' => 'Secrétaire Technique'],
            ['fonction' => 'Secrétaire de Caisse',    'libelle' => 'Secrétaire de Caisse'],
            ["fonction" => "Secrétaire d'Agence",     'libelle' => "Secrétaire d'Agence"],
            ['fonction' => 'Secrétaire DGA',           'libelle' => 'Secrétaire du DGA'],
            ['fonction' => 'Assistante DG',            'libelle' => 'Assistante de Direction'],
            ['fonction' => 'Assistante DG',            'libelle' => 'Assistante Exécutive'],

            // ── Directeurs ───────────────────────────────────────────────────
            ['fonction' => 'Directeur de Direction', 'libelle' => 'Directeur Administratif et Financier'],
            ['fonction' => 'Directeur de Direction', 'libelle' => 'Directeur des Ressources Humaines'],
            ['fonction' => 'Directeur de Direction', 'libelle' => 'Directeur des Opérations'],
            ['fonction' => 'Directeur Technique',    'libelle' => 'Directeur Technique Régional'],
            ['fonction' => 'Directeur de Caisse',    'libelle' => 'Directeur de Caisse Populaire'],
            ['fonction' => 'Directeur de Caisse',    'libelle' => 'Directeur Principal de Caisse'],

            // ── DG / DGA / PCA ───────────────────────────────────────────────
            ['fonction' => 'Directeur Général', 'libelle' => 'Directeur Général'],
            ['fonction' => 'DGA',               'libelle' => 'Directeur Général Adjoint'],
            ['fonction' => 'PCA',               'libelle' => 'Président du Conseil d\'Administration'],
        ];

        foreach ($postes as &$p) {
            $p['created_at'] = $now;
            $p['updated_at'] = $now;
        }

        DB::table('postes')->insert($postes);
    }
}
