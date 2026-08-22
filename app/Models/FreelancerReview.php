<?php

namespace App\Models;

use App\Enums\ReviewApprovalStatus;
use App\Observers\FreelancerReviewObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// status fora do Fillable de proposito -- so' o proprio trabalhador aprova/rejeita
// (Freelancer\ReviewApprovalController), nunca o dono que cria a avaliacao.
#[Fillable(['hire_request_id', 'restaurant_id', 'freelancer_profile_id', 'rating', 'feedback_to_freelancer', 'feedback_to_owners'])]
#[ObservedBy(FreelancerReviewObserver::class)]
class FreelancerReview extends Model
{
    protected $attributes = [
        'status' => ReviewApprovalStatus::PendingApproval->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => ReviewApprovalStatus::class,
        ];
    }

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

    public function isPendingApproval(): bool
    {
        return $this->status === ReviewApprovalStatus::PendingApproval;
    }
}
