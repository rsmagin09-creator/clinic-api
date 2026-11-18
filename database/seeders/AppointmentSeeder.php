<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Записи на приём строго по графику врачей и без пересечений:
     * прошедшие даты — завершён / неявка / отменён, будущие — запланирован.
     */
    public function run(): void
    {
        $patients = User::where('role', UserRole::Patient)->get();
        $doctors = Doctor::with('schedules')->get();
        $busy = [];

        foreach ($patients as $patient) {
            foreach ($doctors->random(3) as $doctor) {
                $slot = $this->freeSlot($doctor, $busy);
                if (! $slot) {
                    continue;
                }
                [$date, $time] = $slot;
                $busy[$doctor->id.'|'.$date.'|'.$time] = true;

                $status = $date < now()->toDateString()
                    ? fake()->randomElement([AppointmentStatus::Completed, AppointmentStatus::Completed, AppointmentStatus::NoShow, AppointmentStatus::Cancelled])
                    : AppointmentStatus::Scheduled;

                Appointment::factory()->create([
                    'user_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'date' => $date,
                    'time' => $time,
                    'status' => $status,
                    'price' => $doctor->price,
                ]);
            }
        }
    }

    private function freeSlot(Doctor $doctor, array $busy): ?array
    {
        $days = CarbonPeriod::create(now()->subDays(14), now()->addDays(14));

        foreach (collect($days)->shuffle() as $day) {
            $schedule = $doctor->schedules->firstWhere('weekday', $day->dayOfWeekIso);
            if (! $schedule) {
                continue;
            }
            $times = [];
            for ($t = strtotime($schedule->start_time); $t < strtotime($schedule->end_time); $t += $schedule->slot_minutes * 60) {
                $times[] = date('H:i', $t);
            }
            foreach (collect($times)->shuffle() as $time) {
                if (! isset($busy[$doctor->id.'|'.$day->toDateString().'|'.$time])) {
                    return [$day->toDateString(), $time];
                }
            }
        }

        return null;
    }
}
