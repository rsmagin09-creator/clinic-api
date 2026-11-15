<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);

        return [
            'name' => fake()->lastName($gender).' '.fake()->firstName($gender).' '.fake()->middleName($gender),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+7701'.fake()->numerify('#######'),
            'birth_date' => fake()->dateTimeBetween('-75 years', '-18 years')->format('Y-m-d'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Patient,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => UserRole::Admin]);
    }

    public function doctor(): static
    {
        return $this->state(fn () => ['role' => UserRole::Doctor]);
    }

    public function patient(): static
    {
        return $this->state(fn () => ['role' => UserRole::Patient]);
    }
}
