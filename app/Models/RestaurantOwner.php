<?php

namespace App\Models;

use App\Enums\RestaurantOwnerRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['restaurant_id', 'user_id', 'role'])]
class RestaurantOwner extends Model
{
    protected function casts(): array
    {
        return [
            'role' => RestaurantOwnerRole::class,
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
}
