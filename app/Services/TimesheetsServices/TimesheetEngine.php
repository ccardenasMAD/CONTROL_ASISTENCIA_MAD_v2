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

        // Sin registro
        if (!$attendance) {
            return [
                'state' => TimesheetState::NONE,
                'minutes' => 0,
            ];
        }

        // Normalización
        $status = strtolower(trim($attendance->status ?? ''));
        $type   = strtolower(trim($attendance->type ?? ''));

        // Vacaciones
        if ($type === 'vacation') {
            return [
                'state' => TimesheetState::VACATION,
                'minutes' => 0,
            ];
        }

        // Estado
        $state = match ($status) {
            'present' => TimesheetState::PRESENT,
            'late' => TimesheetState::LATE,
            'early_exit' => TimesheetState::EARLY_EXIT,
            'incomplete' => TimesheetState::INCOMPLETE,
            'absent' => TimesheetState::ABSENT,
            default => TimesheetState::UNKNOWN,
        };

        return [
            'state' => $state,
            'minutes' => $attendance->getWorkedMinutes() ?? 0,
        ];
    }
}