<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Observers\ReviewObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'visit_id', 'user_id', 'restaurant_id', 'rating',
    'food_rating', 'service_rating', 'ambience_rating', 'value_rating',
    'comment', 'status',
])]
#[ObservedBy(ReviewObserver::class)]
class Review extends Model
{
    protected $attributes = [
        'status' => ReviewStatus::Published->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ReviewPhoto::class)->orderBy('position');
    }

    public function reply(): HasOne
    {
        return $this->hasOne(ReviewReply::class);
    }

    public function coupon(): HasOne
    {
        return $this->hasOne(Coupon::class);
    }
}
