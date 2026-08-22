<?php

namespace App\Http\Controllers\Owner;

use App\Enums\HireRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\FreelancerProfile;
use App\Models\HireRequest;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HireRequestController extends Controller
{
    public function store(Request $request, FreelancerProfile $freelancerProfile): RedirectResponse
    {
        $data = $request->validate([
            'restaurant_id' => ['required', 'integer'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $restaurant = Restaurant::findOrFail($data['restaurant_id']);
        RestaurantController::authorizeOwner($request, $restaurant);

        abort_if(
            HireRequest::where('restaurant_id', $restaurant->id)
                ->where('freelancer_profile_id', $freelancerProfile->id)
                ->where('status', HireRequestStatus::Pending)
                ->exists(),
            422,
            'Já existe um pedido pendente pra esse freelancer nesse estabelecimento.',
        );

        HireRequest::create([
            'restaurant_id' => $restaurant->id,
            'freelancer_profile_id' => $freelancerProfile->id,
            'requested_by' => $request->user()->id,
            'message' => $data['message'] ?? null,
        ]);

        return back()->with('status', 'Pedido de contratação enviado.');
    }

    public function cancel(Request $request, HireRequest $hireRequest): RedirectResponse
    {
        RestaurantController::authorizeOwner($request, $hireRequest->restaurant);
        abort_unless($hireRequest->isPending(), 422, 'Só é possível cancelar um pedido ainda pendente.');

        $hireRequest->update(['status' => HireRequestStatus::Cancelled, 'responded_at' => now()]);

        return back()->with('status', 'Pedido cancelado.');
    }
}
