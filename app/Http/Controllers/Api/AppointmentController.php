<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentIndexRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Requests\UpdateAppointmentStatusRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $service) {}

    /** GET /appointments?status=&doctor_id=&date_from=&date_to=&sort=&order=&page=&per_page= */
    public function index(AppointmentIndexRequest $request)
    {
        return AppointmentResource::collection($this->service->list($request->user(), $request->validated()));
    }

    /** POST /appointments — ключевой сценарий: запись на приём с проверкой бизнес-правил */
    public function store(StoreAppointmentRequest $request)
    {
        Gate::authorize('create', Appointment::class);

        $user = $request->user();
        $patientId = $user->role === UserRole::Patient ? $user->id : (int) $request->validated('user_id');

        return (new AppointmentResource($this->service->book($patientId, $request->validated())))
            ->response()->setStatusCode(201);
    }

    /** GET /appointments/{appointment} */
    public function show(Appointment $appointment)
    {
        Gate::authorize('view', $appointment);

        return new AppointmentResource($this->service->show($appointment));
    }

    /** PUT/PATCH /appointments/{appointment} — причина обращения */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        Gate::authorize('view', $appointment);

        return new AppointmentResource($this->service->update($appointment, $request->validated()));
    }

    /** PATCH /appointments/{appointment}/status — врач приёма или администратор */
    public function updateStatus(UpdateAppointmentStatusRequest $request, Appointment $appointment)
    {
        Gate::authorize('update', $appointment);

        return new AppointmentResource(
            $this->service->changeStatus($appointment, AppointmentStatus::from($request->validated('status')))
        );
    }

    /** POST /appointments/{appointment}/cancel — пациент-владелец или администратор */
    public function cancel(Request $request, Appointment $appointment)
    {
        Gate::authorize('cancel', $appointment);

        return new AppointmentResource(
            $this->service->cancel($appointment, $request->user()->role === UserRole::Admin)
        );
    }

    /** DELETE /appointments/{appointment} — только администратор */
    public function destroy(Appointment $appointment)
    {
        Gate::authorize('delete', $appointment);
        $this->service->delete($appointment);

        return response()->noContent();
    }
}
