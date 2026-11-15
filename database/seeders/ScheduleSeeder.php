<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /** График: каждый врач работает 3 дня в неделю (пн–пт). */
    public function run(): void
    {
        foreach (Doctor::all() as $doctor) {
            foreach (collect([1, 2, 3, 4, 5])->random(3) as $weekday) {
                $start = fake()->randomElement(['08:00', '09:00', '14:00']);
                Schedule::create([
                    'doctor_id' => $doctor->id,
                    'weekday' => $weekday,
                    'start_time' => $start,
                    'end_time' => date('H:i', strtotime($start) + 5 * 3600),
                    'slot_minutes' => 30,
                ]);
            }
        }
    }
}
