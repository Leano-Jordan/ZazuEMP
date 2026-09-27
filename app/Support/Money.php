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
        $negative = $cents < 0;
        $absolute = abs($cents);
        $whole = intdiv($absolute, 100);
        $fraction = str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);

        return ($negative ? '-' : '') . $whole . '.' . $fraction;
    }

    public static function formatCents(int $cents): string
    {
        $value = self::fromCents($cents);
        [$whole, $fraction] = explode('.', $value, 2);
        $negative = str_starts_with($whole, '-');
        $whole = ltrim($whole, '-');

        $formattedWhole = number_format((int) $whole, 0, '.', ',');

        return ($negative ? '-' : '') . $formattedWhole . '.' . $fraction;
    }
}
