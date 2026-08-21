<?php

namespace App\Models;

use App\Enums\ClaimStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['restaurant_id', 'user_id', 'status', 'notes', 'rejection_reason', 'reviewed_by', 'reviewed_at'])]
class RestaurantClaim extends Model
{
    protected $attributes = [
        'status' => ClaimStatus::Pending->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => ClaimStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
