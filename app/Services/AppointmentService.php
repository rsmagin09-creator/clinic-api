<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Pagination\LengthAwarePaginator;

class AppointmentService
{
    private const RELATIONS = ['user', 'doctor.user', 'doctor.specialties'];

    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return Appointment::with(self::RELATIONS)
            ->orderByDesc('date')->orderBy('time')
            ->paginate($perPage);
    }

    public function show(Appointment $appointment): Appointment
    {
        return $appointment->load([...self::RELATIONS, 'medicalRecord']);
    }

    /** Базовое создание записи; бизнес-правила (занятость, график) добавляются в ПР9. */
    public function create(array $data): Appointment
    {
        return Appointment::create($data)->load(self::RELATIONS);
    }

    public function update(Appointment $appointment, array $data): Appointment
    {
        $appointment->update($data);

        return $appointment->load(self::RELATIONS);
    }

    /** Запись с заполненной медкнижкой удалить нельзя (restrict) — вернётся 409. */
    public function delete(Appointment $appointment): void
    {
        $appointment->delete();
    }
}
