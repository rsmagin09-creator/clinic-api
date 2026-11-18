<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'specialty_id' => null,
            'name' => fake()->unique()->words(3, true),
            'price' => fake()->randomElement([2500, 4000, 6000, 9000, 12000]),
            'duration_minutes' => 30,
        ];
    }
}
