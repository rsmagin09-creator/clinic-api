<?php

namespace App\Http\Requests;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Параметры списка записей: ?status=&doctor_id=&date_from=&date_to=&sort=&order=&page=&per_page= */
class AppointmentIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'sort' => ['nullable', 'in:date,created_at'],
            'order' => ['nullable', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.enum' => 'Недопустимый статус. Возможные: scheduled, completed, cancelled, no_show',
            'doctor_id.exists' => 'Врач не найден',
            'date_from.date_format' => 'Дата «с» в формате ГГГГ-ММ-ДД',
            'date_to.date_format' => 'Дата «по» в формате ГГГГ-ММ-ДД',
            'date_to.after_or_equal' => 'Дата «по» не может быть раньше даты «с»',
            'sort.in' => 'Сортировка возможна по полям: date, created_at',
            'order.in' => 'Направление сортировки: asc или desc',
            'per_page.max' => 'На странице не более 50 записей',
        ];
    }
}
