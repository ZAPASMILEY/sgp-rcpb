<?php

namespace Database\Factories;

use App\Models\Entite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entite>
 */
class EntiteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => 'RCPB '.$this->faker->citySuffix(),
            'ville' => $this->faker->city(),
            'directrice_generale_prenom' => $this->faker->firstNameFemale(),
            'directrice_generale_nom' => $this->faker->lastName(),
            'directrice_generale_email' => $this->faker->unique()->safeEmail(),
            'pca_prenom' => $this->faker->firstName(),
            'pca_nom' => $this->faker->lastName(),
            'pca_email' => $this->faker->unique()->safeEmail(),
            'secretariat_telephone' => $this->faker->phoneNumber(),
        ];
    }
}
