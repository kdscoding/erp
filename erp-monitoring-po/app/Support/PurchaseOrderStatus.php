<?php

namespace App\Support;

class PurchaseOrderStatus
{
  public const OPEN = 'Open';
  public const LATE = 'Late';
  public const CLOSED = 'Closed';
  public const CANCELLED = 'Cancelled';

  /**
   * @return array<int, string>
   */
  public static function all(): array
  {
    return [
      self::OPEN,
      self::LATE,
      self::CLOSED,
      self::CANCELLED,
    ];
  }
}
