<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->whenLoaded('user', fn () => $this->user->name),
            'email' => $this->whenLoaded('user', fn () => $this->user->email),
            'cabinet' => $this->cabinet,
            'experience_years' => $this->experience_years,
            'description' => $this->description,
            'specialties' => SpecialtyResource::collection($this->whenLoaded('specialties')),
            'schedules' => ScheduleResource::collection($this->whenLoaded('schedules')),
        ];
    }
}
