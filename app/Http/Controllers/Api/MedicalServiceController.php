<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalServiceIndexRequest;
use App\Http\Requests\StoreMedicalServiceRequest;
use App\Http\Requests\UpdateMedicalServiceRequest;
use App\Http\Resources\MedicalServiceResource;
use App\Models\MedicalService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/** Прейскурант платных услуг (цены в тенге). Просмотр — всем, изменение — администратору. */
class MedicalServiceController extends Controller
{
    /** GET /api/v1/services?specialty_id=&search=&price_min=&price_max=&sort=&order= */
    public function index(MedicalServiceIndexRequest $request): AnonymousResourceCollection
    {
        $f = $request->validated();
        $sort = $f['sort'] ?? 'name';
        $order = $f['order'] ?? 'asc';

        $services = MedicalService::with('specialty')->filter($f)
            ->orderBy($sort, $order)->orderBy('id')->get();

        return MedicalServiceResource::collection($services);
    }

    /** GET /api/v1/services/{service} */
    public function show(MedicalService $service): MedicalServiceResource
    {
        return new MedicalServiceResource($service->load('specialty'));
    }

    /** POST /api/v1/services */
    public function store(StoreMedicalServiceRequest $request)
    {
        $service = MedicalService::create($request->validated())->load('specialty');

        return (new MedicalServiceResource($service))->response()->setStatusCode(201);
    }

    /** PUT/PATCH /api/v1/services/{service} */
    public function update(UpdateMedicalServiceRequest $request, MedicalService $service): MedicalServiceResource
    {
        $service->update($request->validated());

        return new MedicalServiceResource($service->load('specialty'));
    }

    /** DELETE /api/v1/services/{service} */
    public function destroy(MedicalService $service): Response
    {
        $service->delete();

        return response()->noContent();
    }
}
