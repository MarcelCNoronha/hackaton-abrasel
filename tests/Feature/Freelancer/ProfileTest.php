<?php

namespace Tests\Feature\Freelancer;

use App\Enums\AvailabilityStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function enabledFreelancer(): User
    {
        $user = User::factory()->create(['role' => UserRole::Consumer]);
        $user->forceFill(['freelancer_enabled_at' => now()])->save();

        return $user;
    }

    private function defaultSlots(): array
    {
        return array_map(
            fn ($weekday) => ['weekday' => $weekday, 'is_off' => true, 'opens_at' => null, 'closes_at' => null],
            range(0, 6),
        );
    }

    public function test_a_user_without_the_module_enabled_gets_a_403(): void
    {
        $user = User::factory()->create(['role' => UserRole::Consumer]);

        $this->actingAs($user)->get(route('freelancer.profile.edit'))->assertForbidden();
    }

    public function test_an_enabled_user_can_view_and_update_their_profile(): void
    {
        $user = $this->enabledFreelancer();

        $this->actingAs($user)->get(route('freelancer.profile.edit'))->assertOk();

        $this->actingAs($user)->patch(route('freelancer.profile.update'), [
            'headline' => 'Sushiman com 5 anos de experiência',
            'bio' => 'Trabalhei em rodízios japoneses em BH.',
            'availability_status' => AvailabilityStatus::Immediate->value,
            'job_skills' => ['Sushiman', 'Garçom/Garçonete'],
            'slots' => $this->defaultSlots(),
        ])->assertRedirect();

        $profile = $user->fresh()->freelancerProfile;
        $this->assertSame('Sushiman com 5 anos de experiência', $profile->headline);
        $this->assertSame(AvailabilityStatus::Immediate, $profile->availability_status);
        $this->assertCount(2, $profile->jobSkills);
        $this->assertDatabaseHas('job_skills', ['name' => 'Sushiman']);
    }

    public function test_a_free_typed_skill_not_in_the_curated_list_is_created(): void
    {
        $user = $this->enabledFreelancer();

        $this->actingAs($user)->patch(route('freelancer.profile.update'), [
            'availability_status' => AvailabilityStatus::Unavailable->value,
            'job_skills' => ['Pizzaiolo'],
            'slots' => $this->defaultSlots(),
        ])->assertRedirect();

        $this->assertDatabaseHas('job_skills', ['slug' => 'pizzaiolo']);
    }

    public function test_scheduled_availability_stores_the_weekly_slots(): void
    {
        $user = $this->enabledFreelancer();

        $slots = $this->defaultSlots();
        $slots[1] = ['weekday' => 1, 'is_off' => false, 'opens_at' => '18:00', 'closes_at' => '23:00'];

        $this->actingAs($user)->patch(route('freelancer.profile.update'), [
            'availability_status' => AvailabilityStatus::Scheduled->value,
            'slots' => $slots,
        ])->assertRedirect();

        $slot = $user->fresh()->freelancerProfile->availabilitySlots()->where('weekday', 1)->first();
        $this->assertFalse($slot->is_off);
        $this->assertSame('18:00:00', $slot->opens_at);
    }
}
