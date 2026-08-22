<?php

namespace App\Observers;

use App\Enums\ReviewApprovalStatus;
use App\Models\FreelancerProfile;
use App\Models\FreelancerReview;

// Copia direta de ReviewObserver, adaptada pra FreelancerProfile -- ver esse arquivo pra
// contexto completo do porque disso ser um Observer (e nao so' um recalculo manual no
// controller): toda avaliacao precisa refletir na media na hora, incluindo remocao.
class FreelancerReviewObserver
{
    public function saved(FreelancerReview $review): void
    {
        self::recalculate($review->freelancer_profile_id);
    }

    public function deleted(FreelancerReview $review): void
    {
        self::recalculate($review->freelancer_profile_id);
    }

    // Publico e estatico -- reusado pelo comando freelancers:recalculate-ratings pra
    // corrigir avaliacoes que ja existiam antes deste observer existir.
    public static function recalculate(int $freelancerProfileId): void
    {
        // So' avaliacoes aprovadas pelo proprio trabalhador contam -- uma pendente ou
        // rejeitada nao pode influenciar a nota publica.
        $stats = FreelancerReview::where('freelancer_profile_id', $freelancerProfileId)
            ->where('status', ReviewApprovalStatus::Approved)
            ->selectRaw('count(*) as count, avg(rating) as avg_rating')
            ->first();

        FreelancerProfile::whereKey($freelancerProfileId)->update([
            'reviews_count' => $stats->count,
            'average_rating' => round($stats->avg_rating ?? 0, 2),
        ]);
    }
}
