<?php

namespace App\Http\Controllers\Owner;

use App\Enums\JobApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\HireRequest;
use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    // Aceitar uma candidatura vira uma HireRequest ja aceita -- reaproveita 100% do fluxo de
    // avaliacao que a contratacao direta ja usa (ver Owner\FreelancerReviewController),
    // em vez de duplicar a logica de "1 avaliacao por contratacao" pra um segundo caminho.
    public function accept(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        $jobPosting = $jobApplication->jobPosting;
        RestaurantController::authorizeOwner($request, $jobPosting->restaurant);
        abort_unless($jobApplication->isPending(), 422, 'Esta candidatura já foi respondida.');

        $hireRequest = HireRequest::create([
            'restaurant_id' => $jobPosting->restaurant_id,
            'freelancer_profile_id' => $jobApplication->freelancer_profile_id,
            'requested_by' => $request->user()->id,
            'status' => 'accepted',
            'message' => $jobPosting->title,
            'responded_at' => now(),
        ]);

        $jobApplication->update(['status' => JobApplicationStatus::Accepted, 'hire_request_id' => $hireRequest->id]);

        return back()->with('status', 'Candidatura aceita -- contratação registrada.');
    }

    public function decline(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        RestaurantController::authorizeOwner($request, $jobApplication->jobPosting->restaurant);
        abort_unless($jobApplication->isPending(), 422, 'Esta candidatura já foi respondida.');

        $jobApplication->update(['status' => JobApplicationStatus::Declined]);

        return back()->with('status', 'Candidatura recusada.');
    }
}
