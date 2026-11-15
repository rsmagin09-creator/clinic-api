<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Illuminate\Database\Seeder;

class MedicalRecordSeeder extends Seeder
{
    /** Запись в медкнижку для каждого завершённого приёма. */
    public function run(): void
    {
        Appointment::where('status', AppointmentStatus::Completed)->get()
            ->each(fn (Appointment $appointment) => MedicalRecord::factory()->create([
                'appointment_id' => $appointment->id,
            ]));
    }
}
