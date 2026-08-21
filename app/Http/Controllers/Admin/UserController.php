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
}
