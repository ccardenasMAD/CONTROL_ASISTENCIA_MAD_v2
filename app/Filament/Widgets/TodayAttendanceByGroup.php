<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class TodayAttendanceByGroup extends ChartWidget
{
    protected static ?string $heading = 'Asistencia por Grupo';

    protected int | string | array $columnSpan = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $today = now()->toDateString();

        /**
         * Total de personas por grupo (usuarios válidos)
         */
        $totalsByGroup = User::query()
            ->select('groups.name as group_name', DB::raw('COUNT(users.id) as total'))
            ->join('group_user', 'group_user.user_id', '=', 'users.id')
            ->join('groups', 'groups.id', '=', 'group_user.group_id')
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->groupBy('groups.name')
            ->pluck('total', 'group_name');

        /**
         * Asistentes por grupo HOY
         */
        $attendedByGroup = Attendance::query()
            ->select('groups.name as group_name', DB::raw('COUNT(DISTINCT attendances.user_id) as total'))
            ->join('groups', 'groups.id', '=', 'attendances.group_id')
            ->whereDate('attendance_date', $today)
            ->groupBy('groups.name')
            ->pluck('total', 'group_name');

        $labels = $totalsByGroup->keys();

        $present = [];
        $absent  = [];

        foreach ($labels as $group) {
            $total = $totalsByGroup[$group];
            $presentCount = $attendedByGroup[$group] ?? 0;

            $present[] = $presentCount;
            $absent[]  = max($total - $presentCount, 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Asistentes',
                    'data' => $present,
                    'backgroundColor' => '#22c55e',
                    'borderColor' => '#ffffff',
                    'borderWidth' => 1,
                    'barThickness' => 16,
                ],
                [
                    'label' => 'Inasistentes',
                    'data' => $absent,
                    'backgroundColor' => '#ef4444',
                    'borderColor' => '#ffffff',
                    'borderWidth' => 1,
                    'barThickness' => 16,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        // Máximo dinámico basado en el grupo más grande
        $maxUsersInGroup = User::query()
            ->selectRaw('COUNT(users.id) as total')
            ->join('group_user', 'group_user.user_id', '=', 'users.id')
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->groupBy('group_user.group_id')
            ->orderByDesc('total')
            ->value('total') ?? 0;

        return [
            'indexAxis' => 'y', // barras horizontales
            'maintainAspectRatio' => false,
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'max' => $maxUsersInGroup,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                ],
                'y' => [
                    'stacked' => true,
                ],
            ],
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
        return '60s'; // diario = refresco más frecuente
    }

    public static function canView(): bool
    {
        return auth()->check()
            && auth()->user()->can('ver_asistencia');
    }
}