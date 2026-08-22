<?php

namespace Tests\Feature\Admin;

use App\Enums\RestaurantOwnerRole;
use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveOwnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_remove_an_owner_from_a_restaurant(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $restaurant = Restaurant::create([
            'name' => 'Restaurante Gerenciado',
            'slug' => 'restaurante-gerenciado',
            'latitude' => -20.7546,
            'longitude' => -42.8825,
        ]);
        // claimed_at nao e' fillable de proposito (so admin/claim-approval o setam) -- forceFill
        // pra simular um estabelecimento ja reivindicado no setup do teste.
        $restaurant->forceFill(['claimed_at' => now()])->save();
        $restaurant->owners()->attach($owner->id, ['role' => RestaurantOwnerRole::Owner]);

        $this->actingAs($admin)
            ->delete(route('admin.restaurants.owners.destroy', [$restaurant, $owner]))
            ->assertRedirect();

        $this->assertDatabaseMissing('restaurant_owners', [
            'restaurant_id' => $restaurant->id,
            'user_id' => $owner->id,
        ]);

        // Sem gestor nenhum sobrando, o estabelecimento fica disponivel pra reivindicar de novo.
        $this->assertNull($restaurant->refresh()->claimed_at);
    }

    public function test_removing_one_of_multiple_owners_keeps_the_restaurant_claimed(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $ownerA = User::factory()->create(['role' => UserRole::Owner]);
        $ownerB = User::factory()->create(['role' => UserRole::Owner]);
        $restaurant = Restaurant::create([
            'name' => 'Restaurante Com Dois Gestores',
            'slug' => 'restaurante-com-dois-gestores',
            'latitude' => -20.7546,
            'longitude' => -42.8825,
        ]);
        $restaurant->forceFill(['claimed_at' => now()])->save();
        $restaurant->owners()->attach([
            $ownerA->id => ['role' => RestaurantOwnerRole::Owner],
            $ownerB->id => ['role' => RestaurantOwnerRole::Manager],
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.restaurants.owners.destroy', [$restaurant, $ownerA]))
            ->assertRedirect();

        $this->assertNotNull($restaurant->refresh()->claimed_at);
        $this->assertTrue($restaurant->owners()->whereKey($ownerB->id)->exists());
    }

    public function test_non_admin_cannot_remove_an_owner(): void
    {
        $consumer = User::factory()->create(['role' => UserRole::Consumer]);
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $restaurant = Restaurant::create([
            'name' => 'Restaurante Protegido',
            'slug' => 'restaurante-protegido',
            'latitude' => -20.7546,
            'longitude' => -42.8825,
        ]);
        $restaurant->owners()->attach($owner->id, ['role' => RestaurantOwnerRole::Owner]);

        $this->actingAs($consumer)
            ->delete(route('admin.restaurants.owners.destroy', [$restaurant, $owner]))
            ->assertForbidden();
    }
}
