<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorIndexRequest;
use App\Http\Requests\SlotsRequest;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use App\Services\AppointmentService;
use App\Services\DoctorService;
use Illuminate\Support\Carbon;

class DoctorController extends Controller
{
    public function __construct(
        private readonly DoctorService $service,
        private readonly AppointmentService $appointments,
    ) {}

    /** GET /doctors?search=&specialty_id=&experience_min=&sort=&order=&page=&per_page= */
    public function index(DoctorIndexRequest $request)
    {
        return DoctorResource::collection($this->service->list($request->validated()));
    }

    /** GET /doctors/{doctor}/slots?date=ГГГГ-ММ-ДД — свободное время врача */
    public function slots(SlotsRequest $request, Doctor $doctor)
    {
        $date = $request->validated('date');
        $slots = $this->appointments->freeSlots($doctor, $date);

        return response()->json([
            'doctor_id' => $doctor->id,
            'date' => $date,
            'works' => $doctor->schedules()->where('weekday', Carbon::parse($date)->dayOfWeekIso)->exists(),
            'free_slots' => $slots,
            'free_count' => count($slots),
        ]);
    }

    /** POST /doctors */
    public function store(StoreDoctorRequest $request)
    {
        $doctor = $this->service->create($request->validated());

        return (new DoctorResource($doctor))->response()->setStatusCode(201);
    }

    /** GET /doctors/{doctor} */
    public function show(Doctor $doctor): DoctorResource
    {
        return new DoctorResource($doctor->load(['user', 'specialties', 'schedules']));
    }

    /** PUT/PATCH /doctors/{doctor} */
    public function update(UpdateDoctorRequest $request, Doctor $doctor): DoctorResource
    {
        return new DoctorResource($this->service->update($doctor, $request->validated()));
    }

    /** DELETE /doctors/{doctor} */
    public function destroy(Doctor $doctor)
    {
        if (! $this->service->delete($doctor)) {
            return response()->json(['message' => 'Нельзя удалить врача: у него есть записи на приём'], 409);
        }

        return response()->noContent();
    }
}
