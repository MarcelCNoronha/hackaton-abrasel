<?php

namespace Tests\Feature\Owner;

use App\Enums\CouponStatus;
use App\Enums\RestaurantOwnerRole;
use App\Enums\UserRole;
use App\Models\Coupon;
use App\Models\CouponCampaign;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CouponRedemptionTest extends TestCase
{
    use RefreshDatabase;

    private function setUpCoupon(array $overrides = [], array $restaurantOverrides = []): array
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $restaurant = Restaurant::create(array_merge([
            'name' => 'Restaurante Com Cupons',
            'slug' => 'restaurante-com-cupons-'.Str::random(6),
            'latitude' => -20.7546,
            'longitude' => -42.8825,
        ], $restaurantOverrides));
        $restaurant->owners()->attach($owner->id, ['role' => RestaurantOwnerRole::Owner]);

        $campaign = CouponCampaign::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Campanha Teste',
            'benefit_description' => '10% de desconto',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'coupon_validity_days' => 7,
            'per_user_limit' => 1,
        ]);

        $coupon = Coupon::create(array_merge([
            'coupon_campaign_id' => $campaign->id,
            'user_id' => User::factory()->create()->id,
            'restaurant_id' => $restaurant->id,
            'code' => 'ABCD1234',
            'issued_at' => now(),
            'expires_at' => now()->addDays(7),
        ], $overrides));

        return [$owner, $restaurant, $coupon];
    }

    public function test_owner_can_redeem_an_available_coupon(): void
    {
        [$owner, $restaurant, $coupon] = $this->setUpCoupon();

        $this->actingAs($owner)->post(route('owner.coupons.redeem', $restaurant->slug), [
            'code' => 'abcd1234',
        ])->assertRedirect();

        $coupon->refresh();
        $this->assertSame(CouponStatus::Used, $coupon->status);
        $this->assertNotNull($coupon->redemption);
        $this->assertSame($owner->id, $coupon->redemption->redeemed_by);
    }

    public function test_an_already_used_coupon_cannot_be_redeemed_twice(): void
    {
        [$owner, $restaurant, $coupon] = $this->setUpCoupon(['status' => CouponStatus::Used]);

        $this->actingAs($owner)->post(route('owner.coupons.redeem', $restaurant->slug), [
            'code' => $coupon->code,
        ])->assertSessionHasErrors('code');
    }

    public function test_an_expired_coupon_cannot_be_redeemed(): void
    {
        [$owner, $restaurant, $coupon] = $this->setUpCoupon(['expires_at' => now()->subDay()]);

        $this->actingAs($owner)->post(route('owner.coupons.redeem', $restaurant->slug), [
            'code' => $coupon->code,
        ])->assertSessionHasErrors('code');

        $coupon->refresh();
        $this->assertSame(CouponStatus::Available, $coupon->status);
    }

    public function test_a_code_belonging_to_another_restaurant_is_rejected(): void
    {
        [, , $coupon] = $this->setUpCoupon();
        [$otherOwner, $otherRestaurant] = $this->setUpCoupon(['code' => 'OTHER111']);

        $this->actingAs($otherOwner)->post(route('owner.coupons.redeem', $otherRestaurant->slug), [
            'code' => $coupon->code,
        ])->assertSessionHasErrors('code');
    }

    public function test_a_non_owner_cannot_redeem_coupons_for_a_restaurant_they_dont_manage(): void
    {
        [, $restaurant, $coupon] = $this->setUpCoupon();
        $stranger = User::factory()->create(['role' => UserRole::Owner]);

        $this->actingAs($stranger)->post(route('owner.coupons.redeem', $restaurant->slug), [
            'code' => $coupon->code,
        ])->assertForbidden();
    }
}
