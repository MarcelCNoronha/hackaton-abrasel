<?php

namespace App\Models;

use App\Enums\JobApplicationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['job_posting_id', 'freelancer_profile_id', 'message', 'status', 'hire_request_id'])]
class JobApplication extends Model
{
    protected $attributes = [
        'status' => JobApplicationStatus::Pending->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => JobApplicationStatus::class,
        ];
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function freelancerProfile(): BelongsTo
    {
        return $this->belongsTo(FreelancerProfile::class);
    }

    public function hireRequest(): BelongsTo
    {
        return $this->belongsTo(HireRequest::class);
    }

    public function isPending(): bool
    {
        return $this->status === JobApplicationStatus::Pending;
    }
}
