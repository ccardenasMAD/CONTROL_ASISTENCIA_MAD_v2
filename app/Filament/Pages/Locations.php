<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\User;
use Filament\Pages\Page;
use Carbon\Carbon;

class Locations extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Locations';
    protected static ?string $navigationGroup = 'Asistencia';
    protected static string $view = 'filament.pages.locations';

    protected static ?string $slug = 'locations';
    protected static bool $isDiscovered = false;

    public ?string $date = null;
    public ?int $userId = null;

    public function mount(): void
    {
        $this->date = null;
    }

    public function getUsersProperty()
    {
        return User::orderBy('name')->get();
    }

    private function selectedCarbonDate(): ?Carbon
{
    if (! $this->date) {
        return null;
    }

    try {
        return str_contains($this->date, '/')
            ? Carbon::createFromFormat('d/m/Y', $this->date)
            : Carbon::parse($this->date);
    } catch (\Throwable $e) {
        return null;
    }
}

public function getAttendancesProperty()
{
    
    if (! $this->userId || ! $this->date) {
        return collect();
    }

    $date = $this->selectedCarbonDate();

    if (! $date) {
        return collect();
    }

    return Attendance::with('user')
        ->where('user_id', $this->userId)
        ->whereDate('attendance_date', $date->toDateString())
        ->where(function ($query) {
            $query->where(function ($q) {
                $q->whereNotNull('check_in_lat')
                  ->whereNotNull('check_in_lng');
            })->orWhere(function ($q) {
                $q->whereNotNull('check_out_lat')
                  ->whereNotNull('check_out_lng');
            });
        })
        ->orderBy('check_in')
        ->get();
}
public function getAllAttendancesProperty()
{
   
    if (! $this->userId) {
        return collect();
    }


    $date = $this->selectedCarbonDate() ?: now();

    return Attendance::with('user')
        ->where('user_id', $this->userId)
        ->whereYear('attendance_date', $date->year)
        ->whereMonth('attendance_date', $date->month)
        ->orderByDesc('attendance_date')
        ->orderByDesc('check_in')
        ->get();
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
            'allAttendances'  => $this->allAttendances, 
            'users'        => $this->users,
            'selectedDate' => $this->date,
            'selectedUser' => $this->userId,
            'locations'    => $locations,
        ];
    }
}
