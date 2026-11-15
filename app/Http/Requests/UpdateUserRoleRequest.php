<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(UserRole::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Укажите роль',
            'role.enum' => 'Недопустимая роль. Возможные: admin, doctor, patient',
        ];
    }
}
