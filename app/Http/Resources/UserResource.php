<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'role' => $this->role?->value,
            'role_label' => $this->role?->label(),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
