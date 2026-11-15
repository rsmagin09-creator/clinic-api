<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /** Учётные записи для тестирования (пароль у всех: password) и 10 пациентов. */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Администратор Поликлиники',
            'email' => 'admin@clinic.ru',
            'role' => UserRole::Admin,
        ]);

        User::factory()->create([
            'name' => 'Смирнов Алексей Петрович',
            'email' => 'patient@clinic.ru',
            'role' => UserRole::Patient,
        ]);

        User::factory()->patient()->count(10)->create();
    }
}
