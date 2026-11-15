<?php

namespace App\Services;

use App\Models\Specialty;
use Illuminate\Database\Eloquent\Collection;

class SpecialtyService
{
    /** Список специальностей с количеством врачей. */
    public function list(): Collection
    {
        return Specialty::withCount('doctors')->orderBy('name')->get();
    }

    public function create(array $data): Specialty
    {
        return Specialty::create($data);
    }

    public function update(Specialty $specialty, array $data): Specialty
    {
        $specialty->update($data);

        return $specialty;
    }

    public function delete(Specialty $specialty): void
    {
        $specialty->delete(); // связи doctor_specialty удаляются каскадно
    }
}
