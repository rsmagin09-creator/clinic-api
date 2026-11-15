<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'weekday' => ['required', 'integer', 'between:1,7',
                Rule::unique('schedules')->where('doctor_id', $this->route('doctor')->id)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_minutes' => ['sometimes', 'integer', 'in:15,20,30,40,60'],
        ];
    }

    public function messages(): array
    {
        return [
            'weekday.required' => 'Укажите день недели',
            'weekday.between' => 'День недели — число от 1 (понедельник) до 7 (воскресенье)',
            'weekday.unique' => 'На этот день недели у врача уже есть график',
            'start_time.required' => 'Укажите время начала приёма',
            'start_time.date_format' => 'Время начала в формате ЧЧ:ММ',
            'end_time.required' => 'Укажите время окончания приёма',
            'end_time.date_format' => 'Время окончания в формате ЧЧ:ММ',
            'end_time.after' => 'Время окончания должно быть позже времени начала',
            'slot_minutes.in' => 'Длительность приёма: 15, 20, 30, 40 или 60 минут',
        ];
    }
}
