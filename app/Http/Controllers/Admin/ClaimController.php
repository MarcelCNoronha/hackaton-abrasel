<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClaimStatus;
use App\Enums\RestaurantOwnerRole;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\RestaurantClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function approve(Request $request, RestaurantClaim $claim): RedirectResponse
    {
        abort_if($claim->status !== ClaimStatus::Pending, 422, 'Esta reivindicação já foi avaliada.');

        $claim->update([
            'status' => ClaimStatus::Approved,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $claim->restaurant->owners()->syncWithoutDetaching([
            $claim->user_id => ['role' => RestaurantOwnerRole::Owner],
        ]);

        // claimed_at is deliberately absent from Restaurant's #[Fillable] list (it must never
        // be settable through a public form) -- update() would silently no-op here, so this
        // trusted, admin-only write bypasses mass assignment on purpose.
        if ($claim->restaurant->claimed_at === null) {
            $claim->restaurant->forceFill(['claimed_at' => now()])->save();
        }

        // Promocao manual por um admin (User::role) e reivindicacao aprovada sao os dois
        // unicos caminhos pra virar gestor -- ver RegisteredUserController.
        if ($claim->user->role === UserRole::Consumer) {
            $claim->user->update(['role' => UserRole::Owner]);
        }

        return back()->with('status', 'Reivindicação aprovada.');
    }

    public function reject(Request $request, RestaurantClaim $claim): RedirectResponse
    {
        abort_if($claim->status !== ClaimStatus::Pending, 422, 'Esta reivindicação já foi avaliada.');

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $claim->update([
            'status' => ClaimStatus::Rejected,
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'Reivindicação rejeitada.');
    }
}
