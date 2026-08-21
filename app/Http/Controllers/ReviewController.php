<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    public function store(Request $request, Visit $visit): RedirectResponse
    {
        abort_unless($visit->user_id === $request->user()->id, 403);
        abort_unless($visit->isReviewable(), 422, 'Esta visita já foi avaliada.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = $visit->review()->create([
            'user_id' => $visit->user_id,
            'restaurant_id' => $visit->restaurant_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        // A avaliacao vira cupom quando existe uma campanha ativa e aceita pelo dono, com
        // vaga e limite por usuario disponiveis -- ver CouponCampaign::isOpenForIssuance().
        // Nunca depende da nota, so da visita real ser avaliada (docs/SPEC.md #18).
        $campaign = $visit->restaurant->couponCampaigns()
            ->where('is_active', true)
            ->get()
            ->first(fn ($c) => $c->isOpenForIssuance() && ! $c->hasReachedUserLimit($visit->user_id));

        if ($campaign) {
            Coupon::create([
                'coupon_campaign_id' => $campaign->id,
                'user_id' => $visit->user_id,
                'restaurant_id' => $visit->restaurant_id,
                'review_id' => $review->id,
                'code' => Str::upper(Str::random(8)),
                'issued_at' => now(),
                'expires_at' => now()->addDays($campaign->coupon_validity_days),
            ]);
        }

        return back()->with(
            'status',
            $campaign ? 'Avaliação enviada! Você ganhou um cupom.' : 'Avaliação enviada, obrigado!',
        );
    }
}
