<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * CHECK-IN
     */
    public function checkIn(Request $request)
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        return DB::transaction(function () use ($userId, $today, $request) {

            $attendance = Attendance::where('user_id', $userId)
                ->where('attendance_date', $today)
                ->first();

            if ($attendance && $attendance->check_in) {
                return back()->with('error', 'Ya registraste tu entrada.');
            }

            Attendance::updateOrCreate(
                [
                    'user_id' => $userId,
                    'attendance_date' => $today,
                ],
                [
                    'check_in' => now(),
                    'check_in_lat' => $request->lat,
                    'check_in_lng' => $request->lng,
                    'source' => 'web',
                    'ip_address' => $request->ip(),
                ]
            );

            event('attendance-updated');

            return back()->with('success', 'Check-in registrado correctamente.');
        });
    }

    /**
     * BREAK START
     */
    public function breakStart()
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        return DB::transaction(function () use ($userId, $today) {

            $attendance = Attendance::where('user_id', $userId)
                ->where('attendance_date', $today)
                ->first();

            if (!$attendance || !$attendance->check_in) {
                return back()->with('error', 'Debes hacer check-in antes de iniciar un break.');
            }

            if ($attendance->check_out) {
                return back()->with('error', 'No puedes iniciar un break después del check-out.');
            }

            if ($attendance->break_start && !$attendance->break_end) {
                return back()->with('error', 'Ya tienes un break activo.');
            }

            $attendance->update([
                'break_start' => now(),
                'break_end' => null,
            ]);

            event('attendance-updated');

            return back()->with('success', 'Break iniciado correctamente.');
        });
    }

    /**
     * BREAK END
     */
    public function breakEnd()
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        return DB::transaction(function () use ($userId, $today) {

            $attendance = Attendance::where('user_id', $userId)
                ->where('attendance_date', $today)
                ->first();

            if (!$attendance || !$attendance->check_in) {
                return back()->with('error', 'Debes hacer check-in primero.');
            }

            if (!$attendance->break_start) {
                return back()->with('error', 'No tienes un break activo.');
            }

            if ($attendance->break_end) {
                return back()->with('error', 'Este break ya fue finalizado.');
            }

            $attendance->update([
                'break_end' => now(),
            ]);

            event('attendance-updated');

            return back()->with('success', 'Break finalizado correctamente.');
        });
    }

    /**
     * CHECK-OUT
     */
    public function checkOut(Request $request)
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        return DB::transaction(function () use ($userId, $today, $request) {

            $attendance = Attendance::where('user_id', $userId)
                ->where('attendance_date', $today)
                ->first();

            if (!$attendance || !$attendance->check_in) {
                return back()->with('error', 'Debes hacer check-in antes de registrar tu salida.');
            }

            if ($attendance->check_out) {
                return back()->with('error', 'Ya registraste tu salida.');
            }

            if ($attendance->break_start && !$attendance->break_end) {
                return back()->with('error', 'Debes finalizar tu break antes de hacer check-out.');
            }

            $attendance->update([
                'check_out' => now(),
                'check_out_lat' => $request->lat,
                'check_out_lng' => $request->lng,
                'ip_address' => $request->ip(),
            ]);

            event('attendance-updated');

            return back()->with('success', 'Check-out registrado correctamente.');
        });
    }
}
