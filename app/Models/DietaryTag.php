<?php

namespace App\Models;

use App\Enums\DietaryTagKind;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'kind', 'icon', 'position'])]
class DietaryTag extends Model
{
    protected function casts(): array
    {
        return [
            'kind' => DietaryTagKind::class,
        ];
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_dietary_tags')->withTimestamps();
    }
}
