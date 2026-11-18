<?php

namespace App\Support;

/** Форматирование сумм в тенге: 12500 → «12 500 ₸». */
class Money
{
    public static function tenge(?int $amount): ?string
    {
        return $amount === null ? null : number_format($amount, 0, ',', ' ').' ₸';
    }
}
