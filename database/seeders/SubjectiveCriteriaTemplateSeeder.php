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
                'titre' => 'Aptitudes professionnelles et maîtrise du poste',
                'subcriteria' => [
                    'Formation, diplômes ou autres titres',
                    'Expérience professionnelle et niveau de performance dans la filière',
                    'Maîtrise des outils, méthodes et procédures du poste',
                    "Capacité d'adaptation au poste occupé",
                ],
            ],
            [
                'ordre' => 2,
                'titre' => 'Participation à la vie du service',
                'subcriteria' => [
                    'Participation aux réunions de service',
                    "Contribution au travail d'équipe",
                    "Respect des consignes et de l'organisation interne",
                    'Implication dans les activités collectives du service',
                ],
            ],
            [
                'ordre' => 3,
                'titre' => 'Organisation et suivi des activités',
                'subcriteria' => [
                    'Planification des tâches',
                    'Respect des délais',
                    "Capacité d'organisation du travail",
                    'Qualité du suivi des activités réalisées',
                ],
            ],
            [
                'ordre' => 4,
                'titre' => 'Communication',
                'subcriteria' => [
                    'Qualité de la communication professionnelle',
                    'Transmission correcte des informations',
                    'Qualité des échanges avec les collègues et la hiérarchie',
                    "Capacité d'écoute et de restitution",
                ],
            ],
            [
                'ordre' => 5,
                'titre' => 'Discipline et comportement professionnel',
                'subcriteria' => [
                    'Assiduité',
                    'Ponctualité',
                    'Respect des règles internes',
                    'Présentation et attitude au travail',
                ],
            ],
            [
                'ordre' => 6,
                'titre' => 'Initiative et sens des responsabilités',
                'subcriteria' => [
                    'Capacité à prendre des initiatives pertinentes',
                    'Sens des responsabilités',
                    'Réactivité face aux difficultés',
                    "Fiabilité dans l'exécution des tâches",
                ],
            ],
            [
                'ordre' => 7,
                'titre' => 'Qualité du travail',
                'subcriteria' => [
                    'Qualité des productions',
                    "Rigueur dans l'exécution",
                    'Précision du travail fourni',
                    'Respect des normes attendues',
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
