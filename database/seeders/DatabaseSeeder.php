<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'admin@sgp.com',
        ], [
            'name'                 => 'Administrateur',
            'password'             => Hash::make('11111111'),
            'role'                 => 'Admin',
            'is_active'            => true,
            'must_change_password' => false,
            'email_verified_at'    => now(),
        ]);

        $this->call([
            RolesAndPermissionsSeeder::class,
            SubjectiveCriteriaTemplateSeeder::class,
        ]);
    }
}
