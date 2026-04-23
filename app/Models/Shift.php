<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ShiftGroup;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'grace_minutes',
        'min_work_hours',
        'calculation_type',
        'is_night_shift',
        'is_active',
    ];

    public function shiftGroups()
    {
        return $this->hasMany(ShiftGroup::class);
    }

}
