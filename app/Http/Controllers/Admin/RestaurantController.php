<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;

class RestaurantController extends Controller
{
    public function toggleActive(Restaurant $restaurant): RedirectResponse
    {
        $restaurant->update(['is_active' => ! $restaurant->is_active]);

        return back()->with('status', $restaurant->is_active ? 'Estabelecimento reativado.' : 'Estabelecimento desativado.');
    }
}
