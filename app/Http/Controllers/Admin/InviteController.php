<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RestaurantOwnerRole;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InviteController extends Controller
{
    public function store(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            // Nenhum provedor de e-mail configurado em producao (RESEND_API_KEY) -- sem envio
            // de convite de verdade, so linkar quem ja tem conta. Ver docs de deploy.
            throw ValidationException::withMessages([
                'email' => 'Não encontramos uma conta com esse e-mail. Peça para a pessoa se cadastrar em '.route('register').' primeiro.',
            ]);
        }

        $restaurant->owners()->syncWithoutDetaching([
            $user->id => ['role' => RestaurantOwnerRole::Owner],
        ]);

        if ($restaurant->claimed_at === null) {
            $restaurant->forceFill(['claimed_at' => now()])->save();
        }

        if ($user->role === UserRole::Consumer) {
            $user->update(['role' => UserRole::Owner]);
        }

        return back()->with('status', "{$user->name} agora gerencia {$restaurant->name}.");
    }

    public function destroy(Restaurant $restaurant, User $user): RedirectResponse
    {
        $restaurant->owners()->detach($user->id);

        // Sem nenhum gestor restante, o estabelecimento volta a aparecer na lista de
        // reivindicacao (ver Owner\ClaimController@create, que filtra por claimed_at nulo).
        if ($restaurant->owners()->count() === 0) {
            $restaurant->forceFill(['claimed_at' => null])->save();
        }

        return back()->with('status', "{$user->name} não gerencia mais {$restaurant->name}.");
    }
}
