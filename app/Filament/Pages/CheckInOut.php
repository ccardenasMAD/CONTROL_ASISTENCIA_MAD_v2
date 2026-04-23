<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Attendance;
use App\Models\ShiftGroup;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Services\AttendanceCalculatorService;


class CheckInOut extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Marcar Asistencia';
    protected static ?string $navigationGroup = 'Asistencia';
    protected static string $view = 'filament.pages.check-in-out';

    public ?Attendance $attendance = null;
    public ?string $groupName = null;
    public ?string $shiftName = null;
    public ?string $shiftSchedule = null;

    public function mount(): void
    {
        $this->authorize('check-in-out');

        $user = Auth::user();

        // Asistencia del día (si existe)
        $this->attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', now()->toDateString())
            ->first();

        // Validación defensiva: un solo grupo
        if ($user->groups()->count() !== 1) {
            Notification::make()
                ->title('Tu usuario tiene una configuración inválida. Contacta a RRHH.')
                ->danger()
                ->send();
            return;
        }

        $group = $user->groups()->first();
        $this->groupName = $group->name;

        // Turno activo del grupo (consulta directa y robusta)
        $activeShiftGroup = ShiftGroup::where('group_id', $group->id)
            ->where('is_active', true)
            ->where('start_date', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->with('shift')
            ->first();

        if ($activeShiftGroup) {
            $shift = $activeShiftGroup->shift;
            $this->shiftName = $shift->name;
            $this->shiftSchedule = $shift->start_time . ' – ' . $shift->end_time;
        }
    }

    public function checkIn(): void
    {
        if ($this->attendance) {
            return;
        }

        $user = Auth::user();

        if ($user->groups()->count() !== 1) {
            Notification::make()
                ->title('Tu usuario tiene una configuración inválida. Contacta a RRHH.')
                ->danger()
                ->send();
            return;
        }

        $group = $user->groups()->first();

        $activeShiftGroup = ShiftGroup::where('group_id', $group->id)
            ->where('is_active', true)
            ->where('start_date', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->first();

        if (! $activeShiftGroup) {
            Notification::make()
                ->title('Tu grupo no tiene un turno activo.')
                ->danger()
                ->send();
            return;
        }

        $this->attendance = Attendance::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'attendance_date' => now()->toDateString(),
            'check_in' => now(),
            'source' => 'system',
        ]);

        Notification::make()
            ->title('Entrada registrada correctamente')
            ->success()
            ->send();
    }

    public function checkOut(): void
    {
        if (! $this->attendance || $this->attendance->check_out) {
            return;
        }

        $this->attendance->update([
            'check_out' => now(),
        ]);

        app(AttendanceCalculatorService::class)
            ->calculate($this->attendance);

        Notification::make()
            ->title('Salida registrada correctamente')
            ->success()
            ->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->can('registrar_asistencia');
    }
}