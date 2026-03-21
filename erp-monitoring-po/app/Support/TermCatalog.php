<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TermCatalog
{
    private static array $labels = [];
    private static array $groups = [];

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

    public static function options(string $groupKey, array $fallbackCodes = []): array
    {
        if (! Schema::hasTable('document_terms')) {
            return collect($fallbackCodes)->mapWithKeys(fn ($code) => [$code => $code])->all();
        }

        if (! array_key_exists($groupKey, self::$groups)) {
            self::$groups[$groupKey] = DB::table('document_terms')
                ->where('group_key', $groupKey)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['code', 'label'])
                ->mapWithKeys(fn ($term) => [$term->code => $term->label])
                ->all();
        }

        $options = self::$groups[$groupKey];
        if (! empty($options)) {
            return $options;
        }

        return collect($fallbackCodes)->mapWithKeys(fn ($code) => [$code => $code])->all();
    }

    public static function groupedTerms(): array
    {
        if (! Schema::hasTable('document_terms')) {
            return [];
        }

        return DB::table('document_terms')
            ->orderBy('group_key')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('group_key')
            ->map(fn ($group) => $group->values())
            ->all();
    }
}
