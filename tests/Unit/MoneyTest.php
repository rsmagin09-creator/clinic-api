<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_formats_tenge_with_thousands_separator(): void
    {
        $this->assertSame('9 000 ₸', Money::tenge(9000));
        $this->assertSame('1 250 000 ₸', Money::tenge(1250000));
        $this->assertSame('0 ₸', Money::tenge(0));
    }

    public function test_null_amount_stays_null(): void
    {
        $this->assertNull(Money::tenge(null));
    }
}
