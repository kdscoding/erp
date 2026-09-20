<?php

namespace App\Support;

class PurchaseOrderItemStatus
{
  public const WAITING = 'Waiting';
  public const CONFIRMED = 'Confirmed';
  public const PARTIAL = 'Partial';
  public const CLOSED = 'Closed';
  public const CANCELLED = 'Cancelled';
  public const LATE = 'Late';

  public static function all(): array
  {
    return [
      self::WAITING,
      self::CONFIRMED,
      self::PARTIAL,
      self::CLOSED,
      self::CANCELLED,
      self::LATE,
    ];
  }
}
