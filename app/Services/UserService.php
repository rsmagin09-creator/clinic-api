<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function list(?string $role = null, int $perPage = 15): LengthAwarePaginator
    {
        return User::withCount('appointments')
            ->when($role, fn ($q) => $q->where('role', $role))
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function updateRole(User $user, string $role): User
    {
        $user->update(['role' => $role]);

        return $user;
    }

    /** Пользователя с записями на приём удалить нельзя (restrict) — вернётся 409. */
    public function delete(User $user): void
    {
        $user->delete();
    }
}
