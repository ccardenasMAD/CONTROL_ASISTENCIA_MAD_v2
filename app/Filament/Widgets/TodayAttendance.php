<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder;

class TodayAttendance extends BaseWidget
{
    protected static bool $isDiscovered = false;
    protected static ?string $heading = 'Asistencia de Hoy';

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Attendance::query()
            ->whereDate('date', now()->toDateString())
            ->with(['user', 'group'])
            ->orderBy('time')
            ->orderBy('id');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('user.name')
                ->label('Usuario')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('group.name')
                ->label('Grupo')
                ->sortable(),

            Tables\Columns\TextColumn::make('type')
                ->label('Evento')
                ->formatStateUsing(fn ($state) => match ($state) {
                    'in' => 'Entrada',
                    'out' => 'Salida',
                    default => $state,
                }),

            Tables\Columns\TextColumn::make('time')
                ->label('Hora')
                ->formatStateUsing(fn (?string $state) => $state ? substr($state, 0, 5) : '—')
                ->sortable(),

            Tables\Columns\BadgeColumn::make('status')
                ->label('Estado')
                ->colors([
                    'success' => 'present',
                    'warning' => 'late',
                    'danger' => 'absent',
                    'primary' => 'early_exit',
                    'gray' => 'incomplete',
                ])
                ->formatStateUsing(fn ($state) => match ($state) {
                    'present' => 'Presente',
                    'late' => 'Atraso',
                    'early_exit' => 'Salida Anticipada',
                    'absent' => 'Ausente',
                    'incomplete' => 'Incompleta',
                    default => '—',
                }),
        ];
    }

    /**
     * Auto refresco (en segundos)
     * Cambia a 30 si quieres más frecuencia
     */
    protected function getTablePollingInterval(): ?string
    {
        return '60s';
    }

    /**
     * Control de visibilidad por permisos
     */
    public static function canView(): bool
    {
        return auth()->check()
            && (
                auth()->user()->can('ver_asistencia')
                || auth()->user()->can('ver_asistencia_mi_grupo')
            );
    }
}