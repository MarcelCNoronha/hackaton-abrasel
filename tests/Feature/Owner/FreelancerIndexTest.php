<?php

namespace Tests\Feature\Owner;

use App\Enums\AvailabilityStatus;
use App\Enums\UserRole;
use App\Models\JobSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FreelancerIndexTest extends TestCase
{
    use RefreshDatabase;

    private function freelancer(string $name, AvailabilityStatus $status, array $skills = []): User
    {
        $user = User::factory()->create(['name' => $name, 'role' => UserRole::Consumer]);
        $user->forceFill(['freelancer_enabled_at' => now()])->save();
        $profile = $user->freelancerProfile()->create(['availability_status' => $status]);

        if ($skills) {
            $ids = collect($skills)->map(fn ($name) => JobSkill::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'position' => 0],
            )->id);
            $profile->jobSkills()->sync($ids);
        }

        return $user;
    }

    public function test_owner_can_filter_freelancers_by_skill_and_availability(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $this->freelancer('Sushiman Disponível', AvailabilityStatus::Immediate, ['Sushiman']);
        $this->freelancer('Garçom Ocupado', AvailabilityStatus::Unavailable, ['Garçom']);
        $this->freelancer('Sushiman Ocupado', AvailabilityStatus::Unavailable, ['Sushiman']);

        $response = $this->actingAs($owner)->get(route('owner.freelancers.index', [
            'job_skills' => ['sushiman'],
            'availability_status' => 'immediate',
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('freelancers', 1)
            ->where('freelancers.0.user.name', 'Sushiman Disponível')
        );
    }

    public function test_a_consumer_cannot_browse_freelancers(): void
    {
        $consumer = User::factory()->create(['role' => UserRole::Consumer]);

        $this->actingAs($consumer)->get(route('owner.freelancers.index'))->assertForbidden();
    }
}
