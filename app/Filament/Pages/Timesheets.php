<?php

namespace App\Filament\Pages;

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

    public $month;
    public $year;
    public $groupId = null;
    public $search = '';

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

    /**
     * Navega al mes anterior
     */
    public function previousMonth(): void
    {
        $this->changeMonth(-1);
    }

    /**
     * Navega al mes siguiente
     */
    public function nextMonth(): void
    {
        $this->changeMonth(1);
    }

    /**
     * Genera días con día de la semana
     */
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

    /**
     * Carga usuarios y su calendario
     */
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

            foreach ($this->days as $day => $info) {
                $date = $info['date'];

                $attendance = $user->attendances
                    ->firstWhere('attendance_date', $date);

                if (!$attendance) {
                    $calendar[$date] = [
                        'status' => 'none',
                        'type' => null,
                        'color' => 'bg-gray-100 dark:bg-gray-800',
                        'minutes' => 0,
                    ];

                    continue;
                }

                $color = match (true) {
                    $attendance->type === 'vacation' => 'bg-pink-500',
                    $attendance->status === 'present' => 'bg-green-500',
                    $attendance->status === 'late' => 'bg-yellow-400',
                    $attendance->status === 'early_exit' => 'bg-blue-400',
                    $attendance->status === 'incomplete' => 'bg-gray-400',
                    $attendance->status === 'absent' => 'bg-red-500',
                    default => 'bg-gray-200',
                };

                $minutes = $attendance?->getWorkedMinutes() ?? 0;

                $calendar[$date] = [
                    'status' => $attendance->status,
                    'type' => $attendance->type,
                    'color' => $color,
                    'minutes' => $minutes,
                ];
            }

            $this->usersData[] = [
                'id' => $user->id,
                'name' => $user->name,
                'calendar' => $calendar,
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

    public function openAttendanceModal($userId, $date)
    {
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('attendance_date', $date)
            ->first();

        $user = User::find($userId);

        $this->modalData = [
            'user' => $user?->name,
            'date' => $date,
            'status' => $attendance?->status ?? 'Sin registro',
            'check_in' => $attendance?->check_in?->format('H:i') ?? '—',
            'check_out' => $attendance?->check_out?->format('H:i') ?? '—',
            'attendance' => $attendance,
            'worked' => ($attendance?->check_in && $attendance?->check_out)
                ? $attendance->check_in->diffInMinutes($attendance->check_out)
                : 0,
        ];

        $this->showModal = true;
    }

    public function changeMonth($direction)
    {
        $date = Carbon::create($this->year, $this->month, 1)
            ->addMonths($direction);

        $this->month = $date->month;
        $this->year = $date->year;

        $this->refreshData();
    }
}