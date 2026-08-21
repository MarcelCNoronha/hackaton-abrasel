<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
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

        return Inertia::render('Restaurants/Show', [
            'restaurant' => $restaurant,
        ]);
    }
}
