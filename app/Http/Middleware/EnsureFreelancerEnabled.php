<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Independente do role -- um Consumer ou um Owner podem ambos querer virar freelancer, ver
// users.freelancer_enabled_at (so' um admin liga essa coluna, nunca a propria pessoa).
class EnsureFreelancerEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isFreelancerEnabled(), 403, 'O módulo de empregabilidade ainda não foi liberado para sua conta.');

        return $next($request);
    }
}
