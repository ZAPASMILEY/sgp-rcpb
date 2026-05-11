<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

/**
 * Crée toutes les directions et services selon l'organigramme réel du RCPB.
 * Chaque structure a un responsable (agent + user).
 * Mot de passe par défaut : 11111111
 */
class OrganisationSeeder extends Seeder
{
    private Carbon $now;

    public function run(): void
    {
        $this->now = now();

        // ── 1. Renommer les directions existantes ────────────────────────────
        DB::table('directions')->where('id', 1)->update([
            'nom'                => 'Direction Générale',
            'directeur_agent_id' => 2, // DG Issa Sawadogo
        ]);
        DB::table('directions')->where('id', 2)->update([
            'nom' => 'Direction des Ressources Humaines',
        ]);
        DB::table('directions')->where('id', 3)->update([
            'nom' => 'Direction des Finances et de la Comptabilité',
        ]);
        DB::table('directions')->where('id', 4)->update([
            'nom' => "Direction des Systèmes d'Information et de la Digitalisation",
        ]);

        // ── 2. Responsables pour directions existantes sans directeur ────────
        $rhAgentId      = $this->newAgent('Compaoré', 'Awa',      'Directeur RH',       'drh@rcpb.bf',  'Directeur_Technique');
        $financeAgentId = $this->newAgent('Ouédraogo','Blaise',   'Directeur Finances', 'dfc@rcpb.bf',  'Directeur_Technique');
        $siAgentId      = $this->newAgent('Zongo',    'Pascal',   'Directeur SI',       'dsi@rcpb.bf',  'Directeur_Technique');

        DB::table('directions')->where('id', 2)->update(['directeur_agent_id' => $rhAgentId]);
        DB::table('directions')->where('id', 3)->update(['directeur_agent_id' => $financeAgentId]);
        DB::table('directions')->where('id', 4)->update(['directeur_agent_id' => $siAgentId]);

        DB::table('agents')->where('id', $rhAgentId)->update(['direction_id' => 2]);
        DB::table('agents')->where('id', $financeAgentId)->update(['direction_id' => 3]);
        DB::table('agents')->where('id', $siAgentId)->update(['direction_id' => 4]);

        // ── 3. Créer les directions manquantes ───────────────────────────────
        $dgaId   = $this->ensureDirection('Direction du Directeur Général Adjoint', 3); // DGA agent_id=3
        $engId   = $this->ensureDirection('Direction des Engagements',
            $this->newAgent('Kaboré',  'Souleymane','Directeur des Engagements',  'engagement@rcpb.bf', 'Directeur_Technique'));
        $mktId   = $this->ensureDirection('Direction Marketing et Commerciale',
            $this->newAgent('Sawadogo','Aminata',   'Directeur Marketing',         'marketing@rcpb.bf',  'Directeur_Technique'));
        $auditId = $this->ensureDirection("Direction de l'Audit Interne",
            $this->newAgent('Traoré',  'Adama',     'Directeur Audit Interne',    'audit@rcpb.bf',      'Directeur_Technique'));

        // DGA rattaché à sa direction
        DB::table('agents')->where('id', 3)->update(['direction_id' => $dgaId]);

        // ── 4. Services Direction Générale (id=1) ────────────────────────────
        $this->newService('Secrétariat Administratif',                              1,
            $this->newAgent('Koné',    'Salimata', 'Chef Secrétariat Administratif',          'sec.admin@rcpb.bf',     'Chef_Service'));
        $this->newService('Chargés de la Transformation Organisationnelle',         1,
            $this->newAgent('Ouattara','Moussa',   'Chargé Transformation Organisationnelle', 'transform@rcpb.bf',     'Chef_Service'));
        $this->newService('Chargés de missions',                                    1,
            $this->newAgent('Barry',   'Fatouma',  'Chargé de missions',                      'missions@rcpb.bf',      'Chef_Service'));
        $this->newService('Chargé de la Sécurité Informatique',                     1,
            $this->newAgent('Ilboudo', 'Rémi',     'Chargé Sécurité Informatique DG',         'secu.dg@rcpb.bf',       'Chef_Service'));
        $this->newService('Service Communication et Partenariats',                  1,
            $this->newAgent('Nikiéma', 'Joëlle',   'Chef Communication et Partenariats',      'communication@rcpb.bf', 'Chef_Service'));

        // ── 5. Services Direction DGA ─────────────────────────────────────────
        $this->newService('Service du Contrôle Permanent',                          $dgaId,
            $this->newAgent('Barro',   'Inoussa',  'Chef Contrôle Permanent',                 'ctrl.permanent@rcpb.bf','Chef_Service'));
        $this->newService('Service Risque et Conformité',                           $dgaId,
            $this->newAgent('Tapsoba', 'Evariste', 'Chef Risque et Conformité',               'risque@rcpb.bf',        'Chef_Service'));
        $this->newService('Service Planification et Vie Coopérative',               $dgaId,
            $this->newAgent('Sankara', 'Mariam',   'Chef Planification et Vie Coopérative',   'planification@rcpb.bf', 'Chef_Service'));

        // ── 6. Services Direction RH (id=2) ──────────────────────────────────
        $this->newService("Service de l'Administration du Personnel",               2,
            $this->newAgent('Diallo',  'Rokiatou', 'Chef Administration du Personnel',        'admin.rh@rcpb.bf',      'Chef_Service'));
        $this->newService('Service du Recrutement et de la Gestion des Carrières',  2,
            $this->newAgent('Kinda',   'Abdoulaye','Chef Recrutement et Carrières',           'recrutement@rcpb.bf',   'Chef_Service'));
        $this->newService('Service de la Formation et des Relations Sociales',      2,
            $this->newAgent('Ouédraogo','Clarisse','Chef Formation et Relations Sociales',    'formation@rcpb.bf',     'Chef_Service'));

        // ── 7. Services Direction Finances (id=3) ─────────────────────────────
        $this->newService('Service Comptabilité, Budget et Reporting',              3,
            $this->newAgent('Nana',    'Thierry',  'Chef Comptabilité Budget Reporting',      'compta@rcpb.bf',        'Chef_Service'));
        $this->newService('Service de Trésorerie et des Flux Financiers',           3,
            $this->newAgent('Sawadogo','Rasmané',  'Chef Trésorerie et Flux Financiers',      'tresorerie@rcpb.bf',    'Chef_Service'));
        $this->newService('Service des Moyens Généraux et des Archives',            3,
            $this->newAgent('Yago',    'Clémentine','Chef Moyens Généraux et Archives',      'moyens@rcpb.bf',        'Chef_Service'));

        // ── 8. Services Direction Engagements ────────────────────────────────
        $this->newService("Service de l'Analyse et du Pilotage du Portefeuille Crédit", $engId,
            $this->newAgent('Ouédraogo','Bertrand','Chef Analyse Portefeuille Crédit',        'credit@rcpb.bf',        'Chef_Service'));
        $this->newService('Service de Recouvrement et de Soutien aux CP',           $engId,
            $this->newAgent('Zida',    'Aïcha',    'Chef Recouvrement et Soutien CP',         'recouvrement@rcpb.bf',  'Chef_Service'));

        // ── 9. Services Direction Marketing ──────────────────────────────────
        $this->newService('Service Pilotage Commercial et Appui aux CP',            $mktId,
            $this->newAgent('Compaoré','Flavien',  'Chef Pilotage Commercial',                'pilotage.mkt@rcpb.bf',  'Chef_Service'));
        $this->newService('Service Marketing et Développement Produits et Innovation', $mktId,
            $this->newAgent('Belem',   'Sylvie',   'Chef Marketing et Développement Produits','marketing.dev@rcpb.bf', 'Chef_Service'));

        // ── 10. Services Direction SI (id=4) ──────────────────────────────────
        $this->newService("Service des Infrastructures et de la Sécurité Informatique", 4,
            $this->newAgent('Kabré',   'Norbert',  'Chef Infrastructures et Sécurité SI',     'infra@rcpb.bf',         'Chef_Service'));
        $this->newService('Service de la Digitalisation',                           4,
            $this->newAgent('Ouédraogo','Ulrich',  'Chef Digitalisation',                     'digital@rcpb.bf',       'Chef_Service'));
        $this->newService('Service Exploitation et Support',                        4,
            $this->newAgent('Yonaba',  'Patricia', 'Chef Exploitation et Support',            'support@rcpb.bf',       'Chef_Service'));

        // ── 11. Services Direction Audit ──────────────────────────────────────
        $this->newService("Pool d'Auditeurs Internes",                              $auditId,
            $this->newAgent('Badolo',  'Serge',    "Chef Pool Auditeurs Internes",            'audit.pool@rcpb.bf',    'Chef_Service'));

        // ── 12. Supprimer les anciens services de test ────────────────────────
        DB::table('services')->whereIn('nom', [
            'Service Crédit Nord', 'Service Épargne Est', 'Service Comptabilité Ouest'
        ])->delete();

        $this->command->info('Organisation RCPB créée avec succès.');
        $this->command->info('  Directions : ' . DB::table('directions')->count());
        $this->command->info('  Services   : ' . DB::table('services')->count());
        $this->command->info('  Agents     : ' . DB::table('agents')->count());
        $this->command->info('  Users      : ' . DB::table('users')->count());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Crée un agent + son compte user, retourne l'agent_id */
    private function newAgent(string $nom, string $prenom, string $fonction, string $email, string $role): int
    {
        $agentId = DB::table('agents')->insertGetId([
            'nom'        => $nom,
            'prenom'     => $prenom,
            'email'      => $email,
            'fonction'   => $fonction,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        DB::table('users')->insert([
            'name'                 => trim($prenom . ' ' . $nom),
            'email'                => $email,
            'password'             => Hash::make('11111111'),
            'role'                 => $role,
            'agent_id'             => $agentId,
            'must_change_password' => true,
            'created_at'           => $this->now,
            'updated_at'           => $this->now,
        ]);

        return $agentId;
    }

    /** Crée une direction si elle n'existe pas, retourne son id */
    private function ensureDirection(string $nom, int $directeurAgentId): int
    {
        $existing = DB::table('directions')->where('nom', $nom)->first();
        if ($existing) {
            DB::table('directions')->where('id', $existing->id)
                ->update(['directeur_agent_id' => $directeurAgentId]);
            return $existing->id;
        }

        $id = DB::table('directions')->insertGetId([
            'nom'                => $nom,
            'entite_id'          => 1,
            'directeur_agent_id' => $directeurAgentId,
            'created_at'         => $this->now,
            'updated_at'         => $this->now,
        ]);

        DB::table('agents')->where('id', $directeurAgentId)
            ->update(['direction_id' => $id]);

        return $id;
    }

    /** Crée un service avec son chef, retourne son id */
    private function newService(string $nom, int $directionId, int $chefAgentId): int
    {
        $id = DB::table('services')->insertGetId([
            'nom'          => $nom,
            'direction_id' => $directionId,
            'chef_agent_id'=> $chefAgentId,
            'created_at'   => $this->now,
            'updated_at'   => $this->now,
        ]);

        DB::table('agents')->where('id', $chefAgentId)->update([
            'service_id'   => $id,
            'direction_id' => $directionId,
        ]);

        return $id;
    }
}
