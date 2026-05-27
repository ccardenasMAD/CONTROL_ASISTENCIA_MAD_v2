<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TimesheetExport implements FromCollection, WithHeadings, WithMapping
{
    protected int $month;
    protected int $year;
    protected ?int $groupId;
    protected \Carbon\CarbonPeriod $days;

    public function __construct(int $month, int $year, ?int $groupId)
    {
        $this->month = $month;
        $this->year = $year;
        $this->groupId = $groupId;
        
        $date = Carbon::createFromDate($this->year, $this->month, 1);
        $this->days = CarbonPeriod::create($date->copy()->startOfMonth(), $date->copy()->endOfMonth());
    }

    public function collection()
    {
        $query = User::query();
        if ($this->groupId) {
            $query->whereHas('groups', fn($q) => $q->where('groups.id', $this->groupId));
        }

        return $query->with(['attendances' => function($q) {
            $q->whereMonth('attendance_date', $this->month)
              ->whereYear('attendance_date', $this->year);
        }])->get();
    }

    public function headings(): array
    {
        $header = ['Empleado'];
        foreach ($this->days as $day) {
            $header[] = $day->format('d/m');
        }
        return $header;
    }

    public function map($user): array
    {
        $row = [$user->name];
        foreach ($this->days as $day) {
            $attendance = $user->attendances->firstWhere('attendance_date', $day->format('Y-m-d'));
            $row[] = $attendance ? strtoupper(substr($attendance->status, 0, 1)) : '-';
        }
        return $row;
    }
}