<?php

namespace App\Models;

use App\Enums\CouponStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'coupon_campaign_id', 'user_id', 'restaurant_id', 'review_id',
    'code', 'issued_at', 'expires_at', 'status',
])]
class Coupon extends Model
{
    protected $attributes = [
        'status' => CouponStatus::Available->value,
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'status' => CouponStatus::class,
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(CouponCampaign::class, 'coupon_campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function redemption(): HasOne
    {
        return $this->hasOne(CouponRedemption::class);
    }

    public function isRedeemable(): bool
    {
        return $this->status === CouponStatus::Available && $this->expires_at->isFuture();
    }
}
