<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\CouponCampaign;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CouponCampaignController extends Controller
{
    public function store(Request $request, Restaurant $restaurant): RedirectResponse
    {
        RestaurantController::authorizeOwner($request, $restaurant);

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

        $restaurant->couponCampaigns()->create($data);

        return back()->with('status', 'Campanha de cupom criada.');
    }

    public function accept(Request $request, CouponCampaign $couponCampaign): RedirectResponse
    {
        RestaurantController::authorizeOwner($request, $couponCampaign->restaurant);
        abort_unless($couponCampaign->isPending(), 422, 'Esta campanha não está aguardando aceite.');

        $couponCampaign->update(['is_active' => true, 'accepted_at' => now()]);

        return back()->with('status', 'Campanha aceita e ativada.');
    }

    public function reject(Request $request, CouponCampaign $couponCampaign): RedirectResponse
    {
        RestaurantController::authorizeOwner($request, $couponCampaign->restaurant);
        abort_unless($couponCampaign->isPending(), 422, 'Esta campanha não está aguardando aceite.');

        $couponCampaign->delete();

        return back()->with('status', 'Sugestão de campanha recusada.');
    }
}
