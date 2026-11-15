<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttachSpecialtyRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use App\Models\Specialty;

/** Управление связью «многие ко многим» врач ↔ специальность. */
class DoctorSpecialtyController extends Controller
{
    /** POST /doctors/{doctor}/specialties — attach (без дублей) */
    public function store(AttachSpecialtyRequest $request, Doctor $doctor)
    {
        $doctor->specialties()->syncWithoutDetaching([$request->validated('specialty_id')]);

        return new DoctorResource($doctor->load(['user', 'specialties']));
    }

    /** DELETE /doctors/{doctor}/specialties/{specialty} — detach */
    public function destroy(Doctor $doctor, Specialty $specialty)
    {
        $doctor->specialties()->detach($specialty->id);

        return new DoctorResource($doctor->load(['user', 'specialties']));
    }
}
