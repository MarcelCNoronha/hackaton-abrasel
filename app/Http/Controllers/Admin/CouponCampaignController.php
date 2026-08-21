<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CouponCampaignController extends Controller
{
    public function store(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'benefit_description' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'coupon_validity_days' => ['required', 'integer', 'min:1'],
            'quantity_available' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['required', 'integer', 'min:1'],
        ]);

        // Inactive until the owner accepts it (see CouponCampaign::isPending() /
        // Owner\CouponCampaignController::accept) -- an admin suggestion never goes live on
        // its own.
        $restaurant->couponCampaigns()->create($data + [
            'proposed_by' => $request->user()->id,
            'is_active' => false,
        ]);

        return back()->with('status', 'Campanha sugerida ao gestor do estabelecimento.');
    }
}
