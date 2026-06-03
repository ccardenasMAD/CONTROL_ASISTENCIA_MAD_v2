<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class MonthlyProductivityOverview extends Widget
{
    protected static bool $isDiscovered = false;
    protected static string $view = 'filament.widgets.monthly-productivity-overview';
    protected int|string|array $columnSpan = 'full';
    protected $listeners = ['attendance-updated' => '$refresh'];
    
    public static function isLazy(): bool
    {
        return false;
    }
    
    public function getViewData(): array
    {
        $userId = Auth::id();

        // --- SEMANAL ---
        $start = now()->startOfWeek();
        $end = now()->startOfWeek()->addDays(3);

        $weeklyRecords = Attendance::where('user_id', $userId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($a) => $a->attendance_date->toDateString());

        $weeklyLabels = [];
        $weeklyData = [];

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $weeklyLabels[] = $date->translatedFormat('l');
            $attendance = $weeklyRecords->get($date->toDateString());
            $minutes = $attendance ? $attendance->getWorkedMinutes() : 0;
            $weeklyData[] = round($minutes / 60, 2);
        }

        $weeklyAvg = round(array_sum($weeklyData) / max(count($weeklyData), 1), 2);

        // --- MENSUAL ---
        $month = now()->month;
        $year = now()->year;

        $monthlyRecords = Attendance::where('user_id', $userId)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get()
            ->keyBy(fn ($a) => $a->attendance_date->day);

        $daysInMonth = now()->daysInMonth;
        $monthlyLabels = [];
        $monthlyData = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $monthlyLabels[] = $day;
            $attendance = $monthlyRecords->get($day);
            $minutes = $attendance ? $attendance->getWorkedMinutes() : 0;
            $monthlyData[] = round($minutes / 60, 2);
        }

        $monthlyAvg = round(array_sum($monthlyData) / max(count($monthlyData), 1), 2);

        return [
            'weeklyLabels' => $weeklyLabels,
            'weeklyData' => $weeklyData,
            'weeklyAvg' => $weeklyAvg,

            'monthlyLabels' => $monthlyLabels,
            'monthlyData' => $monthlyData,
            'monthlyAvg' => $monthlyAvg,
        ];
    }
}
