<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class AppointmentService
{
    private const RELATIONS = ['user', 'doctor.user', 'doctor.specialties'];

    /** Список с учётом роли: пациент — свои записи, врач — приёмы к себе, админ — все. */
    public function list(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Appointment::with(self::RELATIONS)
            ->when($user->role === UserRole::Patient, fn ($q) => $q->where('user_id', $user->id))
            ->when($user->role === UserRole::Doctor, fn ($q) => $q->where('doctor_id', $user->doctor?->id))
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

    public function delete(Appointment $appointment): void
    {
        $appointment->delete();
    }
}
