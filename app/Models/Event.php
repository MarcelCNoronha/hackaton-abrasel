<?php

namespace App\Models;

use App\Enums\EventType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['type', 'restaurant_id', 'user_id', 'menu_item_id', 'metadata'])]
class Event extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'type' => EventType::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
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

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
