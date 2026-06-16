<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Livewire\Attributes\On;
use Filament\Notifications\Notification;
use App\Services\AttendanceCalculatorService;

class TodayWorkday extends Widget
{     
    protected static bool $isDiscovered = false;
    protected static string $view = 'filament.widgets.today-workday';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $pollingInterval = 5;

    #[On('attendance-updated')]
    public function refreshWidget()
    {
        $this->reset();
    }

    public function handleCheckIn($lat = null, $lng = null): void
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $exists = Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Ya registraste una entrada para el día de hoy.')
                ->warning()
                ->send();
                $this->dispatch('map-focus', lat: $lat, lng: $lng, type: 'checkin');
                
            return;
        }

        Attendance::create([
            'user_id'         => $userId,
            'attendance_date' => $today,
            'check_in'        => now(),
            'check_in_lat'    => $lat,
            'check_in_lng'    => $lng,
        ]);

        Notification::make()
            ->title('Entrada registrada correctamente.')
            ->success()
            ->send();
            $this->dispatch('map-focus', lat: $lat, lng: $lng, type: 'checkin');

            $this->dispatch('attendance-updated');
    }

    public function startBreak(): void
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        if (!$attendance || $attendance->break_start) {
            return;
        }

        $attendance->update([
            'break_start' => now(),
        ]);

        Notification::make()
            ->title('Break iniciado.')
            ->info()
            ->send();

        $this->dispatch('attendance-updated');
    }

    public function endBreak(): void
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        if (!$attendance || !$attendance->break_start || $attendance->break_end) {
            return;
        }

        $attendance->update([
            'break_end' => now(),
        ]);

        Notification::make()
            ->title('Retorno de break registrado.')
            ->success()
            ->send();

        $this->dispatch('attendance-updated');
    }

    public function handleCheckOut($lat = null, $lng = null): void
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        if (!$attendance || $attendance->check_out) {
            return;
        }

        $attendance->update([
            'check_out' => now(),
        ]);

        app(AttendanceCalculatorService::class)->calculate($attendance->fresh());

        Notification::make()
            ->title('Salida registrada correctamente. ¡Buen descanso!')
            ->success()
            ->send();

        $this->dispatch('attendance-updated');
    }

    public function getViewData(): array
{
    $userId = Auth::id();
    $today = now()->toDateString();

    $attendance = Attendance::where('user_id', $userId)
        ->where('attendance_date', $today)
        ->first();

    return [
        'attendance'     => $attendance,
        'status'         => $attendance?->getStatus() ?? 'none',
        'action'         => Attendance::getTodayActionButton($userId),
        'workedMinutes'  => $attendance?->getWorkedMinutes() ?? 0,
        'timeline'       => Attendance::getTodayTimeline($userId),
        'workStart'      => $attendance?->getWorkStartForTimer(),
        'isCheckedOut'   => (bool) $attendance?->check_out,
        'checkOutTime'   => $attendance?->check_out ? $attendance->check_out->timestamp : null,
    ];
}

}
