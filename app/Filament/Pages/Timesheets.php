<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Group;
use App\Models\Turno;
use App\Models\Shift;
use Filament\Pages\Page;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Attributes\Url;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TimesheetExport;
use Illuminate\Support\Facades\Auth;

class Timesheets extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Timesheets';
    protected static ?string $title = 'Timesheets';
    protected static ?string $navigationGroup = 'Asistencia';
    protected static string $view = 'filament.pages.timesheets';

    /**
     * Propiedades sincronizadas con la URL
     * Esto permite que al filtrar, la URL cambie y puedas compartir el enlace filtrado.
     */
    #[Url]
    public $month = null;

    #[Url]
    public $year = null;

    #[Url]
    public $groupId = null;

    #[Url]
    public $search = ''; // Nueva propiedad para el buscador
   
    #[Url]
    public $scheduleId = null; // Filtro de Turnos

    #[Url]
    public $payrollType = 'all'; // Filtro de Horas (all, regular, overtime)



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

    /**
     * Lógica para cambiar de mes
     */
    public function changeMonth($direction)
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->addMonths($direction);
        $this->month = $date->month;
        $this->year = $date->year;
    }

    /**
     * Exportación a PDF
     */
    public function exportPdf()
    {
        $data = $this->getViewData();
        $pdf = Pdf::loadView('pdf.timesheet-report', $data)->setPaper('a4', 'landscape');
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "Reporte_Asistencia_{$this->month}_{$this->year}.pdf");
    }

    /**
     * Exportación a Excel
     */
    public function exportExcel()
    {
        return Excel::download(
            new TimesheetExport($this->month, $this->year, $this->groupId), 
            "Planilla_Asistencia_{$this->month}_{$this->year}.xlsx"
        );
    }

    /**
     * Preparación de datos para la vista (Blade)
     */
    protected function getViewData(): array
    {
        $currentMonthDate = Carbon::createFromDate($this->year, $this->month, 1);
        
        $daysInMonth = CarbonPeriod::create(
            $currentMonthDate->copy()->startOfMonth(),
            $currentMonthDate->copy()->endOfMonth()
        );



        // Iniciamos la consulta de usuarios
        $usersQuery = User::query();

        // Filtro por Búsqueda (Nombre)
        if (!empty($this->search)) {
            $usersQuery->where('name', 'like', "%{$this->search}%");
        }

        // Filtro por Grupo
        if ($this->groupId) {
            $usersQuery->whereHas('groups', function ($q) {
                $q->where('groups.id', $this->groupId);
            });
        }

        // 4.  Filtro por Schedules (Turnos)
    // Asumiendo que tu modelo User tiene una relación 'shifts' o 'schedules'
        if ($this->scheduleId) {
            $usersQuery->whereHas('shifts', function ($q) { 
                $q->where('id', $this->scheduleId);
            });
        }

       // 5. Carga de asistencias con filtro de Payroll Hours
    $users = $usersQuery->with(['attendances' => function($query) {
        $query->whereMonth('attendance_date', $this->month)
              ->whereYear('attendance_date', $this->year);
        
        //  Lógica de Payroll (Ejemplo: filtrar solo horas extra)
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
          'schedules' => \App\Models\Shift::all(),
        ];
    }
}