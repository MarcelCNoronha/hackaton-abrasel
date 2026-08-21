<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PriceRange;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class RestaurantController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        Restaurant::create($data);

        return back()->with('status', 'Estabelecimento criado.');
    }

    public function update(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $restaurant->update($this->validated($request));

        return back()->with('status', 'Estabelecimento atualizado.');
    }

    public function toggleActive(Restaurant $restaurant): RedirectResponse
    {
        $restaurant->update(['is_active' => ! $restaurant->is_active]);

        return back()->with('status', $restaurant->is_active ? 'Estabelecimento reativado.' : 'Estabelecimento desativado.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_number' => ['nullable', 'string', 'max:20'],
            'address_neighborhood' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:255'],
            'address_state' => ['nullable', 'string', 'max:2'],
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'price_range' => ['nullable', new Enum(PriceRange::class)],
            // Both columns are NOT NULL in the schema (a restaurant needs a map position to
            // ever show up as a pin in Discover) -- required, not nullable, here.
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (Restaurant::where('slug', $slug)->exists()) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }
}
