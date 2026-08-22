<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesRestaurantProfile;
use Illuminate\Foundation\Http\FormRequest;

class RestaurantProfileRequest extends FormRequest
{
    use ValidatesRestaurantProfile;

    public function authorize(): bool
    {
        // role:admin ja e' garantido pelo middleware do grupo de rotas.
        return true;
    }

    public function rules(): array
    {
        return $this->restaurantProfileRules() + [
            // Ambas NOT NULL no schema -- um restaurante precisa de posicao no mapa pra
            // aparecer como pino no Discover -- required, nao nullable, aqui.
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
