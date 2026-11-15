<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $service)
    {
    }

    /** GET /appointments — в списке пользователь видит только свои записи */
    public function index(Request $request)
    {
        return AppointmentResource::collection(
            $this->service->list($request->user(), (int) $request->query('per_page', 15))
        );
    }

    /** POST /appointments — пациент записывается сам, администратор указывает user_id */
    public function store(StoreAppointmentRequest $request)
    {
        Gate::authorize('create', Appointment::class);

        $data = $request->validated();
        if ($request->user()->role === UserRole::Patient) {
            $data['user_id'] = $request->user()->id;
        }

        return (new AppointmentResource($this->service->create($data)))->response()->setStatusCode(201);
    }

    /** GET /appointments/{appointment} */
    public function show(Appointment $appointment)
    {
        Gate::authorize('view', $appointment);

        return new AppointmentResource($this->service->show($appointment));
    }

    /** PUT/PATCH /appointments/{appointment} — врач этого приёма или администратор */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        Gate::authorize('update', $appointment);

        return new AppointmentResource($this->service->update($appointment, $request->validated()));
    }

    /** DELETE /appointments/{appointment} — только администратор */
    public function destroy(Appointment $appointment)
    {
        Gate::authorize('delete', $appointment);
        $this->service->delete($appointment);

        return response()->noContent();
    }
}
