<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $service)
    {
    }

    /** GET /appointments */
    public function index(Request $request)
    {
        return AppointmentResource::collection($this->service->list((int) $request->query('per_page', 15)));
    }

    /** POST /appointments */
    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $data['user_id'] ?? $request->user()?->id;

        return (new AppointmentResource($this->service->create($data)))->response()->setStatusCode(201);
    }

    /** GET /appointments/{appointment} */
    public function show(Appointment $appointment)
    {
        return new AppointmentResource($this->service->show($appointment));
    }

    /** PUT/PATCH /appointments/{appointment} */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        return new AppointmentResource($this->service->update($appointment, $request->validated()));
    }

    /** DELETE /appointments/{appointment} */
    public function destroy(Appointment $appointment)
    {
        $this->service->delete($appointment);

        return response()->noContent();
    }
}
