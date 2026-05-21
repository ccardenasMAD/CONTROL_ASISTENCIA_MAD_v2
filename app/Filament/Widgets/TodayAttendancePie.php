<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\User;
use App\Models\Attendance;

class TodayAttendancePie extends ChartWidget
{
    protected static bool $isDiscovered = false;
    protected static ?string $heading = 'Asistencia de Hoy';

    protected int | string | array $columnSpan = 1;

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $today = now()->toDateString();

        /**
         * Usuarios válidos:
         * - tienen exactamente un grupo
         * - no son admin
         */
        $validUsersQuery = User::query()
            ->whereHas('groups')
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'));

        // ✅ Presentes: usuarios válidos con asistencia hoy
        $presentUsers = (clone $validUsersQuery)
            ->whereHas('attendances', fn ($q) =>
                $q->whereDate('date', $today)
            )
            ->count();

        // ✅ Ausentes: usuarios válidos sin asistencia hoy
        $absentUsers = (clone $validUsersQuery)
            ->whereDoesntHave('attendances', fn ($q) =>
                $q->whereDate('date', $today)
            )
            ->count();

        return [
            'datasets' => [
                [
                    'data' => [$presentUsers, $absentUsers],
                    'backgroundColor' => [
                        '#22c55e', // verde
                        '#ef4444', // rojo
                    ],
                ],
            ],
            'labels' => [
                'Asistentes',
                'Inasistentes',
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }

    protected function getMaxHeight(): ?string
    {
        return '320px';
    }

    protected function getPollingInterval(): ?string
    {
        return '60s';
    }

    public static function canView(): bool
    {
        return auth()->check()
            && auth()->user()->can('ver_asistencia');
    }
}