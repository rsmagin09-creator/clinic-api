<?php

namespace App\Services;

use App\Support\Money;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;

class StatsService
{
    /** Статистика записей за период: по статусам, по врачам, популярные специальности. */
    public function appointments(string $from, string $to): array
    {
        $period = fn ($q) => $q->whereBetween('date', [$from, $to]);

        $byStatus = Appointment::query()->tap($period)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statuses = collect(AppointmentStatus::cases())->map(fn ($s) => [
            'status' => $s->value,
            'label' => $s->label(),
            'total' => (int) ($byStatus[$s->value] ?? 0),
        ]);

        $byDoctor = Doctor::with('user')
            ->withCount([
                'appointments as total' => $period,
                'appointments as completed' => fn ($q) => $period($q)->where('status', AppointmentStatus::Completed->value),
                'appointments as no_show' => fn ($q) => $period($q)->where('status', AppointmentStatus::NoShow->value),
            ])
            ->withSum(['appointments as revenue' => fn ($q) => $period($q)->where('status', AppointmentStatus::Completed->value)], 'price')
            ->orderByDesc('total')
            ->get()
            ->map(fn (Doctor $d) => [
                'doctor_id' => $d->id,
                'doctor' => $d->user->name,
                'total' => $d->total,
                'completed' => $d->completed,
                'no_show' => $d->no_show,
                'revenue' => (int) $d->revenue,
                'revenue_formatted' => Money::tenge((int) $d->revenue),
            ]);

        $topSpecialties = DB::table('appointments')
            ->join('doctor_specialty', 'doctor_specialty.doctor_id', '=', 'appointments.doctor_id')
            ->join('specialties', 'specialties.id', '=', 'doctor_specialty.specialty_id')
            ->whereBetween('appointments.date', [$from, $to])
            ->selectRaw('specialties.name, COUNT(*) as total')
            ->groupBy('specialties.id', 'specialties.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Выручка — сумма цен завершённых приёмов за период, в тенге
        $revenue = (int) Appointment::query()->tap($period)
            ->where('status', AppointmentStatus::Completed->value)->sum('price');

        $total = $statuses->sum('total');
        $completed = $statuses->firstWhere('status', 'completed')['total'];

        return [
            'period' => ['from' => $from, 'to' => $to],
            'total' => $total,
            'revenue' => $revenue,
            'revenue_formatted' => Money::tenge($revenue),
            'completed_percent' => $total ? round($completed / $total * 100, 1) : 0,
            'by_status' => $statuses->values(),
            'by_doctor' => $byDoctor->values(),
            'top_specialties' => $topSpecialties,
        ];
    }
}
