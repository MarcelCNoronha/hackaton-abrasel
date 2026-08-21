<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'restaurant_id', 'qr_token_id', 'visited_at'])]
class Visit extends Model
{
    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function qrToken(): BelongsTo
    {
        return $this->belongsTo(QrToken::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function isReviewable(): bool
    {
        return $this->review === null;
    }
}
