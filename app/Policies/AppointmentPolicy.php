<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /** Администратор может всё. */
    public function before(User $user): ?bool
    {
        return $user->role === UserRole::Admin ? true : null;
    }

    /** Просмотр: пациент — свою запись, врач — запись к себе. */
    public function view(User $user, Appointment $appointment): bool
    {
        return $appointment->user_id === $user->id
            || $appointment->doctor?->user_id === $user->id;
    }

    /** Записаться может только пациент. */
    public function create(User $user): bool
    {
        return $user->role === UserRole::Patient;
    }

    /** Менять статус приёма может только его врач. */
    public function update(User $user, Appointment $appointment): bool
    {
        return $appointment->doctor?->user_id === $user->id;
    }

    /** Отменить запись может пациент-владелец. */
    public function cancel(User $user, Appointment $appointment): bool
    {
        return $appointment->user_id === $user->id;
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return false; // только администратор (before)
    }
}
