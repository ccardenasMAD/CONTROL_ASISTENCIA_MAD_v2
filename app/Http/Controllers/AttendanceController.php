<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'attendance_date' => now()->toDateString(),
            ],
            [
                'check_in' => now(),
                'source' => 'web',
            ]
        );

        return back()->with('success', 'Check-in registrado');
    }

    public function breakStart()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('attendance_date', now()->toDateString())
            ->first();

        $attendance->update([
            'break_start' => now(),
        ]);

        return back()->with('success', 'Break iniciado');
    }

    public function breakEnd()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('attendance_date', now()->toDateString())
            ->first();

        $attendance->update([
            'break_end' => now(),
        ]);

        return back()->with('success', 'Break finalizado');
    }

    public function checkOut()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('attendance_date', now()->toDateString())
            ->first();

        $attendance->update([
            'check_out' => now(),
        ]);

        return back()->with('success', 'Check-out registrado');
    }
}
