<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttendanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Helpers internos
    |--------------------------------------------------------------------------
    */

    private function getTodayEvents($user)
    {
        return Attendance::forUser($user->id)
            ->whereDate('date', today())
            ->orderBy('time')
            ->orderBy('id')
            ->get();
    }

    private function resolveState($events)
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

    private function resolveCycleEvents($events)
    {
        $cycle = collect();
        $current = collect();

        foreach ($events as $event) {
            $current->push($event);

            // Un ciclo se cierra SOLO con patrón: in → out → in → out
            if (
                $current->count() === 4 &&
                $current[0]->type === 'in' &&
                $current[1]->type === 'out' &&
                $current[2]->type === 'in' &&
                $current[3]->type === 'out'
            ) {
                // ciclo completo → lo descartamos y empezamos otro
                $current = collect();
                continue;
            }
        }

        return $current; // ciclo ACTUAL en curso
    }

    private function calculateWorkedSeconds($cycleEvents, $state)
    {
        $worked = 0;
        $lastIn = null;

        foreach ($cycleEvents as $event) {
            if ($event->type === 'in') {
                $lastIn = $event;
            }

            if ($event->type === 'out' && $lastIn) {
                $worked += strtotime($event->time) - strtotime($lastIn->time);
                $lastIn = null;
            }
        }

        if ($state === 'working' && $lastIn) {
            $worked += now()->diffInSeconds(
                today()->setTimeFromTimeString($lastIn->time)
            );
        }

        if (in_array($state, ['off', 'finished'])) {
            return 0;
        }

        return max(0, $worked);
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar ENTRADA
    |--------------------------------------------------------------------------
    */
    public function checkIn(Request $request)
    {
        $user = $request->user();
        $events = $this->getTodayEvents($user);
        $state  = $this->resolveState($events);

        // ✅ Solo está prohibido marcar entrada si ya estás trabajando
        if ($state === 'working') {
            return response()->json([
                'message' => 'No puedes marcar entrada porque ya estás trabajando',
            ], Response::HTTP_CONFLICT);
        }

        $attendance = Attendance::create([
            'user_id'  => $user->id,
            'group_id' => $user->groups()->first()?->id,
            'date'     => today(),
            'time'     => now()->format('H:i:s'),
            'type'     => 'in',
            'source'   => 'mobile',
        ]);

        return response()->json([
            'message'    => '✅ Inicio de jornada registrado correctamente',
            'attendance' => $attendance,
        ], Response::HTTP_CREATED);
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar SALIDA
    |--------------------------------------------------------------------------
    */
    public function checkOut(Request $request)
    {
        $user = $request->user();
        $events = $this->getTodayEvents($user);
        $state  = $this->resolveState($events);

        // ✅ SOLO se puede marcar salida si estás trabajando o en colación
        if (!in_array($state, ['working', 'break'])) {
            return response()->json([
                'message' => 'No puedes marcar salida en este estado',
            ], Response::HTTP_CONFLICT);
        }

        $attendance = Attendance::create([
            'user_id'  => $user->id,
            'group_id' => $user->groups()->first()?->id,
            'date'     => today(),
            'time'     => now()->format('H:i:s'),
            'type'     => 'out',
            'source'   => 'mobile',
        ]);

        return response()->json([
            'message'    => '✅ Salida registrada correctamente',
            'attendance' => $attendance,
        ], Response::HTTP_CREATED);
    }

    /*
    |--------------------------------------------------------------------------
    | Estado actual del usuario (HOME)
    |--------------------------------------------------------------------------
    */
    public function status(Request $request)
    {
        $user = $request->user();
        $events = $this->getTodayEvents($user);

        $state = $this->resolveState($events);
        $stateLabel = match ($state) {
            'working'  => 'Trabajando',
            'break'    => 'En colación',
            'finished' => 'Jornada finalizada',
            default    => 'Fuera de jornada',
        };

        $cycleEvents = $this->resolveCycleEvents($events);
        $workedSeconds = $this->calculateWorkedSeconds($cycleEvents, $state);

        // Próxima acción
        $nextAction = match ($state) {
            'off' => [
                'type' => 'in',
                'label' => 'Marcar inicio jornada',
                'enabled' => true,
            ],
            'working' => [
                'type' => 'out',
                'label' => $cycleEvents->count() === 1
                    ? 'Marcar inicio colación'
                    : 'Marcar fin jornada',
                'enabled' => true,
            ],
            'break' => [
                'type' => 'in',
                'label' => 'Marcar fin colación',
                'enabled' => true,
            ],
            'finished' => [
                'type' => 'in',
                'label' => 'Marcar inicio nueva jornada',
                'enabled' => true,
            ],
        };

        // Info colación (solo ciclo actual)
        $breakStart = null;
        $breakEnd = null;
        $breakInProgress = false;

        $outs = $cycleEvents->where('type', 'out')->values();
        $ins  = $cycleEvents->where('type', 'in')->values();

        if ($outs->count() >= 1) {
            $breakStart = substr($outs[0]->time, 0, 5);

            if ($ins->count() >= 2) {
                $breakEnd = substr($ins[1]->time, 0, 5);
            } else {
                $breakInProgress = true;
            }
        }

        $last = $events->last();

        return response()->json([
            'date' => today()->format('Y-m-d'),
            'current_time' => now()->format('H:i:s'),

            'state' => $state,
            'state_label' => $stateLabel,

            'work' => [
                'worked_seconds' => (int) $workedSeconds,
                'worked_human' => gmdate('H \h i \m', $workedSeconds),
                'on_break' => $state === 'break',
            ],

            'last_mark' => $last ? [
                'type' => $last->type,
                'label' => $last->type === 'in' ? 'Entrada' : 'Salida',
                'time' => substr($last->time, 0, 5),
            ] : null,

            'next_action' => $nextAction,

            'break' => [
                'start' => $breakStart,
                'end' => $breakEnd,
                'in_progress' => $breakInProgress,
            ],
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();

        $from = $request->query('from', now()->subDays(7)->toDateString());
        $to   = $request->query('to', now()->toDateString());

        $events = Attendance::forUser($user->id)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->orderBy('time')
            ->orderBy('id')
            ->get()
            ->groupBy('date');

        $result = [];

        foreach ($events as $date => $dayEvents) {

            // === dividir por ciclos (jornadas) ===
            $current = collect();

            foreach ($dayEvents as $event) {
                $current->push($event);

                if (
                    $current->count() === 4 &&
                    $current[0]->type === 'in' &&
                    $current[1]->type === 'out' &&
                    $current[2]->type === 'in' &&
                    $current[3]->type === 'out'
                ) {
                    $result[] = $this->buildDaySummary($date, $current);
                    $current = collect();
                }
            }

            // ciclo incompleto
            if ($current->isNotEmpty()) {
                $result[] = $this->buildDaySummary($date, $current);
            }
        }

        return response()->json([
            'from' => $from,
            'to' => $to,
            'data' => $result,
        ]);
    }

    private function buildDaySummary(string $date, $cycle)
    {
        $entry = $cycle->firstWhere('type', 'in')?->time;
        $exit  = $cycle->reverse()->firstWhere('type', 'out')?->time;

        $breakStart = null;
        $breakEnd = null;

        if ($cycle->count() >= 2 && $cycle[1]->type === 'out') {
            $breakStart = $cycle[1]->time ?? null;
        }

        if ($cycle->count() >= 3 && $cycle[2]->type === 'in') {
            $breakEnd = $cycle[2]->time ?? null;
        }

        // calcular tiempo trabajado
        $workedSeconds = 0;
        $lastIn = null;

        foreach ($cycle as $event) {
            if ($event->type === 'in') {
                $lastIn = $event;
            }

            if ($event->type === 'out' && $lastIn) {
                $workedSeconds += strtotime($event->time) - strtotime($lastIn->time);
                $lastIn = null;
            }
        }

        // estado
        $status = match ($cycle->count()) {
            4 => 'normal',
            2 => 'sin_colacion',
            default => 'incompleta',
        };

        return [
            'date' => $date,
            'entry' => $entry ? substr($entry, 0, 5) : null,
            'break_start' => $breakStart ? substr($breakStart, 0, 5) : null,
            'break_end' => $breakEnd ? substr($breakEnd, 0, 5) : null,
            'exit' => $exit ? substr($exit, 0, 5) : null,
            'worked_seconds' => $workedSeconds,
            'worked_human' => gmdate('H \h i \m', $workedSeconds),
            'status' => $status,
        ];
    }
}