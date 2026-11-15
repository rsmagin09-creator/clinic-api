<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'birth_date',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /** Профиль врача (если пользователь — врач). */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    /** Записи на приём пациента. */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** Медицинская книжка пациента — записи через приёмы. */
    public function medicalRecords(): HasManyThrough
    {
        return $this->hasManyThrough(MedicalRecord::class, Appointment::class);
    }

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }
}
