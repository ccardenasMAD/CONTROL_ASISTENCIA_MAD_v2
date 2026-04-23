<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendancePolicy

{
    public function viewAny(User $user): bool
    {
        return $user->can('ver_asistencia')
            || $user->can('ver_asistencia_mi_grupo');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->can('ver_asistencia')) {
            return true;
        }

        return $user->can('ver_asistencia_mi_grupo')
            && $user->leadingGroups
                ->pluck('id')
                ->contains($attendance->group_id);
    }

    public function create(User $user): bool
    {
        return $user->can('registrar_asistencia');
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->can('regularizar_asistencia');
    }
}
