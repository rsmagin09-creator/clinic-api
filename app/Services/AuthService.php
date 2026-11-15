<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    /** Регистрация: роль по умолчанию — пациент, пароль хешируется (cast 'hashed'). */
    public function register(array $data): array
    {
        $user = User::create($data + ['role' => UserRole::Patient]);
        Log::info('Зарегистрирован пользователь', ['user_id' => $user->id, 'email' => $user->email]);

        return [$user, $user->createToken('api')->plainTextToken];
    }

    /** Возвращает [пользователь, токен] или null при неверном email/пароле. */
    public function login(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            // пароль в журнал не пишется
            Log::warning('Неудачная попытка входа', ['email' => $email]);

            return null;
        }

        Log::info('Вход в систему', ['user_id' => $user->id, 'role' => $user->role->value]);

        return [$user, $user->createToken('api')->plainTextToken];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
        Log::info('Выход из системы', ['user_id' => $user->id]);
    }
}
