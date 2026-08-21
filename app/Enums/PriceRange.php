<?php

namespace App\Enums;

enum PriceRange: string
{
    case Low = '$';
    case Medium = '$$';
    case High = '$$$';
    case VeryHigh = '$$$$';
}
