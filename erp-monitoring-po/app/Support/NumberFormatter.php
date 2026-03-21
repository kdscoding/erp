<?php

namespace App\Support;

class NumberFormatter
{
    public static function trim(float|int|string|null $value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $formatted = number_format((float) $value, $decimals, ',', '.');

        return rtrim(rtrim($formatted, '0'), ',');
    }
}
