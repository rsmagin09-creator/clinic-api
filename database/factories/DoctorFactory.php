<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->doctor(),
            'cabinet' => (string) fake()->numberBetween(101, 350),
            'experience_years' => fake()->numberBetween(1, 35),
            'description' => fake()->randomElement([
                'Врач высшей категории', 'Врач первой категории', 'Кандидат медицинских наук', null,
            ]),
        ];
    }
}
