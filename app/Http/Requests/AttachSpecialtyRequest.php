<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specialty_id' => ['required', 'integer', 'exists:specialties,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'specialty_id.required' => 'Укажите специальность',
            'specialty_id.exists' => 'Специальность не найдена',
        ];
    }
}
