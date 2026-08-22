<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCouponCampaignRequest;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;

class CouponCampaignController extends Controller
{
    public function store(StoreCouponCampaignRequest $request, Restaurant $restaurant): RedirectResponse
    {
        $data = $request->validated();

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
