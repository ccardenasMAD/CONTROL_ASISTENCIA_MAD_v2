<?php

/*namespace App\Filament\Widgets;

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
}*/ 









namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class WeeklyProductivityChart extends ChartWidget
{
     
    protected static bool $isDiscovered = false;
    protected static ?string $heading = null;

    public function getHeading(): string
    {
        $userId = Auth::id();

        $start = now()->startOfWeek();         
        $end   = now()->startOfWeek()->addDays(3); 

        $records = Attendance::where('user_id', $userId)
            ->whereBetween('attendance_date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->get()
            ->keyBy(fn ($a) => $a->attendance_date->toDateString());

        $data = [];

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $attendance = $records->get($date->toDateString());
            $minutes = $attendance ? $attendance->getWorkedMinutes() : 0;
            $hours = round($minutes / 60, 2);
            $data[] = $hours;
        }

        $avg = count($data) ? round(array_sum($data) / count($data), 2) : 0;

        return "Productividad semanal (Promedio: {$avg} h/día)";
    }

    protected function getData(): array
    {
        $userId = Auth::id();

        // Lunes a jueves
        $start = now()->startOfWeek();              // lunes
        $end   = now()->startOfWeek()->addDays(3);  // jueves

        $records = Attendance::where('user_id', $userId)
            ->whereBetween('attendance_date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->get()
            ->keyBy(fn ($a) => $a->attendance_date->toDateString());

        $labels = [];
        $data   = [];
        $colors = [];

        // Colores dinámicos por día
        $dynamicColors = [
            'Lunes'      => '#3b82f6', // azul
            'Martes'     => '#22c55e', // verde
            'Miércoles'  => '#eab308', // amarillo
            'Jueves'     => '#ef4444', // rojo
        ];

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $dayName = $date->translatedFormat('l'); // Lunes, Martes...

            $labels[] = $dayName;

            $attendance = $records->get($date->toDateString());
            $minutes = $attendance ? $attendance->getWorkedMinutes() : 0;
            $hours = round($minutes / 60, 2);

            $data[]   = $hours;
            $colors[] = $dynamicColors[$dayName] ?? '#3b82f6';
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Horas trabajadas',
                    'data'            => $data,
                    'backgroundColor' => $colors,
                    'borderColor'     => '#1e293b',
                    'borderWidth'     => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => \Illuminate\Support\Js::from(
                            fn ($context) =>
                                $context->label . ': ' . $context->formattedValue . ' horas trabajadas'
                        ),
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text'    => 'Horas',
                    ],
                ],
            ],
        ];
    }
}
