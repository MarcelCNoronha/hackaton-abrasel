<?php

namespace App\Observers;

use App\Enums\ReviewStatus;
use App\Models\Restaurant;
use App\Models\Review;

class ReviewObserver
{
    public function saved(Review $review): void
    {
        self::recalculate($review->restaurant_id);
    }

    public function deleted(Review $review): void
    {
        self::recalculate($review->restaurant_id);
    }

    // Toda avaliacao hoje nasce de uma visita real (check-in por QR, ver ReviewController::store) --
    // nao existe caminho pra criar uma sem visita, entao "verificada" e "publicada" coincidem por
    // enquanto. Contadores ficam separados no schema para o dia em que um caminho nao-verificado
    // (ex.: avaliacao sem check-in) passe a existir.
    //
    // Publico e estatico -- alem do observer (avaliacoes novas), o comando
    // restaurants:recalculate-ratings usa o mesmo caminho pra corrigir avaliacoes que ja existiam
    // antes deste observer existir.
    public static function recalculate(int $restaurantId): void
    {
        $stats = Review::where('restaurant_id', $restaurantId)
            ->where('status', ReviewStatus::Published)
            ->selectRaw('count(*) as count, avg(rating) as avg_rating')
            ->first();

        Restaurant::whereKey($restaurantId)->update([
            'reviews_count' => $stats->count,
            'verified_reviews_count' => $stats->count,
            'average_rating' => round($stats->avg_rating ?? 0, 2),
        ]);
    }
}
