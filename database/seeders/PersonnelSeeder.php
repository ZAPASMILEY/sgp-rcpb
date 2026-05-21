<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

/**
 * Peuple le réseau terrain RCPB :
 *  – 3 délégations techniques (Nord-Ouahigouya, Est-Fada, Ouest-Bobo)
 *  – 10 caisses
 *  – 6 agences
 *  – 8 guichets
 *  – ~130 agents (responsables avec compte user, agents ordinaires sans compte)
 *
 * Idempotent : ignoré si les délégations existent déjà.
 * Mot de passe des comptes : password123
 */
class PersonnelSeeder extends Seeder
{
    private Carbon $now;
    private int    $matriculeCounter = 100;
    private int    $nomIdx           = 0;
    private int    $prenomMIdx       = 0;
    private int    $prenomFIdx       = 0;
    private int    $posteIdx         = 0;

    // ────────────────────────────────────────────────────────────────────────
    //  Pools de noms burkinabés
    // ────────────────────────────────────────────────────────────────────────

    private const NOMS = [
        'Ouédraogo', 'Sawadogo', 'Kaboré', 'Traoré', 'Zongo', 'Compaoré', 'Tapsoba',
        'Ouattara', 'Diallo', 'Koné', 'Coulibaly', 'Belem', 'Sankara', 'Nana',
        'Tiendrebeogo', 'Sana', 'Poda', 'Zoungrana', 'Nombré', 'Ilboudo',
        'Badolo', 'Kinda', 'Kiema', 'Nikiema', 'Barro', 'Nacro', 'Bah',
        'Yago', 'Kafando', 'Dargsi', 'Rouamba', 'Zida', 'Dao', 'Baguian',
        'Bationo', 'Lankoandé', 'Sanou', 'Dembélé', 'Drabo', 'Diabaté',
    ];

    private const PRENOMS_M = [
        'Moussa', 'Ibrahim', 'Alassane', 'Boubacar', 'Hamidou', 'Karim', 'Drissa',
        'Issouf', 'Daouda', 'Seydou', 'Abdoulaye', 'Mamadou', 'Lassana', 'Yacouba',
        'Oumarou', 'Noufou', 'Adama', 'Souleymane', 'Bala', 'Rasmané',
        'Amadou', 'Boureima', 'Mahamoudou', 'Inoussa', 'Salif', 'Frédéric', 'Idrissa',
        'Sié', 'Lamine', 'Yaya', 'Patrice', 'Emmanuel', 'Jean-Baptiste', 'Roméo',
    ];

    private const PRENOMS_F = [
        'Fatimata', 'Awa', 'Mariam', 'Aminata', 'Safiatou', 'Fanta', 'Hawa',
        'Salimata', 'Fatoumata', 'Roukiatou', 'Bintou', 'Coumba', 'Kadiatou',
        'Aissata', 'Rasmata', 'Wendyam', 'Yvonne', 'Pascaline',
        'Brigitte', 'Clarisse', 'Estelle', 'Nadège', 'Martine', 'Cécile',
        'Adjaratou', 'Biba', 'Tenin', 'Rokia', 'Djeneba', 'Kadidiatou',
    ];

    // ────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->now = now();

        if (DB::table('delegation_techniques')->count() > 0) {
            $this->command->warn('Des délégations existent déjà — seeder ignoré.');
            return;
        }

        DB::disableQueryLog();

        [$dt1, $dt2, $dt3] = $this->createDelegations();
        $this->createReseauNord($dt1);
        $this->createReseauEst($dt2);
        $this->createReseauOuest($dt3);

        $this->command->info('─────────────────────────────────────────');
        $this->command->info('✓ Délégations : ' . DB::table('delegation_techniques')->count());
        $this->command->info('✓ Caisses     : ' . DB::table('caisses')->count());
        $this->command->info('✓ Agences     : ' . DB::table('agences')->count());
        $this->command->info('✓ Guichets    : ' . DB::table('guichets')->count());
        $this->command->info('✓ Agents      : ' . DB::table('agents')->count());
        $this->command->info('✓ Users       : ' . DB::table('users')->count());
        $this->command->info('─────────────────────────────────────────');
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Délégations Techniques
    // ────────────────────────────────────────────────────────────────────────

    private function createDelegations(): array
    {
        $dt1 = DB::table('delegation_techniques')->insertGetId([
            'region' => 'Nord', 'ville' => 'Ouahigouya',
            'created_at' => $this->now, 'updated_at' => $this->now,
        ]);
        $dt2 = DB::table('delegation_techniques')->insertGetId([
            'region' => 'Est', 'ville' => "Fada N'Gourma",
            'created_at' => $this->now, 'updated_at' => $this->now,
        ]);
        $dt3 = DB::table('delegation_techniques')->insertGetId([
            'region' => 'Ouest', 'ville' => 'Bobo-Dioulasso',
            'created_at' => $this->now, 'updated_at' => $this->now,
        ]);
        return [$dt1, $dt2, $dt3];
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Réseau Nord – Ouahigouya
    // ────────────────────────────────────────────────────────────────────────

    private function createReseauNord(int $dtId): void
    {
        $dir = $this->makeAgent('Ouédraogo', 'Moussa', 'M', 'Directeur Technique',
            'dt.nord.dir@rcpb.bf', $dtId, userRole: 'Directeur_Technique');
        $sec = $this->makeAgent('Sawadogo', 'Fatimata', 'F', 'Secrétaire Technique',
            'dt.nord.sec@rcpb.bf', $dtId, userRole: 'Secretaire_Technique');

        // Agents rattachés à la DT (sans caisse)
        $this->seedAgents($dtId, null, null, null, 3, 'nord.dt');

        DB::table('delegation_techniques')->where('id', $dtId)
            ->update(['directeur_agent_id' => $dir, 'secretaire_agent_id' => $sec]);

        // ── Caisse Ouahigouya Centre (6 agents + 1 agence + 2 guichets) ─────
        $c1 = $this->makeCaisse($dtId, 'Caisse Ouahigouya Centre', '2001', 'Centre');
        $this->makeResponsablesCaisse($dtId, $c1, 'Sourabie', 'Hamidou', 'M', 'ouahi.centre');
        $this->seedAgents($dtId, $c1, null, null, 6, 'ouahi.c');

        $a1 = $this->makeAgence($dtId, $c1, 'Agence Secteur 9', 'ouahi.a1');
        $g1 = $this->makeGuichet($dtId, $c1, $a1, 'Guichet Marché Central', 'ouahi.g1');
        $this->makeGuichet($dtId, $c1, $a1, 'Guichet Gare', 'ouahi.g2');

        // ── Caisse Titao (5 agents) ──────────────────────────────────────────
        $c2 = $this->makeCaisse($dtId, 'Caisse Titao', '2005', 'Centre');
        $this->makeResponsablesCaisse($dtId, $c2, 'Ouattara', 'Karim', 'M', 'titao');
        $this->seedAgents($dtId, $c2, null, null, 5, 'titao.c');

        // ── Caisse Yako (4 agents + 1 agence + 1 guichet) ───────────────────
        $c3 = $this->makeCaisse($dtId, 'Caisse Yako', '2008', null);
        $this->makeResponsablesCaisse($dtId, $c3, 'Bamba', 'Salimata', 'F', 'yako');
        $this->seedAgents($dtId, $c3, null, null, 4, 'yako.c');

        $a2 = $this->makeAgence($dtId, $c3, 'Agence Yako Secteur 2', 'yako.a1');
        $this->makeGuichet($dtId, $c3, $a2, 'Guichet Yako Centre', 'yako.g1');
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Réseau Est – Fada N'Gourma
    // ────────────────────────────────────────────────────────────────────────

    private function createReseauEst(int $dtId): void
    {
        $dir = $this->makeAgent('Zongo', 'Alassane', 'M', 'Directeur Technique',
            'dt.est.dir@rcpb.bf', $dtId, userRole: 'Directeur_Technique');
        $sec = $this->makeAgent('Compaoré', 'Awa', 'F', 'Secrétaire Technique',
            'dt.est.sec@rcpb.bf', $dtId, userRole: 'Secretaire_Technique');

        $this->seedAgents($dtId, null, null, null, 3, 'est.dt');

        DB::table('delegation_techniques')->where('id', $dtId)
            ->update(['directeur_agent_id' => $dir, 'secretaire_agent_id' => $sec]);

        // ── Caisse Fada N'Gourma Centre (6 agents + 1 agence + 1 guichet) ───
        $c1 = $this->makeCaisse($dtId, "Caisse Fada N'Gourma Centre", '1998', 'Secteur 1');
        $this->makeResponsablesCaisse($dtId, $c1, 'Sana', 'Safiatou', 'F', 'fada.centre');
        $this->seedAgents($dtId, $c1, null, null, 6, 'fada.c');

        $a1 = $this->makeAgence($dtId, $c1, 'Agence Bilanga', 'bilanga.a1');
        $this->makeGuichet($dtId, $c1, $a1, 'Guichet Bilanga Gare', 'bilanga.g1');

        // ── Caisse Diapaga (5 agents) ────────────────────────────────────────
        $c2 = $this->makeCaisse($dtId, 'Caisse Diapaga', '2003', null);
        $this->makeResponsablesCaisse($dtId, $c2, 'Poda', 'Daouda', 'M', 'diapaga');
        $this->seedAgents($dtId, $c2, null, null, 5, 'diapaga.c');

        // ── Caisse Bogandé (4 agents) ────────────────────────────────────────
        $c3 = $this->makeCaisse($dtId, 'Caisse Bogandé', '2010', null);
        $this->makeResponsablesCaisse($dtId, $c3, 'Tapsoba', 'Hawa', 'F', 'bogande');
        $this->seedAgents($dtId, $c3, null, null, 4, 'bogande.c');
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Réseau Ouest – Bobo-Dioulasso
    // ────────────────────────────────────────────────────────────────────────

    private function createReseauOuest(int $dtId): void
    {
        $dir = $this->makeAgent('Traoré', 'Boubacar', 'M', 'Directeur Technique',
            'dt.ouest.dir@rcpb.bf', $dtId, userRole: 'Directeur_Technique');
        $sec = $this->makeAgent('Coulibaly', 'Mariam', 'F', 'Secrétaire Technique',
            'dt.ouest.sec@rcpb.bf', $dtId, userRole: 'Secretaire_Technique');

        $this->seedAgents($dtId, null, null, null, 3, 'ouest.dt');

        DB::table('delegation_techniques')->where('id', $dtId)
            ->update(['directeur_agent_id' => $dir, 'secretaire_agent_id' => $sec]);

        // ── Caisse Bobo Centre (7 agents + 1 agence + 2 guichets) ───────────
        $c1 = $this->makeCaisse($dtId, 'Caisse Bobo Centre', '1993', 'Secteur 5');
        $this->makeResponsablesCaisse($dtId, $c1, 'Koné', 'Drissa', 'M', 'bobo.centre');
        $this->seedAgents($dtId, $c1, null, null, 7, 'bobo.c');

        $a1 = $this->makeAgence($dtId, $c1, 'Agence Bobo Secteur 22', 'bobo.a1');
        $this->makeGuichet($dtId, $c1, $a1, 'Guichet Bobo S22 Centre', 'bobo.g1');
        $this->makeGuichet($dtId, $c1, $a1, 'Guichet Bobo S22 Marché', 'bobo.g2');

        // ── Caisse Banfora (5 agents + 1 agence + 1 guichet) ────────────────
        $c2 = $this->makeCaisse($dtId, 'Caisse Banfora', '2000', null);
        $this->makeResponsablesCaisse($dtId, $c2, 'Sanogo', 'Fanta', 'F', 'banfora');
        $this->seedAgents($dtId, $c2, null, null, 5, 'banfora.c');

        $a2 = $this->makeAgence($dtId, $c2, 'Agence Banfora Nord', 'banfora.a1');
        $this->makeGuichet($dtId, $c2, $a2, 'Guichet Banfora Marché', 'banfora.g1');

        // ── Caisse Dédougou (4 agents) ───────────────────────────────────────
        $c3 = $this->makeCaisse($dtId, 'Caisse Dédougou', '2007', null);
        $this->makeResponsablesCaisse($dtId, $c3, 'Diallo', 'Fatou', 'F', 'dedougou');
        $this->seedAgents($dtId, $c3, null, null, 4, 'dedougou.c');

        // ── Caisse Nouna (3 agents) ──────────────────────────────────────────
        $c4 = $this->makeCaisse($dtId, 'Caisse Nouna', '2012', null);
        $this->makeResponsablesCaisse($dtId, $c4, 'Bah', 'Amadou', 'M', 'nouna');
        $this->seedAgents($dtId, $c4, null, null, 3, 'nouna.c');
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Helpers structures
    // ────────────────────────────────────────────────────────────────────────

    private function makeCaisse(int $dtId, string $nom, string $annee, ?string $quartier): int
    {
        return DB::table('caisses')->insertGetId([
            'delegation_technique_id' => $dtId,
            'nom'                     => $nom,
            'annee_ouverture'         => $annee,
            'quartier'                => $quartier,
            'created_at'              => $this->now,
            'updated_at'              => $this->now,
        ]);
    }

    /**
     * Crée directeur + secrétaire, met à jour les FK de la caisse.
     * Le secrétaire est automatiquement du sexe opposé au directeur.
     */
    private function makeResponsablesCaisse(
        int $dtId, int $caisseId,
        string $nom, string $prenom, string $sexe,
        string $emailPfx
    ): void {
        $dir = $this->makeAgent($nom, $prenom, $sexe, 'Directeur de Caisse',
            "{$emailPfx}.dir@rcpb.bf", $dtId, $caisseId, userRole: 'Directeur_Caisse');

        [$sNom, $sPrenom, $sSexe] = $sexe === 'M'
            ? [$this->pickNom(), $this->pickPrenom('F'), 'F']
            : [$this->pickNom(), $this->pickPrenom('M'), 'M'];

        $sec = $this->makeAgent($sNom, $sPrenom, $sSexe, 'Secrétaire de Caisse',
            "{$emailPfx}.sec@rcpb.bf", $dtId, $caisseId, userRole: 'Secretaire_Caisse');

        DB::table('caisses')->where('id', $caisseId)
            ->update(['directeur_agent_id' => $dir, 'secretaire_agent_id' => $sec]);
    }

    private function makeAgence(int $dtId, int $caisseId, string $nom, string $emailPfx): int
    {
        $chef = $this->makeAgent(
            $this->pickNom(), $this->pickPrenom('M'), 'M', "Chef d'Agence",
            "{$emailPfx}.chef@rcpb.bf", $dtId, $caisseId, userRole: 'Chef_Agence'
        );
        $sec = $this->makeAgent(
            $this->pickNom(), $this->pickPrenom('F'), 'F', "Secrétaire d'Agence",
            "{$emailPfx}.sec@rcpb.bf", $dtId, $caisseId, userRole: 'Secretaire_Agence'
        );

        $agenceId = DB::table('agences')->insertGetId([
            'delegation_technique_id' => $dtId,
            'caisse_id'               => $caisseId,
            'nom'                     => $nom,
            'chef_agent_id'           => $chef,
            'secretaire_agent_id'     => $sec,
            'created_at'              => $this->now,
            'updated_at'              => $this->now,
        ]);

        DB::table('agents')->whereIn('id', [$chef, $sec])
            ->update(['agence_id' => $agenceId]);

        // 3 agents ordinaires dans l'agence
        $this->seedAgents($dtId, $caisseId, $agenceId, null, 3, $emailPfx);

        return $agenceId;
    }

    private function makeGuichet(int $dtId, int $caisseId, int $agenceId, string $nom, string $emailPfx): int
    {
        $chef = $this->makeAgent(
            $this->pickNom(), $this->pickPrenom('M'), 'M', 'Chef de Guichet',
            "{$emailPfx}.chef@rcpb.bf", $dtId, $caisseId, $agenceId, 'Chef_Guichet'
        );

        $guichetId = DB::table('guichets')->insertGetId([
            'agence_id'     => $agenceId,
            'nom'           => $nom,
            'chef_agent_id' => $chef,
            'created_at'    => $this->now,
            'updated_at'    => $this->now,
        ]);

        DB::table('agents')->where('id', $chef)
            ->update(['guichet_id' => $guichetId]);

        // 2 agents ordinaires dans le guichet
        $this->seedAgents($dtId, $caisseId, $agenceId, $guichetId, 2, $emailPfx);

        return $guichetId;
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Helpers agents
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Crée un agent avec un compte utilisateur si $userRole est fourni.
     * Retourne l'id de l'agent créé.
     */
    private function makeAgent(
        string  $nom,
        string  $prenom,
        string  $sexe,
        string  $agentRole,
        string  $email,
        ?int    $dtId      = null,
        ?int    $caisseId  = null,
        ?int    $agenceId  = null,
        ?string $userRole  = null,
    ): int {
        $mat = $this->nextMatricule();

        $agentId = DB::table('agents')->insertGetId([
            'nom'                     => $nom,
            'prenom'                  => $prenom,
            'sexe'                    => $sexe === 'M' ? 'Masculin' : 'Féminin',
            'email'                   => $email,
            'matricule'               => $mat,
            'role'                    => $agentRole,
            'delegation_technique_id' => $dtId,
            'caisse_id'               => $caisseId,
            'agence_id'               => $agenceId,
            'date_debut_fonction'     => now()->subMonths(rand(12, 84))->toDateString(),
            'created_at'              => $this->now,
            'updated_at'              => $this->now,
        ]);

        if ($userRole) {
            DB::table('users')->insert([
                'name'                 => trim($prenom . ' ' . $nom),
                'email'                => $email,
                'password'             => Hash::make('password123'),
                'role'                 => $userRole,
                'agent_id'             => $agentId,
                'must_change_password' => true,
                'is_active'            => true,
                'created_at'           => $this->now,
                'updated_at'           => $this->now,
            ]);
        }

        return $agentId;
    }

    /**
     * Insère $count agents ordinaires (role='Agent') sans compte utilisateur.
     * Sexe alterné M/F automatiquement.
     */
    private function seedAgents(
        int  $dtId,
        ?int $caisseId,
        ?int $agenceId,
        ?int $guichetId,
        int  $count,
        string $emailPfx,
    ): void {
        for ($i = 1; $i <= $count; $i++) {
            $sexe   = $i % 2 === 0 ? 'F' : 'M';
            $mat    = $this->nextMatricule();
            DB::table('agents')->insert([
                'nom'                     => $this->pickNom(),
                'prenom'                  => $this->pickPrenom($sexe),
                'sexe'                    => $sexe === 'M' ? 'Masculin' : 'Féminin',
                'email'                   => strtolower("{$emailPfx}.ag{$i}.{$mat}@rcpb.bf"),
                'matricule'               => $mat,
                'role'                    => 'Agent',
                'poste'                   => $this->pickPoste($sexe),
                'delegation_technique_id' => $dtId,
                'caisse_id'               => $caisseId,
                'agence_id'               => $agenceId,
                'guichet_id'              => $guichetId,
                'date_debut_fonction'     => now()->subMonths(rand(6, 60))->toDateString(),
                'created_at'              => $this->now,
                'updated_at'              => $this->now,
            ]);
        }
    }

    private function nextMatricule(): string
    {
        return 'RC' . str_pad(++$this->matriculeCounter, 5, '0', STR_PAD_LEFT);
    }

    private function pickNom(): string
    {
        return self::NOMS[$this->nomIdx++ % count(self::NOMS)];
    }

    private const POSTES = [
        'Caissier',
        'Chargé de crédit',
        'Chargé de compte',
        'Agent de saisie',
        'Préposé aux dépôts',
        'Chargé de recouvrement',
        'Animateur de crédit',
        'Guichetier',
        'Chargé de clientèle',
        'Agent de terrain',
        'Technicien de crédit',
        'Agent de sécurité',
        'Caissière',
        'Chargée de crédit',
        'Chargée de compte',
        'Animatrice de crédit',
        'Guichetière',
        'Chargée de clientèle',
        'Technicienne de crédit',
        'Préposée aux dépôts',
    ];

    private function pickPoste(string $sexe): string
    {
        // Alterner entre postes masculins (index 0-11) et féminins (index 12-19)
        $pool = $sexe === 'F'
            ? array_slice(self::POSTES, 12)
            : array_slice(self::POSTES, 0, 12);
        return $pool[$this->posteIdx++ % count($pool)];
    }

    private function pickPrenom(string $sexe): string
    {
        if ($sexe === 'M') {
            return self::PRENOMS_M[$this->prenomMIdx++ % count(self::PRENOMS_M)];
        }
        return self::PRENOMS_F[$this->prenomFIdx++ % count(self::PRENOMS_F)];
    }
}
