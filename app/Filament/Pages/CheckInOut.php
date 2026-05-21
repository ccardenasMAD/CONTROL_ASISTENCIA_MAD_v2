<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Attendance;
use App\Models\ShiftGroup;
use Illuminate\Support\Collection;
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

    public ?Collection $events = null;
    public ?string $groupName = null;
    public ?string $shiftName = null;
    public ?string $shiftSchedule = null;

    public function mount(): void
    {
        $this->authorize('check-in-out');

        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // Asistencia del día (si existe)
        $this->attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', now()->toDateString())
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

        $this->attendance = Attendance::where('user_id', Auth::id())
        ->whereDate('attendance_date', Carbon::today())
        ->first();

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

    protected function getTodayEvents($user): Collection
    {
        return Attendance::forUser($user->id)
            ->whereDate('date', now()->toDateString())
            ->orderBy('time')
            ->orderBy('id')
            ->get();
    }

    protected function resolveState(): void
    {
        $state = $this->resolveStateForEvents($this->events);

        $this->stateLabel = match ($state) {
            'working' => 'Trabajando',
            'break' => 'En colación',
            'finished' => 'Jornada finalizada',
            default => 'Fuera de jornada',
        };

        $this->resolveAction($state);
    }

    protected function resolveStateForEvents(Collection $events): string
    {
        $cycleEvents = $this->resolveCycleEvents($events);
        $count = $cycleEvents->count();
        $last = $cycleEvents->last();

        if ($count === 0) {
            return $events->count() === 0 ? 'off' : 'finished';
        }

        if ($count === 1 && $last->type === 'in') {
            return 'working';
        }

        if ($count === 2 && $cycleEvents[0]->type === 'in' && $cycleEvents[1]->type === 'out') {
            return 'break';
        }

        if ($count === 3 && $last->type === 'in') {
            return 'working';
        }

        if ($count === 4 && $last->type === 'out') {
            return 'finished';
        }

        return $last->type === 'in' ? 'working' : 'finished';
    }

    protected function resolveCycleEvents(Collection $events): Collection
    {
        $current = collect();

        foreach ($events as $event) {
            $current->push($event);

            if (
                $current->count() === 4 &&
                $current[0]->type === 'in' &&
                $current[1]->type === 'out' &&
                $current[2]->type === 'in' &&
                $current[3]->type === 'out'
            ) {
                $current = collect();
                continue;
            }
        }

        return $current;
    }

    protected function resolveAction(string $state): void
    {
        $this->actionType = match ($state) {
            'off' => 'in',
            'working' => 'out',
            'break' => 'in',
            'finished' => 'in',
            default => 'in',
        };

        $this->actionLabel = match ($state) {
            'off' => 'Marcar entrada',
            'working' => $this->resolveActionLabelForWorking(),
            'break' => 'Marcar fin colación',
            'finished' => 'Marcar inicio nueva jornada',
            default => 'Marcar entrada',
        };
    }

    protected function resolveActionLabelForWorking(): string
    {
        $cycleEvents = $this->resolveCycleEvents($this->events);

        if ($cycleEvents->count() === 1) {
            return 'Marcar inicio colación';
        }

        return 'Marcar fin jornada';
    }

    public function checkIn(): void
    {
        if ($this->actionType !== 'in') {
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
            'user_id' => $user->id,
            'group_id' => $group->id,
            'attendance_date' => now()->toDateString(),
            'check_in' => now(),
            'source' => 'system',
        ]);

        $this->hydrateDayState();

        Notification::make()
            ->title('Entrada registrada correctamente.')
            ->success()
            ->send();

        $this->events = $this->getTodayEvents($user);
        $this->resolveState();
    }

    public function checkOut(): void
    {
        if ($this->actionType !== 'out') {
            return;
        }

        $this->attendance->update([
            'check_out' => now(),
        ]);

        app(AttendanceCalculatorService::class)
            ->calculate($this->attendance);

        Notification::make()
            ->title('Salida registrada correctamente.')
            ->success()
            ->send();

        $this->events = $this->getTodayEvents($user);
        $this->resolveState();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->can('registrar_asistencia');
    }
}
