<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Запись на приём. Пациент — из токена (ПР8); администратор может указать user_id пациента. */
    public function rules(): array
    {
        return [
            'user_id' => [$this->user() ? 'nullable' : 'required', 'integer',
                Rule::exists('users', 'id')->where('role', 'patient')],
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:+30 days'],
            'time' => ['required', 'date_format:H:i'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Укажите пациента',
            'user_id.exists' => 'Пациент не найден',
            'doctor_id.required' => 'Выберите врача',
            'doctor_id.exists' => 'Врач не найден',
            'date.required' => 'Укажите дату приёма',
            'date.date_format' => 'Дата должна быть в формате ГГГГ-ММ-ДД',
            'date.after_or_equal' => 'Нельзя записаться на прошедшую дату',
            'date.before_or_equal' => 'Записаться можно не более чем на 30 дней вперёд',
            'time.required' => 'Укажите время приёма',
            'time.date_format' => 'Время должно быть в формате ЧЧ:ММ',
            'reason.max' => 'Причина обращения не должна превышать 255 символов',
        ];
    }
}
