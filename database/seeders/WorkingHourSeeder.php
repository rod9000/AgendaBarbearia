<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Database\Seeder;

class WorkingHourSeeder extends Seeder
{
    public function run()
    {
        $barbers = User::where('role', 'attendant')->get();

        if ($barbers->isEmpty()) {
            $barbers = User::where('role', 'admin')->get();
        }

        $days = [
            1 => ['09:00', '19:00'],
            2 => ['09:00', '19:00'],
            3 => ['09:00', '19:00'],
            4 => ['09:00', '19:00'],
            5 => ['09:00', '19:00'],
            6 => ['09:00', '17:00'],
            0 => null,
        ];

        foreach ($barbers as $barber) {
            foreach ($days as $day => $hours) {
                WorkingHour::updateOrCreate(
                    ['user_id' => $barber->id, 'day_of_week' => $day],
                    [
                        'start_time' => $hours ? $hours[0] : '09:00',
                        'end_time' => $hours ? $hours[1] : '19:00',
                        'active' => $hours !== null,
                    ]
                );
            }
        }

        $this->command->info('Horarios criados para ' . $barbers->count() . ' barbeiro(s).');
    }
}
