<?php

namespace App\Enums;

enum CouponStatus: string
{
    case Available = 'available';
    case Used = 'used';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
