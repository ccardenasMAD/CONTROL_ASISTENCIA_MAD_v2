<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ShiftGroup;
use Illuminate\Auth\Access\Response;

class ShiftGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver_asignaciones_turnos');
    }

    public function create(User $user): bool
    {
        return $user->can('crear_asignaciones_turnos');
    }

    public function update(User $user, ShiftGroup $shiftGroup): bool
    {
        return $user->can('editar_asignaciones_turnos');
    }

    public function delete(User $user, ShiftGroup $shiftGroup): bool
    {
        return $user->can('eliminar_asignaciones_turnos');
    }
}
