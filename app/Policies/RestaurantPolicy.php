<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;

class RestaurantPolicy
{
    /**
     * Descoberta e perfil publico do estabelecimento nao passam por policy --
     * ficam abertos a visitantes. Esta policy cobre apenas as acoes de gestao.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Owner, UserRole::Admin], true);
    }

    /**
     * Editar dados comerciais, cardapio, fotos, QR e cupons do estabelecimento.
     */
    public function manage(User $user, Restaurant $restaurant): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $restaurant->owners()->where('users.id', $user->id)->exists();
    }

    public function update(User $user, Restaurant $restaurant): bool
    {
        return $this->manage($user, $restaurant);
    }

    public function delete(User $user, Restaurant $restaurant): bool
    {
        return $user->role === UserRole::Admin;
    }
}
