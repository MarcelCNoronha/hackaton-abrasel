<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RestaurantProfileRequest;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    public function store(RestaurantProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);

        Restaurant::create($data);

        return back()->with('status', 'Estabelecimento criado.');
    }

    public function update(RestaurantProfileRequest $request, Restaurant $restaurant): RedirectResponse
    {
        $restaurant->update($request->validated());

        return back()->with('status', 'Estabelecimento atualizado.');
    }

    public function toggleActive(Restaurant $restaurant): RedirectResponse
    {
        $restaurant->update(['is_active' => ! $restaurant->is_active]);

        return back()->with('status', $restaurant->is_active ? 'Estabelecimento reativado.' : 'Estabelecimento desativado.');
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
