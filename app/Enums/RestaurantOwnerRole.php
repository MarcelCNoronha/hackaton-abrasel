<?php

namespace App\Enums;

enum RestaurantOwnerRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
}
