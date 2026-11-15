<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'diagnosis' => $this->diagnosis,
            'examination' => $this->examination,
            'prescription' => $this->prescription,
            'appointment' => new AppointmentResource($this->whenLoaded('appointment')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
