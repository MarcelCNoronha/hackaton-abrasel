<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClaimController extends Controller
{
    public function create(): Response
    {
        // So restaurantes ainda sem dono (claimed_at nulo) aparecem para reivindicar --
        // evita duas reivindicacoes concorrentes pro mesmo estabelecimento.
        $restaurants = Restaurant::whereNull('claimed_at')
            ->orderBy('name')
            ->get(['id', 'name', 'address_city']);

        return Inertia::render('Owner/Claims/Create', [
            'restaurants' => $restaurants,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $restaurant = Restaurant::whereNull('claimed_at')->findOrFail($data['restaurant_id']);

        $request->user()->restaurantClaims()->create([
            'restaurant_id' => $restaurant->id,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('owner.dashboard')
            ->with('status', 'Pedido enviado! Um administrador vai revisar sua reivindicação em breve.');
    }
}
