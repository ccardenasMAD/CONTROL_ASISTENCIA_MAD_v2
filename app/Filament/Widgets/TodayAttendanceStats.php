<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Attendance;

class TodayAttendanceStats extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;
    protected function getStats(): array
    {
        $today = now()->toDateString();

        // Usuarios válidos (con grupo, excluyendo admin)
        $totalUsers = User::query()
            ->whereHas('groups')
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'admin');
            })
            ->count();

        // Usuarios que marcaron hoy
        $markedToday = Attendance::query()
            ->whereDate('attendance_date', $today)
            ->distinct('user_id')
            ->count('user_id');

        $absentToday = max($totalUsers - $markedToday, 0);

        return [
            Stat::make('Total personas', $totalUsers)
                ->description('Personas activas hoy')
                ->icon('heroicon-o-users'),

            Stat::make('Marcados hoy', $markedToday)
                ->description('Entradas registradas')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Faltan', $absentToday)
                ->description('Sin marcar asistencia')
                ->color($absentToday > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-x-circle'),
        ];
    }

    /**
     * Refresco automático
     */
    protected function getPollingInterval(): ?string
    {
        return '60s';
    }

    /**
     * Control de permisos
     */
    public static function canView(): bool
    {
        return auth()->check()
            && auth()->user()->can('ver_asistencia');
    }
}