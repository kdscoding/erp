<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;

class StatusQuery
{
    public static function whereEquals(Builder $query, string $column, string $groupKey, ?string $value): Builder
    {
        if ($value === null || $value === '') {
            return $query;
        }

        $legacyValue = DomainStatus::legacyValue($groupKey, $value);
        $internalCode = DomainStatus::internalCode($groupKey, $value);
        $codeColumn = self::codeColumn($column);

        return $query->where(function (Builder $inner) use ($column, $codeColumn, $legacyValue, $internalCode) {
            $inner->where($column, $legacyValue);

            if ($codeColumn !== null) {
                $inner->orWhere($codeColumn, $internalCode);
            }
        });
    }

    public static function whereNotEquals(Builder $query, string $column, string $groupKey, ?string $value): Builder
    {
        if ($value === null || $value === '') {
            return $query;
        }

        $legacyValue = DomainStatus::legacyValue($groupKey, $value);
        $internalCode = DomainStatus::internalCode($groupKey, $value);
        $codeColumn = self::codeColumn($column);

        return $query->where(function (Builder $inner) use ($column, $codeColumn, $legacyValue, $internalCode) {
            $inner->whereNull($column)->orWhere($column, '!=', $legacyValue);

            if ($codeColumn !== null) {
                $inner->where(function (Builder $codeQuery) use ($codeColumn, $internalCode) {
                    $codeQuery->whereNull($codeColumn)->orWhere($codeColumn, '!=', $internalCode);
                });
            }
        });
    }

    public static function whereIn(Builder $query, string $column, string $groupKey, array $values): Builder
    {
        if ($values === []) {
            return $query;
        }

        $legacyValues = array_values(array_unique(array_map(
            fn ($value) => DomainStatus::legacyValue($groupKey, $value),
            $values
        )));
        $internalCodes = array_values(array_unique(array_map(
            fn ($value) => DomainStatus::internalCode($groupKey, $value),
            $values
        )));
        $codeColumn = self::codeColumn($column);

        return $query->where(function (Builder $inner) use ($column, $codeColumn, $legacyValues, $internalCodes) {
            $inner->whereIn($column, $legacyValues);

            if ($codeColumn !== null) {
                $inner->orWhereIn($codeColumn, $internalCodes);
            }
        });
    }

    public static function whereNotIn(Builder $query, string $column, string $groupKey, array $values): Builder
    {
        if ($values === []) {
            return $query;
        }

        $legacyValues = array_values(array_unique(array_map(
            fn ($value) => DomainStatus::legacyValue($groupKey, $value),
            $values
        )));
        $internalCodes = array_values(array_unique(array_map(
            fn ($value) => DomainStatus::internalCode($groupKey, $value),
            $values
        )));
        $codeColumn = self::codeColumn($column);

        return $query->where(function (Builder $inner) use ($column, $codeColumn, $legacyValues, $internalCodes) {
            $inner->where(function (Builder $legacyQuery) use ($column, $legacyValues) {
                $legacyQuery->whereNull($column)->orWhereNotIn($column, $legacyValues);
            });

            if ($codeColumn !== null) {
                $inner->where(function (Builder $codeQuery) use ($codeColumn, $internalCodes) {
                    $codeQuery->whereNull($codeColumn)->orWhereNotIn($codeColumn, $internalCodes);
                });
            }
        });
    }

    public static function sqlEquals(string $column, string $groupKey, ?string $value): string
    {
        if ($value === null || $value === '') {
            return '1 = 0';
        }

        $legacyValue = self::quote(DomainStatus::legacyValue($groupKey, $value));
        $internalCode = self::quote(DomainStatus::internalCode($groupKey, $value));
        $codeColumn = self::codeColumn($column);

        if ($codeColumn === null) {
            return "{$column} = {$legacyValue}";
        }

        return "({$column} = {$legacyValue} OR {$codeColumn} = {$internalCode})";
    }

    public static function sqlNotEquals(string $column, string $groupKey, ?string $value): string
    {
        if ($value === null || $value === '') {
            return '1 = 1';
        }

        $legacyValue = self::quote(DomainStatus::legacyValue($groupKey, $value));
        $internalCode = self::quote(DomainStatus::internalCode($groupKey, $value));
        $codeColumn = self::codeColumn($column);

        if ($codeColumn === null) {
            return "COALESCE({$column}, '') != {$legacyValue}";
        }

        return "(COALESCE({$column}, '') != {$legacyValue} AND COALESCE({$codeColumn}, '') != {$internalCode})";
    }

    private static function codeColumn(string $column): ?string
    {
        $parts = explode('.', $column);
        $base = array_pop($parts);

        if ($base === null || str_ends_with($base, '_code')) {
            return null;
        }

        $parts[] = $base . '_code';

        return implode('.', $parts);
    }

    private static function quote(?string $value): string
    {
        return "'" . str_replace("'", "''", (string) $value) . "'";
    }
}
