<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('doctor')?->user_id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'cabinet' => ['sometimes', 'required', 'string', 'max:10'],
            'experience_years' => ['sometimes', 'required', 'integer', 'min:0', 'max:60'],
            'description' => ['nullable', 'string', 'max:2000'],
            'specialty_ids' => ['sometimes', 'array', 'min:1'],
            'specialty_ids.*' => ['integer', 'exists:specialties,id'],
        ];
    }

    public function messages(): array
    {
        return (new StoreDoctorRequest)->messages();
    }
}
