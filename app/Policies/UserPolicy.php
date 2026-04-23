<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;


class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver_usuarios');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('ver_usuarios');
    }

    public function create(User $user): bool
    {
        return $user->can('crear_usuarios');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('editar_usuarios');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('eliminar_usuarios');
    }
}
