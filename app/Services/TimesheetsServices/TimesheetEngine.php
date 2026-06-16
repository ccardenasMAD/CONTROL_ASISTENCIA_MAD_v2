<?php

namespace App\Services\TimesheetsServices;

use Carbon\Carbon;
use App\Models\Attendance;
use App\Enums\TimesheetState;

class TimesheetEngine
{
    // Lunes (1) a Jueves (4)
    private array $workingDays = [1, 2, 3, 4];

    public function resolve(Carbon $date, ?Attendance $attendance): array
    {
        // Viernes, sábado, domingo = NO laboral
        if (!in_array($date->dayOfWeekIso, $this->workingDays)) {
            return [
                'state' => TimesheetState::NON_WORKING_DAY,
                'minutes' => 0,
            ];
        }

        // Si no hay registro en la base de datos para este día
        if (!$attendance) {
            return [
                'state' => TimesheetState::NONE,
                'minutes' => 0,
            ];
        }

       
        $status = strtolower(trim($attendance->getStatus() ?? ''));
        $type   = strtolower(trim($attendance->type ?? ''));

        // Vacaciones
        if ($type === 'vacation' || $status === 'vacation') {
            return [
                'state' => TimesheetState::VACATION,
                'minutes' => 0,
            ];
        }

        // Si la base de datos está vacía en la columna status pero tiene tiempos de entrada/salida
        if (empty($status) && ($attendance->check_in || $attendance->check_out)) {
            $status = 'present';
        }

        // Mapeo seguro al Enum de estados
        $state = match ($status) {
            'present'    => TimesheetState::PRESENT,
            'late'       => TimesheetState::LATE,
            'early_exit' => TimesheetState::EARLY_EXIT,
            'incomplete' => TimesheetState::INCOMPLETE,
            'absent'     => TimesheetState::ABSENT,
            'none'       => TimesheetState::NONE,
            default      => TimesheetState::PRESENT, 
        };

        return [
            'state' => $state,
            'minutes' => $attendance->getWorkedMinutes() ?? 0,
        ];
    }
}