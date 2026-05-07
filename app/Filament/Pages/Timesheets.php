<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Group;
use App\Models\Shift;
use Filament\Pages\Page;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Attributes\Url;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TimesheetExport;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class Timesheets extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Timesheets';
    protected static ?string $title = 'Timesheets';
    protected static ?string $navigationGroup = 'Asistencia';
    protected static string $view = 'filament.pages.timesheets';

    #[Url]
    public $month = null;

    #[Url]
    public $year = null;

    #[Url]
    public $groupId = null;

    #[Url]
    public $search = '';

    #[Url]
    public $scheduleId = null;

    #[Url]
    public $payrollType = 'all';

    public function mount()
    {
        $this->month ??= Carbon::now()->month;
        $this->year ??= Carbon::now()->year;
    }

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return Auth::check() && $user && $user->hasRole('admin');
    }

    public function changeMonth($direction)
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->addMonths($direction);
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function exportPdf()
    {
        $data = $this->getViewData();
        $pdf = Pdf::loadView('pdf.timesheet-report', $data)->setPaper('a4', 'landscape');
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "Reporte_Asistencia_{$this->month}_{$this->year}.pdf");
    }

    public function exportExcel()
    {
        return Excel::download(
            new TimesheetExport($this->month, $this->year, $this->groupId), 
            "Planilla_Asistencia_{$this->month}_{$this->year}.xlsx"
        );
    }

    /**
     *  Maneja el clic en las celdas de la tabla
     */
    public function openAttendanceModal($userId, $date)
    {
        Notification::make()
            ->title('Registro de Asistencia')
            ->body("Abriendo detalles para el usuario ID: {$userId} en la fecha: {$date}")
            ->info()
            ->send();
            
        // Aquí puedes integrar un modal de Filament más adelante
    }

    protected function getViewData(): array
    {
        $currentMonthDate = Carbon::createFromDate($this->year, $this->month, 1);
        
        $daysInMonth = CarbonPeriod::create(
            $currentMonthDate->copy()->startOfMonth(),
            $currentMonthDate->copy()->endOfMonth()
        );

        $usersQuery = User::query();

        if (!empty($this->search)) {
            $usersQuery->where('name', 'like', "%{$this->search}%");
        }

        if ($this->groupId) {
            $usersQuery->whereHas('groups', function ($q) {
                $q->where('groups.id', $this->groupId);
            });
        }

        if ($this->scheduleId) {
            $usersQuery->whereHas('shifts', function ($q) { 
                $q->where('id', $this->scheduleId);
            });
        }

        $users = $usersQuery->with(['attendances' => function($query) {
            $query->whereMonth('attendance_date', $this->month)
                  ->whereYear('attendance_date', $this->year);
            
            if ($this->payrollType === 'overtime') {
                $query->where('is_overtime', true); 
            } elseif ($this->payrollType === 'regular') {
                $query->where('is_overtime', false);
            }
        }])->get();

        return [
            'users' => $users,
            'daysInMonth' => $daysInMonth,
            'currentMonthName' => $currentMonthDate->translatedFormat('F Y'),
            'groups' => Group::all(),
            'schedules' => Shift::all(),
        ];
    }
}