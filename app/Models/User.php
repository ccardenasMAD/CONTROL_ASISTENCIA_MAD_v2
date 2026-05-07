<?php

namespace App\Models;
use Spatie\Permission\Traits\HasRoles;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable 
{
    /** @use HasFactory<UserFactory> */
    use HasRoles, HasFactory, Notifiable;

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
        // El usuario 'común' no podrá ni siquiera ver la pantalla de login.
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

    /**
     * Turnos (Schedules) asignados al usuario
     */
    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'shift_user')
            ->withTimestamps();
    }
}
