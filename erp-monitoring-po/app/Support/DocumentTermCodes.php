<?php

namespace App\Support;

class DocumentTermCodes
{
  public const GROUP_PO_STATUS = 'po_status';
  public const GROUP_PO_ITEM_STATUS = 'po_item_status';
  public const GROUP_SHIPMENT_STATUS = 'shipment_status';
  public const GROUP_GOODS_RECEIPT_STATUS = 'goods_receipt_status';
  public const GROUP_PO_HISTORY_NOTE = 'po_history_note';

  // PO header statuses
  public const PO_ISSUED = 'PO Issued';
  public const PO_OPEN = 'Open';
  public const PO_LATE = 'Late';
  public const PO_CLOSED = 'Closed';
  public const PO_CANCELLED = 'Cancelled';

  // PO item statuses
  public const ITEM_WAITING = 'Waiting';
  public const ITEM_CONFIRMED = 'Confirmed';
  public const ITEM_LATE = 'Late';
  public const ITEM_PARTIAL = 'Partial';
  public const ITEM_CLOSED = 'Closed';
  public const ITEM_FORCE_CLOSED = 'Force Closed';
  public const ITEM_CANCELLED = 'Cancelled';

  // Shipment statuses
  public const SHIPMENT_DRAFT = 'Draft';
  public const SHIPMENT_SHIPPED = 'Shipped';
  public const SHIPMENT_PARTIAL_RECEIVED = 'Partial Received';
  public const SHIPMENT_RECEIVED = 'Received';
  public const SHIPMENT_CANCELLED = 'Cancelled';

  // Goods receipt statuses
  public const GR_POSTED = 'Posted';
  public const GR_CANCELLED = 'Cancelled';

  // Notes
  public const NOTE_RELEASED_NEW_PO = 'released_new_po';

  public static function poStatuses(): array
  {
    return [
      self::PO_ISSUED,
      self::PO_OPEN,
      self::PO_LATE,
      self::PO_CLOSED,
      self::PO_CANCELLED,
    ];
  }

  public static function poItemStatuses(): array
  {
    return [
      self::ITEM_WAITING,
      self::ITEM_CONFIRMED,
      self::ITEM_LATE,
      self::ITEM_PARTIAL,
      self::ITEM_CLOSED,
      self::ITEM_FORCE_CLOSED,
      self::ITEM_CANCELLED,
    ];
  }

  public static function shipmentStatuses(): array
  {
    return [
      self::SHIPMENT_DRAFT,
      self::SHIPMENT_SHIPPED,
      self::SHIPMENT_PARTIAL_RECEIVED,
      self::SHIPMENT_RECEIVED,
      self::SHIPMENT_CANCELLED,
    ];
  }

  public static function goodsReceiptStatuses(): array
  {
    return [
      self::GR_POSTED,
      self::GR_CANCELLED,
    ];
  }
}
