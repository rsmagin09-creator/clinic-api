<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\User;

class MedicalRecordPolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === UserRole::Admin ? true : null;
    }

    /** Пациент видит свою медкнижку, врач — записи по своим приёмам. */
    public function view(User $user, MedicalRecord $record): bool
    {
        $appointment = $record->appointment;

        return $appointment->user_id === $user->id || $appointment->doctor?->user_id === $user->id;
    }

    /** Внести запись может только врач, который вёл приём. */
    public function create(User $user, Appointment $appointment): bool
    {
        return $appointment->doctor?->user_id === $user->id;
    }

    public function update(User $user, MedicalRecord $record): bool
    {
        return $record->appointment->doctor?->user_id === $user->id;
    }

    public function delete(User $user, MedicalRecord $record): bool
    {
        return false;
    }
}
