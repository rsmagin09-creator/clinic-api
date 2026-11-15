<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Укажите ФИО',
            'email.required' => 'Укажите email',
            'email.email' => 'Email указан в неверном формате',
            'email.unique' => 'Пользователь с таким email уже зарегистрирован',
            'birth_date.date_format' => 'Дата рождения в формате ГГГГ-ММ-ДД',
            'birth_date.before' => 'Дата рождения должна быть в прошлом',
            'password.required' => 'Укажите пароль',
            'password.min' => 'Пароль должен содержать не менее 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ];
    }
}
