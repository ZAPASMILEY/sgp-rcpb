<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

/**
 * Crée les secrétaires manquants pour toutes les structures.
 * Chaque direction, caisse, DT doit avoir un secrétaire.
 */
class SecretairesSeeder extends Seeder
{
    private Carbon $now;

    public function run(): void
    {
        $this->now = now();

        // ── Secrétaires des Directions ───────────────────────────────────────
        $directionsSecretaires = [
            1 => ['Konaté',   'Marcelline', 'sec.dg@rcpb.bf'],        // Direction Générale
            2 => ['Ouédraogo','Flore',      'sec.rh@rcpb.bf'],         // Direction RH
            3 => ['Traoré',   'Aminata',    'sec.finance@rcpb.bf'],    // Direction Finances
            4 => ['Nikiéma',  'Christelle', 'sec.si@rcpb.bf'],         // Direction SI
            5 => ['Diallo',   'Halimatou',  'sec.dga@rcpb.bf'],        // Direction DGA
            6 => ['Sawadogo', 'Blandine',   'sec.eng@rcpb.bf'],        // Direction Engagements
            7 => ['Barry',    'Kadiatou',   'sec.mkt@rcpb.bf'],        // Direction Marketing
            8 => ['Kaboré',   'Rasmata',    'sec.audit@rcpb.bf'],      // Direction Audit
        ];

        foreach ($directionsSecretaires as $dirId => $info) {
            [$nom, $prenom, $email] = $info;

            // Vérifier si déjà un secrétaire
            $dir = DB::table('directions')->where('id', $dirId)->first();
            if ($dir && $dir->secretaire_agent_id) continue;

            $agentId = $this->newAgent($nom, $prenom, 'Secrétaire de Direction', $email, 'Secretaire_Assistante', $dirId);

            DB::table('directions')->where('id', $dirId)
                ->update(['secretaire_agent_id' => $agentId]);
        }

        // ── Directeurs + Secrétaires des Caisses manquantes ─────────────────
        $caissesManquantes = [
            2 => ['Caisse Titao',       'Ouattara', 'Karim',    'dir.titao@rcpb.bf',    'Bamba',     'Salimata', 'sec.titao@rcpb.bf'],
            4 => ['Caisse Diapaga',     'Zongo',    'Seydou',   'dir.diapaga@rcpb.bf',  'Compaoré',  'Awa',      'sec.diapaga@rcpb.bf'],
            5 => ['Caisse Bobo Centre', 'Traoré',   'Boubacar', 'dir.bobo@rcpb.bf',     'Coulibaly', 'Mariam',   'sec.bobo@rcpb.bf'],
            6 => ['Caisse Banfora',     'Kone',     'Drissa',   'dir.banfora@rcpb.bf',  'Sanogo',    'Fanta',    'sec.banfora@rcpb.bf'],
        ];

        foreach ($caissesManquantes as $caisseId => $info) {
            [$caisseName, $dirNom, $dirPrenom, $dirEmail, $secNom, $secPrenom, $secEmail] = $info;

            $caisse = DB::table('caisses')->where('id', $caisseId)->first();
            $dtId   = $caisse?->delegation_technique_id;

            // Directeur de caisse
            if (!$caisse?->directeur_agent_id) {
                $dirAgentId = $this->newAgentCaisse($dirNom, $dirPrenom, 'Directeur de Caisse', $dirEmail, 'Directeur_Caisse', $caisseId, $dtId);
                DB::table('caisses')->where('id', $caisseId)->update(['directeur_agent_id' => $dirAgentId]);
            }

            // Secrétaire de caisse
            if (!$caisse?->secretaire_agent_id) {
                $secAgentId = $this->newAgentCaisse($secNom, $secPrenom, 'Secrétaire de Caisse', $secEmail, 'Secretaire_Caisse', $caisseId, $dtId);
                DB::table('caisses')->where('id', $caisseId)->update(['secretaire_agent_id' => $secAgentId]);
            }
        }

        // ── Secrétaire DT 3 (Ouest / Bobo-Dioulasso) manquant ───────────────
        $dt3 = DB::table('delegation_techniques')->where('id', 3)->first();
        if ($dt3 && !$dt3->secretaire_agent_id) {
            $agentId = DB::table('agents')->insertGetId([
                'nom'                      => 'Kinda',
                'prenom'                   => 'Sylvestre',
                'email'                    => 'sec.dt.ouest@rcpb.bf',
                'fonction'                 => 'Secrétaire Technique',
                'delegation_technique_id'  => 3,
                'created_at'               => $this->now,
                'updated_at'               => $this->now,
            ]);
            DB::table('users')->insert([
                'name'                 => 'Sylvestre Kinda',
                'email'                => 'sec.dt.ouest@rcpb.bf',
                'password'             => Hash::make('11111111'),
                'role'                 => 'Secretaire_Technique',
                'agent_id'             => $agentId,
                'must_change_password' => true,
                'created_at'           => $this->now,
                'updated_at'           => $this->now,
            ]);
            DB::table('delegation_techniques')->where('id', 3)
                ->update(['secretaire_agent_id' => $agentId]);
        }

        $this->command->info('Secrétaires créés avec succès.');
        $this->command->info('  Directions avec secrétaire : ' . DB::table('directions')->whereNotNull('secretaire_agent_id')->count() . '/8');
        $this->command->info('  Caisses avec directeur     : ' . DB::table('caisses')->whereNotNull('directeur_agent_id')->count() . '/6');
        $this->command->info('  Caisses avec secrétaire    : ' . DB::table('caisses')->whereNotNull('secretaire_agent_id')->count() . '/6');
        $this->command->info('  DT avec secrétaire         : ' . DB::table('delegation_techniques')->whereNotNull('secretaire_agent_id')->count() . '/3');
    }

    private function newAgent(string $nom, string $prenom, string $fonction, string $email, string $role, int $directionId): int
    {
        $agentId = DB::table('agents')->insertGetId([
            'nom'          => $nom,
            'prenom'       => $prenom,
            'email'        => $email,
            'fonction'     => $fonction,
            'direction_id' => $directionId,
            'created_at'   => $this->now,
            'updated_at'   => $this->now,
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

    private function newAgentCaisse(string $nom, string $prenom, string $fonction, string $email, string $role, int $caisseId, ?int $dtId): int
    {
        $agentId = DB::table('agents')->insertGetId([
            'nom'                     => $nom,
            'prenom'                  => $prenom,
            'email'                   => $email,
            'fonction'                => $fonction,
            'caisse_id'               => $caisseId,
            'delegation_technique_id' => $dtId,
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
