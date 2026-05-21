<?php

use Illuminate\Support\Facades\Route;
use App\Models\Attendance;
use App\Services\ReportsCenterPdfService;
use App\Http\Controllers\AttendanceController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])
        ->name('attendance.checkin');

    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])
        ->name('attendance.break_start');

    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])
        ->name('attendance.break_end');

    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])
        ->name('attendance.checkout');
});

/*
|--------------------------------------------------------------------------
| Reportes PDF
|--------------------------------------------------------------------------
*/
Route::get('/reports/pdf', function (ReportsCenterPdfService $pdfService) {
    $start   = request('start');
    $end     = request('end');
    $userId  = request('user_id');
    $groupId = request('group_id');
    $orderBy = request('order_by', 'group');

    $query = Attendance::query()
        ->select('attendances.*')
        ->with(['user', 'group'])
        ->whereBetween('date', [$start, $end]);

    if ($userId) {
        $query->where('user_id', $userId);
    }

    if ($groupId) {
        $query->where('group_id', $groupId);
    }

    match ($orderBy) {
        'group' => $query
            ->join('groups', 'groups.id', '=', 'attendances.group_id')
            ->orderBy('groups.name'),

        'user'  => $query
            ->join('users', 'users.id', '=', 'attendances.user_id')
            ->orderBy('users.name'),

        'date'  => $query->orderBy('date'),
    };

    $records = $query->get();

    return $pdfService
        ->generate($records, [
            'start' => $start,
            'end'   => $end,
            'user'  => optional($records->first()?->user)->name,
            'group' => optional($records->first()?->group)->name,
        ])
        ->download('reporte-asistencia.pdf');
})->middleware('auth')->name('reports.pdf');