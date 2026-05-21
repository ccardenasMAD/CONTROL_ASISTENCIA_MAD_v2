<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;

class TimesheetsTestCommand extends Command
{
    protected $signature = 'timesheets:test';
    protected $description = 'Genera datos de prueba para Timesheets';

    public function handle()
    {
        $userId = 1;
        $groupId = 2;

        $data = [
            ['date' => '2026-05-19', 'check_in' => '08:00', 'check_out' => '17:00'], // present
            ['date' => '2026-05-20', 'check_in' => '09:15', 'check_out' => '17:00'], // late
            ['date' => '2026-05-21', 'check_in' => '08:00', 'check_out' => '15:00'], // early_exit
            ['date' => '2026-05-22'], // absent
            ['date' => '2026-05-23', 'status' => 'vacation'], // vacation
        ];

        foreach ($data as $item) {
            Attendance::updateOrCreate(
                [
                    'user_id' => $userId,
                    'attendance_date' => $item['date'],
                ],
                array_merge([
                    'group_id' => $groupId,
                ], $item)
            );
        }

        $this->info('Datos de prueba generados correctamente.');
    }
}
