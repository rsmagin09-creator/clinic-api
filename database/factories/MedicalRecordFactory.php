<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalRecord>
 */
class MedicalRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'diagnosis' => fake()->randomElement([
                'ОРВИ', 'Артериальная гипертензия', 'Остеохондроз', 'Фарингит',
                'Миопия слабой степени', 'Гастрит', 'Мигрень', 'Здоров',
            ]),
            'examination' => fake()->randomElement([
                'Состояние удовлетворительное', 'Температура 37,5', 'АД 145/90', 'Без особенностей',
            ]),
            'prescription' => fake()->randomElement([
                'Обильное питьё, постельный режим', 'Контроль АД, повторный приём через 2 недели',
                'ЛФК, массаж', 'Полоскание горла', 'Очки для дали', 'Диета, повторный приём через месяц',
            ]),
        ];
    }
}
