<?php

namespace Tests\Feature\Owner;

use App\Enums\RestaurantOwnerRole;
use App\Enums\UserRole;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturedItemTest extends TestCase
{
    use RefreshDatabase;

    private function ownedRestaurantWithItems(): array
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $restaurant = Restaurant::create([
            'name' => 'Restaurante Com Destaque',
            'slug' => 'restaurante-com-destaque',
            'latitude' => -20.7546,
            'longitude' => -42.8825,
        ]);
        $restaurant->owners()->attach($owner->id, ['role' => RestaurantOwnerRole::Owner]);
        $menu = Menu::create(['restaurant_id' => $restaurant->id, 'name' => 'Cardápio principal']);
        $category = MenuCategory::create(['menu_id' => $menu->id, 'name' => 'Pratos', 'position' => 0]);
        $itemA = MenuItem::create(['menu_category_id' => $category->id, 'name' => 'Prato A', 'price' => 20]);
        $itemB = MenuItem::create(['menu_category_id' => $category->id, 'name' => 'Prato B', 'price' => 30]);

        return [$owner, $restaurant, $itemA, $itemB];
    }

    public function test_owner_can_mark_an_item_as_featured(): void
    {
        [$owner, $restaurant, $itemA] = $this->ownedRestaurantWithItems();

        $this->actingAs($owner)->patch(route('owner.menu-items.toggle-featured', $itemA))->assertRedirect();

        $this->assertSame($itemA->id, $restaurant->fresh()->featured_menu_item_id);
    }

    public function test_marking_a_second_item_replaces_the_first(): void
    {
        [$owner, $restaurant, $itemA, $itemB] = $this->ownedRestaurantWithItems();

        $this->actingAs($owner)->patch(route('owner.menu-items.toggle-featured', $itemA));
        $this->actingAs($owner)->patch(route('owner.menu-items.toggle-featured', $itemB));

        $this->assertSame($itemB->id, $restaurant->fresh()->featured_menu_item_id);
    }

    public function test_toggling_the_same_item_twice_unfeatures_it(): void
    {
        [$owner, $restaurant, $itemA] = $this->ownedRestaurantWithItems();

        $this->actingAs($owner)->patch(route('owner.menu-items.toggle-featured', $itemA));
        $this->actingAs($owner)->patch(route('owner.menu-items.toggle-featured', $itemA));

        $this->assertNull($restaurant->fresh()->featured_menu_item_id);
    }

    public function test_a_non_owner_cannot_feature_an_item(): void
    {
        [, , $itemA] = $this->ownedRestaurantWithItems();
        $stranger = User::factory()->create(['role' => UserRole::Owner]);

        $this->actingAs($stranger)->patch(route('owner.menu-items.toggle-featured', $itemA))->assertForbidden();
    }

    public function test_deleting_the_featured_item_clears_the_restaurants_pick(): void
    {
        [$owner, $restaurant, $itemA] = $this->ownedRestaurantWithItems();
        $this->actingAs($owner)->patch(route('owner.menu-items.toggle-featured', $itemA));

        $itemA->delete();

        $this->assertNull($restaurant->fresh()->featured_menu_item_id);
    }
}
