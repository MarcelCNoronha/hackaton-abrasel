<?php

namespace App\Models;

use App\Observers\FreelancerReviewObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['hire_request_id', 'restaurant_id', 'freelancer_profile_id', 'rating', 'feedback_to_freelancer', 'feedback_to_owners'])]
#[ObservedBy(FreelancerReviewObserver::class)]
class FreelancerReview extends Model
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
