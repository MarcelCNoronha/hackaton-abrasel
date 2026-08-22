<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // WithoutModelEvents removida de proposito -- ReviewObserver/FreelancerReviewObserver
    // precisam disparar durante o seed pra average_rating/reviews_count refletirem as
    // avaliacoes de demonstracao de verdade, em vez de ficarem presos nos numeros ficticios
    // que RestaurantSeeder usa so' como valor inicial (achado ao notar freelancers com
    // avaliacao real ainda mostrando nota 0 -- o mesmo ja acontecia com os restaurantes,
    // so' nao tinha sido percebido porque o baseline generico parecia plausivel).

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            CuisineSeeder::class,
            DietaryTagSeeder::class,
            FoodTagSeeder::class,
            JobSkillSeeder::class,
            RestaurantSeeder::class,
            DemoActivitySeeder::class,
            DemoFreelancerSeeder::class,
            DemoTrafficSeeder::class,
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

        // freelancer_enabled_at fora do Fillable de proposito -- forceFill aqui, nao update().
        $freelancer = User::updateOrCreate(
            ['email' => 'freelancer@vicosafood.test'],
            ['name' => 'Freelancer Teste', 'password' => 'password', 'role' => UserRole::Consumer, 'email_verified_at' => now()]
        );
        $freelancer->forceFill(['freelancer_enabled_at' => now()])->save();
    }
}
