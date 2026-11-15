<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date?->format('Y-m-d'),
            'time' => substr($this->time, 0, 5),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'reason' => $this->reason,
            'patient' => new UserResource($this->whenLoaded('user')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'medical_record' => new MedicalRecordResource($this->whenLoaded('medicalRecord')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
