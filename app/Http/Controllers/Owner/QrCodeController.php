<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\QrToken;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function store(Request $request, Restaurant $restaurant): RedirectResponse
    {
        RestaurantController::authorizeOwner($request, $restaurant);

        // Expira o token anterior em vez de deixar dois validos ao mesmo tempo -- so o QR
        // exibido na tela deve funcionar pro check-in.
        $restaurant->qrTokens()->whereNull('used_at')->update(['expires_at' => now()]);

        QrToken::generateFor($restaurant, validForMinutes: 60);

        return back()->with('status', 'Novo QR Code gerado.');
    }
}
