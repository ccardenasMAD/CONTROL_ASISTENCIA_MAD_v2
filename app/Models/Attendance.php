<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Attendance extends Model
{

    protected $table = 'attendances';

    // Horario oficial
    public const SHIFT_START = '07:30';
    public const SHIFT_END   = '18:30';

    protected $fillable = [
        'user_id',
        'group_id',
        'attendance_date',
        'check_in',
        'check_out',
        'break_start',
        'break_end',
        'source',
        'status',

        'check_in_lat',
        'check_in_lng',
        'check_out_lat',
        'check_out_lng',
        'photo_path',
        'ip_address',
        'type',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
        'attendance_date' => 'date',
    ];

    /*
     |  RELACIONES
      */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /* 
     |  ESTADO PROFESIONAL (late, early_exit, incomplete, etc.)
     */

    public function getStatus()
    {
        if ($this->type === 'vacation') {
            return 'vacation';
        }

        if (!$this->check_in && !$this->check_out) {
            return 'absent';
        }

        $shiftStart = Carbon::parse(self::SHIFT_START);
        $shiftEnd   = Carbon::parse(self::SHIFT_END);

        if ($this->check_in && $this->check_in->gt($shiftStart)) {
            return 'late';
        }

        if ($this->check_out && $this->check_out->lt($shiftEnd)) {
            return 'early_exit';
        }

        if ($this->check_in && !$this->check_out) {
            return 'incomplete';
        }

        return 'present';
    }

  

   public function getWorkedMinutes(): int
    {
        if (!$this->check_in) {
            return 0;
        }

        $end = $this->check_out ?? now();
        $worked = $this->check_in->diffInMinutes($end);

        if ($this->break_start) {
            $breakEnd = $this->break_end ?? now();
            $worked -= $this->break_start->diffInMinutes($breakEnd);
        }

        return max($worked, 0);
    }

    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->getWorkedMinutes();

        if ($minutes <= 0) {
            return '0h 0m';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}h {$remainingMinutes}m";
    }


    public static function getTodayActionButton($userId)
    {
        $today = now()->toDateString();

        $attendance = self::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        // No ha marcado nada → Check-in
        if (!$attendance) {
            return 'CHECK_IN';
        }

        // Está en break → Return
        if ($attendance->break_start && !$attendance->break_end) {
            return 'RETURN';
        }

        // Tiene check-in pero no check-out
        if ($attendance->check_in && !$attendance->check_out) {
            return 'BREAK';
        }

        // Ya tiene check-out → no hay acción
        return 'NO_ACTION';
    }

    public static function getTodayTimeline($userId)
{
    $today = now()->toDateString();

    $attendance = self::where('user_id', $userId)
        ->where('attendance_date', $today)
        ->first();

    if (!$attendance) {
        return [];
    }

    $timeline = [];

    if ($attendance->check_in) {
        $timeline[] = [
            'time' => $attendance->check_in->format('H:i'),
            'label' => 'Check-in',
            'color' => 'text-green-400'
        ];
    }

    if ($attendance->break_start) {
        $timeline[] = [
            'time' => $attendance->break_start->format('H:i'),
            'label' => 'Break',
            'color' => 'text-yellow-400'
        ];
    }

    if ($attendance->break_end) {
        $timeline[] = [
            'time' => $attendance->break_end->format('H:i'),
            'label' => 'Return',
            'color' => 'text-blue-400'
        ];
    }

    if ($attendance->check_out) {
        $timeline[] = [
            'time' => $attendance->check_out->format('H:i'),
            'label' => 'Check-out',
            'color' => 'text-gray-300'
        ];
    }

    return $timeline;
}

public function getWorkStartForTimer()
{
    return $this->check_in ? $this->check_in->toIso8601String() : null;
}

   

    public function getStatusColorAttribute(): string
    {
        return match ($this->getStatus()) {
            'present'    => 'bg-green-500',
            'late'       => 'bg-yellow-400',
            'absent'     => 'bg-red-500',
            'incomplete' => 'bg-gray-400',
            'early_exit' => 'bg-blue-400',
            'vacation'   => 'bg-pink-500',
            default      => 'bg-gray-200',
        };
    }

    public function getLocationStatus(): string
{
    // No ha marcado nada
    if (!$this->check_in && !$this->check_out) {
        return 'none';
    }

    // Check-in sin check-out → está dentro
    if ($this->check_in && !$this->check_out && !$this->break_start) {
        return 'in';
    }

    // Está en break
    if ($this->break_start && !$this->break_end) {
        return 'break';
    }

    // Volvió del break pero aún no hace check-out
    if ($this->break_end && !$this->check_out) {
        return 'return';
    }

    // Ya hizo check-out → fuera
    if ($this->check_out) {
        return 'out';
    }

    return 'none';
}

}
