<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Database\Seeders\CuisineSeeder;
use Database\Seeders\DietaryTagSeeder;
use Database\Seeders\FoodTagSeeder;
use Database\Seeders\RestaurantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            CategorySeeder::class,
            CuisineSeeder::class,
            DietaryTagSeeder::class,
            FoodTagSeeder::class,
            RestaurantSeeder::class,
        ]);
    }

    public function test_home_page_starts_with_an_empty_list_but_a_full_map_without_login(): void
    {
        // Lista vazia por escolha ate' selecionar algo (ver DiscoveryController) -- senao ela
        // cresceria sem limite conforme mais restaurantes se cadastrassem. O mapa e' diferente:
        // continua trazendo todo mundo, pra dar pra explorar livremente pelos pinos.
        $this->get('/restaurantes')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Discover/Index')
                ->has('restaurants', 0)
                ->has('mapRestaurants', 10)
            );
    }

    public function test_selecting_an_establishment_type_populates_the_list_and_matches_the_map(): void
    {
        $response = $this->get('/restaurantes?category=pizzaria');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Discover/Index')
            ->has('restaurants', 4)
            ->has('mapRestaurants', 4)
        );
    }

    public function test_within_distance_filter_excludes_far_restaurants(): void
    {
        // centro de Vicosa/MG; so O Barbante fica a ~155m, o proximo mais perto
        // (Sabor e Cia, ~265m) ja fica fora do raio de 200m -- margem confortavel.
        $response = $this->get('/restaurantes?lat=-20.7546&lng=-42.8825&radius_km=0.2');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Discover/Index')
            ->has('restaurants', 1)
            ->where('restaurants.0.slug', 'o-barbante')
        );
    }

    public function test_dietary_tags_filter_requires_a_single_dish_matching_all_tags(): void
    {
        $response = $this->get('/restaurantes?'.http_build_query([
            'dietary_tags' => ['vegetariano', 'sem-gluten'],
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Discover/Index')
            ->has('restaurants', 1)
            ->where('restaurants.0.slug', 'choperia-e-churrascaria-devan')
        );
    }

    public function test_cuisine_and_food_tag_are_combined_with_or_not_and(): void
    {
        // "Japonês" (cozinha) bate com Sushi Mirim + Japa Nobre; "Bowls" (food tag) bate so'
        // com Beco das Flores -- nenhum restaurante e' as duas coisas ao mesmo tempo. Isso so'
        // devolve os 3 se a combinacao for OR; a semantica antiga (AND entre as duas
        // categorias) devolveria 0.
        $response = $this->get('/restaurantes?'.http_build_query([
            'cuisines' => ['japones'],
            'food_tags' => ['bowls'],
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Discover/Index')
            ->has('restaurants', 3)
        );
    }

    public function test_text_search_matches_menu_item_names(): void
    {
        $response = $this->get('/restaurantes?q=bowl');

        $response->assertInertia(fn (Assert $page) => $page
            ->has('restaurants', 1)
            ->where('restaurants.0.slug', 'beco-das-flores')
        );
    }

    public function test_restaurant_profile_page_renders_menu_and_business_hours(): void
    {
        $response = $this->get('/restaurantes/arte-sabor');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Restaurants/Show')
            ->where('restaurant.name', 'Arte & Sabor')
            ->has('restaurant.menus.0.categories.0.items')
            ->has('restaurant.business_hours', 7)
        );
    }

    public function test_unknown_restaurant_slug_returns_404(): void
    {
        $this->get('/restaurantes/nao-existe')->assertNotFound();
    }
}
