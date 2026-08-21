<?php

namespace App\Http\Controllers\Owner;

use App\Enums\PriceRange;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;

class RestaurantController extends Controller
{
    public function edit(Request $request, Restaurant $restaurant): Response
    {
        $this->authorizeOwner($request, $restaurant);

        // Um restaurante recem-reivindicado nao tem nenhum cardapio ainda -- garante que a
        // tela de gestao sempre tenha pelo menos um pra editar, sem precisar de UI separada
        // so pra criar o primeiro cardapio (multi-cardapio nao faz parte do escopo atual).
        $restaurant->menus()->firstOrCreate([], ['name' => 'Cardápio principal']);

        $restaurant->load([
            'businessHours',
            'menus.categories.items.dietaryTags',
            'couponCampaigns',
            'reviews' => fn ($query) => $query->latest()->with(['user:id,name', 'reply']),
            'activeQrToken',
        ]);

        return Inertia::render('Owner/Restaurants/Edit', [
            'restaurant' => $restaurant,
            'priceRanges' => array_column(PriceRange::cases(), 'value'),
        ]);
    }

    public function update(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $this->authorizeOwner($request, $restaurant);

        $data = $request->validate([
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
        ]);

        $restaurant->update($data);

        return back()->with('status', 'Perfil atualizado.');
    }

    public function updateHours(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $this->authorizeOwner($request, $restaurant);

        $data = $request->validate([
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.weekday' => ['required', 'integer', 'min:0', 'max:6'],
            'hours.*.is_closed' => ['required', 'boolean'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
        ]);

        foreach ($data['hours'] as $day) {
            $restaurant->businessHours()->updateOrCreate(
                ['weekday' => $day['weekday']],
                [
                    'is_closed' => $day['is_closed'],
                    'opens_at' => $day['is_closed'] ? null : $day['opens_at'],
                    'closes_at' => $day['is_closed'] ? null : $day['closes_at'],
                ],
            );
        }

        return back()->with('status', 'Horários atualizados.');
    }

    public static function authorizeOwner(Request $request, Restaurant $restaurant): void
    {
        abort_unless(
            $request->user()->ownedRestaurants()->whereKey($restaurant->id)->exists(),
            403,
            'Você não gerencia este estabelecimento.',
        );
    }
}
