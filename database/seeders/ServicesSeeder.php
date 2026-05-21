<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Crée les services au sein de chaque caisse (Service Opérations + Service Crédit),
 * nomme un Chef de Service pour chacun (avec compte utilisateur),
 * et distribue les agents ordinaires déjà en caisse vers leurs services.
 *
 * Idempotent : ignoré si des services existent déjà.
 * Mot de passe des comptes : password123
 */
class ServicesSeeder extends Seeder
{
    private const SERVICES_PAR_CAISSE = [
        'Service Opérations',
        'Service Crédit',
    ];

    private const NOMS = [
        'Ouédraogo', 'Sawadogo', 'Kaboré', 'Traoré', 'Zongo', 'Compaoré', 'Tapsoba',
        'Ouattara', 'Diallo', 'Koné', 'Coulibaly', 'Belem', 'Sankara', 'Nana',
        'Tiendrebeogo', 'Sana', 'Poda', 'Zoungrana', 'Nombré', 'Ilboudo',
        'Badolo', 'Kinda', 'Kiema', 'Nikiema', 'Barro', 'Nacro', 'Kafando',
        'Rouamba', 'Dao', 'Bationo', 'Sanou', 'Dembélé', 'Drabo', 'Diabaté',
    ];

    private const PRENOMS_M = [
        'Moussa', 'Ibrahim', 'Alassane', 'Boubacar', 'Hamidou', 'Karim', 'Drissa',
        'Daouda', 'Seydou', 'Abdoulaye', 'Mamadou', 'Yacouba', 'Adama', 'Bala',
        'Amadou', 'Salif', 'Idrissa', 'Lamine', 'Yaya', 'Frédéric',
    ];

    private const PRENOMS_F = [
        'Fatimata', 'Awa', 'Mariam', 'Aminata', 'Safiatou', 'Fanta', 'Hawa',
        'Salimata', 'Fatoumata', 'Rasmata', 'Yvonne', 'Pascaline',
        'Clarisse', 'Adjaratou', 'Rokia', 'Djeneba', 'Bintou', 'Roukiatou',
    ];

    private int $matCounter = 500;
    private int $nomIdx     = 0;
    private int $prenomMIdx = 0;
    private int $prenomFIdx = 0;

    // ────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        if (DB::table('services')->count() > 0) {
            $this->command->warn('Services existent déjà — seeder ignoré.');
            return;
        }

        $now     = now();
        $caisses = DB::table('caisses')->get();

        foreach ($caisses as $caisse) {
            foreach (self::SERVICES_PAR_CAISSE as $idx => $nomService) {
                // Alterner sexe entre les deux services de la caisse
                $sexe   = $idx % 2 === 0 ? 'M' : 'F';
                $nom    = $this->pickNom();
                $prenom = $this->pickPrenom($sexe);
                $mat    = $this->nextMatricule();
                $slug   = strtolower(preg_replace('/[^a-z0-9]/i', '.', $nomService));
                $email  = "{$slug}." . strtolower($nom) . ".{$mat}@rcpb.bf";

                // ── Agent Chef de Service ────────────────────────────────────
                $agentId = DB::table('agents')->insertGetId([
                    'nom'                     => $nom,
                    'prenom'                  => $prenom,
                    'sexe'                    => $sexe === 'M' ? 'Masculin' : 'Féminin',
                    'email'                   => $email,
                    'matricule'               => $mat,
                    'role'                    => 'Chef de Service',
                    'delegation_technique_id' => $caisse->delegation_technique_id,
                    'caisse_id'               => $caisse->id,
                    'date_debut_fonction'     => now()->subMonths(rand(12, 72))->toDateString(),
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ]);

                // ── Compte utilisateur ───────────────────────────────────────
                DB::table('users')->insert([
                    'name'                 => trim($prenom . ' ' . $nom),
                    'email'                => $email,
                    'password'             => Hash::make('password123'),
                    'role'                 => 'Chef_Service',
                    'agent_id'             => $agentId,
                    'must_change_password' => true,
                    'is_active'            => true,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]);

                // ── Service ──────────────────────────────────────────────────
                $serviceId = DB::table('services')->insertGetId([
                    'nom'                     => $nomService,
                    'delegation_technique_id' => $caisse->delegation_technique_id,
                    'caisse_id'               => $caisse->id,
                    'chef_agent_id'           => $agentId,
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ]);

                // ── Rattacher le chef à son propre service ───────────────────
                DB::table('agents')->where('id', $agentId)
                    ->update(['service_id' => $serviceId]);
            }
        }

        // ── Distribuer les agents ordinaires en caisse vers leurs services ──
        $services = DB::table('services')
            ->whereNotNull('caisse_id')
            ->get()
            ->groupBy('caisse_id');

        $agents = DB::table('agents')
            ->where('role', 'Agent')
            ->whereNull('service_id')
            ->whereNotNull('caisse_id')
            ->whereNull('agence_id')
            ->whereNull('guichet_id')
            ->get()
            ->groupBy('caisse_id');

        foreach ($agents as $caisseId => $caisseAgents) {
            $caisseServices = $services->get($caisseId, collect());
            if ($caisseServices->isEmpty()) continue;

            foreach ($caisseAgents as $i => $agent) {
                $service = $caisseServices->values()->get($i % $caisseServices->count());
                DB::table('agents')->where('id', $agent->id)
                    ->update(['service_id' => $service->id]);
            }
        }

        $this->command->info('─────────────────────────────────────────');
        $this->command->info('✓ Services créés       : ' . DB::table('services')->count());
        $this->command->info('✓ Agents avec service  : ' . DB::table('agents')->whereNotNull('service_id')->count());
        $this->command->info('─────────────────────────────────────────');
    }

    // ────────────────────────────────────────────────────────────────────────

    private function nextMatricule(): string
    {
        return 'RC' . str_pad(++$this->matCounter, 5, '0', STR_PAD_LEFT);
    }

    private function pickNom(): string
    {
        return self::NOMS[$this->nomIdx++ % count(self::NOMS)];
    }

    private function pickPrenom(string $sexe): string
    {
        if ($sexe === 'M') {
            return self::PRENOMS_M[$this->prenomMIdx++ % count(self::PRENOMS_M)];
        }
        return self::PRENOMS_F[$this->prenomFIdx++ % count(self::PRENOMS_F)];
    }
}
