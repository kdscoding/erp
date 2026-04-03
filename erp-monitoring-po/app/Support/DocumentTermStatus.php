<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DocumentTermStatus
{
  private static array $badgeMeta = [];

  public static function internalCode(string $groupKey, ?string $code): ?string
  {
    return DomainStatus::internalCode($groupKey, $code);
  }

  public static function legacyValue(string $groupKey, ?string $code): ?string
  {
    return DomainStatus::legacyValue($groupKey, $code);
  }

  public static function label(string $groupKey, ?string $code, ?string $fallback = null): string
  {
    $legacyCode = DomainStatus::legacyValue($groupKey, $code);
    $legacyFallback = $fallback !== null ? DomainStatus::legacyValue($groupKey, $fallback) : $fallback;

    return TermCatalog::label($groupKey, $legacyCode, $legacyFallback);
  }

  public static function options(string $groupKey, array $fallbackCodes = []): array
  {
    $legacyFallbackCodes = collect($fallbackCodes)
      ->map(fn ($code) => DomainStatus::legacyValue($groupKey, $code))
      ->all();

    return TermCatalog::options($groupKey, $legacyFallbackCodes);
  }

  public static function isAllowed(string $groupKey, ?string $code, array $fallbackCodes = []): bool
  {
    if ($code === null || $code === '') {
      return false;
    }

    $legacyCode = DomainStatus::legacyValue($groupKey, $code);

    return array_key_exists($legacyCode, self::options($groupKey, $fallbackCodes));
  }

  public static function badgeClasses(string $groupKey, ?string $code, string $default = 'bg-secondary text-white'): string
  {
    if ($code === null || $code === '') {
      return $default;
    }

    $code = DomainStatus::legacyValue($groupKey, $code);

    if (! Schema::hasTable('document_terms')) {
      return $default;
    }

    $cacheKey = $groupKey . '|' . $code;
    if (! array_key_exists($cacheKey, self::$badgeMeta)) {
      self::$badgeMeta[$cacheKey] = DB::table('document_terms')
        ->where('group_key', $groupKey)
        ->where(function ($query) use ($code) {
          $query->where('code', $code);

          if (Schema::hasColumn('document_terms', 'internal_code')) {
            $query->orWhere('internal_code', $code);
          }
        })
        ->first(['badge_class', 'badge_text']);
    }

    $meta = self::$badgeMeta[$cacheKey];
    if (! $meta) {
      return $default;
    }

    $class = trim(implode(' ', array_filter([
      $meta->badge_class ?? null,
      $meta->badge_text ?? null,
    ])));

    return $class !== '' ? $class : $default;
  }

  public static function poStatusLabel(?string $code): string
  {
    return self::label(DocumentTermCodes::GROUP_PO_STATUS, $code, $code ?? '-');
  }

  public static function poItemStatusLabel(?string $code): string
  {
    return self::label(DocumentTermCodes::GROUP_PO_ITEM_STATUS, $code, $code ?? '-');
  }

  public static function shipmentStatusLabel(?string $code): string
  {
    return self::label(DocumentTermCodes::GROUP_SHIPMENT_STATUS, $code, $code ?? '-');
  }

  public static function goodsReceiptStatusLabel(?string $code): string
  {
    return self::label(DocumentTermCodes::GROUP_GOODS_RECEIPT_STATUS, $code, $code ?? '-');
  }
}
