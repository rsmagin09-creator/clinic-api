<?php

namespace App\Http\Requests;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Укажите новый статус',
            'status.enum' => 'Недопустимый статус. Возможные: completed, no_show, cancelled',
        ];
    }
}
