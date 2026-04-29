<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Agence;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Guichet;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    private const FONCTION_TO_ROLE = [
        'PCA'                     => 'PCA',
        'Directeur Général'       => 'DG',
        'DGA'                     => 'DGA',
        'Assistante DG'           => 'Assistante_Dg',
        'Conseiller DG'           => 'Conseillers_Dg',
        'Secrétaire Assistante'   => 'Secretaire_Assistante',
        'Directeur de Direction'  => 'Directeur_Direction',
        'Secrétaire de Direction' => 'Secretaire_Direction',
        'Directeur Technique'     => 'Directeur_Technique',
        'Secrétaire Technique'    => 'Secretaire_Technique',
        'Directeur de Caisse'     => 'Directeur_Caisse',
        'Secrétaire de Caisse'    => 'Secretaire_Caisse',
        "Chef d'Agence"           => 'Chef_Agence',
        "Secrétaire d'Agence"     => 'Secretaire_Agence',
        'Chef de Guichet'         => 'Chef_Guichet',
        'Chef de Service'         => 'Chef_Service',
        'Agent'                   => 'Agent',
    ];

    public function run(): void
    {
        $pwd = Hash::make('11111111');

        // 1. FAÎTIÈRE
        $entite = Entite::create([
            'nom'    => 'RCPB',
            'ville'  => 'Ouagadougou',
            'region' => 'Centre',
        ]);

        // 2. DÉLÉGATIONS TECHNIQUES
        $dt1 = DelegationTechnique::create(['entite_id' => $entite->id, 'region' => 'Nord',  'ville' => 'Ouahigouya',    'secretariat_telephone' => '25451100']);
        $dt2 = DelegationTechnique::create(['entite_id' => $entite->id, 'region' => 'Est',   'ville' => "Fada N'Gourma", 'secretariat_telephone' => '25451200']);
        $dt3 = DelegationTechnique::create(['entite_id' => $entite->id, 'region' => 'Ouest', 'ville' => 'Bobo-Dioulasso','secretariat_telephone' => '25451300']);

        // 3. CAISSES (2 par délégation)
        $c1 = Caisse::create(['nom' => 'Caisse Ouahigouya Centre', 'delegation_technique_id' => $dt1->id]);
        $c2 = Caisse::create(['nom' => 'Caisse Titao',             'delegation_technique_id' => $dt1->id]);
        $c3 = Caisse::create(['nom' => 'Caisse Fada Centre',       'delegation_technique_id' => $dt2->id]);
        $c4 = Caisse::create(['nom' => 'Caisse Diapaga',           'delegation_technique_id' => $dt2->id]);
        $c5 = Caisse::create(['nom' => 'Caisse Bobo Centre',       'delegation_technique_id' => $dt3->id]);
        $c6 = Caisse::create(['nom' => 'Caisse Banfora',           'delegation_technique_id' => $dt3->id]);

        // 4. AGENCES
        $ag1 = Agence::create(['nom' => 'Agence Ouahigouya Principale', 'delegation_technique_id' => $dt1->id, 'caisse_id' => $c1->id]);
        $ag2 = Agence::create(['nom' => 'Agence Fada Principale',       'delegation_technique_id' => $dt2->id, 'caisse_id' => $c3->id]);
        $ag3 = Agence::create(['nom' => 'Agence Bobo Principale',       'delegation_technique_id' => $dt3->id, 'caisse_id' => $c5->id]);

        // 5. GUICHETS
        $gu1 = Guichet::create(['nom' => 'Guichet Ouahigouya A', 'agence_id' => $ag1->id]);
        $gu2 = Guichet::create(['nom' => 'Guichet Fada A',       'agence_id' => $ag2->id]);
        $gu3 = Guichet::create(['nom' => 'Guichet Bobo A',       'agence_id' => $ag3->id]);

        // 6. SERVICES
        $sv1 = Service::create(['nom' => 'Service Crédit Nord',         'delegation_technique_id' => $dt1->id, 'caisse_id' => $c1->id]);
        $sv2 = Service::create(['nom' => "Service Épargne Est",         'delegation_technique_id' => $dt2->id, 'caisse_id' => $c3->id]);
        $sv3 = Service::create(['nom' => 'Service Comptabilité Ouest',  'delegation_technique_id' => $dt3->id, 'caisse_id' => $c5->id]);

        // 7. AGENTS (25 au total, toutes fonctions représentées)
        $agents = [
            // Faîtière
            ['prenom' => 'Amadou',    'nom' => 'Ouedraogo',    'fonction' => 'PCA',                   'sexe' => 'homme', 'email' => 'pca@rcpb.bf',         'aff' => ['entite_id' => $entite->id]],
            ['prenom' => 'Issa',      'nom' => 'Sawadogo',     'fonction' => 'Directeur Général',      'sexe' => 'homme', 'email' => 'dg@rcpb.bf',          'aff' => ['entite_id' => $entite->id]],
            ['prenom' => 'Fatou',     'nom' => 'Diallo',       'fonction' => 'DGA',                   'sexe' => 'femme', 'email' => 'dga@rcpb.bf',         'aff' => ['entite_id' => $entite->id]],
            ['prenom' => 'Aissata',   'nom' => 'Kone',         'fonction' => 'Assistante DG',         'sexe' => 'femme', 'email' => 'assistante@rcpb.bf',  'aff' => ['entite_id' => $entite->id]],
            ['prenom' => 'Moussa',    'nom' => 'Traore',       'fonction' => 'Conseiller DG',         'sexe' => 'homme', 'email' => 'conseiller@rcpb.bf',  'aff' => ['entite_id' => $entite->id]],
            ['prenom' => 'Bintou',    'nom' => 'Coulibaly',    'fonction' => 'Secrétaire Assistante', 'sexe' => 'femme', 'email' => 'secassist@rcpb.bf',   'aff' => ['entite_id' => $entite->id]],
            // Délégations
            ['prenom' => 'Ibrahim',   'nom' => 'Zongo',        'fonction' => 'Directeur Technique',   'sexe' => 'homme', 'email' => 'dt.nord@rcpb.bf',     'aff' => ['delegation_technique_id' => $dt1->id]],
            ['prenom' => 'Mariam',    'nom' => 'Ouattara',     'fonction' => 'Secrétaire Technique',  'sexe' => 'femme', 'email' => 'sec.dt.nord@rcpb.bf', 'aff' => ['delegation_technique_id' => $dt1->id]],
            ['prenom' => 'Salif',     'nom' => 'Compaore',     'fonction' => 'Directeur Technique',   'sexe' => 'homme', 'email' => 'dt.est@rcpb.bf',      'aff' => ['delegation_technique_id' => $dt2->id]],
            ['prenom' => 'Kadiatou',  'nom' => 'Barry',        'fonction' => 'Secrétaire Technique',  'sexe' => 'femme', 'email' => 'sec.dt.est@rcpb.bf',  'aff' => ['delegation_technique_id' => $dt2->id]],
            ['prenom' => 'Boureima',  'nom' => 'Kabore',       'fonction' => 'Directeur Technique',   'sexe' => 'homme', 'email' => 'dt.ouest@rcpb.bf',    'aff' => ['delegation_technique_id' => $dt3->id]],
            // Caisses
            ['prenom' => 'Rokia',     'nom' => 'Tiendrebeogo', 'fonction' => 'Directeur de Caisse',   'sexe' => 'femme', 'email' => 'dir.c1@rcpb.bf',      'aff' => ['caisse_id' => $c1->id]],
            ['prenom' => 'Adama',     'nom' => 'Nana',         'fonction' => 'Secrétaire de Caisse',  'sexe' => 'homme', 'email' => 'sec.c1@rcpb.bf',      'aff' => ['caisse_id' => $c1->id]],
            ['prenom' => 'Safiatou',  'nom' => 'Sana',         'fonction' => 'Directeur de Caisse',   'sexe' => 'femme', 'email' => 'dir.c3@rcpb.bf',      'aff' => ['caisse_id' => $c3->id]],
            ['prenom' => 'Daouda',    'nom' => 'Poda',         'fonction' => 'Secrétaire de Caisse',  'sexe' => 'homme', 'email' => 'sec.c3@rcpb.bf',      'aff' => ['caisse_id' => $c3->id]],
            // Agences
            ['prenom' => 'Awa',       'nom' => 'Tapsoba',      'fonction' => "Chef d'Agence",         'sexe' => 'femme', 'email' => 'chef.ag1@rcpb.bf',    'aff' => ['agence_id' => $ag1->id]],
            ['prenom' => 'Seydou',    'nom' => 'Ouedraogo',    'fonction' => "Secrétaire d'Agence",   'sexe' => 'homme', 'email' => 'sec.ag1@rcpb.bf',     'aff' => ['agence_id' => $ag1->id]],
            ['prenom' => 'Hawa',      'nom' => 'Belem',        'fonction' => "Chef d'Agence",         'sexe' => 'femme', 'email' => 'chef.ag2@rcpb.bf',    'aff' => ['agence_id' => $ag2->id]],
            // Guichets
            ['prenom' => 'Lamine',    'nom' => 'Sawadogo',     'fonction' => 'Chef de Guichet',       'sexe' => 'homme', 'email' => 'chef.gu1@rcpb.bf',    'aff' => ['guichet_id' => $gu1->id]],
            // Services
            ['prenom' => 'Fatoumata', 'nom' => 'Ouattara',     'fonction' => 'Chef de Service',       'sexe' => 'femme', 'email' => 'chef.sv1@rcpb.bf',    'aff' => ['service_id' => $sv1->id]],
            ['prenom' => 'Hamidou',   'nom' => 'Ilboudo',      'fonction' => 'Chef de Service',       'sexe' => 'homme', 'email' => 'chef.sv2@rcpb.bf',    'aff' => ['service_id' => $sv2->id]],
            // Agents simples
            ['prenom' => 'Marthe',    'nom' => 'Kinda',        'fonction' => 'Agent',                 'sexe' => 'femme', 'email' => 'agent1@rcpb.bf',      'aff' => ['caisse_id' => $c1->id]],
            ['prenom' => 'Pascal',    'nom' => 'Bikienga',     'fonction' => 'Agent',                 'sexe' => 'homme', 'email' => 'agent2@rcpb.bf',      'aff' => ['caisse_id' => $c3->id]],
            ['prenom' => 'Aminata',   'nom' => 'Yameogo',      'fonction' => 'Agent',                 'sexe' => 'femme', 'email' => 'agent3@rcpb.bf',      'aff' => ['agence_id' => $ag3->id]],
            ['prenom' => 'Jules',     'nom' => 'Kaboret',      'fonction' => 'Agent',                 'sexe' => 'homme', 'email' => 'agent4@rcpb.bf',      'aff' => ['service_id' => $sv3->id]],
        ];

        // Map email → agent créé (pour lier les FK inverses après)
        $createdAgents = [];

        foreach ($agents as $data) {
            $aff = $data['aff'];
            unset($data['aff']);

            $agent = Agent::create(array_merge($data, $aff, [
                'date_debut_fonction' => '2020-01-01',
                'numero_telephone'    => '7000' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT),
            ]));

            $createdAgents[$data['email']] = $agent;

            User::create([
                'name'                 => $data['prenom'] . ' ' . $data['nom'],
                'email'                => $data['email'],
                'password'             => $pwd,
                'role'                 => self::FONCTION_TO_ROLE[$data['fonction']] ?? 'Agent',
                'agent_id'             => $agent->id,
                'must_change_password' => 0,
            ]);
        }

        // 8. FK INVERSES — lier chefs/directeurs/secrétaires à leurs structures
        $dt1->update([
            'directeur_agent_id' => $createdAgents['dt.nord@rcpb.bf']->id,
            'secretaire_agent_id' => $createdAgents['sec.dt.nord@rcpb.bf']->id,
        ]);
        $dt2->update([
            'directeur_agent_id'  => $createdAgents['dt.est@rcpb.bf']->id,
            'secretaire_agent_id' => $createdAgents['sec.dt.est@rcpb.bf']->id,
        ]);
        $dt3->update([
            'directeur_agent_id'  => $createdAgents['dt.ouest@rcpb.bf']->id,
        ]);
        $c1->update([
            'directeur_agent_id'  => $createdAgents['dir.c1@rcpb.bf']->id,
            'secretaire_agent_id' => $createdAgents['sec.c1@rcpb.bf']->id,
        ]);
        $c3->update([
            'directeur_agent_id'  => $createdAgents['dir.c3@rcpb.bf']->id,
            'secretaire_agent_id' => $createdAgents['sec.c3@rcpb.bf']->id,
        ]);
        $ag1->update([
            'chef_agent_id'       => $createdAgents['chef.ag1@rcpb.bf']->id,
            'secretaire_agent_id' => $createdAgents['sec.ag1@rcpb.bf']->id,
        ]);
        $ag2->update([
            'chef_agent_id'       => $createdAgents['chef.ag2@rcpb.bf']->id,
        ]);
        $gu1->update([
            'chef_agent_id'       => $createdAgents['chef.gu1@rcpb.bf']->id,
        ]);
        $sv1->update([
            'chef_agent_id'       => $createdAgents['chef.sv1@rcpb.bf']->id,
        ]);
        $sv2->update([
            'chef_agent_id'       => $createdAgents['chef.sv2@rcpb.bf']->id,
        ]);

        // Lier entité aux agents DG/DGA/PCA/Assistante
        $entite->update([
            'pca_agent_id'        => $createdAgents['pca@rcpb.bf']->id,
            'dg_agent_id'         => $createdAgents['dg@rcpb.bf']->id,
            'dga_agent_id'        => $createdAgents['dga@rcpb.bf']->id,
            'assistante_agent_id' => $createdAgents['assistante@rcpb.bf']->id,
        ]);

        // 9. DIRECTIONS
        // Direction Générale (obligatoire, référencée par DirectionGeneraleController)
        $directionGenerale = Direction::create([
            'entite_id'          => $entite->id,
            'nom'                => 'Direction Générale',
            'directeur_agent_id' => $createdAgents['dg@rcpb.bf']->id,
        ]);

        // Directions fonctionnelles
        Direction::create(['entite_id' => $entite->id, 'nom' => 'Direction des Ressources Humaines']);
        Direction::create(['entite_id' => $entite->id, 'nom' => 'Direction Administrative et Financière']);
        Direction::create(['entite_id' => $entite->id, 'nom' => "Direction des Technologies et de l'Information"]);

        $this->command->info('✓ Faîtière, 3 délégations, 6 caisses, 3 agences, 3 guichets, 3 services créés.');
        $this->command->info('✓ Direction Générale + 3 directions fonctionnelles créées.');
        $this->command->info('✓ 25 agents + 25 comptes + toutes FK inverses liées (mdp: 11111111).');
    }
}
