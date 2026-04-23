<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShiftGroup extends Model
{
    use HasFactory;

    protected $table = 'shift_group';

    protected $fillable = [
        'shift_id',
        'group_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}