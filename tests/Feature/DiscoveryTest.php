<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Database\Seeders\CuisineSeeder;
use Database\Seeders\DietaryTagSeeder;
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
            RestaurantSeeder::class,
        ]);
    }

    public function test_home_page_lists_active_restaurants_without_login(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Discover/Index')
                ->has('restaurants', 6)
            );
    }

    public function test_within_distance_filter_excludes_far_restaurants(): void
    {
        // centro de Vicosa/MG; Burger Local (~170m) e Veggie Green Bowl (~335m) ficam
        // dentro de 500m, os demais estao a 700m+ -- margem confortavel para o teste.
        $response = $this->get('/?lat=-20.7546&lng=-42.8825&radius_km=0.5');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Discover/Index')
            ->has('restaurants', 2)
        );
    }

    public function test_dietary_tags_filter_requires_a_single_dish_matching_all_tags(): void
    {
        $response = $this->get('/?'.http_build_query([
            'dietary_tags' => ['vegano', 'sem-gluten'],
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Discover/Index')
            ->has('restaurants', 1)
            ->where('restaurants.0.slug', 'veggie-green-bowl')
        );
    }

    public function test_text_search_matches_menu_item_names(): void
    {
        $response = $this->get('/?q=bowl');

        $response->assertInertia(fn (Assert $page) => $page
            ->has('restaurants', 1)
            ->where('restaurants.0.slug', 'veggie-green-bowl')
        );
    }

    public function test_restaurant_profile_page_renders_menu_and_business_hours(): void
    {
        $response = $this->get('/restaurantes/burger-local');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Restaurants/Show')
            ->where('restaurant.name', 'Burger Local')
            ->has('restaurant.menus.0.categories.0.items')
            ->has('restaurant.business_hours', 7)
        );
    }

    public function test_unknown_restaurant_slug_returns_404(): void
    {
        $this->get('/restaurantes/nao-existe')->assertNotFound();
    }
}
