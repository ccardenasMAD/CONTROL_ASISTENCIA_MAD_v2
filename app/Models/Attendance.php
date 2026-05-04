<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Attendance extends Model
{

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'group_id',
        'date',
        'time',
        'type',
        'source',
        'status',
        'edited_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForGroup(Builder $query, int $groupId): Builder
    {
        return $query->where('group_id', $groupId);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('date', today());
    }

    public function scopeLast(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeOnlyIn(Builder $query): Builder
    {
        return $query->where('type', 'in');
    }

    public function scopeOnlyOut(Builder $query): Builder
    {
        return $query->where('type', 'out');
    }

    public function isCheckIn(): bool
    {
        return $this->type === 'in';
    }

    public function isCheckOut(): bool
    {
        return $this->type === 'out';
    }

    public function getEventLabelAttribute(): string
    {
        // Necesitamos los eventos del mismo día, en orden
        $events = self::where('user_id', $this->user_id)
            ->whereDate('date', $this->date)
            ->orderBy('time')
            ->orderBy('id')
            ->get();

        // Obtener solo el ciclo actual
        $cycle = collect();
        foreach ($events as $event) {
            $cycle->push($event);

            if (
                $cycle->count() === 4 &&
                $cycle[0]->type === 'in' &&
                $cycle[1]->type === 'out' &&
                $cycle[2]->type === 'in' &&
                $cycle[3]->type === 'out'
            ) {
                $cycle = collect();
            }

            if ($event->id === $this->id) {
                break;
            }
        }

        $position = $cycle->count();

        return match (true) {
            $this->type === 'in' && $position === 1 => 'Inicio jornada',
            $this->type === 'out' && $position === 2 => 'Inicio colación',
            $this->type === 'in' && $position === 3 => 'Fin colación',
            $this->type === 'out' && $position === 4 => 'Fin jornada',
            default => ucfirst($this->type),
        };
    }

    public function getJourneyLabelAttribute(): string
    {
        $events = self::where('user_id', $this->user_id)
            ->whereDate('date', $this->date)
            ->orderBy('time')
            ->orderBy('id')
            ->get();

        $journey = 1;
        $sequence = [];

        foreach ($events as $event) {
            $sequence[] = $event->type;

            if (
                count($sequence) === 4 &&
                $sequence === ['in', 'out', 'in', 'out']
            ) {
                if ($event->id === $this->id) break;
                $journey++;
                $sequence = [];
            }

            if ($event->id === $this->id) break;
        }

        return "Jornada {$journey}";
    }

}
