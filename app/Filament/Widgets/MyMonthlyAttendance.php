<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class MyMonthlyAttendance extends Widget
{
    protected static string $view = 'filament.widgets.my-monthly-attendance';

    protected static ?string $heading = 'Mi asistencia del mes';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $userId = Auth::id();
        $month = now()->month;

        $records = Attendance::where('user_id', $userId)
            ->whereMonth('attendance_date', $month)
            ->get();

        return [
            'present' => $records->whereNotNull('check_in')->count(),
            'absent' => $records->whereNull('check_in')->count(),
            'late' => $records->filter(fn($a) => $a->getStatus() === 'late')->count(),
            'early_exit' => $records->filter(fn($a) => $a->getStatus() === 'early_exit')->count(),
            'hours' => $records->sum(fn($a) => $a->getWorkedMinutes()),
        ];
    }
}



