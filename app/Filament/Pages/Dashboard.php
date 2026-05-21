<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

// IMPORTACIONES REALES VERIFICADAS EN TU CARPETA WIDGETS
use App\Filament\Widgets\MyMonthlyAttendance;
use App\Filament\Widgets\TodayWorkday;
use App\Filament\Widgets\WeeklyProductivityChart;
use App\Filament\Widgets\MonthlyProductivityChart;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard';
    
    protected static string $view = 'filament.pages.dashboard';

    public function getVisibleHeaderWidgets(): array
    {
        return [
            MyMonthlyAttendance::class,
            TodayWorkday::class,
            WeeklyProductivityChart::class,
            MonthlyProductivityChart::class,
        ];
    }

    public function getHeaderWidgets(): array 
    { 
        return []; 
    }

    public function getFooterWidgets(): array 
    { 
        return []; 
    }

    public function getViewData(): array
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        return [
            'attendance' => $attendance,
        ];
    }
}