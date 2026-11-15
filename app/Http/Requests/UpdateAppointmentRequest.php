<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Изменение причины обращения. Статус меняется отдельным действием PATCH /appointments/{id}/status. */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.max' => 'Причина обращения не должна превышать 255 символов',
        ];
    }
}
