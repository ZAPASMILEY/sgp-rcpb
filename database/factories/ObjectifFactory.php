<?php

namespace Database\Factories;

use App\Models\Objectif;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Objectif>
 */
class ObjectifFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignable_type' => Service::class,
            'assignable_id' => Service::factory(),
            'date' => now()->toDateString(),
            'date_echeance' => now()->addMonth()->toDateString(),
            'commentaire' => $this->faker->sentence(16),
            'avancement_percentage' => 0,
        ];
    }
}