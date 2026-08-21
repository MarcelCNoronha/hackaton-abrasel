<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            CuisineSeeder::class,
            DietaryTagSeeder::class,
            RestaurantSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@vicosafood.test',
            'role' => UserRole::Admin,
        ]);

        User::factory()->create([
            'name' => 'Consumidor Teste',
            'email' => 'test@example.com',
            'role' => UserRole::Consumer,
        ]);

        User::factory()->create([
            'name' => 'Gestor Teste',
            'email' => 'gestor@vicosafood.test',
            'role' => UserRole::Owner,
        ]);
    }
}
