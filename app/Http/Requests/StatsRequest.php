<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }

    public function messages(): array
    {
        return [
            'from.date_format' => 'Дата «с» в формате ГГГГ-ММ-ДД',
            'to.date_format' => 'Дата «по» в формате ГГГГ-ММ-ДД',
            'to.after_or_equal' => 'Дата «по» не может быть раньше даты «с»',
        ];
    }
}
