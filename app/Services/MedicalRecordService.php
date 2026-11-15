<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Exceptions\BusinessException;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MedicalRecordService
{
    /**
     * Медкнижка с учётом роли: пациент — только своя,
     * врач — записи по своим приёмам (можно отфильтровать по пациенту), админ — все.
     */
    public function list(User $user, ?int $patientId = null): Collection
    {
        return MedicalRecord::with('appointment.doctor.user', 'appointment.user')
            ->whereHas('appointment', function ($a) use ($user, $patientId) {
                match ($user->role) {
                    UserRole::Patient => $a->where('user_id', $user->id),
                    UserRole::Doctor => $a->where('doctor_id', $user->doctor?->id),
                    default => null,
                };
                if ($patientId && $user->role !== UserRole::Patient) {
                    $a->where('user_id', $patientId);
                }
            })
            ->latest()
            ->get();
    }

    /** Запись в медкнижку — только по завершённому приёму и только одна на приём. */
    public function create(Appointment $appointment, array $data): MedicalRecord
    {
        return DB::transaction(function () use ($appointment, $data) {
            $appointment = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            if ($appointment->status !== AppointmentStatus::Completed) {
                throw new BusinessException('Запись в медкнижку можно внести только по завершённому приёму');
            }
            if ($appointment->medicalRecord()->exists()) {
                throw new BusinessException('Запись в медкнижке по этому приёму уже существует');
            }

            return $appointment->medicalRecord()->create($data)->load('appointment.doctor.user');
        });
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
