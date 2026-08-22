<?php

namespace App\Enums;

enum AvailabilityStatus: string
{
    // verde -- pode comecar a trabalhar agora
    case Immediate = 'immediate';
    // amarela -- so' nos dias/horarios marcados em FreelancerAvailabilitySlot
    case Scheduled = 'scheduled';
    // vermelha -- fora do mercado no momento
    case Unavailable = 'unavailable';
}
