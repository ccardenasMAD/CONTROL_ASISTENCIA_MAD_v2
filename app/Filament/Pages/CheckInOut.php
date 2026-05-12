<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Attendance;
use App\Models\ShiftGroup;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Services\AttendanceCalculatorService;
use Carbon\Carbon;

class CheckInOut extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Marcar asistencia';
    protected static ?string $navigationGroup = 'Asistencia';
    protected static string $view = 'filament.pages.check-in-out';

    public ?Attendance $attendance = null;
    public ?string $groupName = null;
    public ?string $shiftName = null;
    public ?string $shiftSchedule = null;

    public ?string $dayStatus = null;
    public ?string $dayStatusLabel = null;
    public ?string $dayStatusColor = null;
    public ?string $checkInTime = null;
    public ?string $checkOutTime = null;
    public ?string $workedDuration = null;

    public function mount(): void
    {
        $this->authorize('check-in-out');

        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $this->attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($user->groups()->count() !== 1) {
            Notification::make()
                ->title('Tu usuario tiene una configuración inválida. Contacta a RRHH.')
                ->danger()
                ->send();
            return;
        }

        $group = $user->groups()->first();
        $this->groupName = $group->name;

        $activeShiftGroup = ShiftGroup::where('group_id', $group->id)
            ->where('is_active', true)
            ->where('start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $today);
            })
            ->with('shift')
            ->first();

        if ($activeShiftGroup) {
            $shift = $activeShiftGroup->shift;
            $this->shiftName = $shift->name;
            $this->shiftSchedule = $shift->start_time . ' – ' . $shift->end_time;
        }

        $this->hydrateDayState();
    }

    protected function hydrateDayState(): void
    {
        if (! $this->attendance) {
            $this->dayStatus = 'no_record';
            $this->dayStatusLabel = 'Sin registro hoy';
            $this->dayStatusColor = 'bg-gray-200 text-gray-700';
            $this->checkInTime = null;
            $this->checkOutTime = null;
            $this->workedDuration = null;
            return;
        }

        if ($this->attendance->type === 'vacation') {
            $this->dayStatus = 'vacation';
            $this->dayStatusLabel = 'Vacaciones';
            $this->dayStatusColor = 'bg-pink-100 text-pink-700';
        } else {
            $status = $this->attendance->status;

            [$label, $color] = match ($status) {
                'present' => ['Presente', 'bg-green-100 text-green-700'],
                'late' => ['Atraso', 'bg-yellow-100 text-yellow-700'],
                'early_exit' => ['Salida anticipada', 'bg-blue-100 text-blue-700'],
                'absent' => ['Ausente', 'bg-red-100 text-red-700'],
                'incomplete' => ['Incompleta', 'bg-gray-100 text-gray-700'],
                default => ['Estado desconocido', 'bg-gray-200 text-gray-700'],
            };

            $this->dayStatus = $status;
            $this->dayStatusLabel = $label;
            $this->dayStatusColor = $color;
        }

        $this->checkInTime = $this->attendance->check_in?->format('H:i');
        $this->checkOutTime = $this->attendance->check_out?->format('H:i');

        if ($this->attendance->check_in && $this->attendance->check_out) {
            $minutes = $this->attendance->check_out->diffInMinutes($this->attendance->check_in);
            $hours = intdiv($minutes, 60);
            $rest = $minutes % 60;
            $this->workedDuration = "{$hours}h {$rest}m";
        } else {
            $this->workedDuration = null;
        }
    }

    public function checkIn(): void
    {
        if ($this->attendance) {
            return;
        }

        $user = Auth::user();
        $today = Carbon::today()->toDateString();

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
            ->where('start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $today);
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
            'user_id'         => $user->id,
            'group_id'        => $group->id,
            'attendance_date' => $today,
            'check_in'        => now(),
            'source'          => 'system',
            'status'          => 'present',
        ]);

        $this->hydrateDayState();

        Notification::make()
            ->title('Entrada registrada correctamente.')
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
            ->calculate($this->attendance->fresh());

        $this->attendance->refresh();
        $this->hydrateDayState();

        Notification::make()
            ->title('Salida registrada correctamente.')
            ->success()
            ->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->can('registrar_asistencia');
    }
}
