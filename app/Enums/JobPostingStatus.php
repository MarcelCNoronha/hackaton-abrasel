<?php

namespace App\Enums;

enum JobPostingStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
}
