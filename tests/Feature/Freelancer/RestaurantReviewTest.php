<?php

namespace Tests\Feature\Freelancer;

use App\Enums\RestaurantOwnerRole;
use App\Enums\UserRole;
use App\Models\HireRequest;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RestaurantReviewTest extends TestCase
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

    private function acceptedHireRequest(): array
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.hire-requests.store', $freelancer->freelancerProfile), ['restaurant_id' => $restaurant->id]);
        $hireRequest = HireRequest::first();
        $this->actingAs($freelancer)->patch(route('freelancer.hires.accept', $hireRequest));

        return [$owner, $restaurant, $freelancer, $hireRequest->fresh()];
    }

    public function test_freelancer_can_review_the_restaurant_after_an_accepted_hire(): void
    {
        [, $restaurant, $freelancer, $hireRequest] = $this->acceptedHireRequest();

        $this->actingAs($freelancer)->post(route('freelancer.restaurant-reviews.store', $hireRequest), [
            'rating' => 4,
            'comment' => 'Ambiente organizado, recomendo.',
        ])->assertRedirect();

        $restaurant = $restaurant->fresh();
        $this->assertSame(1, $restaurant->freelancer_reviews_count);
        $this->assertEquals(4.0, (float) $restaurant->freelancer_average_rating);
    }

    public function test_a_pending_hire_request_cannot_be_reviewed(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        $freelancer = $this->freelancer();
        $this->actingAs($owner)->post(route('owner.hire-requests.store', $freelancer->freelancerProfile), ['restaurant_id' => $restaurant->id]);
        $hireRequest = HireRequest::first();

        $this->actingAs($freelancer)->post(route('freelancer.restaurant-reviews.store', $hireRequest), [
            'rating' => 5,
        ])->assertStatus(422);
    }

    public function test_a_hire_request_can_only_be_reviewed_once_by_the_freelancer(): void
    {
        [, , $freelancer, $hireRequest] = $this->acceptedHireRequest();
        $this->actingAs($freelancer)->post(route('freelancer.restaurant-reviews.store', $hireRequest), ['rating' => 5]);

        $this->actingAs($freelancer)->post(route('freelancer.restaurant-reviews.store', $hireRequest), ['rating' => 1])
            ->assertStatus(422);
    }

    public function test_a_stranger_freelancer_cannot_review_someone_elses_hire_request(): void
    {
        [, , , $hireRequest] = $this->acceptedHireRequest();
        $stranger = $this->freelancer();

        $this->actingAs($stranger)->post(route('freelancer.restaurant-reviews.store', $hireRequest), ['rating' => 5])
            ->assertForbidden();
    }

    public function test_the_rating_is_visible_to_freelancers_but_hidden_from_the_owner(): void
    {
        [$owner, $restaurant, $freelancer, $hireRequest] = $this->acceptedHireRequest();
        $this->actingAs($freelancer)->post(route('freelancer.restaurant-reviews.store', $hireRequest), [
            'rating' => 5,
            'comment' => 'Só pra outros freelancers verem.',
        ]);

        $ownerEditResponse = $this->actingAs($owner)->get(route('owner.restaurants.edit', $restaurant));
        $ownerEditResponse->assertInertia(fn ($page) => $page
            ->missing('restaurant.freelancer_average_rating')
            ->missing('restaurant.freelancer_reviews_count')
        );
    }
}
