<?php

use Illuminate\Support\Facades\Route;
use App\Models\Attendance;
use App\Services\ReportsCenterPdfService;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reports/pdf', function (ReportsCenterPdfService $pdfService) {

    $start   = request('start');
    $end     = request('end');
    $userId  = request('user_id');
    $groupId = request('group_id');
    $orderBy = request('order_by', 'group');

    $query = Attendance::query()
        ->select('attendances.*')
        ->with(['user', 'group'])
        ->whereBetween('attendance_date', [$start, $end]);

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

        'date'  => $query->orderBy('attendance_date'),
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
