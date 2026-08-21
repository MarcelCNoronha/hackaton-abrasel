<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable(['restaurant_id', 'code', 'expires_at'])]
class QrToken extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public static function generateFor(Restaurant $restaurant, int $validForMinutes = 5): self
    {
        return self::create([
            'restaurant_id' => $restaurant->id,
            'code' => Str::random(48),
            'expires_at' => now()->addMinutes($validForMinutes),
        ]);
    }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function visit(): HasOne
    {
        return $this->hasOne(Visit::class);
    }
}
