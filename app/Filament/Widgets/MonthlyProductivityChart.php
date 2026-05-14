<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class MonthlyProductivityChart extends ChartWidget
{
    protected static ?string $heading = 'Productividad mensual';

    protected function getData(): array
    {
        $userId = Auth::id();
        $month = now()->month;
        $year = now()->year;

        // Obtener todos los registros del mes
        $records = Attendance::where('user_id', $userId)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get()
            ->keyBy(fn ($a) => $a->attendance_date->day);

        $daysInMonth = now()->daysInMonth;

        $labels = [];
        $data = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $labels[] = $day;

            $attendance = $records->get($day);
            $minutes = $attendance ? $attendance->getWorkedMinutes() : 0;
            $hours = round($minutes / 60, 2);

            $data[] = $hours;
        }

        $avg = count($data) ? round(array_sum($data) / count($data), 2) : 0;

        return [
            'datasets' => [
                [
                    'label' => "Horas trabajadas (Promedio: {$avg} h/día)",
                    'data' => $data,
                    'backgroundColor' => 'rgba(96, 165, 250, 0.4)', // azul suave
                    'borderColor' => '#2563eb', // azul fuerte
                    'borderWidth' => 2,
                    'tension' => 0.3, // línea curva
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    // TOOLTIP PERSONALIZADO
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => \Illuminate\Support\Js::from(
                            fn ($context) =>
                                'Día ' . $context->label . ': ' . $context->formattedValue . ' horas trabajadas'
                        ),
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Horas',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Día del mes',
                    ],
                ],
            ],
        ];
    }
}
