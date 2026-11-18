<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicalServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('service')?->id;
        $specialtyId = $this->input('specialty_id', $this->route('service')?->specialty_id);

        return [
            'specialty_id' => ['nullable', 'integer', 'exists:specialties,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255',
                Rule::unique('medical_services', 'name')->where('specialty_id', $specialtyId)->ignore($id)],
            'price' => ['sometimes', 'required', 'integer', 'min:0', 'max:10000000'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'specialty_id.exists' => 'Специальность не найдена',
            'name.required' => 'Укажите название услуги',
            'name.unique' => 'Такая услуга уже есть в прейскуранте',
            'price.required' => 'Укажите стоимость в тенге',
            'price.integer' => 'Стоимость должна быть целым числом (в тенге)',
            'price.min' => 'Стоимость не может быть отрицательной',
            'price.max' => 'Стоимость не может превышать 10 000 000 ₸',
            'duration_minutes.integer' => 'Длительность должна быть целым числом минут',
            'duration_minutes.min' => 'Длительность — не менее 5 минут',
            'duration_minutes.max' => 'Длительность — не более 480 минут',
        ];
    }
}
