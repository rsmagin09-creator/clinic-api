<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use App\Services\DoctorService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoctorController extends Controller
{
    public function __construct(private readonly DoctorService $service)
    {
    }

    /** GET /api/v1/doctors */
    public function index(): AnonymousResourceCollection
    {
        return DoctorResource::collection($this->service->list());
    }

    /** POST /api/v1/doctors */
    public function store(StoreDoctorRequest $request)
    {
        $doctor = $this->service->create($request->validated());

        return (new DoctorResource($doctor))->response()->setStatusCode(201);
    }

    /** GET /api/v1/doctors/{doctor} */
    public function show(Doctor $doctor): DoctorResource
    {
        return new DoctorResource($doctor->load(['user', 'specialties', 'schedules']));
    }

    /** PUT/PATCH /api/v1/doctors/{doctor} */
    public function update(UpdateDoctorRequest $request, Doctor $doctor): DoctorResource
    {
        return new DoctorResource($this->service->update($doctor, $request->validated()));
    }

    /** DELETE /api/v1/doctors/{doctor} */
    public function destroy(Doctor $doctor)
    {
        if (! $this->service->delete($doctor)) {
            return response()->json(['message' => 'Нельзя удалить врача: у него есть записи на приём'], 409);
        }

        return response()->noContent();
    }
}
