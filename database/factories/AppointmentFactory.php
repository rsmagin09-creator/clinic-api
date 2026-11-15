<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->patient(),
            'doctor_id' => Doctor::factory(),
            'date' => fake()->dateTimeBetween('+1 day', '+14 days')->format('Y-m-d'),
            'time' => fake()->randomElement(['09:00', '09:30', '10:00', '10:30', '11:00']),
            'status' => AppointmentStatus::Scheduled,
            'reason' => fake()->randomElement([
                'Головная боль', 'Температура, кашель', 'Боли в спине', 'Давление',
                'Плановый осмотр', 'Боль в горле', 'Снижение зрения', 'Консультация',
            ]),
        ];
    }
}
