<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver_permisos');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->can('ver_permisos');
    }

    public function create(User $user): bool
    {
        return $user->can('crear_permisos');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->can('editar_permisos');
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->can('eliminar_permisos');
    }
}
