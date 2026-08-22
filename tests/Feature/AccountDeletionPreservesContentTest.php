<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\CouponCampaign;
use App\Models\QrToken;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDeletionPreservesContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_user_keeps_their_review_but_clears_the_author(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::create([
            'name' => 'Restaurante',
            'slug' => 'restaurante-conta-apagada',
            'latitude' => -20.7546,
            'longitude' => -42.8825,
        ]);
        $visit = Visit::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'qr_token_id' => QrToken::generateFor($restaurant)->id,
            'visited_at' => now(),
        ]);
        $review = Review::create([
            'visit_id' => $visit->id,
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'rating' => 5,
            'comment' => 'Excelente!',
        ]);

        $user->delete();
        $review->refresh();

        $this->assertNull($review->user_id);
        $this->assertSame('Excelente!', $review->comment);
        $this->assertSame(5, $review->rating);
    }

    public function test_deleting_a_user_keeps_their_coupon_but_clears_the_owner(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::create([
            'name' => 'Restaurante',
            'slug' => 'restaurante-conta-apagada-cupom',
            'latitude' => -20.7546,
            'longitude' => -42.8825,
        ]);
        $campaign = CouponCampaign::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Campanha',
            'benefit_description' => '10% off',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'coupon_validity_days' => 7,
            'per_user_limit' => 1,
        ]);
        $coupon = Coupon::create([
            'coupon_campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'code' => 'KEEPME01',
            'issued_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);

        $user->delete();
        $coupon->refresh();

        $this->assertNull($coupon->user_id);
        $this->assertSame('KEEPME01', $coupon->code);
    }
}
