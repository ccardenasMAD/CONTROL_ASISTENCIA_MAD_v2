<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GroupPolicy
{
    /**
     * Ver listado de grupos
     * - Admin: sí (ver_grupos)
     * - Jefes: sí, pero lo filtraremos en el Resource
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver_grupos')
            || $user->can('ver_mi_grupo');
    }

    /**
     * Ver un grupo específico
     */
    public function view(User $user, Group $group): bool
    {
        // Admin
        if ($user->can('ver_grupos')) {
            return true;
        }

        // Jefe del grupo
        if (
            $user->can('ver_mi_grupo') &&
            $group->leaders->contains($user->id)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Crear grupos (solo admin)
     */
    public function create(User $user): bool
    {
        return $user->can('crear_grupos');
    }

    /**
     * Editar grupos
     */
    public function update(User $user, Group $group): bool
    {
        // Admin
        if ($user->can('editar_grupos')) {
            return true;
        }

        // (futuro) Jefe del grupo
        if (
            $user->can('gestionar_mi_grupo') &&
            $group->leaders->contains($user->id)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Eliminar grupos (solo admin)
     */
    public function delete(User $user, Group $group): bool
    {
        return $user->can('eliminar_grupos');
    }
}