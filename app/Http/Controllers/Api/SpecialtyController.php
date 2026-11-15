<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpecialtyRequest;
use App\Http\Requests\UpdateSpecialtyRequest;
use App\Http\Resources\SpecialtyResource;
use App\Models\Specialty;
use App\Services\SpecialtyService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SpecialtyController extends Controller
{
    public function __construct(private readonly SpecialtyService $service)
    {
    }

    /** GET /api/v1/specialties */
    public function index(): AnonymousResourceCollection
    {
        return SpecialtyResource::collection($this->service->list());
    }

    /** POST /api/v1/specialties */
    public function store(StoreSpecialtyRequest $request)
    {
        $specialty = $this->service->create($request->validated());

        return (new SpecialtyResource($specialty))->response()->setStatusCode(201);
    }

    /** GET /api/v1/specialties/{specialty} */
    public function show(Specialty $specialty): SpecialtyResource
    {
        return new SpecialtyResource($specialty->load('doctors.user')->loadCount('doctors'));
    }

    /** PUT/PATCH /api/v1/specialties/{specialty} */
    public function update(UpdateSpecialtyRequest $request, Specialty $specialty): SpecialtyResource
    {
        return new SpecialtyResource($this->service->update($specialty, $request->validated()));
    }

    /** DELETE /api/v1/specialties/{specialty} */
    public function destroy(Specialty $specialty): Response
    {
        $this->service->delete($specialty);

        return response()->noContent();
    }
}
