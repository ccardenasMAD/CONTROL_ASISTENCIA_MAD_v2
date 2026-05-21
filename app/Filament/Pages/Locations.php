<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\User;
use Filament\Pages\Page;

class Locations extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Locations';
    protected static ?string $navigationGroup = 'Asistencia';
    protected static string $view = 'filament.pages.locations';

    public ?string $date = null;
    public ?int $userId = null;

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function getUsersProperty()
    {
        return User::orderBy('name')->get();
    }

    public function getAttendancesProperty()
    {
        $query = Attendance::with('user')
            ->whereNotNull('check_in_lat')
            ->whereNotNull('check_in_lng');

        if ($this->date) {
            $query->whereDate('attendance_date', $this->date);
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        return $query->get();
    }

    protected function getViewData(): array
    {
        $attendances = $this->attendances;

        $locations = $attendances->map(function ($a) {
            return [
                'user_name'      => $a->user->name,
                'date'           => $a->attendance_date->format('d/m/Y'),
                'check_in_lat'   => $a->check_in_lat,
                'check_in_lng'   => $a->check_in_lng,
                'check_out_lat'  => $a->check_out_lat,
                'check_out_lng'  => $a->check_out_lng,
                'check_in_time'  => optional($a->check_in)->format('H:i'),
                'check_out_time' => optional($a->check_out)->format('H:i'),
            ];
        })->values();

        return [
            'attendances'  => $attendances,
            'users'        => $this->users,
            'selectedDate' => $this->date,
            'selectedUser' => $this->userId,
            'locations'    => $locations,
        ];
    }
}
