<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'restaurant_id', 'proposed_by', 'name', 'description', 'benefit_description',
    'starts_at', 'ends_at', 'coupon_validity_days',
    'quantity_available', 'per_user_limit', 'min_consumption',
    'allowed_weekdays', 'allowed_hours_start', 'allowed_hours_end', 'is_active', 'accepted_at',
])]
class CouponCampaign extends Model
{
    protected $attributes = [
        'is_active' => true,
        'per_user_limit' => 1,
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'allowed_weekdays' => 'array',
            'is_active' => 'boolean',
            'min_consumption' => 'decimal:2',
            'accepted_at' => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    /**
     * A suggestion an admin created that the owner hasn't accepted (or rejected -- rejecting
     * just deletes the row, there's no separate rejected state) yet.
     */
    public function isPending(): bool
    {
        return $this->proposed_by !== null && $this->accepted_at === null;
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     * Elegibilidade de emissao: janela de vigencia, ativa e quantidade/limite por usuario
     * disponiveis. Nunca depende da nota da avaliacao -- ver docs/SPEC.md #18.
     */
    public function isOpenForIssuance(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = now()->toDateString();

        if ($today < $this->starts_at->toDateString() || $today > $this->ends_at->toDateString()) {
            return false;
        }

        if ($this->quantity_available !== null && $this->coupons()->count() >= $this->quantity_available) {
            return false;
        }

        return true;
    }

    public function hasReachedUserLimit(int $userId): bool
    {
        return $this->coupons()->where('user_id', $userId)->count() >= $this->per_user_limit;
    }
}
