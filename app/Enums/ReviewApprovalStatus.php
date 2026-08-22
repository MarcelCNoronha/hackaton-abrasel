<?php

namespace App\Enums;

enum ReviewApprovalStatus: string
{
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
