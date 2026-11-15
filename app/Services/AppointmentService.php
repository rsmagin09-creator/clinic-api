<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Exceptions\BusinessException;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentService
{
    private const RELATIONS = ['user', 'doctor.user', 'doctor.specialties'];

    /** Минимальное время до приёма, когда пациент ещё может отменить запись (часы). */
    public const CANCEL_HOURS_BEFORE = 2;

    private const SORTS = ['date', 'created_at'];

    /** Список с учётом роли + фильтры, сортировка, пагинация. */
    public function list(User $user, array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'] ?? null, self::SORTS, true) ? $filters['sort'] : 'date';
        $order = ($filters['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return Appointment::with(self::RELATIONS)
            ->when($user->role === UserRole::Patient, fn ($q) => $q->where('user_id', $user->id))
            ->when($user->role === UserRole::Doctor, fn ($q) => $q->where('doctor_id', $user->doctor?->id))
            ->filter($filters)
            ->orderBy($sort, $order)->orderBy('time', $order)
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();
    }

    public function show(Appointment $appointment): Appointment
    {
        return $appointment->load([...self::RELATIONS, 'medicalRecord']);
    }

    /**
     * Свободное время врача на дату: интервалы графика минус занятые записи
     * (для сегодняшней даты — только время, которое ещё не наступило).
     */
    public function freeSlots(Doctor $doctor, string $date): array
    {
        $day = Carbon::parse($date);
        $schedule = $doctor->schedules()->where('weekday', $day->dayOfWeekIso)->first();

        if (! $schedule) {
            return [];
        }

        $busy = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('date', $day->toDateString())
            ->whereIn('status', AppointmentStatus::occupying())
            ->pluck('time')
            ->map(fn ($t) => substr($t, 0, 5))
            ->all();

        $slots = [];
        $start = Carbon::parse($day->toDateString().' '.substr($schedule->start_time, 0, 5));
        $end = Carbon::parse($day->toDateString().' '.substr($schedule->end_time, 0, 5));

        for ($t = $start->copy(); $t->copy()->addMinutes($schedule->slot_minutes)->lte($end); $t->addMinutes($schedule->slot_minutes)) {
            if (! in_array($t->format('H:i'), $busy, true) && $t->isFuture()) {
                $slots[] = $t->format('H:i');
            }
        }

        return $slots;
    }

    /**
     * КЛЮЧЕВОЙ СЦЕНАРИЙ — запись на приём.
     * Выполняется в транзакции с блокировкой строки врача (lockForUpdate), чтобы два пациента
     * не записались на одно время одновременно. Нарушение любого правила — BusinessException (409).
     */
    public function book(int $patientId, array $data): Appointment
    {
        return DB::transaction(function () use ($patientId, $data) {
            /** @var Doctor $doctor */
            $doctor = Doctor::whereKey($data['doctor_id'])->lockForUpdate()->firstOrFail();

            $date = Carbon::parse($data['date'])->toDateString();
            $time = $data['time'];
            $startsAt = Carbon::parse("$date $time");

            // Правило 1. Приём должен быть в будущем
            if ($startsAt->isPast()) {
                throw new BusinessException('Нельзя записаться на время, которое уже прошло');
            }

            // Правило 2. Врач работает в этот день недели
            $schedule = $doctor->schedules()->where('weekday', $startsAt->dayOfWeekIso)->first();
            if (! $schedule) {
                throw new BusinessException('Врач не принимает в этот день недели');
            }

            // Правило 3. Время входит в график врача и совпадает с началом интервала приёма
            $from = substr($schedule->start_time, 0, 5);
            $to = substr($schedule->end_time, 0, 5);
            $minutesFromStart = (int) Carbon::parse("$date $from")->diffInMinutes($startsAt, false);
            $fitsEnd = $startsAt->copy()->addMinutes($schedule->slot_minutes)->lte(Carbon::parse("$date $to"));
            if ($minutesFromStart < 0 || ! $fitsEnd || $minutesFromStart % $schedule->slot_minutes !== 0) {
                throw new BusinessException(
                    "Врач принимает в этот день с $from до $to, приём каждые {$schedule->slot_minutes} минут"
                );
            }

            // Правило 4. Время у врача свободно
            $busy = Appointment::where('doctor_id', $doctor->id)
                ->whereDate('date', $date)->where('time', $time)
                ->whereIn('status', AppointmentStatus::occupying())
                ->lockForUpdate()->exists();
            if ($busy) {
                throw new BusinessException('Это время у врача уже занято. Выберите другое время');
            }

            // Правило 5. У пациента нет другой записи на это же время
            $patientBusy = Appointment::where('user_id', $patientId)
                ->whereDate('date', $date)->where('time', $time)
                ->where('status', AppointmentStatus::Scheduled->value)->exists();
            if ($patientBusy) {
                throw new BusinessException('У вас уже есть запись на это время к другому врачу');
            }

            // Правило 6. Не больше одной активной записи к одному врачу в день
            $sameDoctorSameDay = Appointment::where('user_id', $patientId)
                ->where('doctor_id', $doctor->id)->whereDate('date', $date)
                ->where('status', AppointmentStatus::Scheduled->value)->exists();
            if ($sameDoctorSameDay) {
                throw new BusinessException('У вас уже есть запись к этому врачу на этот день');
            }

            $appointment = Appointment::create([
                'user_id' => $patientId,
                'doctor_id' => $doctor->id,
                'date' => $date,
                'time' => $time,
                'status' => AppointmentStatus::Scheduled,
                'reason' => $data['reason'] ?? null,
            ]);

            Log::info('Создана запись на приём', [
                'appointment_id' => $appointment->id, 'patient_id' => $patientId,
                'doctor_id' => $doctor->id, 'date' => $date, 'time' => $time,
            ]);

            return $appointment->load(self::RELATIONS);
        });
    }

    /** Смена статуса врачом/администратором только по разрешённым переходам (см. AppointmentStatus). */
    public function changeStatus(Appointment $appointment, AppointmentStatus $new): Appointment
    {
        return DB::transaction(function () use ($appointment, $new) {
            $appointment = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            if (! $appointment->status->canTransitionTo($new)) {
                throw new BusinessException(sprintf(
                    'Нельзя изменить статус «%s» на «%s». Допустимо: %s',
                    $appointment->status->label(), $new->label(),
                    implode(', ', array_map(fn ($s) => '«'.$s->label().'»', $appointment->status->allowedTransitions())) ?: 'нет'
                ));
            }

            $appointment->update(['status' => $new]);
            Log::info('Изменён статус приёма', ['appointment_id' => $appointment->id, 'status' => $new->value]);

            return $appointment->load(self::RELATIONS);
        });
    }

    /** Отмена записи пациентом — только запланированной и не позже чем за 2 часа до приёма. */
    public function cancel(Appointment $appointment, bool $byAdmin = false): Appointment
    {
        if (! $byAdmin && $appointment->status === AppointmentStatus::Scheduled
            && now()->diffInMinutes($appointment->startsAt(), false) < self::CANCEL_HOURS_BEFORE * 60) {
            throw new BusinessException('Отменить запись можно не позднее чем за '.self::CANCEL_HOURS_BEFORE.' часа до приёма');
        }

        return $this->changeStatus($appointment, AppointmentStatus::Cancelled);
    }

    public function update(Appointment $appointment, array $data): Appointment
    {
        $appointment->update($data);

        return $appointment->load(self::RELATIONS);
    }

    public function delete(Appointment $appointment): void
    {
        $appointment->delete();
    }
}
