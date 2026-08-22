<?php

namespace App\Http\Requests\Concerns;

use App\Enums\PriceRange;
use Illuminate\Validation\Rules\Enum;

// Compartilhado entre Admin e Owner: o CORE dos campos de perfil (nome, endereco, contato)
// e' identico nos dois formularios -- so' os campos extras diferem (admin exige
// latitude/longitude pra posicionar o pino ao criar; dono pode trocar as fotos, mas nunca
// reposiciona o proprio pino).
trait ValidatesRestaurantProfile
{
    protected function restaurantProfileRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_number' => ['nullable', 'string', 'max:20'],
            'address_neighborhood' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:255'],
            'address_state' => ['nullable', 'string', 'max:2'],
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'price_range' => ['nullable', new Enum(PriceRange::class)],
        ];
    }
}
