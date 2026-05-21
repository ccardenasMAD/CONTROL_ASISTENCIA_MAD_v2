<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder;

class TodayAbsent extends BaseWidget
{
    protected static bool $isDiscovered = false;
    protected static ?string $heading = 'Personas sin marcar hoy';

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        // IDs de usuarios que ya marcaron hoy
        $usersWithAttendanceToday = Attendance::query()
            ->whereDate('date', now()->toDateString())
            ->pluck('user_id');

        return User::query()
            // Usuarios activos (ajusta si tienes columna is_active)
            ->whereDoesntHave('attendances', function ($query) {
                $query->whereDate('date', now()->toDateString());
            })
            // Excluir usuarios sin grupo (configuración inválida)
            ->whereHas('groups')
            // Excluir administradores si quieres
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'admin');
            })
            ->with('groups');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Usuario')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('groups.name')
                ->label('Grupo')
                ->badge(),

            Tables\Columns\TextColumn::make('email')
                ->label('Correo')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * Refresco automático
     */
    protected function getTablePollingInterval(): ?string
    {
        return '60s';
    }

    /**
     * Quién puede ver este widget
     */
    public static function canView(): bool
    {
        return auth()->check()
            && auth()->user()->can('ver_asistencia');
    }
}
