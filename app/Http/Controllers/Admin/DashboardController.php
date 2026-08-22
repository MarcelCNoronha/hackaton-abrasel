<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClaimStatus;
use App\Enums\PriceRange;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantClaim;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users' => User::count(),
                'restaurants' => Restaurant::count(),
                'pendingClaims' => RestaurantClaim::where('status', ClaimStatus::Pending)->count(),
            ],
            'pendingClaims' => RestaurantClaim::where('status', ClaimStatus::Pending)
                ->with(['restaurant:id,name', 'user:id,name,email'])
                ->latest()
                ->get(),
            'users' => User::orderBy('name')
                ->paginate(15, ['id', 'name', 'email', 'role'], 'users_page')
                ->withQueryString(),
            // Full profile columns (not just the ones the table renders) -- the "Editar"
            // dialog pre-fills its form straight from this same list.
            'restaurants' => Restaurant::orderBy('name')
                ->with('owners:id,name,email')
                ->paginate(15, ['*'], 'restaurants_page')
                ->withQueryString(),
            'priceRanges' => array_column(PriceRange::cases(), 'value'),
        ]);
    }
}
