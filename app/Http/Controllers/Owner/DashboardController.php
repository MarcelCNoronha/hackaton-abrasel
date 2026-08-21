<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $restaurants = $request->user()
            ->ownedRestaurants()
            ->withCount(['reviews', 'menus'])
            ->get();

        return Inertia::render('Owner/Dashboard', [
            'restaurants' => $restaurants,
        ]);
    }
}
