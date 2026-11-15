<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Тестовые учётные записи (пароль: password):
     * admin@clinic.ru — администратор, doctor@clinic.ru — врач, patient@clinic.ru — пациент.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SpecialtySeeder::class,
            DoctorSeeder::class,
            ScheduleSeeder::class,
            AppointmentSeeder::class,
            MedicalRecordSeeder::class,
        ]);
    }
}
