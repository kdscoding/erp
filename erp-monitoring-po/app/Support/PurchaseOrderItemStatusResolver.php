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

    public static function statusHelpText(string $status): ?string
    {
        return match ($status) {
            DocumentTermCodes::ITEM_WAITING => 'Belum ada konfirmasi ETD dari supplier.',
            DocumentTermCodes::ITEM_CONFIRMED => 'Sudah dikonfirmasi, menunggu pengiriman atau receiving.',
            DocumentTermCodes::ITEM_LATE => 'ETD lewat, item belum selesai diterima.',
            DocumentTermCodes::ITEM_PARTIAL => 'Sudah diterima sebagian, outstanding masih tersisa.',
            DocumentTermCodes::ITEM_CLOSED => 'Item selesai. Seluruh qty PO sudah diterima.',
            DocumentTermCodes::ITEM_FORCE_CLOSED => 'Item ditutup paksa. Outstanding dihentikan secara manual.',
            DocumentTermCodes::ITEM_CANCELLED => 'Item dibatalkan.',
            default => null,
        };
    }
}
