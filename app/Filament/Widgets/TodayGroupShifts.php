<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use App\Models\ShiftGroup;
use Illuminate\Database\Eloquent\Builder;

class TodayGroupShifts extends BaseWidget
{
    protected static ?string $heading = 'Turnos por grupo ';

    protected int | string | array $columnSpan = '1';

    protected function getTableQuery(): Builder
    {
        return ShiftGroup::query()
            ->where('is_active', true)
            ->where('start_date', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->with(['group', 'shift'])
            ->orderBy('group_id');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('group.name')
                ->label('Grupo')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('shift.name')
                ->label('Turno')
                ->sortable(),

            Tables\Columns\TextColumn::make('shift.start_time')
                ->label('Horario')
                ->formatStateUsing(fn ($state, $record) =>
                    $record->shift->start_time . ' – ' . $record->shift->end_time
                ),

            Tables\Columns\BadgeColumn::make('is_active')
                ->label('Estado')
                ->formatStateUsing(fn ($state) => $state ? 'Activo' : 'Inactivo')
                ->colors([
                    'success' => true,
                    'gray' => false,
                ]),
        ];
    }

    protected function getMaxHeight(): ?string
    {
        return '320px';
    }

    /**
     * Refresco automático
     */
    protected function getTablePollingInterval(): ?string
    {
        return '300s'; // cada 5 minutos
    }

    /**
     * Permisos
     */
    public static function canView(): bool
    {
        return auth()->check()
            && auth()->user()->can('ver_asistencia');
    }
}