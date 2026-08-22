<?php

namespace App\Observers;

use App\Models\FreelancerRestaurantReview;
use App\Models\Restaurant;

// Copia de FreelancerReviewObserver, na direcao contraria (recalcula o restaurante, nao o
// freelancer) -- sem filtro de status porque essa avaliacao nao passa por aprovacao (ver
// FreelancerRestaurantReview).
class FreelancerRestaurantReviewObserver
{
    public function saved(FreelancerRestaurantReview $review): void
    {
        self::recalculate($review->restaurant_id);
    }

    public function deleted(FreelancerRestaurantReview $review): void
    {
        self::recalculate($review->restaurant_id);
    }

    public static function recalculate(int $restaurantId): void
    {
        $stats = FreelancerRestaurantReview::where('restaurant_id', $restaurantId)
            ->selectRaw('count(*) as count, avg(rating) as avg_rating')
            ->first();

        Restaurant::whereKey($restaurantId)->update([
            'freelancer_reviews_count' => $stats->count,
            'freelancer_average_rating' => round($stats->avg_rating ?? 0, 2),
        ]);
    }
}
