<?php

namespace App\Http\Controllers\Owner;

use App\Enums\CouponStatus;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponCampaign;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    // Ponto que faltava no fluxo de cupom: o cliente ve o codigo no proprio dashboard, mas
    // nada no app marcava o cupom como usado no balcao -- ficava "disponivel" pra sempre e
    // dava pra resgatar o mesmo codigo indefinidamente. Chamado pelo dono/funcionario ao
    // atender o cliente pessoalmente.
    public function redeem(Request $request, Restaurant $restaurant): RedirectResponse
    {
        RestaurantController::authorizeOwner($request, $restaurant);

        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $coupon = Coupon::where('restaurant_id', $restaurant->id)
            ->where('code', Str::upper(trim($data['code'])))
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['code' => 'Cupom não encontrado para este estabelecimento.']);
        }

        if (! $coupon->isRedeemable()) {
            throw ValidationException::withMessages(['code' => 'Este cupom não pode ser resgatado (já usado, expirado ou cancelado).']);
        }

        $coupon->update(['status' => CouponStatus::Used]);
        $coupon->redemption()->create([
            'redeemed_by' => $request->user()->id,
            'redeemed_at' => now(),
        ]);

        return back()->with('status', "Cupom {$coupon->code} resgatado com sucesso.");
    }
}
