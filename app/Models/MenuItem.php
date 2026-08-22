<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'menu_category_id', 'name', 'description', 'price', 'compare_at_price', 'main_photo_path',
    'is_available', 'is_spicy', 'size', 'serves_count', 'position',
])]
class MenuItem extends Model
{
    protected $attributes = [
        'is_available' => true,
        'is_spicy' => false,
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'is_available' => 'boolean',
            'is_spicy' => 'boolean',
        ];
    }

    public function isOnSale(): bool
    {
        return $this->compare_at_price !== null && $this->compare_at_price > $this->price;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(MenuItemPhoto::class)->orderBy('position');
    }

    public function dietaryTags(): BelongsToMany
    {
        return $this->belongsToMany(DietaryTag::class, 'menu_item_dietary_tags')->withTimestamps();
    }

    public function foodTags(): BelongsToMany
    {
        return $this->belongsToMany(FoodTag::class, 'menu_item_food_tags')->withTimestamps();
    }

    public function restaurant(): ?Restaurant
    {
        return $this->category?->menu?->restaurant;
    }
}
