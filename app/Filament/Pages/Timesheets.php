<?php

namespace App\Filament\Pages;

use App\Services\TimesheetsServices\TimesheetEngine;
use App\Services\TimesheetsServices\TimesheetPresenter;
use Filament\Pages\Page;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class Timesheets extends Page
{
    public static string $view = 'filament.pages.timesheets';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Timesheets';
    protected static ?string $title = 'Timesheets';
    protected static ?string $navigationGroup = 'Asistencia';

    public int $month;
    public int $year;
    public ?int $groupId = null;
    public string $search = '';

    public array $days = [];
    public array $usersData = [];

    public bool $showModal = false;
    public ?array $modalData = null;

    public function mount()
    {
        $this->month = now()->month;
        $this->year = now()->year;

        $this->refreshData();
    }

    public function refreshData()
    {
        $this->generateDays();
        $this->loadUsers();
    }

    public function previousMonth(): void
    {
        $this->changeMonth(-1);
    }

    public function nextMonth(): void
    {
        $this->changeMonth(1);
    }

    public function generateDays()
    {
        $this->days = [];

        $start = Carbon::create($this->year, $this->month, 1);
        $end = $start->copy()->endOfMonth();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $this->days[$date->day] = [
                'date' => $date->toDateString(),
                'weekday' => $date->translatedFormat('D'),
                'weekday_full' => $date->translatedFormat('l'),
            ];
        }
    }

    public function loadUsers()
    {
        $query = User::query();

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        if ($this->groupId) {
            $query->whereHas('groups', fn($q) => $q->where('groups.id', $this->groupId));
        }

        $users = $query->with([
            'attendances' => function ($q) {
                $q->whereMonth('attendance_date', $this->month)
                    ->whereYear('attendance_date', $this->year);
            }
        ])->get();

        $this->usersData = [];

        foreach ($users as $user) {
            $calendar = [];

            $engine = new TimesheetEngine();
            $presenter = new TimesheetPresenter();

            foreach ($this->days as $day => $info) {
                $date = Carbon::parse($info['date']);

                $attendance = $user->attendances
                    ->first(fn($a) => $a->attendance_date->isSameDay($date));

                $result = $engine->resolve($date, $attendance);

                $calendar[$date->toDateString()] = [
                    'status' => $result['state']->value,
                    'type' => null,
                    'color' => $presenter->color($result['state']),
                    'minutes' => $result['minutes'],
                ];
            }

            $this->usersData[] = [
                'id' => $user->id,
                'name' => $user->name,
                'calendar' => $calendar,
                'attendances' => $user->attendances,
                'totalMinutes' => array_sum(array_column($calendar, 'minutes')),
            ];
        }
    }

    public function updatedSearch()
    {
        $this->loadUsers();
    }

    public function updatedGroupId()
    {
        $this->loadUsers();
    }

   
    public function openAttendanceModal(int $userId, string $date): void
    {
        $carbonDate = Carbon::parse($date);
    
        $userData = collect($this->usersData)->firstWhere('id', $userId);
    
        if (!$userData) {
            return;
        }
    
     
        $dayData = $userData['calendar'][$carbonDate->toDateString()] ?? null;
 
        $user = User::with(['attendances'])->find($userId);
    
        $attendance = $user->attendances
            ->first(fn($a) => $a->attendance_date->isSameDay($carbonDate));
    
        $this->modalData = [
            'user' => $user?->name,
            'date' => $carbonDate->toDateString(),
            'status' => $attendance ? $attendance->getStatus() : 'Sin registro',
            'attendance' => $attendance,
            'worked' => $attendance?->getWorkedMinutes() ?? 0,
        ];
    
        $this->showModal = true;
    }
    
    

    public function changeMonth(int $direction): void
    {
        $date = Carbon::create($this->year, $this->month, 1)
            ->addMonths($direction);

        $this->month = $date->month;
        $this->year = $date->year;

        $this->refreshData();
    }
}
