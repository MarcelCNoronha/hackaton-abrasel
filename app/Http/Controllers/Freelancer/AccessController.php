<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Diferente do modulo de dono (so ativado por reivindicacao + aprovacao do admin -- ver
 * Admin\ClaimController), o modulo de freelancer o proprio usuario ativa/desativa na hora,
 * sem aprovacao de ninguem. freelancer_enabled_at continua fora do #[Fillable] do User e so
 * e' setado aqui, via forceFill numa acao de controller dedicada -- nao veio de um campo
 * arbitrario no payload do usuario.
 */
class AccessController extends Controller
{
    public function activate(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isFreelancerEnabled()) {
            $user->forceFill(['freelancer_enabled_at' => now()])->save();
        }

        return back()->with('status', 'Módulo freelancer ativado! Complete seu perfil para começar a aparecer pros restaurantes.');
    }

    public function deactivate(Request $request): RedirectResponse
    {
        $request->user()->forceFill(['freelancer_enabled_at' => null])->save();

        return back()->with('status', 'Módulo freelancer desativado.');
    }
}
