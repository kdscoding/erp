<?php

namespace App\Support;

class PurchaseOrderStatus
{
  public const PO_ISSUED = 'PO Issued';
  public const OPEN = 'Open';
  public const LATE = 'Late';
  public const CLOSED = 'Closed';
  public const CANCELLED = 'Cancelled';

  public static function all(): array
  {
    return [
      self::PO_ISSUED,
      self::OPEN,
      self::LATE,
      self::CLOSED,
      self::CANCELLED,
    ];
  }
}
