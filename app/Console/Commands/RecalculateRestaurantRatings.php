<?php

namespace App\Console\Commands;

use App\Models\Review;
use App\Observers\ReviewObserver;
use Illuminate\Console\Command;

class RecalculateRestaurantRatings extends Command
{
    protected $signature = 'restaurants:recalculate-ratings';

    protected $description = 'Recalcula average_rating/reviews_count/verified_reviews_count de todos os restaurantes com avaliações -- corrige dados que existiam antes do ReviewObserver.';

    public function handle(): int
    {
        $restaurantIds = Review::query()->distinct()->pluck('restaurant_id');

        $this->withProgressBar($restaurantIds, function (int $restaurantId) {
            ReviewObserver::recalculate($restaurantId);
        });

        $this->newLine(2);
        $this->info("{$restaurantIds->count()} restaurante(s) recalculado(s).");

        return self::SUCCESS;
    }
}
