<?php

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Models\QrToken;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewRatingRecalculationTest extends TestCase
{
    use RefreshDatabase;

    private function restaurant(): Restaurant
    {
        return Restaurant::create([
            'name' => 'Restaurante Avaliado',
            'slug' => 'restaurante-avaliado',
            'latitude' => -20.7546,
            'longitude' => -42.8825,
        ]);
    }

    private function visit(Restaurant $restaurant): Visit
    {
        return Visit::create([
            'user_id' => User::factory()->create()->id,
            'restaurant_id' => $restaurant->id,
            'qr_token_id' => QrToken::generateFor($restaurant)->id,
            'visited_at' => now(),
        ]);
    }

    public function test_submitting_a_review_updates_the_restaurants_rating_and_count(): void
    {
        $restaurant = $this->restaurant();
        $visit = $this->visit($restaurant);

        $this->actingAs($visit->user)->post(route('reviews.store', $visit), [
            'rating' => 4,
            'comment' => 'Muito bom.',
        ])->assertRedirect();

        $restaurant->refresh();

        $this->assertSame(1, $restaurant->reviews_count);
        $this->assertSame(1, $restaurant->verified_reviews_count);
        $this->assertEquals(4.0, (float) $restaurant->average_rating);
    }

    public function test_average_rating_reflects_multiple_reviews(): void
    {
        $restaurant = $this->restaurant();

        $ratings = [5, 3, 4];
        foreach ($ratings as $rating) {
            $visit = $this->visit($restaurant);
            $this->actingAs($visit->user)->post(route('reviews.store', $visit), [
                'rating' => $rating,
            ]);
        }

        $restaurant->refresh();

        $this->assertSame(3, $restaurant->reviews_count);
        $this->assertEquals(4.0, (float) $restaurant->average_rating);
    }

    public function test_hiding_a_review_removes_it_from_the_average(): void
    {
        $restaurant = $this->restaurant();
        $visitA = $this->visit($restaurant);
        $visitB = $this->visit($restaurant);

        $this->actingAs($visitA->user)->post(route('reviews.store', $visitA), ['rating' => 5]);
        $this->actingAs($visitB->user)->post(route('reviews.store', $visitB), ['rating' => 1]);

        $restaurant->refresh();
        $this->assertSame(2, $restaurant->reviews_count);

        Review::where('visit_id', $visitB->id)->first()->update(['status' => ReviewStatus::Hidden]);

        $restaurant->refresh();
        $this->assertSame(1, $restaurant->reviews_count);
        $this->assertEquals(5.0, (float) $restaurant->average_rating);
    }

    public function test_deleting_the_only_review_resets_the_restaurant_to_zero(): void
    {
        $restaurant = $this->restaurant();
        $visit = $this->visit($restaurant);
        $this->actingAs($visit->user)->post(route('reviews.store', $visit), ['rating' => 5]);

        $restaurant->refresh();
        $this->assertSame(1, $restaurant->reviews_count);

        Review::where('visit_id', $visit->id)->first()->delete();

        $restaurant->refresh();
        $this->assertSame(0, $restaurant->reviews_count);
        $this->assertSame(0, $restaurant->verified_reviews_count);
        $this->assertEquals(0.0, (float) $restaurant->average_rating);
    }
}
