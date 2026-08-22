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
            DemoActivitySeeder::class,
        ]);

        // updateOrCreate (nao Factory) -- Factory::definition() chama fake(), que quebra em
        // producao porque fakerphp/faker e' require-dev e a imagem e' construida com --no-dev.
        User::updateOrCreate(
            ['email' => 'admin@vicosafood.test'],
            ['name' => 'Admin', 'password' => 'password', 'role' => UserRole::Admin, 'email_verified_at' => now()]
        );

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Consumidor Teste', 'password' => 'password', 'role' => UserRole::Consumer, 'email_verified_at' => now()]
        );

        User::updateOrCreate(
            ['email' => 'gestor@vicosafood.test'],
            ['name' => 'Gestor Teste', 'password' => 'password', 'role' => UserRole::Owner, 'email_verified_at' => now()]
        );
    }
}
