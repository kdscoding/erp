<?php

namespace App\Support;

class PurchaseOrderItemStatusResolver
{
    public function resolve(float $receivedQty, float $outstandingQty, ?string $etdDate): string
    {
        if ($outstandingQty <= 0) {
            return DocumentTermCodes::ITEM_CLOSED;
        }

        if ($receivedQty > 0) {
            return DocumentTermCodes::ITEM_PARTIAL;
        }

        return $etdDate
            ? DocumentTermCodes::ITEM_CONFIRMED
            : DocumentTermCodes::ITEM_WAITING;
    }
}
