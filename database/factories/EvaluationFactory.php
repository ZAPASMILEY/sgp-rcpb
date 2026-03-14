<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evaluation>
 */
class EvaluationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'evaluable_type' => Agent::class,
            'evaluable_id' => Agent::factory(),
            'evaluable_role' => 'entity',
            'evaluateur_id' => User::factory(),
            'date_debut' => now()->subMonth()->startOfMonth()->toDateString(),
            'date_fin' => now()->subMonth()->endOfMonth()->toDateString(),
            'note_objectifs' => $this->faker->numberBetween(0, 100),
            'note_manuelle' => $this->faker->optional()->numberBetween(0, 100),
            'note_finale' => $this->faker->numberBetween(0, 100),
            'commentaire' => $this->faker->optional()->sentence(14),
            'statut' => 'brouillon',
        ];
    }
}
