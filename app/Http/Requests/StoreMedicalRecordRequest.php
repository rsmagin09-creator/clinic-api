<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'diagnosis' => [$required, 'string', 'max:255'],
            'examination' => ['nullable', 'string', 'max:5000'],
            'prescription' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'diagnosis.required' => 'Укажите диагноз',
            'diagnosis.max' => 'Диагноз не должен превышать 255 символов',
            'examination.max' => 'Результаты осмотра не должны превышать 5000 символов',
            'prescription.max' => 'Назначения не должны превышать 5000 символов',
        ];
    }
}
