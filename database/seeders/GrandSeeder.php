<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Peuple intégralement la base SGP-RCPB :
 *
 *  • Faîtière RCPB (entite) avec PCA, DG, DGA, assistante, conseillers, RH
 *  • 3 Directions internes (DAF, DRH, DOR) avec services et agents
 *  • 5 Délégations Techniques avec leurs villes
 *  • 15 Caisses avec services, agences et guichets
 *  • 20 Agences + 16 Guichets
 *  • ~550 agents au total (> 400 agents attendus)
 *  • Compte User + mot de passe pour chaque responsable
 *  • 60 formations réparties sur des agents aléatoires
 *  • Année 2026 (S1 ouvert, S2 cloturé)
 *
 * Idempotent : ignoré si la table entites n'est pas vide.
 * Mot de passe des comptes : 11111111
 */
class GrandSeeder extends Seeder
{
    private Carbon $now;
    private int    $matriculeCounter = 1000;
    private int    $nomIdx           = 0;
    private int    $prenomMIdx       = 0;
    private int    $prenomFIdx       = 0;
    private int    $posteIdx         = 0;

    // ── Pools ────────────────────────────────────────────────────────────────

    private const NOMS = [
        'Ouédraogo','Sawadogo','Kaboré','Traoré','Zongo','Compaoré','Tapsoba',
        'Ouattara','Diallo','Koné','Coulibaly','Belem','Sankara','Nana',
        'Tiendrebeogo','Sana','Poda','Zoungrana','Nombré','Ilboudo',
        'Badolo','Kinda','Kiema','Nikiema','Barro','Nacro','Bah',
        'Yago','Kafando','Dargsi','Rouamba','Zida','Dao','Baguian',
        'Bationo','Lankoandé','Sanou','Dembélé','Drabo','Diabaté',
        'Sore','Bamogo','Ouoba','Boro','Guiro','Konate','Bande',
        'Hien','Some','Pale','Toure','Bandaogo','Dabre','Tankoano',
        'Bazié','Nebie','Yameogo','Compaore','Kabore','Tall',
    ];

    private const PRENOMS_M = [
        'Moussa','Ibrahim','Alassane','Boubacar','Hamidou','Karim','Drissa',
        'Issouf','Daouda','Seydou','Abdoulaye','Mamadou','Lassana','Yacouba',
        'Oumarou','Noufou','Adama','Souleymane','Bala','Rasmané',
        'Amadou','Boureima','Mahamoudou','Inoussa','Salif','Frédéric','Idrissa',
        'Sié','Lamine','Yaya','Patrice','Emmanuel','Jean-Baptiste','Roméo',
        'Théophile','Gervais','Rodrigue','Issa','Barnabé','Valentin',
        'Gustave','Arsène','Célestin','Dieudonné','Prosper',
    ];

    private const PRENOMS_F = [
        'Fatimata','Awa','Mariam','Aminata','Safiatou','Fanta','Hawa',
        'Salimata','Fatoumata','Roukiatou','Bintou','Coumba','Kadiatou',
        'Aissata','Rasmata','Wendyam','Yvonne','Pascaline',
        'Brigitte','Clarisse','Estelle','Nadège','Martine','Cécile',
        'Adjaratou','Biba','Tenin','Rokia','Djeneba','Kadidiatou',
        'Odile','Grâce','Christine','Véronique','Monique','Joséphine',
        'Nathalie','Angélique','Solange','Bernadette','Honorine','Albertine',
        'Jacqueline','Madeleine','Rosalie',
    ];

    private const POSTES_M = [
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
        'Gestionnaire de portefeuille',
        'Analyste de crédit',
        'Chargé des opérations',
    ];

    private const POSTES_F = [
        'Caissière',
        'Chargée de crédit',
        'Chargée de compte',
        'Agente de saisie',
        'Préposée aux dépôts',
        'Chargée de recouvrement',
        'Animatrice de crédit',
        'Guichetière',
        'Chargée de clientèle',
        'Agente de terrain',
        'Technicienne de crédit',
        'Agente d\'accueil',
        'Gestionnaire de portefeuille',
        'Analyste de crédit',
        'Chargée des opérations',
    ];

    private const THEMES_FORMATION = [
        ['theme' => 'Gestion des risques de crédit',               'domaine' => 'finance'],
        ['theme' => 'Techniques de recouvrement de créances',      'domaine' => 'finance'],
        ['theme' => 'Analyse financière des PME/PMI',              'domaine' => 'finance'],
        ['theme' => 'Microfinance et finance inclusive',           'domaine' => 'finance'],
        ['theme' => 'Gestion de la relation client',               'domaine' => 'commercial'],
        ['theme' => 'Techniques de vente et de négociation',       'domaine' => 'commercial'],
        ['theme' => 'Prospection et fidélisation de la clientèle', 'domaine' => 'commercial'],
        ['theme' => 'Lutte contre le blanchiment de capitaux',     'domaine' => 'juridique'],
        ['theme' => 'Réglementation BCEAO et normes prudentielles','domaine' => 'juridique'],
        ['theme' => 'Droit du travail et contrats',                'domaine' => 'juridique'],
        ['theme' => 'Leadership et management d\'équipe',          'domaine' => 'management'],
        ['theme' => 'Planification et suivi des activités',        'domaine' => 'management'],
        ['theme' => 'Communication professionnelle efficace',      'domaine' => 'management'],
        ['theme' => 'Gestion du temps et des priorités',           'domaine' => 'management'],
        ['theme' => 'Gestion des ressources humaines',             'domaine' => 'rh'],
        ['theme' => 'Évaluation des performances du personnel',    'domaine' => 'rh'],
        ['theme' => 'Recrutement et intégration des collaborateurs','domaine' => 'rh'],
        ['theme' => 'Prévention et gestion des conflits',          'domaine' => 'rh'],
        ['theme' => 'Sécurité informatique et protection des données','domaine' => 'securite'],
        ['theme' => 'Prévention des fraudes et détournements',     'domaine' => 'securite'],
        ['theme' => 'Utilisation du logiciel PERFECT Comptable',   'domaine' => 'informatique'],
        ['theme' => 'Maîtrise des outils bureautiques (Excel avancé)','domaine' => 'informatique'],
        ['theme' => 'Gestion des opérations de caisse',            'domaine' => 'operations'],
        ['theme' => 'Procédures de contrôle interne',              'domaine' => 'operations'],
        ['theme' => 'Gestion du stress et bien-être au travail',   'domaine' => 'rh'],
    ];

    // ── Point d'entrée ───────────────────────────────────────────────────────

    public function run(): void
    {
        $this->now = now();

        if (DB::table('entites')->count() > 0) {
            $this->command->warn('Faîtière déjà créée — GrandSeeder ignoré.');
            return;
        }

        DB::disableQueryLog();

        // 1. Année d'exercice 2026
        $this->createAnnee();

        // 2. Faîtière RCPB
        $entiteId = $this->createFaitiere();

        // 3. Directions internes
        $this->createDirections($entiteId);

        // 4. Réseau terrain (DTs → Caisses → Agences → Guichets)
        $this->createReseauTerrain($entiteId);

        // 5. Formations
        $this->createFormations();

        $this->printStats();
    }

    // ── Année 2026 ───────────────────────────────────────────────────────────

    private function createAnnee(): void
    {
        if (DB::table('annees')->where('annee', 2026)->exists()) {
            return;
        }

        $anneeId = DB::table('annees')->insertGetId([
            'annee'      => 2026,
            'statut'     => 'ouvert',
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);

        DB::table('semestres')->insert([
            [
                'annee_id'   => $anneeId,
                'numero'     => 1,
                'statut'     => 'ouvert',
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ],
            [
                'annee_id'   => $anneeId,
                'numero'     => 2,
                'statut'     => 'cloture',
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ],
        ]);
    }

    // ── Faîtière ─────────────────────────────────────────────────────────────

    private function createFaitiere(): int
    {
        $entiteId = DB::table('entites')->insertGetId([
            'nom'                    => 'Réseau des Caisses Populaires du Burkina (RCPB)',
            'ville'                  => 'Ouagadougou',
            'region'                 => 'Centre',
            'secretariat_telephone'  => '25 30 60 60',
            'created_at'             => $this->now,
            'updated_at'             => $this->now,
        ]);

        $pca = $this->makeAgent([
            'nom'        => 'Kaboré',
            'prenom'     => 'Seydou',
            'sexe'       => 'M',
            'role'       => 'PCA',
            'poste'      => 'Président du Conseil d\'Administration',
            'email'      => 'pca@rcpb.bf',
            'entite_id'  => $entiteId,
        ], 'PCA');

        $dg = $this->makeAgent([
            'nom'       => 'Ouédraogo',
            'prenom'    => 'Mahamoudou',
            'sexe'      => 'M',
            'role'      => 'Directeur Général',
            'poste'     => 'Directeur Général',
            'email'     => 'dg@rcpb.bf',
            'entite_id' => $entiteId,
        ], 'DG');

        $dga = $this->makeAgent([
            'nom'       => 'Traoré',
            'prenom'    => 'Aminata',
            'sexe'      => 'F',
            'role'      => 'DGA',
            'poste'     => 'Directeur Général Adjoint',
            'email'     => 'dga@rcpb.bf',
            'entite_id' => $entiteId,
        ], 'DGA');

        $dgaSec = $this->makeAgent([
            'nom'       => 'Sawadogo',
            'prenom'    => 'Awa',
            'sexe'      => 'F',
            'role'      => 'Secrétaire DGA',
            'poste'     => 'Secrétaire de Direction',
            'email'     => 'sec.dga@rcpb.bf',
            'entite_id' => $entiteId,
        ], 'Secretaire_Assistante');

        $assistante = $this->makeAgent([
            'nom'       => 'Zoungrana',
            'prenom'    => 'Fatimata',
            'sexe'      => 'F',
            'role'      => 'Assistante DG',
            'poste'     => 'Assistante de Direction',
            'email'     => 'assistante.dg@rcpb.bf',
            'entite_id' => $entiteId,
        ], 'Assistante_Dg');

        $conseil1 = $this->makeAgent([
            'nom'       => 'Compaoré',
            'prenom'    => 'Ibrahim',
            'sexe'      => 'M',
            'role'      => 'Conseiller DG',
            'poste'     => 'Conseiller juridique',
            'email'     => 'conseil1.dg@rcpb.bf',
            'entite_id' => $entiteId,
        ], 'Conseillers_Dg');

        $conseil2 = $this->makeAgent([
            'nom'       => 'Belem',
            'prenom'    => 'Salimata',
            'sexe'      => 'F',
            'role'      => 'Conseiller DG',
            'poste'     => 'Conseillère en développement institutionnel',
            'email'     => 'conseil2.dg@rcpb.bf',
            'entite_id' => $entiteId,
        ], 'Conseillers_Dg');

        $rh = $this->makeAgent([
            'nom'       => 'Nikiema',
            'prenom'    => 'Pascaline',
            'sexe'      => 'F',
            'role'      => 'Agent',
            'poste'     => 'Responsable Ressources Humaines',
            'email'     => 'rh@rcpb.bf',
            'entite_id' => $entiteId,
        ], 'RH');

        DB::table('entites')->where('id', $entiteId)->update([
            'pca_agent_id'            => $pca,
            'dg_agent_id'             => $dg,
            'dga_agent_id'            => $dga,
            'dga_secretaire_agent_id' => $dgaSec,
            'assistante_agent_id'     => $assistante,
        ]);

        return $entiteId;
    }

    // ── Directions internes ──────────────────────────────────────────────────

    private function createDirections(int $entiteId): void
    {
        $defs = [
            ['nom' => 'Direction Administrative et Financière (DAF)', 'pfx' => 'daf'],
            ['nom' => 'Direction des Ressources Humaines (DRH)',      'pfx' => 'drh'],
            ['nom' => 'Direction des Opérations et du Réseau (DOR)',  'pfx' => 'dor'],
        ];

        foreach ($defs as $d) {
            $dirId = DB::table('directions')->insertGetId([
                'nom'        => $d['nom'],
                'entite_id'  => $entiteId,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);

            $dir = $this->makeAgent([
                'nom'          => $this->pickNom(),
                'prenom'       => $this->pickPrenom('M'),
                'sexe'         => 'M',
                'role'         => 'Directeur de Direction',
                'poste'        => 'Directeur de Direction',
                'email'        => "{$d['pfx']}.dir@rcpb.bf",
                'direction_id' => $dirId,
            ], 'Directeur_Direction');

            $sec = $this->makeAgent([
                'nom'          => $this->pickNom(),
                'prenom'       => $this->pickPrenom('F'),
                'sexe'         => 'F',
                'role'         => 'Secrétaire de Direction',
                'poste'        => 'Secrétaire de Direction',
                'email'        => "{$d['pfx']}.sec@rcpb.bf",
                'direction_id' => $dirId,
            ], 'Secretaire_Direction');

            DB::table('directions')->where('id', $dirId)->update([
                'directeur_agent_id'  => $dir,
                'secretaire_agent_id' => $sec,
            ]);

            // 2 services par direction, 1 chef + 4 agents chacun
            for ($s = 1; $s <= 2; $s++) {
                $svcId = DB::table('services')->insertGetId([
                    'nom'          => "Service {$s} — {$d['nom']}",
                    'direction_id' => $dirId,
                    'created_at'   => $this->now,
                    'updated_at'   => $this->now,
                ]);

                $sxChef = $s === 1 ? 'M' : 'F';
                $chef = $this->makeAgent([
                    'nom'          => $this->pickNom(),
                    'prenom'       => $this->pickPrenom($sxChef),
                    'sexe'         => $sxChef,
                    'role'         => 'Chef de Service',
                    'poste'        => 'Chef de Service',
                    'email'        => "{$d['pfx']}.svc{$s}.chef@rcpb.bf",
                    'direction_id' => $dirId,
                    'service_id'   => $svcId,
                ], 'Chef_Service');

                DB::table('services')->where('id', $svcId)->update(['chef_agent_id' => $chef]);

                for ($a = 1; $a <= 4; $a++) {
                    $sx  = $a % 2 === 0 ? 'F' : 'M';
                    $mat = $this->nextMatricule();
                    $this->makeAgent([
                        'nom'          => $this->pickNom(),
                        'prenom'       => $this->pickPrenom($sx),
                        'sexe'         => $sx,
                        'role'         => 'Agent',
                        'poste'        => $this->pickPoste($sx),
                        'matricule'    => $mat,
                        'email'        => "{$d['pfx']}.svc{$s}.ag{$a}.{$mat}@rcpb.bf",
                        'direction_id' => $dirId,
                        'service_id'   => $svcId,
                    ]);
                }
            }

            // 5 agents directs de la direction (hors service)
            for ($a = 1; $a <= 5; $a++) {
                $sx  = $a % 2 === 0 ? 'F' : 'M';
                $mat = $this->nextMatricule();
                $this->makeAgent([
                    'nom'          => $this->pickNom(),
                    'prenom'       => $this->pickPrenom($sx),
                    'sexe'         => $sx,
                    'role'         => 'Agent',
                    'poste'        => $this->pickPoste($sx),
                    'matricule'    => $mat,
                    'email'        => "{$d['pfx']}.ag{$a}.{$mat}@rcpb.bf",
                    'direction_id' => $dirId,
                ]);
            }
        }
    }

    // ── Réseau terrain ───────────────────────────────────────────────────────

    private function createReseauTerrain(int $entiteId): void
    {
        foreach ($this->getDtsDef() as $dtDef) {
            $this->createDT($entiteId, $dtDef);
        }
    }

    private function getDtsDef(): array
    {
        return [
            [
                'region' => 'Nord',
                'ville'  => 'Ouahigouya',
                'tel'    => '25 55 20 10',
                'pfx'    => 'nord',
                'caisses' => [
                    [
                        'nom' => 'Caisse Ouahigouya Centre', 'annee' => '2001',
                        'quartier' => 'Centre', 'ville' => 'Ouahigouya',
                        'pfx' => 'ouahi.c',
                        'agences' => [
                            ['nom' => 'Agence Secteur 9',    'pfx' => 'ouahi.a1',
                             'guichets' => ['Guichet Marché Central', 'Guichet Gare Routière']],
                            ['nom' => 'Agence Secteur 22',   'pfx' => 'ouahi.a2',
                             'guichets' => ['Guichet Secteur 22 Centre']],
                        ],
                    ],
                    [
                        'nom' => 'Caisse Titao', 'annee' => '2005',
                        'quartier' => 'Centre', 'ville' => 'Titao',
                        'pfx' => 'titao.c',
                        'agences' => [
                            ['nom' => 'Agence Titao Nord',   'pfx' => 'titao.a1',
                             'guichets' => ['Guichet Titao Marché']],
                        ],
                    ],
                    [
                        'nom' => 'Caisse Yako', 'annee' => '2008',
                        'quartier' => null, 'ville' => 'Yako',
                        'pfx' => 'yako.c',
                        'agences' => [
                            ['nom' => 'Agence Yako Secteur 2', 'pfx' => 'yako.a1',
                             'guichets' => ['Guichet Yako Centre']],
                        ],
                    ],
                ],
            ],
            [
                'region' => 'Est',
                'ville'  => "Fada N'Gourma",
                'tel'    => '25 77 00 15',
                'pfx'    => 'est',
                'caisses' => [
                    [
                        'nom' => "Caisse Fada N'Gourma Centre", 'annee' => '1998',
                        'quartier' => 'Secteur 1', 'ville' => "Fada N'Gourma",
                        'pfx' => 'fada.c',
                        'agences' => [
                            ['nom' => 'Agence Bilanga',     'pfx' => 'bilanga.a1',
                             'guichets' => ['Guichet Bilanga Gare']],
                            ['nom' => 'Agence Kantchari',   'pfx' => 'kantchari.a1',
                             'guichets' => []],
                        ],
                    ],
                    [
                        'nom' => 'Caisse Diapaga', 'annee' => '2003',
                        'quartier' => null, 'ville' => 'Diapaga',
                        'pfx' => 'diapaga.c',
                        'agences' => [
                            ['nom' => 'Agence Diapaga Centre', 'pfx' => 'diapaga.a1',
                             'guichets' => ['Guichet Diapaga Marché']],
                        ],
                    ],
                    [
                        'nom' => 'Caisse Bogandé', 'annee' => '2010',
                        'quartier' => null, 'ville' => 'Bogandé',
                        'pfx' => 'bogande.c',
                        'agences' => [
                            ['nom' => 'Agence Bogandé Centre', 'pfx' => 'bogande.a1',
                             'guichets' => []],
                        ],
                    ],
                ],
            ],
            [
                'region' => 'Hauts-Bassins',
                'ville'  => 'Bobo-Dioulasso',
                'tel'    => '25 97 10 30',
                'pfx'    => 'ouest',
                'caisses' => [
                    [
                        'nom' => 'Caisse Bobo-Dioulasso Centre', 'annee' => '1993',
                        'quartier' => 'Secteur 5', 'ville' => 'Bobo-Dioulasso',
                        'pfx' => 'bobo.c',
                        'agences' => [
                            ['nom' => 'Agence Bobo Secteur 22', 'pfx' => 'bobo.a1',
                             'guichets' => ['Guichet Bobo S22 Centre', 'Guichet Bobo S22 Marché']],
                            ['nom' => 'Agence Bobo Secteur 30', 'pfx' => 'bobo.a2',
                             'guichets' => ['Guichet Bobo S30']],
                        ],
                    ],
                    [
                        'nom' => 'Caisse Banfora', 'annee' => '2000',
                        'quartier' => null, 'ville' => 'Banfora',
                        'pfx' => 'banfora.c',
                        'agences' => [
                            ['nom' => 'Agence Banfora Nord', 'pfx' => 'banfora.a1',
                             'guichets' => ['Guichet Banfora Marché']],
                            ['nom' => 'Agence Sindou',       'pfx' => 'sindou.a1',
                             'guichets' => []],
                        ],
                    ],
                    [
                        'nom' => 'Caisse Dédougou', 'annee' => '2007',
                        'quartier' => 'Centre', 'ville' => 'Dédougou',
                        'pfx' => 'dedougou.c',
                        'agences' => [
                            ['nom' => 'Agence Dédougou Centre', 'pfx' => 'dedougou.a1',
                             'guichets' => ['Guichet Dédougou Gare']],
                        ],
                    ],
                    [
                        'nom' => 'Caisse Nouna', 'annee' => '2012',
                        'quartier' => null, 'ville' => 'Nouna',
                        'pfx' => 'nouna.c',
                        'agences' => [
                            ['nom' => 'Agence Nouna Centre', 'pfx' => 'nouna.a1',
                             'guichets' => []],
                        ],
                    ],
                ],
            ],
            [
                'region' => 'Centre-Ouest',
                'ville'  => 'Koudougou',
                'tel'    => '25 44 05 20',
                'pfx'    => 'centre',
                'caisses' => [
                    [
                        'nom' => 'Caisse Koudougou Centre', 'annee' => '1997',
                        'quartier' => 'Centre', 'ville' => 'Koudougou',
                        'pfx' => 'koudou.c',
                        'agences' => [
                            ['nom' => 'Agence Koudougou Secteur 6',  'pfx' => 'koudou.a1',
                             'guichets' => ['Guichet Koudougou Marché', 'Guichet Koudougou Gare']],
                            ['nom' => 'Agence Koudougou Secteur 12', 'pfx' => 'koudou.a2',
                             'guichets' => []],
                        ],
                    ],
                    [
                        'nom' => 'Caisse Réo', 'annee' => '2004',
                        'quartier' => null, 'ville' => 'Réo',
                        'pfx' => 'reo.c',
                        'agences' => [
                            ['nom' => 'Agence Réo Centre', 'pfx' => 'reo.a1',
                             'guichets' => ['Guichet Réo Marché']],
                        ],
                    ],
                    [
                        'nom' => 'Caisse Léo', 'annee' => '2009',
                        'quartier' => null, 'ville' => 'Léo',
                        'pfx' => 'leo.c',
                        'agences' => [
                            ['nom' => 'Agence Léo Centre', 'pfx' => 'leo.a1',
                             'guichets' => []],
                        ],
                    ],
                ],
            ],
            [
                'region' => 'Sahel',
                'ville'  => 'Dori',
                'tel'    => '25 46 00 05',
                'pfx'    => 'sahel',
                'caisses' => [
                    [
                        'nom' => 'Caisse Dori', 'annee' => '2002',
                        'quartier' => 'Centre', 'ville' => 'Dori',
                        'pfx' => 'dori.c',
                        'agences' => [
                            ['nom' => 'Agence Dori Centre', 'pfx' => 'dori.a1',
                             'guichets' => ['Guichet Dori Marché']],
                        ],
                    ],
                    [
                        'nom' => 'Caisse Djibo', 'annee' => '2006',
                        'quartier' => null, 'ville' => 'Djibo',
                        'pfx' => 'djibo.c',
                        'agences' => [
                            ['nom' => 'Agence Djibo Centre', 'pfx' => 'djibo.a1',
                             'guichets' => []],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ── Création d'une DT ────────────────────────────────────────────────────

    private function createDT(int $entiteId, array $info): void
    {
        $dtId = DB::table('delegation_techniques')->insertGetId([
            'entite_id'             => $entiteId,
            'region'                => $info['region'],
            'ville'                 => $info['ville'],
            'secretariat_telephone' => $info['tel'],
            'created_at'            => $this->now,
            'updated_at'            => $this->now,
        ]);

        $dir = $this->makeAgent([
            'nom'                     => $this->pickNom(),
            'prenom'                  => $this->pickPrenom('M'),
            'sexe'                    => 'M',
            'role'                    => 'Directeur Technique',
            'poste'                   => 'Directeur Technique',
            'email'                   => "dt.{$info['pfx']}.dir@rcpb.bf",
            'delegation_technique_id' => $dtId,
        ], 'Directeur_Technique');

        $sec = $this->makeAgent([
            'nom'                     => $this->pickNom(),
            'prenom'                  => $this->pickPrenom('F'),
            'sexe'                    => 'F',
            'role'                    => 'Secrétaire Technique',
            'poste'                   => 'Secrétaire Technique',
            'email'                   => "dt.{$info['pfx']}.sec@rcpb.bf",
            'delegation_technique_id' => $dtId,
        ], 'Secretaire_Technique');

        DB::table('delegation_techniques')->where('id', $dtId)->update([
            'directeur_agent_id'  => $dir,
            'secretaire_agent_id' => $sec,
        ]);

        // 4 agents directs rattachés à la DT
        for ($i = 1; $i <= 4; $i++) {
            $sx  = $i % 2 === 0 ? 'F' : 'M';
            $mat = $this->nextMatricule();
            $this->makeAgent([
                'nom'                     => $this->pickNom(),
                'prenom'                  => $this->pickPrenom($sx),
                'sexe'                    => $sx,
                'role'                    => 'Agent',
                'poste'                   => $this->pickPoste($sx),
                'matricule'               => $mat,
                'email'                   => "dt.{$info['pfx']}.ag{$i}.{$mat}@rcpb.bf",
                'delegation_technique_id' => $dtId,
            ]);
        }

        foreach ($info['caisses'] as $caisseDef) {
            $this->createCaisse($dtId, $caisseDef);
        }
    }

    // ── Création d'une Caisse ────────────────────────────────────────────────

    private function createCaisse(int $dtId, array $info): void
    {
        $villeId = DB::table('villes')->insertGetId([
            'delegation_technique_id' => $dtId,
            'nom'                     => $info['ville'],
            'created_at'              => $this->now,
            'updated_at'              => $this->now,
        ]);

        $caisseId = DB::table('caisses')->insertGetId([
            'delegation_technique_id' => $dtId,
            'ville_id'                => $villeId,
            'nom'                     => $info['nom'],
            'annee_ouverture'         => $info['annee'],
            'quartier'                => $info['quartier'],
            'secretariat_telephone'   => $this->pickTelFixe(),
            'created_at'              => $this->now,
            'updated_at'              => $this->now,
        ]);

        // Alternance M/F selon la position dans la liste
        $sxDir = (strlen($info['pfx']) % 2 === 0) ? 'M' : 'F';
        $sxSec = $sxDir === 'M' ? 'F' : 'M';

        $dir = $this->makeAgent([
            'nom'                     => $this->pickNom(),
            'prenom'                  => $this->pickPrenom($sxDir),
            'sexe'                    => $sxDir,
            'role'                    => 'Directeur de Caisse',
            'poste'                   => 'Directeur de Caisse',
            'email'                   => "{$info['pfx']}.dir@rcpb.bf",
            'delegation_technique_id' => $dtId,
            'caisse_id'               => $caisseId,
        ], 'Directeur_Caisse');

        $sec = $this->makeAgent([
            'nom'                     => $this->pickNom(),
            'prenom'                  => $this->pickPrenom($sxSec),
            'sexe'                    => $sxSec,
            'role'                    => 'Secrétaire de Caisse',
            'poste'                   => 'Secrétaire de Caisse',
            'email'                   => "{$info['pfx']}.sec@rcpb.bf",
            'delegation_technique_id' => $dtId,
            'caisse_id'               => $caisseId,
        ], 'Secretaire_Caisse');

        DB::table('caisses')->where('id', $caisseId)->update([
            'directeur_agent_id'  => $dir,
            'secretaire_agent_id' => $sec,
        ]);

        // 2 services par caisse (Opérations + Crédit)
        foreach (['Service Opérations', 'Service Crédit'] as $idx => $svcNom) {
            $svcId = DB::table('services')->insertGetId([
                'nom'                     => $svcNom,
                'caisse_id'               => $caisseId,
                'delegation_technique_id' => $dtId,
                'created_at'              => $this->now,
                'updated_at'              => $this->now,
            ]);

            $sxChef = $idx === 0 ? 'M' : 'F';
            $chef = $this->makeAgent([
                'nom'                     => $this->pickNom(),
                'prenom'                  => $this->pickPrenom($sxChef),
                'sexe'                    => $sxChef,
                'role'                    => 'Chef de Service',
                'poste'                   => 'Chef de Service',
                'email'                   => "{$info['pfx']}.svc{$idx}.chef@rcpb.bf",
                'delegation_technique_id' => $dtId,
                'caisse_id'               => $caisseId,
                'service_id'              => $svcId,
            ], 'Chef_Service');

            DB::table('services')->where('id', $svcId)->update(['chef_agent_id' => $chef]);

            // 3 agents par service
            for ($a = 1; $a <= 3; $a++) {
                $sx  = $a % 2 === 0 ? 'F' : 'M';
                $mat = $this->nextMatricule();
                $this->makeAgent([
                    'nom'                     => $this->pickNom(),
                    'prenom'                  => $this->pickPrenom($sx),
                    'sexe'                    => $sx,
                    'role'                    => 'Agent',
                    'poste'                   => $this->pickPoste($sx),
                    'matricule'               => $mat,
                    'email'                   => "{$info['pfx']}.svc{$idx}.ag{$a}.{$mat}@rcpb.bf",
                    'delegation_technique_id' => $dtId,
                    'caisse_id'               => $caisseId,
                    'service_id'              => $svcId,
                ]);
            }
        }

        // 8 agents ordinaires rattachés directement à la caisse
        for ($a = 1; $a <= 8; $a++) {
            $sx  = $a % 2 === 0 ? 'F' : 'M';
            $mat = $this->nextMatricule();
            $this->makeAgent([
                'nom'                     => $this->pickNom(),
                'prenom'                  => $this->pickPrenom($sx),
                'sexe'                    => $sx,
                'role'                    => 'Agent',
                'poste'                   => $this->pickPoste($sx),
                'matricule'               => $mat,
                'email'                   => "{$info['pfx']}.cag{$a}.{$mat}@rcpb.bf",
                'delegation_technique_id' => $dtId,
                'caisse_id'               => $caisseId,
            ]);
        }

        foreach ($info['agences'] as $agenceDef) {
            $this->createAgence($dtId, $caisseId, $agenceDef);
        }
    }

    // ── Création d'une Agence ────────────────────────────────────────────────

    private function createAgence(int $dtId, int $caisseId, array $info): void
    {
        $chef = $this->makeAgent([
            'nom'                     => $this->pickNom(),
            'prenom'                  => $this->pickPrenom('M'),
            'sexe'                    => 'M',
            'role'                    => "Chef d'Agence",
            'poste'                   => "Chef d'Agence",
            'email'                   => "{$info['pfx']}.chef@rcpb.bf",
            'delegation_technique_id' => $dtId,
            'caisse_id'               => $caisseId,
        ], 'Chef_Agence');

        $sec = $this->makeAgent([
            'nom'                     => $this->pickNom(),
            'prenom'                  => $this->pickPrenom('F'),
            'sexe'                    => 'F',
            'role'                    => "Secrétaire d'Agence",
            'poste'                   => "Secrétaire d'Agence",
            'email'                   => "{$info['pfx']}.sec@rcpb.bf",
            'delegation_technique_id' => $dtId,
            'caisse_id'               => $caisseId,
        ], 'Secretaire_Agence');

        $agenceId = DB::table('agences')->insertGetId([
            'nom'                     => $info['nom'],
            'delegation_technique_id' => $dtId,
            'caisse_id'               => $caisseId,
            'chef_agent_id'           => $chef,
            'secretaire_agent_id'     => $sec,
            'telephone_accueil'       => $this->pickTelFixe(),
            'created_at'              => $this->now,
            'updated_at'              => $this->now,
        ]);

        DB::table('agents')->whereIn('id', [$chef, $sec])->update(['agence_id' => $agenceId]);

        // 5 agents dans l'agence
        for ($a = 1; $a <= 5; $a++) {
            $sx  = $a % 2 === 0 ? 'F' : 'M';
            $mat = $this->nextMatricule();
            $this->makeAgent([
                'nom'                     => $this->pickNom(),
                'prenom'                  => $this->pickPrenom($sx),
                'sexe'                    => $sx,
                'role'                    => 'Agent',
                'poste'                   => $this->pickPoste($sx),
                'matricule'               => $mat,
                'email'                   => "{$info['pfx']}.ag{$a}.{$mat}@rcpb.bf",
                'delegation_technique_id' => $dtId,
                'caisse_id'               => $caisseId,
                'agence_id'               => $agenceId,
            ]);
        }

        foreach ($info['guichets'] as $gIdx => $gNom) {
            $this->createGuichet($dtId, $caisseId, $agenceId, $gNom, "{$info['pfx']}.g{$gIdx}");
        }
    }

    // ── Création d'un Guichet ────────────────────────────────────────────────

    private function createGuichet(int $dtId, int $caisseId, int $agenceId, string $nom, string $pfx): void
    {
        $chef = $this->makeAgent([
            'nom'                     => $this->pickNom(),
            'prenom'                  => $this->pickPrenom('M'),
            'sexe'                    => 'M',
            'role'                    => 'Chef de Guichet',
            'poste'                   => 'Chef de Guichet',
            'email'                   => "{$pfx}.chef@rcpb.bf",
            'delegation_technique_id' => $dtId,
            'caisse_id'               => $caisseId,
            'agence_id'               => $agenceId,
        ], 'Chef_Guichet');

        $guichetId = DB::table('guichets')->insertGetId([
            'nom'               => $nom,
            'agence_id'         => $agenceId,
            'chef_agent_id'     => $chef,
            'telephone_accueil' => $this->pickTelFixe(),
            'created_at'        => $this->now,
            'updated_at'        => $this->now,
        ]);

        DB::table('agents')->where('id', $chef)->update(['guichet_id' => $guichetId]);

        // 3 agents dans le guichet
        for ($a = 1; $a <= 3; $a++) {
            $sx  = $a % 2 === 0 ? 'F' : 'M';
            $mat = $this->nextMatricule();
            $this->makeAgent([
                'nom'                     => $this->pickNom(),
                'prenom'                  => $this->pickPrenom($sx),
                'sexe'                    => $sx,
                'role'                    => 'Agent',
                'poste'                   => $this->pickPoste($sx),
                'matricule'               => $mat,
                'email'                   => "{$pfx}.ag{$a}.{$mat}@rcpb.bf",
                'delegation_technique_id' => $dtId,
                'caisse_id'               => $caisseId,
                'agence_id'               => $agenceId,
                'guichet_id'              => $guichetId,
            ]);
        }
    }

    // ── Formations ───────────────────────────────────────────────────────────

    private function createFormations(): void
    {
        $adminId = DB::table('users')->where('role', 'Admin')->value('id');
        if (! $adminId) {
            return;
        }

        $agentIds = DB::table('agents')->pluck('id')->shuffle()->take(60)->values();

        foreach ($agentIds as $i => $agentId) {
            $f     = self::THEMES_FORMATION[$i % count(self::THEMES_FORMATION)];
            $duree = (rand(2, 5)) * 8; // 16, 24, 32, 40 h
            $debut = now()->subMonths(rand(2, 30))->subDays(rand(0, 15));
            $fin   = (clone $debut)->addDays((int) ($duree / 8));

            DB::table('formations')->insert([
                'agent_id'     => $agentId,
                'theme'        => $f['theme'],
                'domaine'      => $f['domaine'],
                'date_debut'   => $debut->toDateString(),
                'date_fin'     => $fin->toDateString(),
                'duree_heures' => $duree,
                'created_by'   => $adminId,
                'created_at'   => $this->now,
                'updated_at'   => $this->now,
            ]);
        }
    }

    // ── makeAgent ────────────────────────────────────────────────────────────

    /**
     * Insère un agent avec toutes ses colonnes.
     * Si $userRole est fourni, crée également le compte User associé.
     * Retourne l'id de l'agent créé.
     */
    private function makeAgent(array $attrs, ?string $userRole = null): int
    {
        $sexeCode = $attrs['sexe'] ?? 'M';  // 'M' ou 'F' (code interne)
        $mat      = $attrs['matricule'] ?? $this->nextMatricule();

        $agentId = DB::table('agents')->insertGetId([
            'entite_id'               => $attrs['entite_id']               ?? null,
            'direction_id'            => $attrs['direction_id']            ?? null,
            'delegation_technique_id' => $attrs['delegation_technique_id'] ?? null,
            'caisse_id'               => $attrs['caisse_id']               ?? null,
            'agence_id'               => $attrs['agence_id']               ?? null,
            'guichet_id'              => $attrs['guichet_id']              ?? null,
            'service_id'              => $attrs['service_id']              ?? null,
            'nom'                     => $attrs['nom'],
            'prenom'                  => $attrs['prenom'],
            'sexe'                    => $sexeCode === 'F' ? 'Féminin' : 'Masculin',
            'email'                   => $attrs['email'],
            'numero_telephone'        => $this->pickTelMobile(),
            'photo_path'              => null,
            'matricule'               => $mat,
            'role'                    => $attrs['role'],
            'poste'                   => $attrs['poste'] ?? null,
            'date_debut_fonction'     => now()->subMonths(rand(6, 144))->toDateString(),
            'created_at'              => $this->now,
            'updated_at'              => $this->now,
        ]);

        if ($userRole !== null) {
            DB::table('users')->insert([
                'name'                 => trim($attrs['prenom'] . ' ' . $attrs['nom']),
                'email'                => $attrs['email'],
                'password'             => Hash::make('11111111'),
                'role'                 => $userRole,
                'agent_id'             => $agentId,
                'manager_id'           => null,
                'must_change_password' => true,
                'is_active'            => true,
                'created_at'           => $this->now,
                'updated_at'           => $this->now,
            ]);
        }

        return $agentId;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function nextMatricule(): string
    {
        return 'RC' . str_pad(++$this->matriculeCounter, 5, '0', STR_PAD_LEFT);
    }

    private function pickNom(): string
    {
        return self::NOMS[$this->nomIdx++ % count(self::NOMS)];
    }

    private function pickPrenom(string $sx): string
    {
        if ($sx === 'F') {
            return self::PRENOMS_F[$this->prenomFIdx++ % count(self::PRENOMS_F)];
        }
        return self::PRENOMS_M[$this->prenomMIdx++ % count(self::PRENOMS_M)];
    }

    private function pickPoste(string $sx): string
    {
        if ($sx === 'F') {
            return self::POSTES_F[$this->posteIdx++ % count(self::POSTES_F)];
        }
        return self::POSTES_M[$this->posteIdx++ % count(self::POSTES_M)];
    }

    /** Numéro mobile burkinabé (7X XX XX XX) */
    private function pickTelMobile(): string
    {
        return '7' . rand(0, 9) . ' '
            . str_pad((string) rand(0, 99), 2, '0') . ' '
            . str_pad((string) rand(0, 99), 2, '0') . ' '
            . str_pad((string) rand(0, 99), 2, '0');
    }

    /** Numéro fixe burkinabé (25 XX XX XX) */
    private function pickTelFixe(): string
    {
        return '25 '
            . str_pad((string) rand(10, 99), 2, '0') . ' '
            . str_pad((string) rand(0, 99), 2, '0') . ' '
            . str_pad((string) rand(0, 99), 2, '0');
    }

    // ── Stats finales ────────────────────────────────────────────────────────

    private function printStats(): void
    {
        $this->command->info('═══════════════════════════════════════════════');
        $this->command->info('  GRAND SEEDER — Résultats');
        $this->command->info('───────────────────────────────────────────────');
        $this->command->info('✓ Entites (faîtière) : ' . DB::table('entites')->count());
        $this->command->info('✓ Directions         : ' . DB::table('directions')->count());
        $this->command->info('✓ Délégations Tech.  : ' . DB::table('delegation_techniques')->count());
        $this->command->info('✓ Villes             : ' . DB::table('villes')->count());
        $this->command->info('✓ Caisses            : ' . DB::table('caisses')->count());
        $this->command->info('✓ Services           : ' . DB::table('services')->count());
        $this->command->info('✓ Agences            : ' . DB::table('agences')->count());
        $this->command->info('✓ Guichets           : ' . DB::table('guichets')->count());
        $this->command->info('✓ Agents             : ' . DB::table('agents')->count());
        $this->command->info('✓ Users (avec compte): ' . DB::table('users')->count());
        $this->command->info('✓ Formations         : ' . DB::table('formations')->count());
        $this->command->info('✓ Annees             : ' . DB::table('annees')->count());
        $this->command->info('✓ Semestres          : ' . DB::table('semestres')->count());
        $this->command->info('═══════════════════════════════════════════════');
    }
}
