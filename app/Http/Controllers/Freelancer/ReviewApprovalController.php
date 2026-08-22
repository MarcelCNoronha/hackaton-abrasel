<?php

namespace App\Http\Controllers\Freelancer;

use App\Enums\ReviewApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\FreelancerReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewApprovalController extends Controller
{
    public function approve(Request $request, FreelancerReview $freelancerReview): RedirectResponse
    {
        $this->authorizeSubject($request, $freelancerReview);
        abort_unless($freelancerReview->isPendingApproval(), 422, 'Esta avaliação já foi respondida.');

        $freelancerReview->forceFill(['status' => ReviewApprovalStatus::Approved])->save();

        return back()->with('status', 'Vínculo confirmado -- a avaliação já está visível pra outros donos.');
    }

    public function reject(Request $request, FreelancerReview $freelancerReview): RedirectResponse
    {
        $this->authorizeSubject($request, $freelancerReview);
        abort_unless($freelancerReview->isPendingApproval(), 422, 'Esta avaliação já foi respondida.');

        $freelancerReview->forceFill(['status' => ReviewApprovalStatus::Rejected])->save();

        return back()->with('status', 'Vínculo contestado -- essa avaliação não ficará visível.');
    }

    private function authorizeSubject(Request $request, FreelancerReview $freelancerReview): void
    {
        abort_unless(
            $freelancerReview->freelancerProfile->user_id === $request->user()->id,
            403,
            'Esta avaliação não é sua.',
        );
    }
}
