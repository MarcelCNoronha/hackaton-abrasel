<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Hierarquia simples: admin acessa qualquer area restrita a role menor.
     * Uso: ->middleware('role:owner') ou ->middleware('role:admin').
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        abort_unless($user, 403);

        $required = UserRole::from($role);

        $allowed = match ($required) {
            UserRole::Admin => $user->role === UserRole::Admin,
            UserRole::Owner => in_array($user->role, [UserRole::Owner, UserRole::Admin], true),
            UserRole::Consumer => true,
        };

        abort_unless($allowed, 403);

        return $next($request);
    }
}
