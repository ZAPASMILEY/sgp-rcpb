<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectiveCriteriaTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Vider les tables dans l'ordre correct (FK constraint)
        DB::table('subjective_subcriteria_templates')->delete();
        DB::table('subjective_criteria_templates')->delete();

        $now = now();

        $criteria = [
            [
                'ordre' => 1,
                'titre' => 'Créativité et esprit d\'initiative',
                'subcriteria' => [
                    'Est capable de proposer de nouvelles solutions aux problèmes posés.',
                    'Sait prendre les devants en minimisant les risques d\'échec.',
                    'Entreprend des actions de coachings au profit des agents des caisses populaires.',
                ],
            ],
            [
                'ordre' => 2,
                'titre' => 'Disponibilité et conduite',
                'subcriteria' => [
                    'Sait faire preuve de rigueur et fermeté.',
                    'Est disponible en dehors des heures et jours légaux de travail.',
                    'Suit les instructions.',
                    'Possède une compréhension facile et juste des problèmes informatiques.',
                ],
            ],
            [
                'ordre' => 3,
                'titre' => 'Organisation',
                'subcriteria' => [
                    'Respecte les délais et les échéances.',
                    'Fait face aux situations.',
                ],
            ],
            [
                'ordre' => 4,
                'titre' => 'Dynamisme',
                'subcriteria' => [
                    'Est prêt à mettre du sien pour la réussite d\'une mission en ne ménageant aucun effort physique ou intellectuel.',
                ],
            ],
            [
                'ordre' => 5,
                'titre' => 'Communication',
                'subcriteria' => [
                    'Sait écouter.',
                    'S\'exprime et écrit de façon claire et responsable.',
                ],
            ],
            [
                'ordre' => 6,
                'titre' => 'Promotion du travail d\'équipe',
                'subcriteria' => [
                    'Sait établir des objectifs en collaboration avec autrui.',
                    'Fait des critiques constructives.',
                    'A un esprit d\'ouverture.',
                ],
            ],
            [
                'ordre' => 7,
                'titre' => 'Éthique',
                'subcriteria' => [
                    'Respecte les principes et valeurs morales du RCPB.',
                    'A un comportement socialement admis.',
                    'Est intègre.',
                ],
            ],
        ];

        foreach ($criteria as $criterion) {
            $templateId = DB::table('subjective_criteria_templates')->insertGetId([
                'ordre'       => $criterion['ordre'],
                'titre'       => $criterion['titre'],
                'description' => null,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            foreach ($criterion['subcriteria'] as $index => $libelle) {
                DB::table('subjective_subcriteria_templates')->insert([
                    'subjective_criteria_template_id' => $templateId,
                    'ordre'      => $index + 1,
                    'libelle'    => $libelle,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
