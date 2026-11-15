<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Создание врача: учётная запись (users) + профиль (doctors) + специальности. */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'cabinet' => ['required', 'string', 'max:10'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'description' => ['nullable', 'string', 'max:2000'],
            'specialty_ids' => ['required', 'array', 'min:1'],
            'specialty_ids.*' => ['integer', 'exists:specialties,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Укажите ФИО врача',
            'email.required' => 'Укажите email',
            'email.email' => 'Email указан в неверном формате',
            'email.unique' => 'Пользователь с таким email уже существует',
            'password.required' => 'Укажите пароль',
            'password.min' => 'Пароль должен содержать не менее 8 символов',
            'cabinet.required' => 'Укажите номер кабинета',
            'cabinet.max' => 'Номер кабинета не должен превышать 10 символов',
            'experience_years.required' => 'Укажите стаж',
            'experience_years.integer' => 'Стаж должен быть целым числом',
            'experience_years.min' => 'Стаж не может быть отрицательным',
            'experience_years.max' => 'Стаж не может быть больше 60 лет',
            'specialty_ids.required' => 'Выберите хотя бы одну специальность',
            'specialty_ids.min' => 'Выберите хотя бы одну специальность',
            'specialty_ids.*.exists' => 'Выбранная специальность не найдена',
        ];
    }
}
