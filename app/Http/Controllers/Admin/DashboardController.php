<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClaimStatus;
use App\Enums\PriceRange;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantClaim;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
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
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'role']),
            // Full profile columns (not just the ones the table renders) -- the "Editar"
            // dialog pre-fills its form straight from this same list.
            'restaurants' => Restaurant::orderBy('name')->get(),
            'priceRanges' => array_column(PriceRange::cases(), 'value'),
        ]);
    }
}
