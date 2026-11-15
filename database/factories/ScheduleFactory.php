<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->randomElement(['08:00', '09:00', '14:00']);

        return [
            'doctor_id' => Doctor::factory(),
            'weekday' => fake()->numberBetween(1, 5),
            'start_time' => $start,
            'end_time' => date('H:i', strtotime($start) + 5 * 3600),
            'slot_minutes' => fake()->randomElement([20, 30]),
        ];
    }
}
