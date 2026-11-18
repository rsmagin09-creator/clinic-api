<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Платная медицинская услуга из прейскуранта. */
class MedicalService extends Model
{
    use HasFactory;

    protected $fillable = ['specialty_id', 'name', 'price', 'duration_minutes', 'description'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'duration_minutes' => 'integer'];
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    /** Фильтр: специальность, поиск по названию, диапазон цены. */
    public function scopeFilter(Builder $query, array $f): Builder
    {
        return $query
            ->when($f['specialty_id'] ?? null, fn (Builder $q, $id) => $q->where('specialty_id', $id))
            ->when($f['search'] ?? null, fn (Builder $q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when(isset($f['price_min']), fn (Builder $q) => $q->where('price', '>=', $f['price_min']))
            ->when(isset($f['price_max']), fn (Builder $q) => $q->where('price', '<=', $f['price_max']));
    }
}
