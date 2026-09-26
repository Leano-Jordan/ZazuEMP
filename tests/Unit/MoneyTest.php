<?php

namespace Tests\Unit;

use App\Support\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_amount_is_converted_to_hundredths_without_float_rounding(): void
    {
        $this->assertSame(2550, Money::toCents('25.50'));
        $this->assertSame(2551, Money::toCents('25.51'));
    }

    public function test_amount_with_more_than_two_decimal_places_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::toCents('25.501');
    }

    public function test_quantity_and_unit_price_are_multiplied_and_rounded_to_cents(): void
    {
        $this->assertSame(255000, Money::multiplyQuantityByPrice(10000, 2550));
        $this->assertSame('2550.00', Money::fromCents(2550));
        $this->assertSame(1006, Money::multiplyQuantityByPrice(333, 302));
    }
}
