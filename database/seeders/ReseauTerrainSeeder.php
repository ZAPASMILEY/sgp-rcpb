<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

/**
 * Recrée le réseau terrain RCPB :
 *   – 6 caisses (2 par DT : Nord, Est, Ouest)
 *   – 3 agences (une par DT)
 *   – 3 guichets (un par agence)
 *
 * Réutilise les agents existants (par id) et crée ceux qui manquent.
 * Met à jour les colonnes FK des agents (caisse_id, agence_id, guichet_id,
 * delegation_technique_id) pour refléter leur affectation réelle.
 */
class ReseauTerrainSeeder extends Seeder
{
    private Carbon $now;

    public function run(): void
    {
        $this->now = now();

        // ── 1. CAISSES ────────────────────────────────────────────────────────
        // DT 1 – Nord / Ouahigouya
        $c1 = $this->createCaisse(
            dtId: 1, nom: 'Caisse Ouahigouya Centre', annee: '1995', quartier: 'Centre',
            dirId: 12, secId: 13  // Tiendrebeogo Rokia + Nana Adama  (existants)
        );
        $this->createCaisse(
            dtId: 1, nom: 'Caisse Titao', annee: '2001', quartier: null,
            dirId: 65, secId: 66  // Ouattara Karim + Bamba Salimata   (existants)
        );

        // DT 2 – Est / Fada N'Gourma
        $c3 = $this->createCaisse(
            dtId: 2, nom: "Caisse Fada N'Gourma", annee: '1997', quartier: 'Secteur 1',
            dirId: 14, secId: 15  // Sana Safiatou + Poda Daouda        (existants)
        );
        $this->createCaisse(
            dtId: 2, nom: 'Caisse Diapaga', annee: '2003', quartier: null,
            dirId: 67, secId: 68  // Zongo Seydou + Compaoré Awa        (existants)
        );

        // DT 3 – Ouest / Bobo-Dioulasso
        $c5 = $this->createCaisse(
            dtId: 3, nom: 'Caisse Bobo Centre', annee: '1993', quartier: 'Secteur 5',
            dirId: 69, secId: 70  // Traoré Boubacar + Coulibaly Mariam (existants)
        );
        $this->createCaisse(
            dtId: 3, nom: 'Caisse Banfora', annee: '2005', quartier: null,
            dirId: 71, secId: 72  // Kone Drissa + Sanogo Fanta         (existants)
        );

        // ── 2. AGENCES ───────────────────────────────────────────────────────
        // Agence 1 – sous Caisse Ouahigouya Centre (DT 1)
        //   chef: Tapsoba Awa (16), sec: Ouedraogo Seydou (17)  – existants
        $a1 = $this->createAgence(
            dtId: 1, caisseId: $c1, nom: 'Agence Secteur 9',
            chefId: 16, secId: 17
        );

        // Agence 2 – sous Caisse Fada N'Gourma (DT 2)
        //   chef: Belem Hawa (18), sec: à créer
        $secA2 = $this->createAgent(
            nom: 'Kaboré', prenom: 'Awa', fonction: "Secrétaire d'Agence",
            email: 'sec.agence.bilanga@rcpb.bf', role: 'Secretaire_Agence',
            dtId: 2, caisseId: $c3
        );
        $a2 = $this->createAgence(
            dtId: 2, caisseId: $c3, nom: 'Agence Bilanga',
            chefId: 18, secId: $secA2
        );

        // Agence 3 – sous Caisse Bobo Centre (DT 3) – chef et sec à créer
        $chefA3 = $this->createAgent(
            nom: 'Diallo', prenom: 'Fatou', fonction: "Chef d'Agence",
            email: 'chef.agence.bobo22@rcpb.bf', role: 'Chef_Agence',
            dtId: 3, caisseId: $c5
        );
        $secA3 = $this->createAgent(
            nom: 'Traoré', prenom: 'Mariam', fonction: "Secrétaire d'Agence",
            email: 'sec.agence.bobo22@rcpb.bf', role: 'Secretaire_Agence',
            dtId: 3, caisseId: $c5
        );
        $a3 = $this->createAgence(
            dtId: 3, caisseId: $c5, nom: 'Agence Bobo Secteur 22',
            chefId: $chefA3, secId: $secA3
        );

        // ── 3. GUICHETS ──────────────────────────────────────────────────────
        // Guichet 1 – sous Agence Secteur 9 (DT 1)
        //   chef: Sawadogo Lamine (19) – existant
        $this->createGuichet(
            agenceId: $a1, dtId: 1, caisseId: $c1,
            nom: 'Guichet Marché Central', chefId: 19
        );

        // Guichet 2 – sous Agence Bilanga (DT 2) – chef à créer
        $chefG2 = $this->createAgent(
            nom: 'Ouattara', prenom: 'Moussa', fonction: 'Chef de Guichet',
            email: 'chef.guichet.bilanga@rcpb.bf', role: 'Chef_Guichet',
            dtId: 2, caisseId: $c3
        );
        $this->createGuichet(
            agenceId: $a2, dtId: 2, caisseId: $c3,
            nom: 'Guichet Bilanga Gare', chefId: $chefG2
        );

        // Guichet 3 – sous Agence Bobo Secteur 22 (DT 3) – chef à créer
        $chefG3 = $this->createAgent(
            nom: 'Zongo', prenom: 'Ibrahim', fonction: 'Chef de Guichet',
            email: 'chef.guichet.bobo22@rcpb.bf', role: 'Chef_Guichet',
            dtId: 3, caisseId: $c5
        );
        $this->createGuichet(
            agenceId: $a3, dtId: 3, caisseId: $c5,
            nom: 'Guichet Bobo S22 Centre', chefId: $chefG3
        );

        // ── 4. Mise à jour agence_id sur les agents de guichet ───────────────
        foreach ([
            19 => $a1,
            $chefG2 => $a2,
            $chefG3 => $a3,
        ] as $agentId => $agenceId) {
            DB::table('agents')->where('id', $agentId)
                ->update(['agence_id' => $agenceId]);
        }

        // ── Rapport ──────────────────────────────────────────────────────────
        $this->command->info('Réseau terrain recréé avec succès.');
        $this->command->info('  Caisses  : ' . DB::table('caisses')->count() . '/6');
        $this->command->info('  Agences  : ' . DB::table('agences')->count() . '/3');
        $this->command->info('  Guichets : ' . DB::table('guichets')->count() . '/3');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Crée une caisse et met à jour les agents responsables
     * (delegation_technique_id + caisse_id).
     */
    private function createCaisse(
        int $dtId, string $nom, string $annee, ?string $quartier,
        int $dirId, int $secId
    ): int {
        $caisseId = DB::table('caisses')->insertGetId([
            'delegation_technique_id' => $dtId,
            'nom'                     => $nom,
            'annee_ouverture'         => $annee,
            'quartier'                => $quartier,
            'directeur_agent_id'      => $dirId,
            'secretaire_agent_id'     => $secId,
            'created_at'              => $this->now,
            'updated_at'              => $this->now,
        ]);

        // Rattacher les responsables à la caisse et à la DT
        DB::table('agents')->whereIn('id', [$dirId, $secId])->update([
            'caisse_id'               => $caisseId,
            'delegation_technique_id' => $dtId,
        ]);

        return $caisseId;
    }

    /**
     * Crée une agence et met à jour les agents responsables
     * (delegation_technique_id + caisse_id + agence_id).
     */
    private function createAgence(
        int $dtId, int $caisseId, string $nom,
        int $chefId, int $secId
    ): int {
        $agenceId = DB::table('agences')->insertGetId([
            'delegation_technique_id' => $dtId,
            'caisse_id'               => $caisseId,
            'nom'                     => $nom,
            'chef_agent_id'           => $chefId,
            'secretaire_agent_id'     => $secId,
            'created_at'              => $this->now,
            'updated_at'              => $this->now,
        ]);

        DB::table('agents')->whereIn('id', [$chefId, $secId])->update([
            'delegation_technique_id' => $dtId,
            'caisse_id'               => $caisseId,
            'agence_id'               => $agenceId,
        ]);

        return $agenceId;
    }

    /**
     * Crée un guichet et met à jour le chef
     * (delegation_technique_id + caisse_id + agence_id + guichet_id).
     */
    private function createGuichet(
        int $agenceId, int $dtId, int $caisseId,
        string $nom, int $chefId
    ): int {
        $guichetId = DB::table('guichets')->insertGetId([
            'agence_id'    => $agenceId,
            'nom'          => $nom,
            'chef_agent_id' => $chefId,
            'created_at'   => $this->now,
            'updated_at'   => $this->now,
        ]);

        DB::table('agents')->where('id', $chefId)->update([
            'delegation_technique_id' => $dtId,
            'caisse_id'               => $caisseId,
            'agence_id'               => $agenceId,
            'guichet_id'              => $guichetId,
        ]);

        return $guichetId;
    }

    /**
     * Crée un agent + un user compte associé et retourne l'agent id.
     */
    private function createAgent(
        string $nom, string $prenom, string $fonction,
        string $email, string $role,
        int $dtId, int $caisseId
    ): int {
        $agentId = DB::table('agents')->insertGetId([
            'nom'                     => $nom,
            'prenom'                  => $prenom,
            'email'                   => $email,
            'fonction'                => $fonction,
            'delegation_technique_id' => $dtId,
            'caisse_id'               => $caisseId,
            'created_at'              => $this->now,
            'updated_at'              => $this->now,
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
}
