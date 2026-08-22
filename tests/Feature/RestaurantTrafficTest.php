<?php

namespace Tests\Feature;

use App\Enums\RestaurantOwnerRole;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\QrToken;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Visit;
use App\Support\RestaurantTrafficReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantTrafficTest extends TestCase
{
    use RefreshDatabase;

    private function ownedRestaurant(): array
    {
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $restaurant = Restaurant::create([
            'name' => 'Restaurante Movimentado',
            'slug' => 'restaurante-movimentado',
            'latitude' => -20.7546,
            'longitude' => -42.8825,
        ]);
        $restaurant->owners()->attach($owner->id, ['role' => RestaurantOwnerRole::Owner]);

        return [$owner, $restaurant];
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

    public function test_the_tracking_endpoint_creates_an_event(): void
    {
        [, $restaurant] = $this->ownedRestaurant();

        $this->post(route('events.track'), [
            'restaurant_id' => $restaurant->id,
            'type' => 'whatsapp_click',
        ])->assertNoContent();

        $this->assertDatabaseHas('events', [
            'restaurant_id' => $restaurant->id,
            'type' => 'whatsapp_click',
        ]);
    }

    public function test_the_tracking_endpoint_rejects_an_untracked_event_type(): void
    {
        [, $restaurant] = $this->ownedRestaurant();

        $this->post(route('events.track'), [
            'restaurant_id' => $restaurant->id,
            'type' => 'review_created',
        ])->assertInvalid('type');
    }

    public function test_visiting_the_restaurant_page_logs_a_view_and_creating_a_review_logs_an_event(): void
    {
        [, $restaurant] = $this->ownedRestaurant();
        $visit = $this->visit($restaurant);

        $this->get(route('restaurants.show', $restaurant))->assertOk();
        $this->assertDatabaseHas('events', ['restaurant_id' => $restaurant->id, 'type' => 'restaurant_view']);

        $this->actingAs($visit->user)->post(route('reviews.store', $visit), ['rating' => 5]);
        $this->assertDatabaseHas('events', ['restaurant_id' => $restaurant->id, 'type' => 'review_created']);
    }

    public function test_the_owner_sees_the_full_traffic_report_with_counts_and_segments(): void
    {
        [$owner, $restaurant] = $this->ownedRestaurant();
        Event::create(['type' => 'restaurant_view', 'restaurant_id' => $restaurant->id])->forceFill(['created_at' => now()->setHour(12)])->save();
        Event::create(['type' => 'phone_click', 'restaurant_id' => $restaurant->id])->forceFill(['created_at' => now()->setHour(12)])->save();

        $response = $this->actingAs($owner)->get(route('owner.restaurants.edit', $restaurant));

        $response->assertInertia(fn ($page) => $page
            ->where('trafficReport.total', 2)
            ->has('trafficReport.by_segment', 2)
        );
    }

    public function test_the_public_page_hides_traffic_insights_until_theres_enough_data(): void
    {
        [, $restaurant] = $this->ownedRestaurant();

        $response = $this->get(route('restaurants.show', $restaurant));
        $response->assertInertia(fn ($page) => $page->where('trafficInsights', null));

        for ($i = 0; $i < 5; $i++) {
            Event::create(['type' => 'restaurant_view', 'restaurant_id' => $restaurant->id])
                ->forceFill(['created_at' => now()->setHour(12)])->save();
        }

        $response = $this->get(route('restaurants.show', $restaurant));
        $response->assertInertia(fn ($page) => $page->has('trafficInsights.by_hour', 24));
    }

    public function test_report_marks_the_busiest_hour_as_high_and_never_exposes_raw_counts_publicly(): void
    {
        [, $restaurant] = $this->ownedRestaurant();
        for ($i = 0; $i < 6; $i++) {
            Event::create(['type' => 'restaurant_view', 'restaurant_id' => $restaurant->id])
                ->forceFill(['created_at' => now()->setHour(12)])->save();
        }

        $report = RestaurantTrafficReport::for($restaurant->fresh());
        $this->assertSame(12, $report['peak_hour']['hour']);
        $this->assertSame('alto', $report['peak_hour']['level']);

        $summary = RestaurantTrafficReport::publicSummary($report);
        $this->assertArrayNotHasKey('count', $summary['by_hour'][12]);
        $this->assertArrayNotHasKey('by_segment', $summary);
    }
}
