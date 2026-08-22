<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class UserController extends Controller
{
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', new Enum(UserRole::class)],
        ]);

        abort_if(
            $user->is($request->user()) && $data['role'] !== UserRole::Admin->value,
            422,
            'Você não pode remover sua própria permissão de administrador.',
        );

        $user->update(['role' => $data['role']]);

        return back()->with('status', 'Permissão atualizada.');
    }

    public function toggleFreelancerAccess(User $user): RedirectResponse
    {
        // Fora do #[Fillable] de proposito (mesmo padrao de restaurants.claimed_at) --
        // forceFill, nunca update() em massa.
        $user->forceFill([
            'freelancer_enabled_at' => $user->isFreelancerEnabled() ? null : now(),
        ])->save();

        return back()->with(
            'status',
            $user->isFreelancerEnabled() ? 'Módulo de empregabilidade liberado.' : 'Módulo de empregabilidade removido.',
        );
    }
}
