<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\DietaryTag;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscoveryController extends Controller
{
    public function index(Request $request): Response
    {
        $lat = $request->filled('lat') ? (float) $request->input('lat') : null;
        $lng = $request->filled('lng') ? (float) $request->input('lng') : null;
        $hasLocation = $lat !== null && $lng !== null;

        $query = Restaurant::query()
            ->where('is_active', true)
            ->with(['categories', 'cuisines', 'businessHours']);

        if ($hasLocation) {
            $query->selectDistance($lat, $lng);

            if ($radiusKm = $request->input('radius_km')) {
                $query->withinDistance($lat, $lng, (float) $radiusKm);
            }
        }

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'ilike', "%{$q}%")
                    ->orWhereHas('menus.categories.items', function ($items) use ($q) {
                        $items->where('is_available', true)->where('name', 'ilike', "%{$q}%");
                    });
            });
        }

        if ($category = $request->input('category')) {
            $query->whereHas('categories', fn ($c) => $c->where('slug', $category));
        }

        if ($cuisines = array_filter((array) $request->input('cuisines', []))) {
            $query->whereHas('cuisines', fn ($c) => $c->whereIn('slug', $cuisines));
        }

        // restaurante precisa ter pelo menos 1 prato que atenda a TODAS as restricoes
        // marcadas -- ver docs/SPEC.md #4 (filtro serve tanto preferencia quanto alergia).
        if ($dietaryTags = array_filter((array) $request->input('dietary_tags', []))) {
            $query->whereHas('menus.categories.items', function ($items) use ($dietaryTags) {
                $items->where('is_available', true);

                foreach ($dietaryTags as $slug) {
                    $items->whereHas('dietaryTags', fn ($dt) => $dt->where('slug', $slug));
                }
            });
        }

        if ($priceRange = $request->input('price_range')) {
            $query->where('price_range', $priceRange);
        }

        if ($minRating = $request->input('min_rating')) {
            $query->where('average_rating', '>=', (float) $minRating);
        }

        if ($request->boolean('open_now')) {
            $query->openNow();
        }

        $sort = $request->input('sort', $hasLocation ? 'distance' : 'rating');

        match (true) {
            $sort === 'distance' && $hasLocation => $query->orderByDistance($lat, $lng),
            $sort === 'rating' => $query->orderByDesc('average_rating')->orderByDesc('reviews_count'),
            $sort === 'reviews' => $query->orderByDesc('reviews_count'),
            default => $query->orderByDesc('average_rating'),
        };

        $restaurants = $query->limit(60)->get();
        $restaurants->each(fn (Restaurant $r) => $r->is_open_now = $r->isOpenAt());

        return Inertia::render('Discover/Index', [
            'restaurants' => $restaurants,
            'categories' => Category::orderBy('position')->get(),
            'cuisines' => Cuisine::orderBy('position')->get(),
            'dietaryTags' => DietaryTag::orderBy('position')->get(),
            // chaves sempre presentes (mesmo vazias/null) -- um array PHP sem
            // nenhuma delas presente na query string serializa como `[]` em JSON,
            // e no front `filters.sort` colidiria com o Array.prototype.sort nativo.
            'filters' => [
                'q' => $request->input('q'),
                'category' => $request->input('category'),
                'cuisines' => array_values($cuisines ?? []),
                'dietary_tags' => array_values($dietaryTags ?? []),
                'radius_km' => $request->input('radius_km'),
                'price_range' => $request->input('price_range'),
                'min_rating' => $request->input('min_rating'),
                'open_now' => $request->boolean('open_now'),
                'sort' => $sort,
                'lat' => $lat,
                'lng' => $lng,
            ],
        ]);
    }
}
