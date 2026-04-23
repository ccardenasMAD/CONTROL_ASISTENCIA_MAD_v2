<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Shift;
use Illuminate\Auth\Access\Response;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver_turnos');
    }

    public function create(User $user): bool
    {
        return $user->can('crear_turnos');
    }

    public function update(User $user, Shift $shift): bool
    {
        return $user->can('editar_turnos');
    }

    public function delete(User $user, Shift $shift): bool
    {
        return $user->can('eliminar_turnos');
    }
}
