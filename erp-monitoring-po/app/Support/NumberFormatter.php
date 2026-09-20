<?php

namespace App\Support;

class NumberFormatter
{
    public static function parseFlexible(float|int|string|null $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            $number = (float) $value;

            return is_finite($number) ? $number : null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = str_replace(["\xC2\xA0", ' '], '', $value);
        $value = trim((string) preg_replace('/^(?:Rp|IDR)\s*/i', '', $value));
        $value = trim((string) preg_replace('/\s*(?:Rp|IDR)$/i', '', $value));

        $hasComma = str_contains($value, ',');
        $hasDot = str_contains($value, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($value, ',');
            $lastDot = strrpos($value, '.');
            $decimalSeparator = $lastComma > $lastDot ? ',' : '.';
            $thousandsSeparator = $decimalSeparator === ',' ? '.' : ',';
            $value = str_replace($thousandsSeparator, '', $value);
            $value = str_replace($decimalSeparator, '.', $value);
        } elseif ($hasComma || $hasDot) {
            $separator = $hasComma ? ',' : '.';
            $parts = explode($separator, $value);
            $lastPart = array_key_last($parts);
            $fractionLength = strlen($parts[$lastPart]);

            if ($fractionLength === 3 && $parts[0] !== '0' && (count($parts) === 2 || self::hasValidThousandsGroups($parts))) {
                $value = implode('', $parts);
            } else {
                $integerParts = array_slice($parts, 0, -1);

                if (! self::hasValidThousandsGroups($integerParts)) {
                    return null;
                }

                $value = implode('', $integerParts).'.'. $parts[$lastPart];
            }
        }

        if (! preg_match('/^[+-]?\d+(\.\d+)?$/', $value)) {
            return null;
        }

        $number = (float) $value;

        return is_finite($number) ? $number : null;
    }

    private static function hasValidThousandsGroups(array $parts): bool
    {
        if ($parts === []) {
            return false;
        }

        $firstPart = array_shift($parts);

        return $firstPart !== null
            && preg_match('/^\d{1,3}$/', $firstPart) === 1
            && array_reduce($parts, fn (bool $valid, string $part): bool => $valid && preg_match('/^\d{3}$/', $part) === 1, true);
    }
    public static function trim(float|int|string|null $value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $formatted = number_format((float) $value, $decimals, ',', '.');

        return rtrim(rtrim($formatted, '0'), ',');
    }

    public static function input(float|int|string|null $value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $formatted = number_format((float) $value, $decimals, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }
}
