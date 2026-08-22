<?php

namespace App\Http\Requests\Owner;

use App\Http\Requests\Concerns\ValidatesRestaurantProfile;
use Illuminate\Foundation\Http\FormRequest;

class RestaurantProfileRequest extends FormRequest
{
    use ValidatesRestaurantProfile;

    public function authorize(): bool
    {
        // Dono do restaurante e' checado explicitamente via RestaurantController::authorizeOwner()
        // no controller (precisa do model resolvido da rota primeiro).
        return true;
    }

    public function rules(): array
    {
        return $this->restaurantProfileRules() + [
            'cover_photo' => ['nullable', 'image', 'max:4096'],
            'banner_photo' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
