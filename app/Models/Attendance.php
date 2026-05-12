<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

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
        'latitude',
        'longitude',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->duration_minutes ?? 0;
        if ($minutes === 0) {
            return '0h 0m';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}h {$remainingMinutes}m";
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present'    => 'bg-green-500',
            'late'       => 'bg-orange-400',
            'absent'     => 'bg-red-500',
            'incomplete' => 'bg-gray-400',
            'early_exit' => 'bg-blue-400',
            default      => 'bg-gray-200',
        };
    }
}
