<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['freelancer_profile_id', 'weekday', 'opens_at', 'closes_at', 'is_off'])]
class FreelancerAvailabilitySlot extends Model
{
    protected function casts(): array
    {
        return [
            'is_off' => 'boolean',
        ];
    }

    public function freelancerProfile(): BelongsTo
    {
        return $this->belongsTo(FreelancerProfile::class);
    }
}
