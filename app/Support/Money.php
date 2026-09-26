<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    public static function toCents(int|float|string $amount): int
    {
        return self::toHundredths($amount);
    }

    public static function toHundredths(int|float|string $amount): int
    {
        $value = trim((string) $amount);

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/D', $value)) {
            throw new InvalidArgumentException('Amount must contain at most two decimal places.');
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    public static function multiplyQuantityByPrice(int $quantityHundredths, int $unitPriceCents): int
    {
        return intdiv(($quantityHundredths * $unitPriceCents) + 50, 100);
    }

    public static function fromCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
