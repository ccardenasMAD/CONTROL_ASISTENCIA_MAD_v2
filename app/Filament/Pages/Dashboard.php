<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Filament\Widgets\MyMonthlyAttendance;
use App\Filament\Widgets\WeeklyProductivityChart;
use App\Filament\Widgets\MonthlyProductivityChart;
use App\Filament\Widgets\MonthlyProductivityOverview;


class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard';

    protected static string $view = 'filament.pages.dashboard';

    public function getHeaderWidgets(): array
    {
        return [
             MyMonthlyAttendance::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
        \App\Filament\Widgets\WeeklyProductivityChart::class,
        \App\Filament\Widgets\MonthlyProductivityChart::class,
        //\App\Filament\Widgets\MonthlyProductivityOverview::class,

      
         //\App\Filament\Widgets\TodayAttendance::class,
        // \App\Filament\Widgets\TodayAbsent::class,
        // \App\Filament\Widgets\TodayAttendanceStats::class,
        //\App\Filament\Widgets\TodayAttendanceByGroup::class,
         //\App\Filament\Widgets\WeeklyProductivityChart::class,
        ];
    }
    public function getViewData(): array
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        return [
            'action' => Attendance::getTodayActionButton($userId),
            'status' => $attendance?->getStatus() ?? 'none',
            'workedMinutes' => $attendance?->getWorkedMinutes() ?? 0,
            'attendance' => $attendance,
            'timeline' => Attendance::getTodayTimeline($userId),
            'workStart' => $attendance?->getWorkStartForTimer(),
        ];
    }
}




