<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Group;
use Carbon\Carbon;

class AttendanceTestSeeder extends Seeder
{
    public function run(): void
    {
        // Buscamos los usuarios creados por el UserSeeder
        $users = User::all();
        // Buscamos el grupo creado por el GroupSeeder
        $group = Group::first();

        if ($users->isEmpty() || !$group) {
            return;
        }

        foreach ($users as $user) {
            // Generamos datos para los últimos 30 días
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::now()->subDays($i);
                
                // Saltamos domingos para que se vea más real
                if ($date->isSunday()) continue;

                Attendance::create([
                    'user_id' => $user->id,
                    'group_id' => $group->id,
                    'attendance_date' => $date->format('Y-m-d'),
                    'check_in' => $date->copy()->hour(8)->minute(rand(0, 45)),
                    'check_out' => $date->copy()->hour(17)->minute(rand(0, 30)),
                    'status' => $this->getRandomStatus(),
                    'source' => 'biometric',
                    'latitude' => -33.4489, 
                    'longitude' => -70.6693,
                ]);
            }
        }
    }

    private function getRandomStatus() {
        $statuses = ['present', 'present', 'present', 'late', 'absent'];
        return $statuses[array_rand($statuses)];
    }
}