<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver_roles');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('ver_roles');
    }

    public function create(User $user): bool
    {
        return $user->can('crear_roles');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('editar_roles');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('eliminar_roles');
    }
}
