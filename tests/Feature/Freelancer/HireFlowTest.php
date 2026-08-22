<?php

namespace Tests\Feature\Freelancer;

use App\Enums\HireRequestStatus;
use App\Enums\RestaurantOwnerRole;
use App\Enums\UserRole;
use App\Models\HireRequest;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HireFlowTest extends TestCase
{
    use RefreshDatabase;

    private function ownedRestaurant(): array
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $restaurant = Restaurant::create([
            'name' => 'Restaurante Contratante',
            'slug' => 'restaurante-contratante-'.Str::random(6),
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

    public function test_owner_can_request_a_hire_and_freelancer_can_accept_it(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();

        $this->actingAs($owner)->post(
            route('owner.hire-requests.store', $freelancer->freelancerProfile),
            ['restaurant_id' => $restaurant->id, 'message' => 'Precisamos de sushiman no fim de semana.'],
        )->assertRedirect();

        $hireRequest = HireRequest::first();
        $this->assertSame(HireRequestStatus::Pending, $hireRequest->status);

        $this->actingAs($freelancer)->patch(route('freelancer.hires.accept', $hireRequest))->assertRedirect();

        $this->assertSame(HireRequestStatus::Accepted, $hireRequest->fresh()->status);
    }

    public function test_a_second_pending_request_for_the_same_restaurant_is_blocked(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();

        $this->actingAs($owner)->post(route('owner.hire-requests.store', $freelancer->freelancerProfile), ['restaurant_id' => $restaurant->id]);

        $this->actingAs($owner)->post(
            route('owner.hire-requests.store', $freelancer->freelancerProfile),
            ['restaurant_id' => $restaurant->id],
        )->assertStatus(422);
    }

    public function test_freelancer_can_decline_a_pending_request(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.hire-requests.store', $freelancer->freelancerProfile), ['restaurant_id' => $restaurant->id]);
        $hireRequest = HireRequest::first();

        $this->actingAs($freelancer)->patch(route('freelancer.hires.decline', $hireRequest))->assertRedirect();

        $this->assertSame(HireRequestStatus::Declined, $hireRequest->fresh()->status);
    }

    public function test_owner_can_cancel_their_own_pending_request(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.hire-requests.store', $freelancer->freelancerProfile), ['restaurant_id' => $restaurant->id]);
        $hireRequest = HireRequest::first();

        $this->actingAs($owner)->delete(route('owner.hire-requests.cancel', $hireRequest))->assertRedirect();

        $this->assertSame(HireRequestStatus::Cancelled, $hireRequest->fresh()->status);
    }

    public function test_a_stranger_freelancer_cannot_accept_someone_elses_request(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $stranger = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.hire-requests.store', $freelancer->freelancerProfile), ['restaurant_id' => $restaurant->id]);
        $hireRequest = HireRequest::first();

        $this->actingAs($stranger)->patch(route('freelancer.hires.accept', $hireRequest))->assertForbidden();
    }

    public function test_review_requires_an_accepted_hire_and_updates_the_average_rating(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.hire-requests.store', $freelancer->freelancerProfile), ['restaurant_id' => $restaurant->id]);
        $hireRequest = HireRequest::first();

        // Ainda pendente -- avaliacao deve ser bloqueada.
        $this->actingAs($owner)->post(route('owner.freelancer-reviews.store', $hireRequest), [
            'rating' => 5,
        ])->assertStatus(422);

        $this->actingAs($freelancer)->patch(route('freelancer.hires.accept', $hireRequest));

        $this->actingAs($owner)->post(route('owner.freelancer-reviews.store', $hireRequest), [
            'rating' => 5,
            'feedback_to_freelancer' => 'Ótimo trabalho, pontual e caprichoso.',
            'feedback_to_owners' => 'Recomendo -- profissional confiável.',
        ])->assertRedirect();

        $profile = $freelancer->fresh()->freelancerProfile;
        $this->assertSame(1, $profile->reviews_count);
        $this->assertEquals(5.0, (float) $profile->average_rating);
    }

    public function test_a_hire_request_can_only_be_reviewed_once(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.hire-requests.store', $freelancer->freelancerProfile), ['restaurant_id' => $restaurant->id]);
        $hireRequest = HireRequest::first();
        $this->actingAs($freelancer)->patch(route('freelancer.hires.accept', $hireRequest));
        $this->actingAs($owner)->post(route('owner.freelancer-reviews.store', $hireRequest), ['rating' => 4]);

        $this->actingAs($owner)->post(route('owner.freelancer-reviews.store', $hireRequest), ['rating' => 2])
            ->assertStatus(422);
    }

    public function test_owners_browsing_never_see_the_feedback_meant_for_the_freelancer(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        [$otherOwner] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.hire-requests.store', $freelancer->freelancerProfile), ['restaurant_id' => $restaurant->id]);
        $hireRequest = HireRequest::first();
        $this->actingAs($freelancer)->patch(route('freelancer.hires.accept', $hireRequest));
        $this->actingAs($owner)->post(route('owner.freelancer-reviews.store', $hireRequest), [
            'rating' => 5,
            'feedback_to_freelancer' => 'Segredo so pro freelancer.',
            'feedback_to_owners' => 'Referencia publica pros donos.',
        ]);

        $response = $this->actingAs($otherOwner)->get(route('owner.freelancers.show', $freelancer->freelancerProfile));

        $response->assertInertia(fn ($page) => $page
            ->where('freelancer.reviews.0.feedback_to_owners', 'Referencia publica pros donos.')
            ->missing('freelancer.reviews.0.feedback_to_freelancer')
        );
    }

    public function test_freelancer_never_sees_the_feedback_meant_for_other_owners(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.hire-requests.store', $freelancer->freelancerProfile), ['restaurant_id' => $restaurant->id]);
        $hireRequest = HireRequest::first();
        $this->actingAs($freelancer)->patch(route('freelancer.hires.accept', $hireRequest));
        $this->actingAs($owner)->post(route('owner.freelancer-reviews.store', $hireRequest), [
            'rating' => 5,
            'feedback_to_freelancer' => 'Segredo so pro freelancer.',
            'feedback_to_owners' => 'Referencia publica pros donos.',
        ]);

        $response = $this->actingAs($freelancer)->get(route('freelancer.hires.index'));

        $response->assertInertia(fn ($page) => $page
            ->where('hireRequests.0.review.feedback_to_freelancer', 'Segredo so pro freelancer.')
            ->missing('hireRequests.0.review.feedback_to_owners')
        );
    }
}
