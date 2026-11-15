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

    /**
     * Допустимые переходы статуса:
     * запланирован → завершён | неявка | отменён; из конечных статусов переходов нет.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Scheduled => [self::Completed, self::NoShow, self::Cancelled],
            default => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    /** Статусы, при которых время у врача считается занятым. */
    public static function occupying(): array
    {
        return [self::Scheduled->value, self::Completed->value];
    }
}
