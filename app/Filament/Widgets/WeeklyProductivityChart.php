<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WeeklyProductivityChart extends ChartWidget
{
    protected static ?string $heading = 'Productividad semanal (horas por día)';

    protected function getData(): array
    {
        $userId = Auth::id();

        // Lunes a domingo de la semana actual
        $startOfWeek = now()->startOfWeek(); // lunes
        $endOfWeek = now()->endOfWeek();     // domingo

        $records = Attendance::where('user_id', $userId)
            ->whereBetween('attendance_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->get()
            ->keyBy(fn ($a) => $a->attendance_date->toDateString());

        $labels = [];
        $data = [];

        for ($date = $startOfWeek->copy(); $date <= $endOfWeek; $date->addDay()) {
            $key = $date->toDateString();
            $labels[] = $date->translatedFormat('D'); // Lun, Mar, Mié...

            $attendance = $records->get($key);
            $minutes = $attendance ? $attendance->getWorkedMinutes() : 0;
            $hours = round($minutes / 60, 2);

            $data[] = $hours;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Horas trabajadas',
                    'data' => $data,
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#60a5fa',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
