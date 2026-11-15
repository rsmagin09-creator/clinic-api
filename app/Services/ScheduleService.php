<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;

class ScheduleService
{
    public function forDoctor(Doctor $doctor): Collection
    {
        return $doctor->schedules()->orderBy('weekday')->get();
    }

    public function create(Doctor $doctor, array $data): Schedule
    {
        return $doctor->schedules()->create($data);
    }

    public function update(Schedule $schedule, array $data): Schedule
    {
        $schedule->update($data);

        return $schedule;
    }

    public function delete(Schedule $schedule): void
    {
        $schedule->delete();
    }
}
