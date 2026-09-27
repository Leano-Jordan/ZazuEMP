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

    public function test_display_formatting_preserves_exact_cents_and_adds_separators(): void
    {
        $this->assertSame('12,500.00', Money::formatCents(1250000));
        $this->assertSame('1,000.01', Money::formatCents(100001));
    }

    public function test_quantity_and_unit_price_are_multiplied_and_rounded_to_cents(): void
    {
        $this->assertSame(255000, Money::multiplyQuantityByPrice(10000, 2550));
        $this->assertSame('25.50', Money::fromCents(2550));
        $this->assertSame(1006, Money::multiplyQuantityByPrice(333, 302));
    }
    public function test_oversized_money_string_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::toCents('999999999999999999999999999.99');
    }

    public function test_monetary_multiplication_rejects_overflow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::multiplyQuantityByPrice(PHP_INT_MAX, 100);
    }

    public function test_monetary_multiplication_rejects_negative_inputs(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::multiplyQuantityByPrice(-1, 100);
    }

}
