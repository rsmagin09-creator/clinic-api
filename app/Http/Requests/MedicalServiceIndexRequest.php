<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicalServiceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specialty_id' => ['nullable', 'integer', 'exists:specialties,id'],
            'search' => ['nullable', 'string', 'max:100'],
            'price_min' => ['nullable', 'integer', 'min:0'],
            'price_max' => ['nullable', 'integer', 'min:0', 'gte:price_min'],
            'sort' => ['nullable', 'in:name,price'],
            'order' => ['nullable', 'in:asc,desc'],
        ];
    }

    public function messages(): array
    {
        return [
            'specialty_id.exists' => 'Специальность не найдена',
            'price_max.gte' => 'Максимальная цена должна быть не меньше минимальной',
            'sort.in' => 'Сортировка возможна по полям: name, price',
        ];
    }
}
