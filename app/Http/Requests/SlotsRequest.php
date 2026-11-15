<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:+30 days'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Укажите дату',
            'date.date_format' => 'Дата в формате ГГГГ-ММ-ДД',
            'date.after_or_equal' => 'Дата не может быть в прошлом',
            'date.before_or_equal' => 'Можно смотреть расписание не более чем на 30 дней вперёд',
        ];
    }
}
