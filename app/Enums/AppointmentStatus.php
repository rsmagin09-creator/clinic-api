<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Запланирован',
            self::Completed => 'Завершён',
            self::Cancelled => 'Отменён',
            self::NoShow => 'Неявка',
        };
    }

    /** Допустимые переходы статуса: из «запланирован» в любой конечный. */
    public function canTransitionTo(self $next): bool
    {
        return $this === self::Scheduled && $next !== self::Scheduled;
    }
}
