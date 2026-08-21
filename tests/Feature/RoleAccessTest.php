<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_consumer_cannot_access_owner_or_admin_areas(): void
    {
        $consumer = User::factory()->create(['role' => UserRole::Consumer]);

        $this->actingAs($consumer)->get('/gestor')->assertForbidden();
        $this->actingAs($consumer)->get('/admin')->assertForbidden();
    }

    public function test_owner_can_access_the_owner_area_but_not_admin(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);

        $this->actingAs($owner)->get('/gestor')->assertOk();
        $this->actingAs($owner)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_access_both_owner_and_admin_areas(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get('/gestor')->assertOk();
        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_public_registration_always_creates_a_consumer(): void
    {
        $response = $this->post('/register', [
            'name' => 'Novo Usuário',
            'email' => 'novo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'novo@example.com')->firstOrFail();

        $this->assertSame(UserRole::Consumer, $user->role);
    }

    public function test_guests_are_redirected_to_login_for_owner_and_admin_areas(): void
    {
        $this->get('/gestor')->assertRedirect('/login');
        $this->get('/admin')->assertRedirect('/login');
    }
}
