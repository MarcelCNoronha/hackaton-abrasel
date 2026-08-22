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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    private function ownedRestaurant(): array
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $restaurant = Restaurant::create([
            'name' => 'Restaurante Com Fotos',
            'slug' => 'restaurante-com-fotos',
            'latitude' => -20.7546,
            'longitude' => -42.8825,
        ]);
        $restaurant->owners()->attach($owner->id, ['role' => RestaurantOwnerRole::Owner]);

        return [$owner, $restaurant];
    }

    public function test_owner_can_upload_cover_and_banner_photos(): void
    {
        Storage::fake('public');
        [$owner, $restaurant] = $this->ownedRestaurant();

        $this->actingAs($owner)->patch(route('owner.restaurants.update', $restaurant), [
            'name' => $restaurant->name,
            'cover_photo' => UploadedFile::fake()->image('cover.jpg'),
            'banner_photo' => UploadedFile::fake()->image('banner.jpg'),
        ])->assertRedirect();

        $restaurant->refresh();

        $this->assertNotNull($restaurant->cover_photo_path);
        $this->assertNotNull($restaurant->banner_photo_path);
        Storage::disk('public')->assertExists($restaurant->cover_photo_path);
        Storage::disk('public')->assertExists($restaurant->banner_photo_path);
    }

    public function test_owner_can_upload_a_menu_item_photo(): void
    {
        Storage::fake('public');
        [$owner, $restaurant] = $this->ownedRestaurant();
        $menu = Menu::create(['restaurant_id' => $restaurant->id, 'name' => 'Cardápio principal']);
        $category = MenuCategory::create(['menu_id' => $menu->id, 'name' => 'Pratos', 'position' => 0]);

        $this->actingAs($owner)->post(route('owner.menu-items.store', $category), [
            'name' => 'Prato com foto',
            'price' => 29.9,
            'is_available' => true,
            'photo' => UploadedFile::fake()->image('prato.jpg'),
        ])->assertRedirect();

        $item = MenuItem::where('name', 'Prato com foto')->firstOrFail();
        $this->assertNotNull($item->main_photo_path);
        Storage::disk('public')->assertExists($item->main_photo_path);
    }

    public function test_replacing_a_menu_item_photo_deletes_the_old_file(): void
    {
        Storage::fake('public');
        [$owner, $restaurant] = $this->ownedRestaurant();
        $menu = Menu::create(['restaurant_id' => $restaurant->id, 'name' => 'Cardápio principal']);
        $category = MenuCategory::create(['menu_id' => $menu->id, 'name' => 'Pratos', 'position' => 0]);
        $item = MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => 'Prato',
            'price' => 10,
            'main_photo_path' => 'menu-items/old.jpg',
        ]);
        Storage::disk('public')->put('menu-items/old.jpg', 'fake-content');

        $this->actingAs($owner)->patch(route('owner.menu-items.update', $item), [
            'name' => 'Prato',
            'price' => 10,
            'is_available' => true,
            'photo' => UploadedFile::fake()->image('novo.jpg'),
        ])->assertRedirect();

        $item->refresh();
        Storage::disk('public')->assertMissing('menu-items/old.jpg');
        Storage::disk('public')->assertExists($item->main_photo_path);
        $this->assertNotSame('menu-items/old.jpg', $item->main_photo_path);
    }
}
