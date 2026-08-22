<?php

namespace App\Http\Controllers\Freelancer;

use App\Enums\JobApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function store(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($jobPosting->isOpen(), 422, 'Esta vaga não está mais disponível.');

        $profile = $request->user()->freelancerProfile()->firstOrCreate([]);

        abort_if(
            JobApplication::where('job_posting_id', $jobPosting->id)
                ->where('freelancer_profile_id', $profile->id)
                ->exists(),
            422,
            'Você já se candidatou a esta vaga.',
        );

        JobApplication::create([
            'job_posting_id' => $jobPosting->id,
            'freelancer_profile_id' => $profile->id,
            'message' => $data['message'] ?? null,
        ]);

        return back()->with('status', 'Candidatura enviada.');
    }

    public function destroy(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        abort_unless(
            $jobApplication->freelancerProfile->user_id === $request->user()->id,
            403,
            'Esta candidatura não é sua.',
        );
        abort_unless($jobApplication->isPending(), 422, 'Só é possível retirar uma candidatura ainda pendente.');

        $jobApplication->update(['status' => JobApplicationStatus::Withdrawn]);

        return back()->with('status', 'Candidatura retirada.');
    }
}
