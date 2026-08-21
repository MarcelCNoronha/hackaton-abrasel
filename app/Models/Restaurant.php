<?php

namespace App\Models;

use App\Enums\PriceRange;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'name', 'slug', 'description',
    'address_street', 'address_number', 'address_neighborhood', 'address_city', 'address_state', 'address_zip_code',
    'latitude', 'longitude', 'phone', 'whatsapp', 'social_links',
    'price_range', 'cover_photo_path', 'is_active',
])]
class Restaurant extends Model
{
    protected $attributes = [
        'is_active' => true,
        'average_rating' => 0,
        'reviews_count' => 0,
        'verified_reviews_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'social_links' => 'array',
            'rating_distribution' => 'array',
            'price_range' => PriceRange::class,
            'average_rating' => 'decimal:2',
            'is_active' => 'boolean',
            'claimed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isClaimed(): bool
    {
        return $this->claimed_at !== null;
    }

    /**
     * Usa a relacao businessHours ja carregada em memoria (sem query extra) --
     * chamador deve eager-load 'businessHours' antes de usar em listagens.
     */
    public function isOpenAt(?Carbon $at = null): bool
    {
        $at ??= now();

        return $this->businessHours->contains(
            fn (BusinessHour $hours) => $hours->weekday === (int) $at->format('w')
                && ! $hours->is_closed
                && $hours->opens_at !== null
                && $hours->closes_at !== null
                && $at->format('H:i:s') >= $hours->opens_at
                && $at->format('H:i:s') <= $hours->closes_at
        );
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'restaurant_categories')->withTimestamps();
    }

    public function cuisines(): BelongsToMany
    {
        return $this->belongsToMany(Cuisine::class, 'restaurant_cuisines')->withTimestamps();
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_owners')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function claims(): HasMany
    {
        return $this->hasMany(RestaurantClaim::class);
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(RestaurantPhoto::class)->orderBy('position');
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function qrTokens(): HasMany
    {
        return $this->hasMany(QrToken::class);
    }

    public function activeQrToken(): HasOne
    {
        return $this->hasOne(QrToken::class)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latestOfMany();
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function couponCampaigns(): HasMany
    {
        return $this->hasMany(CouponCampaign::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Adiciona a coluna calculada distance_km (haversine) sem alterar ordenacao --
     * quem decide o ORDER BY e o chamador (ver DiscoveryController). MVP sem PostGIS,
     * ver docs/ARQUITETURA_BASE.md.
     */
    public function scopeSelectDistance(Builder $query, float $latitude, float $longitude): Builder
    {
        $haversine = self::haversineExpression();

        return $query->selectRaw("restaurants.*, {$haversine} AS distance_km", [$latitude, $longitude, $latitude]);
    }

    public function scopeOrderByDistance(Builder $query, float $latitude, float $longitude): Builder
    {
        $haversine = self::haversineExpression();

        return $query->orderByRaw("{$haversine} asc", [$latitude, $longitude, $latitude]);
    }

    public function scopeWithinDistance(Builder $query, float $latitude, float $longitude, float $maxKm): Builder
    {
        $haversine = self::haversineExpression();

        return $query->whereRaw("{$haversine} <= ?", [$latitude, $longitude, $latitude, $maxKm]);
    }

    /**
     * "Aberto agora": existe um business_hours para o dia da semana atual cujo
     * intervalo cobre o horario atual. Nao trata horarios que passam da meia-noite.
     */
    public function scopeOpenNow(Builder $query): Builder
    {
        $now = now();

        return $query->whereHas('businessHours', function (Builder $hours) use ($now) {
            $hours->where('weekday', (int) $now->format('w'))
                ->where('is_closed', false)
                ->where('opens_at', '<=', $now->format('H:i:s'))
                ->where('closes_at', '>=', $now->format('H:i:s'));
        });
    }

    private static function haversineExpression(): string
    {
        return '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) *
            cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))';
    }
}
