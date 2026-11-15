<?php

namespace App\Http\Requests;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Изменение записи: смена статуса врачом/админом или причины обращения. */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', Rule::enum(AppointmentStatus::class)],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Укажите статус',
            'status.enum' => 'Недопустимый статус. Возможные: scheduled, completed, cancelled, no_show',
            'reason.max' => 'Причина обращения не должна превышать 255 символов',
        ];
    }
}
