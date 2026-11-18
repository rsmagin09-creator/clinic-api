<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doctor_id',
        'date',
        'time',
        'status',
        'reason',
        'price',
    ];

    protected $attributes = [
        'status' => 'scheduled',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'status' => AppointmentStatus::class,
            'price' => 'integer',
        ];
    }

    /** Пациент. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    /** Дата и время приёма одним объектом. */
    public function startsAt(): Carbon
    {
        return Carbon::parse($this->date->format('Y-m-d').' '.substr($this->time, 0, 5));
    }

    /** Фильтр списка: статус, врач, период дат. */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, fn (Builder $q, $s) => $q->where('status', $s))
            ->when($filters['doctor_id'] ?? null, fn (Builder $q, $d) => $q->where('doctor_id', $d))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $d) => $q->whereDate('date', '>=', $d))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $d) => $q->whereDate('date', '<=', $d));
    }
}
