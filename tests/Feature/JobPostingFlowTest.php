<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatus;
use App\Enums\RestaurantOwnerRole;
use App\Enums\UserRole;
use App\Models\HireRequest;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class JobPostingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function ownedRestaurant(): array
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $restaurant = Restaurant::create([
            'name' => 'Restaurante Vaga',
            'slug' => 'restaurante-vaga-'.Str::random(6),
            'latitude' => -20.7546,
            'longitude' => -42.8825,
        ]);
        $restaurant->owners()->attach($owner->id, ['role' => RestaurantOwnerRole::Owner]);

        return [$owner, $restaurant];
    }

    private function freelancer(): User
    {
        $user = User::factory()->create(['role' => UserRole::Consumer]);
        $user->forceFill(['freelancer_enabled_at' => now()])->save();
        $user->freelancerProfile()->create([]);

        return $user;
    }

    public function test_owner_can_publish_a_job_posting_with_free_typed_skills(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();

        $this->actingAs($owner)->post(route('owner.job-postings.store'), [
            'restaurant_id' => $restaurant->id,
            'title' => 'Churrasqueiro pro fim de semana',
            'description' => 'Sábados e domingos à noite.',
            'job_skills' => ['Churrasqueiro(a)'],
        ])->assertRedirect();

        $posting = JobPosting::first();
        $this->assertSame('Churrasqueiro pro fim de semana', $posting->title);
        $this->assertSame('open', $posting->status->value);
        $this->assertDatabaseHas('job_skills', ['name' => 'Churrasqueiro(a)']);
    }

    public function test_freelancer_sees_only_open_postings_and_can_filter_by_skill(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();

        $this->actingAs($owner)->post(route('owner.job-postings.store'), [
            'restaurant_id' => $restaurant->id, 'title' => 'Vaga de sushiman', 'job_skills' => ['Sushiman'],
        ]);
        $this->actingAs($owner)->post(route('owner.job-postings.store'), [
            'restaurant_id' => $restaurant->id, 'title' => 'Vaga de garçom', 'job_skills' => ['Garçom/Garçonete'],
        ]);
        $closed = JobPosting::where('title', 'Vaga de garçom')->first();
        $closed->update(['status' => 'closed']);

        $response = $this->actingAs($freelancer)->get(route('freelancer.jobs.index', ['job_skills' => ['sushiman']]));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('jobPostings', 1)
            ->where('jobPostings.0.title', 'Vaga de sushiman')
        );
    }

    public function test_freelancer_can_apply_and_owner_can_accept_creating_an_accepted_hire(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.job-postings.store'), [
            'restaurant_id' => $restaurant->id, 'title' => 'Vaga de cozinheira',
        ]);
        $posting = JobPosting::first();

        $this->actingAs($freelancer)->post(route('freelancer.job-applications.store', $posting), [
            'message' => 'Tenho experiência e disponibilidade imediata.',
        ])->assertRedirect();

        $application = JobApplication::first();
        $this->assertTrue($application->isPending());

        $this->actingAs($owner)->patch(route('owner.job-applications.accept', $application))->assertRedirect();

        $application->refresh();
        $this->assertSame(JobApplicationStatus::Accepted, $application->status);
        $this->assertNotNull($application->hire_request_id);
        $this->assertSame('accepted', HireRequest::find($application->hire_request_id)->status->value);
    }

    public function test_a_freelancer_cannot_apply_twice_to_the_same_posting(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.job-postings.store'), ['restaurant_id' => $restaurant->id, 'title' => 'Vaga']);
        $posting = JobPosting::first();
        $this->actingAs($freelancer)->post(route('freelancer.job-applications.store', $posting));

        $this->actingAs($freelancer)->post(route('freelancer.job-applications.store', $posting))->assertStatus(422);
    }

    public function test_cannot_apply_to_a_closed_posting(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.job-postings.store'), ['restaurant_id' => $restaurant->id, 'title' => 'Vaga']);
        $posting = JobPosting::first();
        $posting->update(['status' => 'closed']);

        $this->actingAs($freelancer)->post(route('freelancer.job-applications.store', $posting))->assertStatus(422);
    }

    public function test_freelancer_can_withdraw_a_pending_application(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.job-postings.store'), ['restaurant_id' => $restaurant->id, 'title' => 'Vaga']);
        $posting = JobPosting::first();
        $this->actingAs($freelancer)->post(route('freelancer.job-applications.store', $posting));
        $application = JobApplication::first();

        $this->actingAs($freelancer)->delete(route('freelancer.job-applications.destroy', $application))->assertRedirect();

        $this->assertSame(JobApplicationStatus::Withdrawn, $application->fresh()->status);
    }

    public function test_owner_can_decline_an_application(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.job-postings.store'), ['restaurant_id' => $restaurant->id, 'title' => 'Vaga']);
        $posting = JobPosting::first();
        $this->actingAs($freelancer)->post(route('freelancer.job-applications.store', $posting));
        $application = JobApplication::first();

        $this->actingAs($owner)->patch(route('owner.job-applications.decline', $application))->assertRedirect();

        $this->assertSame(JobApplicationStatus::Declined, $application->fresh()->status);
        $this->assertNull($application->fresh()->hire_request_id);
    }

    public function test_a_stranger_owner_cannot_manage_another_restaurants_posting(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        [$strangerOwner] = $this->ownedRestaurant();
        $this->actingAs($owner)->post(route('owner.job-postings.store'), ['restaurant_id' => $restaurant->id, 'title' => 'Vaga']);
        $posting = JobPosting::first();

        $this->actingAs($strangerOwner)->patch(route('owner.job-postings.close', $posting))->assertForbidden();
    }
}
