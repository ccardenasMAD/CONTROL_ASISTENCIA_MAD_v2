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
        'source',
        'status',
        'latitude',
        'longitude',
        'photo_path',
        'ip_address',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
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
/**
     * Formatea el tiempo trabajado como : "8h 30m"
     */
    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->duration_minutes;
        if ($minutes === 0) return '0h 0m';

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}h {$remainingMinutes}m";
    }

    /**
     * Determina el color del cuadrito según el estado.
     
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present'    => 'bg-green-500', // Verde 
            'late'       => 'bg-orange-400',// Naranja 
            'absent'     => 'bg-red-500',   // Rojo 
            'incomplete' => 'bg-gray-400',  // Gris
            default      => 'bg-gray-200',
        };
    }
}
