<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Collection;

class MedicalRecordService
{
    /** Медкнижка пациента или все записи. */
    public function list(?int $patientId = null): Collection
    {
        return MedicalRecord::with('appointment.doctor.user')
            ->when($patientId, fn ($q) => $q->whereHas('appointment', fn ($a) => $a->where('user_id', $patientId)))
            ->latest()
            ->get();
    }

    /** Возвращает null, если у приёма уже есть запись в медкнижке. */
    public function create(Appointment $appointment, array $data): ?MedicalRecord
    {
        if ($appointment->medicalRecord()->exists()) {
            return null;
        }

        return $appointment->medicalRecord()->create($data)->load('appointment.doctor.user');
    }

    public function update(MedicalRecord $record, array $data): MedicalRecord
    {
        $record->update($data);

        return $record->load('appointment.doctor.user');
    }

    public function delete(MedicalRecord $record): void
    {
        $record->delete();
    }
}
