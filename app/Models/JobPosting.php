<?php

namespace App\Models;

use App\Enums\JobPostingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['restaurant_id', 'created_by', 'title', 'description', 'status'])]
class JobPosting extends Model
{
    protected $attributes = [
        'status' => JobPostingStatus::Open->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => JobPostingStatus::class,
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function jobSkills(): BelongsToMany
    {
        return $this->belongsToMany(JobSkill::class, 'job_posting_job_skills')->withTimestamps();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function isOpen(): bool
    {
        return $this->status === JobPostingStatus::Open;
    }
}
