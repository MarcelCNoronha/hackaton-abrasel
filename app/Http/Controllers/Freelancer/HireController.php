<?php

namespace App\Http\Controllers\Freelancer;

use App\Enums\HireRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\HireRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HireController extends Controller
{
    public function accept(Request $request, HireRequest $hireRequest): RedirectResponse
    {
        $this->authorizeFreelancer($request, $hireRequest);
        abort_unless($hireRequest->isPending(), 422, 'Este pedido já foi respondido.');

        $hireRequest->update(['status' => HireRequestStatus::Accepted, 'responded_at' => now()]);

        return back()->with('status', 'Pedido de contratação aceito.');
    }

    public function decline(Request $request, HireRequest $hireRequest): RedirectResponse
    {
        $this->authorizeFreelancer($request, $hireRequest);
        abort_unless($hireRequest->isPending(), 422, 'Este pedido já foi respondido.');

        $hireRequest->update(['status' => HireRequestStatus::Declined, 'responded_at' => now()]);

        return back()->with('status', 'Pedido de contratação recusado.');
    }

    private function authorizeFreelancer(Request $request, HireRequest $hireRequest): void
    {
        abort_unless(
            $hireRequest->freelancerProfile->user_id === $request->user()->id,
            403,
            'Este pedido não é seu.',
        );
    }
}
