<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Doctor;
use App\Models\Schedule;
use App\Services\ScheduleService;

class ScheduleController extends Controller
{
    public function __construct(private readonly ScheduleService $service)
    {
    }

    /** GET /doctors/{doctor}/schedules */
    public function index(Doctor $doctor)
    {
        return ScheduleResource::collection($this->service->forDoctor($doctor));
    }

    /** POST /doctors/{doctor}/schedules */
    public function store(StoreScheduleRequest $request, Doctor $doctor)
    {
        return (new ScheduleResource($this->service->create($doctor, $request->validated())))
            ->response()->setStatusCode(201);
    }

    /** GET /schedules/{schedule} */
    public function show(Schedule $schedule)
    {
        return new ScheduleResource($schedule);
    }

    /** PUT/PATCH /schedules/{schedule} */
    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        return new ScheduleResource($this->service->update($schedule, $request->validated()));
    }

    /** DELETE /schedules/{schedule} */
    public function destroy(Schedule $schedule)
    {
        $this->service->delete($schedule);

        return response()->noContent();
    }
}
