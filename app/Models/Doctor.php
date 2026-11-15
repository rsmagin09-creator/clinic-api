<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cabinet',
        'experience_years',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class)->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** Поиск по части ФИО врача или номеру кабинета. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->whereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$term}%"))
                ->orWhere('cabinet', 'like', "%{$term}%");
        }));
    }

    /** Фильтрация по специальности и минимальному стажу. */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['specialty_id'] ?? null,
                fn (Builder $q, $id) => $q->whereHas('specialties', fn (Builder $s) => $s->whereKey($id)))
            ->when(isset($filters['experience_min']),
                fn (Builder $q) => $q->where('experience_years', '>=', $filters['experience_min']));
    }
}
