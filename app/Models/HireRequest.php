<?php

namespace App\Models;

use App\Enums\HireRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['restaurant_id', 'freelancer_profile_id', 'requested_by', 'status', 'message', 'responded_at'])]
class HireRequest extends Model
{
    protected $attributes = [
        'status' => HireRequestStatus::Pending->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => HireRequestStatus::class,
            'responded_at' => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function freelancerProfile(): BelongsTo
    {
        return $this->belongsTo(FreelancerProfile::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function review(): HasOne
    {
        return $this->hasOne(FreelancerReview::class);
    }

    public function isPending(): bool
    {
        return $this->status === HireRequestStatus::Pending;
    }

    public function isReviewable(): bool
    {
        return $this->status === HireRequestStatus::Accepted && $this->review === null;
    }
}
