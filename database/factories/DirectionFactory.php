<?php

namespace Database\Factories;

use App\Models\Direction;
use App\Models\Entite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Direction>
 */
class DirectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => 'Direction '.$this->faker->word(),
            'entite_id' => Entite::factory(),
            'directeur_nom' => $this->faker->name(),
            'directeur_email' => $this->faker->unique()->safeEmail(),
            'secretariat_telephone' => $this->faker->phoneNumber(),
        ];
    }
}
