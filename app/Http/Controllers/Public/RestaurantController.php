<?php

namespace App\Http\Controllers\Public;

use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Restaurant;
use App\Support\RestaurantTrafficReport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RestaurantController extends Controller
{
    public function show(Request $request, Restaurant $restaurant): Response
    {
        $restaurant->load([
            'categories',
            'cuisines',
            'photos',
            'businessHours' => fn ($q) => $q->orderBy('weekday'),
            'menus' => fn ($q) => $q->where('is_active', true)->orderBy('position'),
            'menus.categories' => fn ($q) => $q->orderBy('position'),
            'menus.categories.items' => fn ($q) => $q->orderBy('position'),
            'menus.categories.items.dietaryTags',
            'menus.categories.items.foodTags',
            'reviews' => fn ($q) => $q->where('status', 'published')->latest()->limit(20),
            'reviews.user:id,name',
            'reviews.reply',
        ]);

        $restaurant->is_open_now = $restaurant->isOpenAt();

        if ($request->filled('lat') && $request->filled('lng')) {
            $restaurant->distance_km = Restaurant::selectDistance(
                (float) $request->input('lat'),
                (float) $request->input('lng')
            )->find($restaurant->id)->distance_km;
        }

        Event::create(['type' => EventType::RestaurantView, 'restaurant_id' => $restaurant->id, 'user_id' => $request->user()?->id]);

        return Inertia::render('Restaurants/Show', [
            'restaurant' => $restaurant,
            'trafficInsights' => RestaurantTrafficReport::publicSummary(RestaurantTrafficReport::for($restaurant)),
        ]);
    }
}
