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
            'nom' => 'Faitiere',
            'ville' => $this->faker->city(),
            'region' => $this->faker->state(),
            'directrice_generale_prenom' => $this->faker->firstNameFemale(),
            'directrice_generale_nom' => $this->faker->lastName(),
            'directrice_generale_email' => $this->faker->unique()->safeEmail(),
            'dga_prenom' => $this->faker->firstName(),
            'dga_nom' => $this->faker->lastName(),
            'dga_email' => $this->faker->unique()->safeEmail(),
            'assistante_dg_prenom' => $this->faker->firstName(),
            'assistante_dg_nom' => $this->faker->lastName(),
            'assistante_dg_email' => $this->faker->unique()->safeEmail(),
            'pca_prenom' => $this->faker->firstName(),
            'pca_nom' => $this->faker->lastName(),
            'pca_email' => $this->faker->unique()->safeEmail(),
            'secretariat_telephone' => $this->faker->phoneNumber(),
        ];
    }
}
