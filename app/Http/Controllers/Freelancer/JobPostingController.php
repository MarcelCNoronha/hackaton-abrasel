<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\JobSkill;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobPostingController extends Controller
{
    public function index(Request $request): Response
    {
        $profile = $request->user()->freelancerProfile()->firstOrCreate([]);

        $query = JobPosting::query()
            ->where('status', 'open')
            ->with(['restaurant:id,name,slug,address_neighborhood', 'jobSkills']);

        if ($skills = array_filter((array) $request->input('job_skills', []))) {
            $query->whereHas('jobSkills', fn ($s) => $s->whereIn('slug', $skills));
        }

        $postings = $query->latest()->limit(60)->get();

        $myApplicationByPosting = $profile->jobApplications()
            ->whereIn('job_posting_id', $postings->pluck('id'))
            ->get()
            ->keyBy('job_posting_id');

        $postings->each(function (JobPosting $posting) use ($myApplicationByPosting) {
            $application = $myApplicationByPosting->get($posting->id);
            $posting->my_application_status = $application?->status;
            $posting->my_application_id = $application?->id;
        });

        return Inertia::render('Freelancer/Jobs/Index', [
            'jobPostings' => $postings,
            'jobSkills' => JobSkill::orderBy('position')->get(['id', 'name', 'slug']),
            'filters' => ['job_skills' => array_values($skills ?? [])],
        ]);
    }
}
