<?php

namespace App\Http\Controllers;

use App\Enums\CouponStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'stats' => [
                'visits' => $user->visits()->count(),
                'reviews' => $user->reviews()->count(),
                'coupons' => $user->coupons()->where('status', CouponStatus::Available)->count(),
                'favorites' => $user->favoriteRestaurants()->count(),
            ],
            'coupons' => $user->coupons()
                ->where('status', CouponStatus::Available)
                ->with('restaurant:id,name,slug')
                ->latest('issued_at')
                ->get(),
            'visits' => $user->visits()
                ->with(['restaurant:id,name,slug', 'review:id,visit_id'])
                ->latest('visited_at')
                ->limit(10)
                ->get(),
            'favorites' => $user->favoriteRestaurants()->get(['restaurants.id', 'name', 'slug', 'average_rating']),
        ]);
    }
}
