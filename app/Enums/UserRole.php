<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Doctor = 'doctor';
    case Patient = 'patient';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Администратор',
            self::Doctor => 'Врач',
            self::Patient => 'Пациент',
        };
    }
}
