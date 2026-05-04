<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ShiftGroup;
use Carbon\Carbon;

class AttendanceCalculatorService
{
    public function calculate(Attendance $attendance): void
    {
        // Si no hay entrada y salida, es incompleta
        if (! $attendance->check_in || ! $attendance->check_out) {
            $attendance->update(['status' => 'incomplete']);
            return;
        }

        // Obtener el turno activo del grupo para ese día
        $shiftGroup = ShiftGroup::where('group_id', $attendance->group_id)
            ->where('is_active', true)
            ->where('start_date', '<=', $attendance->date)
            ->where(function ($q) use ($attendance) {
                    $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $attendance->date);
            })
            ->with('shift')
            ->first();

        if (! $shiftGroup) {
            $attendance->update(['status' => 'incomplete']);
            return;
        }

        $shift = $shiftGroup->shift;

        // Construir horarios reales del turno
        $shiftStart = Carbon::parse($attendance->date)->setTimeFromTimeString($shift->start_time);
        $shiftEnd = Carbon::parse($attendance->date)->setTimeFromTimeString($shift->end_time);

        $checkIn  = Carbon::parse($attendance->check_in);
        $checkOut = Carbon::parse($attendance->check_out);

        // Tolerancia para atrasos
        $lateLimit = $shiftStart->copy()->addMinutes($shift->grace_minutes);

        // Evaluaciones
        $isLate  = $checkIn->greaterThan($lateLimit);
        $isEarly = $checkOut->lessThan($shiftEnd);

        // Determinar estado
        if ($isLate && $isEarly) {
            $status = 'late'; // atrasado + salida anticipada
        } elseif ($isLate) {
            $status = 'late';
        } elseif ($isEarly) {
            $status = 'early_exit';
        } else {
            $status = 'present';
        }

        $attendance->update(['status' => $status]);
    }
}
