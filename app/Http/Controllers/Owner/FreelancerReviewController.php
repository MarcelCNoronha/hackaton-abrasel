<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\HireRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FreelancerReviewController extends Controller
{
    public function store(Request $request, HireRequest $hireRequest): RedirectResponse
    {
        RestaurantController::authorizeOwner($request, $hireRequest->restaurant);
        abort_unless($hireRequest->isReviewable(), 422, 'Esta contratação não pode ser avaliada.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback_to_freelancer' => ['nullable', 'string', 'max:1000'],
            'feedback_to_owners' => ['nullable', 'string', 'max:1000'],
        ]);

        $hireRequest->review()->create([
            'restaurant_id' => $hireRequest->restaurant_id,
            'freelancer_profile_id' => $hireRequest->freelancer_profile_id,
            ...$data,
        ]);

        return back()->with('status', 'Avaliação enviada.');
    }
}
