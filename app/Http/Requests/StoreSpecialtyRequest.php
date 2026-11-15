<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:specialties,name'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Укажите название специальности',
            'name.max' => 'Название не должно превышать 255 символов',
            'name.unique' => 'Такая специальность уже существует',
            'description.max' => 'Описание не должно превышать 2000 символов',
        ];
    }
}
