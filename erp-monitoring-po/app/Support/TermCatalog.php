<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TermCatalog
{
    private static array $labels = [];

    public static function label(string $groupKey, ?string $code, ?string $fallback = null): string
    {
        if ($code === null || $code === '') {
            return $fallback ?? '-';
        }

        if (! Schema::hasTable('document_terms')) {
            return $fallback ?? $code;
        }

        $cacheKey = $groupKey.'|'.$code;
        if (! array_key_exists($cacheKey, self::$labels)) {
            self::$labels[$cacheKey] = DB::table('document_terms')
                ->where('group_key', $groupKey)
                ->where('code', $code)
                ->value('label');
        }

        return self::$labels[$cacheKey] ?: ($fallback ?? $code);
    }
}
