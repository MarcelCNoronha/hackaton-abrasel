<?php

namespace App\Enums;

enum DietaryTagKind: string
{
    // preferencia alimentar (vegetariano, vegano...)
    case Diet = 'diet';
    // restricao medica -- alergia ou intolerancia (sem lactose, sem amendoim...)
    case Allergen = 'allergen';
}
