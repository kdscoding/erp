<?php

namespace App\Support;

class DomainStatus
{
    public const GROUP_PO_STATUS = 'po_status';
    public const GROUP_PO_ITEM_STATUS = 'po_item_status';
    public const GROUP_SHIPMENT_STATUS = 'shipment_status';
    public const GROUP_GOODS_RECEIPT_STATUS = 'goods_receipt_status';

    public const PO_ISSUED = 'po_issued';
    public const PO_OPEN = 'po_open';
    public const PO_LATE = 'po_late';
    public const PO_CLOSED = 'po_closed';
    public const PO_CANCELLED = 'po_cancelled';

    public const ITEM_WAITING = 'item_waiting';
    public const ITEM_CONFIRMED = 'item_confirmed';
    public const ITEM_LATE = 'item_late';
    public const ITEM_PARTIAL = 'item_partial';
    public const ITEM_CLOSED = 'item_closed';
    public const ITEM_FORCE_CLOSED = 'item_force_closed';
    public const ITEM_CANCELLED = 'item_cancelled';

    public const SHIPMENT_DRAFT = 'shipment_draft';
    public const SHIPMENT_SHIPPED = 'shipment_shipped';
    public const SHIPMENT_PARTIAL_RECEIVED = 'shipment_partial_received';
    public const SHIPMENT_RECEIVED = 'shipment_received';
    public const SHIPMENT_CANCELLED = 'shipment_cancelled';

    public const GR_POSTED = 'gr_posted';
    public const GR_CANCELLED = 'gr_cancelled';

    private const LEGACY_MAP = [
        self::GROUP_PO_STATUS => [
            self::PO_ISSUED => 'PO Issued',
            self::PO_OPEN => 'Open',
            self::PO_LATE => 'Late',
            self::PO_CLOSED => 'Closed',
            self::PO_CANCELLED => 'Cancelled',
        ],
        self::GROUP_PO_ITEM_STATUS => [
            self::ITEM_WAITING => 'Waiting',
            self::ITEM_CONFIRMED => 'Confirmed',
            self::ITEM_LATE => 'Late',
            self::ITEM_PARTIAL => 'Partial',
            self::ITEM_CLOSED => 'Closed',
            self::ITEM_FORCE_CLOSED => 'Force Closed',
            self::ITEM_CANCELLED => 'Cancelled',
        ],
        self::GROUP_SHIPMENT_STATUS => [
            self::SHIPMENT_DRAFT => 'Draft',
            self::SHIPMENT_SHIPPED => 'Shipped',
            self::SHIPMENT_PARTIAL_RECEIVED => 'Partial Received',
            self::SHIPMENT_RECEIVED => 'Received',
            self::SHIPMENT_CANCELLED => 'Cancelled',
        ],
        self::GROUP_GOODS_RECEIPT_STATUS => [
            self::GR_POSTED => 'Posted',
            self::GR_CANCELLED => 'Cancelled',
        ],
    ];

    public static function internalCode(string $groupKey, ?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $map = self::LEGACY_MAP[$groupKey] ?? [];

        if (array_key_exists($value, $map)) {
            return $value;
        }

        foreach ($map as $internalCode => $legacyValue) {
            if ($legacyValue === $value) {
                return $internalCode;
            }
        }

        return $value;
    }

    public static function legacyValue(string $groupKey, ?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $map = self::LEGACY_MAP[$groupKey] ?? [];

        if (array_key_exists($value, $map)) {
            return $map[$value];
        }

        return $value;
    }

    public static function internalOptions(string $groupKey): array
    {
        return array_keys(self::LEGACY_MAP[$groupKey] ?? []);
    }

    public static function legacyOptions(string $groupKey): array
    {
        return array_values(self::LEGACY_MAP[$groupKey] ?? []);
    }

    public static function pairs(string $groupKey): array
    {
        return self::LEGACY_MAP[$groupKey] ?? [];
    }

    public static function payload(string $groupKey, string $column, ?string $value): array
    {
        return [
            $column => self::legacyValue($groupKey, $value),
            $column . '_code' => self::internalCode($groupKey, $value),
        ];
    }
}
