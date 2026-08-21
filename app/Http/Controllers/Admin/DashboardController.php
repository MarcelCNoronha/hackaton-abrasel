<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClaimStatus;
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
            'restaurants' => Restaurant::orderBy('name')->get(['id', 'slug', 'name', 'address_city', 'is_active', 'claimed_at']),
        ]);
    }
}
