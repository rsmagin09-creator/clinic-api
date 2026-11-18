<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /** 8 врачей, у каждого 1–2 специальности (связь многие ко многим). */
    public function run(): void
    {
        $specialties = Specialty::all();

        // Врач с известным логином для тестирования
        $main = Doctor::factory()
            ->for(User::factory()->doctor()->state([
                'name' => 'Иванов Сергей Петрович',
                'email' => 'doctor@clinic.ru',
            ]))
            ->create(['cabinet' => '101', 'experience_years' => 20, 'price' => 8000, 'description' => 'Врач высшей категории']);
        $main->specialties()->attach($specialties->whereIn('name', ['Терапевт', 'Кардиолог'])->pluck('id'));

        Doctor::factory()->count(7)->create()->each(function (Doctor $doctor) use ($specialties) {
            $doctor->specialties()->attach($specialties->random(rand(1, 2))->pluck('id'));
        });
    }
}
