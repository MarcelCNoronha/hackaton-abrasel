<?php

namespace App\Models;

use App\Observers\FreelancerRestaurantReviewObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Freelancer avalia o restaurante que o contratou -- direcao oposta de FreelancerReview
 * (dono avalia freelancer). Sem gate de aprovacao: ao contrario da avaliacao do dono (que
 * precisa do freelancer confirmar o vinculo pra evitar referencia fabricada/vingativa sobre
 * uma pessoa), aqui quem seria o alvo do veto (o proprio restaurante) nunca chega a ver essa
 * avaliacao -- so' outros freelancers -- entao nao ha quem aprovar. Mesma logica imediata do
 * Review original (cliente avalia restaurante), so' que a interacao que legitima a nota e' o
 * HireRequest aceito, no lugar da Visit.
 */
#[Fillable(['hire_request_id', 'restaurant_id', 'freelancer_profile_id', 'rating', 'comment'])]
#[ObservedBy(FreelancerRestaurantReviewObserver::class)]
class FreelancerRestaurantReview extends Model
{
    public function hireRequest(): BelongsTo
    {
        return $this->belongsTo(HireRequest::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function freelancerProfile(): BelongsTo
    {
        return $this->belongsTo(FreelancerProfile::class);
    }
}
