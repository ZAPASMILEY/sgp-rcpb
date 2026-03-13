<?php

namespace Database\Factories;

use App\Models\Direction;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => 'Service '.$this->faker->word(),
            'direction_id' => Direction::factory(),
            'chef_prenom' => $this->faker->firstName(),
            'chef_nom' => $this->faker->lastName(),
            'chef_email' => $this->faker->unique()->safeEmail(),
            'chef_telephone' => $this->faker->phoneNumber(),
        ];
    }
}
