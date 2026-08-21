<?php

namespace App\Enums;

enum UserRole: string
{
    case Consumer = 'consumer';
    case Owner = 'owner';
    case Admin = 'admin';
}
