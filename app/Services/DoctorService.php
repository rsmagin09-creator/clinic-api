<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DoctorService
{
    private const USER_FIELDS = ['name', 'email', 'phone', 'password'];
    private const DOCTOR_FIELDS = ['cabinet', 'experience_years', 'description'];

    public function list(): Collection
    {
        return Doctor::with(['user', 'specialties'])->orderBy('id')->get();
    }

    /** Создание врача: учётная запись + профиль + специальности в одной транзакции. */
    public function create(array $data): Doctor
    {
        return DB::transaction(function () use ($data) {
            $user = User::create(Arr::only($data, self::USER_FIELDS) + ['role' => UserRole::Doctor]);
            $doctor = $user->doctor()->create(Arr::only($data, self::DOCTOR_FIELDS));
            $doctor->specialties()->sync($data['specialty_ids']);

            return $doctor->load(['user', 'specialties']);
        });
    }

    public function update(Doctor $doctor, array $data): Doctor
    {
        return DB::transaction(function () use ($doctor, $data) {
            $doctor->user->update(Arr::only($data, ['name', 'email', 'phone']));
            $doctor->update(Arr::only($data, self::DOCTOR_FIELDS));
            if (array_key_exists('specialty_ids', $data)) {
                $doctor->specialties()->sync($data['specialty_ids']);
            }

            return $doctor->load(['user', 'specialties']);
        });
    }

    /** Удаление врача вместе с учётной записью. Врача с записями на приём удалить нельзя. */
    public function delete(Doctor $doctor): bool
    {
        if ($doctor->appointments()->exists()) {
            return false;
        }

        DB::transaction(fn () => $doctor->user->delete()); // профиль, график и связи удаляются каскадно

        return true;
    }
}
