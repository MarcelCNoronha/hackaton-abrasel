<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'headline', 'bio', 'availability_status'])]
class FreelancerProfile extends Model
{
    protected $attributes = [
        'availability_status' => AvailabilityStatus::Unavailable->value,
        'average_rating' => 0,
        'reviews_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'availability_status' => AvailabilityStatus::class,
            'average_rating' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobSkills(): BelongsToMany
    {
        return $this->belongsToMany(JobSkill::class, 'freelancer_profile_job_skills')->withTimestamps();
    }

    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(FreelancerAvailabilitySlot::class);
    }

    public function hireRequests(): HasMany
    {
        return $this->hasMany(HireRequest::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(FreelancerReview::class);
    }
}
