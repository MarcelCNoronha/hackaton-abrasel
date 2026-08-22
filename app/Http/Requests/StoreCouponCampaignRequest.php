<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Compartilhado entre Admin\CouponCampaignController e Owner\CouponCampaignController --
// as regras eram identicas nos dois, so' o que acontece depois de validar diferia (admin
// cria uma sugestao inativa pro dono aceitar; dono cria uma campanha propria ja ativa).
class StoreCouponCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Quem pode chamar a rota (role:admin, ou dono do restaurante) ja e' resolvido pelo
        // middleware de rota / authorizeOwner() no controller antes de validar.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'benefit_description' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'coupon_validity_days' => ['required', 'integer', 'min:1'],
            'quantity_available' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['required', 'integer', 'min:1'],
        ];
    }
}
