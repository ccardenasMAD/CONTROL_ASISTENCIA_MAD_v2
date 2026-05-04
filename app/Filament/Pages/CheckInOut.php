<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Attendance;
use App\Models\ShiftGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CheckInOut extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Marcar Asistencia';
    protected static ?string $navigationGroup = 'Asistencia';
    protected static string $view = 'filament.pages.check-in-out';

    public ?Collection $events = null;
    public ?string $groupName = null;
    public ?string $shiftName = null;
    public ?string $shiftSchedule = null;
    public ?string $stateLabel = null;
    public ?string $actionLabel = null;
    public ?string $actionType = null;

    public function mount(): void
    {
        $this->authorize('check-in-out');

        $user = Auth::user();

        $this->events = $this->getTodayEvents($user);
        $this->resolveState();

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

        Attendance::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'date' => now()->toDateString(),
            'time' => now()->format('H:i:s'),
            'type' => 'in',
            'source' => 'system',
        ]);

        Notification::make()
            ->title('Entrada registrada correctamente')
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

        $user = Auth::user();

        Attendance::create([
            'user_id' => $user->id,
            'group_id' => $user->groups()->first()?->id,
            'date' => now()->toDateString(),
            'time' => now()->format('H:i:s'),
            'type' => 'out',
            'source' => 'system',
        ]);

        Notification::make()
            ->title('Salida registrada correctamente')
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
