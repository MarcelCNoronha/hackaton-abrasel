<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\FreelancerRestaurantReview;
use App\Models\HireRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RestaurantReviewController extends Controller
{
    public function store(Request $request, HireRequest $hireRequest): RedirectResponse
    {
        abort_unless(
            $hireRequest->freelancerProfile->user_id === $request->user()->id,
            403,
            'Esta contratação não é sua.',
        );
        abort_unless($hireRequest->isReviewableByFreelancer(), 422, 'Esta contratação já foi avaliada ou ainda não foi aceita.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        FreelancerRestaurantReview::create([
            'hire_request_id' => $hireRequest->id,
            'restaurant_id' => $hireRequest->restaurant_id,
            'freelancer_profile_id' => $hireRequest->freelancer_profile_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return back()->with('status', 'Avaliação enviada. Visível só pra outros freelancers.');
    }
}
