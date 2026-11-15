<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_register_and_gets_token(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Тестовый Пациент',
            'email' => 'new@clinic.ru',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('user.email', 'new@clinic.ru')
            ->assertJsonPath('user.role', 'patient')
            ->assertJsonMissingPath('user.password')
            ->assertJsonStructure(['user' => ['id', 'name'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'new@clinic.ru', 'role' => 'patient']);
    }

    public function test_register_validation_errors(): void
    {
        User::factory()->create(['email' => 'busy@clinic.ru']);

        $this->postJson('/api/v1/register', [
            'name' => '',
            'email' => 'busy@clinic.ru',
            'password' => '123',
            'password_confirmation' => '321',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Ошибка валидации')
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        User::factory()->create(['email' => 'patient@clinic.ru']);

        $this->postJson('/api/v1/login', ['email' => 'patient@clinic.ru', 'password' => 'wrong'])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Неверный email или пароль');
    }

    public function test_login_returns_token(): void
    {
        User::factory()->create(['email' => 'patient@clinic.ru']);

        $this->postJson('/api/v1/login', ['email' => 'patient@clinic.ru', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['user', 'token', 'token_type']);
    }

    public function test_protected_route_without_token_returns_401(): void
    {
        $this->getJson('/api/v1/appointments')
            ->assertStatus(401)
            ->assertJsonPath('message', 'Требуется авторизация');
    }
}
