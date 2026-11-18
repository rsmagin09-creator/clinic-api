<?php

namespace App\Http\Resources;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'price_formatted' => Money::tenge($this->price),
            'duration_minutes' => $this->duration_minutes,
            'description' => $this->description,
            'specialty' => $this->whenLoaded('specialty', fn () => $this->specialty
                ? ['id' => $this->specialty->id, 'name' => $this->specialty->name] : null),
        ];
    }
}
