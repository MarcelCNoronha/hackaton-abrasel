<?php

namespace Tests\Feature\Owner;

use App\Enums\RestaurantOwnerRole;
use App\Enums\UserRole;
use App\Models\Cuisine;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodTagCuisineOverlapTest extends TestCase
{
    use RefreshDatabase;

    private function ownedPizzeria(): array
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $restaurant = Restaurant::create([
            'name' => 'Pizzaria Teste',
            'slug' => 'pizzaria-teste',
            'latitude' => -20.7546,
            'longitude' => -42.8825,
        ]);
        $restaurant->owners()->attach($owner->id, ['role' => RestaurantOwnerRole::Owner]);
        $restaurant->cuisines()->attach(Cuisine::create(['name' => 'Pizza', 'slug' => 'pizza'])->id);
        $menu = Menu::create(['restaurant_id' => $restaurant->id, 'name' => 'Cardápio principal']);
        $category = MenuCategory::create(['menu_id' => $menu->id, 'name' => 'Pratos', 'position' => 0]);

        return [$owner, $restaurant, $category];
    }

    public function test_a_food_tag_matching_the_restaurants_own_cuisine_is_rejected(): void
    {
        [$owner, , $category] = $this->ownedPizzeria();

        $this->actingAs($owner)->post(route('owner.menu-items.store', $category), [
            'name' => 'Pizza margherita',
            'price' => 36.90,
            'is_available' => true,
            'food_tags' => ['Pizza'],
        ])->assertSessionHasErrors('food_tags');
    }

    public function test_a_food_tag_matching_a_different_cuisine_is_allowed(): void
    {
        [$owner, , $category] = $this->ownedPizzeria();

        $this->actingAs($owner)->post(route('owner.menu-items.store', $category), [
            'name' => 'Sanduíche do dia',
            'price' => 22.90,
            'is_available' => true,
            'food_tags' => ['Hambúrguer'],
        ])->assertRedirect();

        $this->assertDatabaseHas('food_tags', ['name' => 'Hambúrguer']);
    }
}
