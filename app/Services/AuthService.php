<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /** Регистрация: роль по умолчанию — пациент, пароль хешируется (cast 'hashed'). */
    public function register(array $data): array
    {
        $user = User::create($data + ['role' => UserRole::Patient]);

        return [$user, $user->createToken('api')->plainTextToken];
    }

    /** Возвращает [пользователь, токен] или null при неверном email/пароле. */
    public function login(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return [$user, $user->createToken('api')->plainTextToken];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
