<?php

namespace Tests\Feature\Admin;

use App\Enums\ClaimStatus;
use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\RestaurantClaim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminActionsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    private function restaurant(): Restaurant
    {
        return Restaurant::create([
            'name' => 'Restaurante Admin',
            'slug' => 'restaurante-admin',
            'latitude' => -20.7546,
            'longitude' => -42.8825,
        ]);
    }

    public function test_admin_can_promote_a_user_to_owner(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['role' => UserRole::Consumer]);

        $this->actingAs($admin)->patch(route('admin.users.update-role', $user), [
            'role' => UserRole::Owner->value,
        ])->assertRedirect();

        $this->assertSame(UserRole::Owner, $user->fresh()->role);
    }

    public function test_admin_cannot_remove_their_own_admin_role(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->patch(route('admin.users.update-role', $admin), [
            'role' => UserRole::Consumer->value,
        ])->assertStatus(422);

        $this->assertSame(UserRole::Admin, $admin->fresh()->role);
    }

    public function test_a_non_admin_cannot_update_roles(): void
    {
        $consumer = User::factory()->create(['role' => UserRole::Consumer]);
        $target = User::factory()->create(['role' => UserRole::Consumer]);

        $this->actingAs($consumer)->patch(route('admin.users.update-role', $target), [
            'role' => UserRole::Owner->value,
        ])->assertForbidden();
    }

    public function test_admin_can_toggle_a_restaurant_active_status(): void
    {
        $admin = $this->admin();
        $restaurant = $this->restaurant();

        $this->actingAs($admin)->patch(route('admin.restaurants.toggle-active', $restaurant))->assertRedirect();
        $this->assertFalse($restaurant->fresh()->is_active);

        $this->actingAs($admin)->patch(route('admin.restaurants.toggle-active', $restaurant))->assertRedirect();
        $this->assertTrue($restaurant->fresh()->is_active);
    }

    public function test_admin_can_approve_a_pending_claim_and_promote_the_claimant(): void
    {
        $admin = $this->admin();
        $restaurant = $this->restaurant();
        $consumer = User::factory()->create(['role' => UserRole::Consumer]);
        $claim = RestaurantClaim::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $consumer->id,
        ]);

        $this->actingAs($admin)->patch(route('admin.claims.approve', $claim))->assertRedirect();

        $claim->refresh();
        $this->assertSame(ClaimStatus::Approved, $claim->status);
        $this->assertSame($admin->id, $claim->reviewed_by);
        $this->assertNotNull($restaurant->fresh()->claimed_at);
        $this->assertSame(UserRole::Owner, $consumer->fresh()->role);
        $this->assertTrue($restaurant->owners()->whereKey($consumer->id)->exists());
    }

    public function test_admin_can_reject_a_pending_claim_with_a_reason(): void
    {
        $admin = $this->admin();
        $restaurant = $this->restaurant();
        $consumer = User::factory()->create(['role' => UserRole::Consumer]);
        $claim = RestaurantClaim::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $consumer->id,
        ]);

        $this->actingAs($admin)->patch(route('admin.claims.reject', $claim), [
            'rejection_reason' => 'Documentação insuficiente.',
        ])->assertRedirect();

        $claim->refresh();
        $this->assertSame(ClaimStatus::Rejected, $claim->status);
        $this->assertSame('Documentação insuficiente.', $claim->rejection_reason);
        $this->assertNull($restaurant->fresh()->claimed_at);
        $this->assertSame(UserRole::Consumer, $consumer->fresh()->role);
    }

    public function test_rejecting_a_claim_requires_a_reason(): void
    {
        $admin = $this->admin();
        $restaurant = $this->restaurant();
        $consumer = User::factory()->create(['role' => UserRole::Consumer]);
        $claim = RestaurantClaim::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $consumer->id,
        ]);

        $this->actingAs($admin)->patch(route('admin.claims.reject', $claim), [])
            ->assertSessionHasErrors('rejection_reason');
    }

    public function test_a_claim_already_reviewed_cannot_be_approved_again(): void
    {
        $admin = $this->admin();
        $restaurant = $this->restaurant();
        $consumer = User::factory()->create(['role' => UserRole::Consumer]);
        $claim = RestaurantClaim::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $consumer->id,
            'status' => ClaimStatus::Approved,
        ]);

        $this->actingAs($admin)->patch(route('admin.claims.approve', $claim))->assertStatus(422);
    }

    public function test_admin_can_toggle_freelancer_access_for_a_user(): void
    {
        $admin = $this->admin();
        $consumer = User::factory()->create(['role' => UserRole::Consumer]);
        $this->assertFalse($consumer->isFreelancerEnabled());

        $this->actingAs($admin)->patch(route('admin.users.toggle-freelancer', $consumer))->assertRedirect();
        $this->assertTrue($consumer->fresh()->isFreelancerEnabled());

        $this->actingAs($admin)->patch(route('admin.users.toggle-freelancer', $consumer))->assertRedirect();
        $this->assertFalse($consumer->fresh()->isFreelancerEnabled());
    }
}
