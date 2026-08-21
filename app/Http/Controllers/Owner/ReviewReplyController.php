<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewReplyController extends Controller
{
    public function store(Request $request, Review $review): RedirectResponse
    {
        RestaurantController::authorizeOwner($request, $review->restaurant);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $review->reply()->updateOrCreate([], [
            'user_id' => $request->user()->id,
            'message' => $data['message'],
        ]);

        return back()->with('status', 'Resposta publicada.');
    }
}
