<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasRoles, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'face_descriptor',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Solo permitimos la entrada a usuarios que tengan rol de admin o rrhh.
        return $this->hasRole(['admin', 'rrhh']);
    }

    /**
     * Grupos a los que pertenece el usuario
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')
            ->withTimestamps();
    }

    /**
     * Grupos donde el usuario es jefe
     */
    public function leadingGroups()
    {
        return $this->belongsToMany(Group::class, 'group_leaders')
            ->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function getDailyStatus($date = null)
    {
        $date = $date ?? now()->toDateString();

        $attendance = $this->attendances()
            ->whereDate('attendance_date', $date)
            ->first();

        // 1. Vacaciones
        if ($attendance?->type === 'vacation') {
            return 'VACATION';
        }

        // 2. Break activo
        if ($attendance?->break_start && !$attendance?->break_end) {
            return 'BREAK';
        }

        // 3. Check-in sin check-out
        if ($attendance?->check_in && !$attendance?->check_out) {
            return 'IN';
        }

        // 4. Día trabajado completo
        if ($attendance?->check_in && $attendance?->check_out) {
            return 'OUT';
        }

        // 5. Ausente si ya pasó el turno (lógica simple)
        if (now()->greaterThan(now()->setTime(18, 0))) {
            return 'ABSENT';
        }

        // 6. Antes del turno → OUT
        return 'OUT';
    }

    public function getLastAction($date = null)
    {
        $date = $date ?? now()->toDateString();

        $attendance = $this->attendances()
            ->whereDate('attendance_date', $date)
            ->first();

        if (!$attendance) {
            return 'Sin registros';
        }

        if ($attendance->break_start && !$attendance->break_end) {
            return 'Break iniciado a las ' . $attendance->break_start->format('H:i');
        }

        if ($attendance->check_out) {
            return 'Check-out a las ' . $attendance->check_out->format('H:i');
        }

        if ($attendance->check_in) {
            return 'Check-in a las ' . $attendance->check_in->format('H:i');
        }

        return 'Sin registros';
    }

    public function getActionButton($date = null)
    {
        $status = $this->getDailyStatus($date);

        return match ($status) {
            'OUT' => 'CHECK_IN',
            'IN' => 'BREAK_OR_CHECK_OUT',
            'BREAK' => 'RETURN',
            'VACATION' => 'NO_ACTION',
            'ABSENT' => 'CHECK_IN',
            default => 'NO_ACTION',
        };
    }

    /**
     * Turnos (Schedules) asignados al usuario
     */
    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'shift_user')
            ->withTimestamps();
    }
}