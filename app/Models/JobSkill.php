<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'position'])]
class JobSkill extends Model
{
    public function freelancerProfiles(): BelongsToMany
    {
        return $this->belongsToMany(FreelancerProfile::class, 'freelancer_profile_job_skills')->withTimestamps();
    }

    public function jobPostings(): BelongsToMany
    {
        return $this->belongsToMany(JobPosting::class, 'job_posting_job_skills')->withTimestamps();
    }
}
