<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjective_criteria_templates', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('ordre')->default(0);
            $table->string('titre');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subjective_subcriteria_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subjective_criteria_template_id')
                ->constrained('subjective_criteria_templates', 'id', 'subcriterion_template_fk')
                ->cascadeOnDelete();
            $table->unsignedInteger('ordre')->default(0);
            $table->string('libelle');
            $table->timestamps();
        });

        $now = now();

        $criteria = [
            [
                'ordre' => 1,
                'titre' => 'Aptitudes professionnelles et maitrise du poste',
                'subcriteria' => [
                    'Formation, diplomes ou autres titres',
                    'Experience professionnelle et niveau de performance dans la filiere',
                    'Maitrise des outils, methodes et procedures du poste',
                    'Capacite d\'adaptation au poste occupe',
                ],
            ],
            [
                'ordre' => 2,
                'titre' => 'Participation a la vie du service',
                'subcriteria' => [
                    'Participation aux reunions de service',
                    'Contribution au travail d\'equipe',
                    'Respect des consignes et de l\'organisation interne',
                    'Implication dans les activites collectives du service',
                ],
            ],
            [
                'ordre' => 3,
                'titre' => 'Organisation et suivi des activites',
                'subcriteria' => [
                    'Planification des taches',
                    'Respect des delais',
                    'Capacite d\'organisation du travail',
                    'Qualite du suivi des activites realisees',
                ],
            ],
            [
                'ordre' => 4,
                'titre' => 'Communication',
                'subcriteria' => [
                    'Qualite de la communication professionnelle',
                    'Transmission correcte des informations',
                    'Qualite des echanges avec les collegues et la hierarchie',
                    'Capacite d\'ecoute et de restitution',
                ],
            ],
            [
                'ordre' => 5,
                'titre' => 'Discipline et comportement professionnel',
                'subcriteria' => [
                    'Assiduite',
                    'Ponctualite',
                    'Respect des regles internes',
                    'Presentation et attitude au travail',
                ],
            ],
            [
                'ordre' => 6,
                'titre' => 'Initiative et sens des responsabilites',
                'subcriteria' => [
                    'Capacite a prendre des initiatives pertinentes',
                    'Sens des responsabilites',
                    'Reactivite face aux difficultes',
                    'Fiabilite dans l\'execution des taches',
                ],
            ],
            [
                'ordre' => 7,
                'titre' => 'Qualite du travail',
                'subcriteria' => [
                    'Qualite des productions',
                    'Rigueur dans l\'execution',
                    'Precision du travail fourni',
                    'Respect des normes attendues',
                ],
            ],
        ];

        foreach ($criteria as $criterion) {
            $templateId = DB::table('subjective_criteria_templates')->insertGetId([
                'ordre' => $criterion['ordre'],
                'titre' => $criterion['titre'],
                'description' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($criterion['subcriteria'] as $index => $subcriterion) {
                DB::table('subjective_subcriteria_templates')->insert([
                    'subjective_criteria_template_id' => $templateId,
                    'ordre' => $index + 1,
                    'libelle' => $subcriterion,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subjective_subcriteria_templates');
        Schema::dropIfExists('subjective_criteria_templates');
    }
};
