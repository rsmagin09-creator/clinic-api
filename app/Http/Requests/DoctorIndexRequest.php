<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Параметры списка врачей: ?search=&specialty_id=&experience_min=&sort=&order=&page=&per_page= */
class DoctorIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'specialty_id' => ['nullable', 'integer', 'exists:specialties,id'],
            'experience_min' => ['nullable', 'integer', 'min:0', 'max:60'],
            'sort' => ['nullable', 'in:name,experience_years,cabinet,id'],
            'order' => ['nullable', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'search.max' => 'Строка поиска не должна превышать 100 символов',
            'specialty_id.exists' => 'Специальность не найдена',
            'experience_min.integer' => 'Минимальный стаж должен быть числом',
            'sort.in' => 'Сортировка возможна по полям: name, experience_years, cabinet, id',
            'order.in' => 'Направление сортировки: asc или desc',
            'per_page.max' => 'На странице не более 50 записей',
            'per_page.min' => 'На странице не менее 1 записи',
        ];
    }
}
