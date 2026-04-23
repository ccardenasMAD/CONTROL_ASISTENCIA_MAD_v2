<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ShiftGroup;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * Usuarios miembros del grupo
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withTimestamps();
    }

    /**
     * Usuarios jefes del grupo
     */
    public function leaders()
    {
        return $this->belongsToMany(User::class, 'group_leaders')
            ->withTimestamps();
    }

    public function shiftGroups()
    {
        return $this->hasMany(ShiftGroup::class);
    }

    public function activeShiftGroup()
    {
        return $this->hasOne(ShiftGroup::class)
            ->where('is_active', true)
            ->where('start_date', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('end_date')
                ->orWhere('end_date', '>=', now()->toDateString());
            });
    }

    public function activeShift()
    {
        return $this->activeShiftGroup?->shift;
    }
}