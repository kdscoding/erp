<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class ErpFlow
{
    public const PO_STATUS_FULL = 'Full';
    public const PO_STATUS_PARTIAL = 'Partial';
    public const PO_STATUS_DELAYED = 'Delayed';
    public const PO_STATUS_CANCELLED = 'Cancelled';

    public static function currentDateExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "date('now')"
            : 'CURDATE()';
    }

    public static function generateNumber(string $prefix, string $table, string $column): string
    {
        $date = now()->format('Ymd');
        $last = DB::table($table)
            ->whereDate('created_at', now()->toDateString())
            ->where($column, 'like', $prefix . '-' . $date . '-%')
            ->orderByDesc('id')
            ->value($column);

        $next = 1;
        if ($last) {
            $parts = explode('-', (string) $last);
            $next = ((int) end($parts)) + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $date, $next);
    }

    public static function pushPoStatus(int $poId, ?string $from, string $to, ?int $userId = null, ?string $note = null): void
    {
        DB::table('po_status_histories')->insert([
            'purchase_order_id' => $poId,
            'from_status' => $from,
            'from_status_code' => DomainStatus::internalCode(DomainStatus::GROUP_PO_STATUS, $from),
            'to_status' => $to,
            'to_status_code' => DomainStatus::internalCode(DomainStatus::GROUP_PO_STATUS, $to),
            'changed_by' => $userId,
            'changed_at' => now(),
            'note' => $note,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function audit(string $module, ?int $recordId, string $action, mixed $oldValues, mixed $newValues, ?int $userId = null, ?string $ip = null): void
    {
        DB::table('audit_logs')->insert([
            'module' => $module,
            'record_id' => $recordId,
            'action' => $action,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'user_id' => $userId,
            'ip_address' => $ip,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function refreshPoStatusByOutstanding(int $poId, ?int $userId = null): string
    {
        $currentDate = now()->toDateString();

        $summary = DB::table('purchase_order_items')
            ->where('purchase_order_id', $poId)
            ->whereRaw("COALESCE(item_status, '') != '" . DocumentTermCodes::ITEM_CANCELLED . "'")
            ->selectRaw('COUNT(*) as total_items')
            ->selectRaw('SUM(CASE WHEN received_qty >= ordered_qty AND ordered_qty > 0 THEN 1 ELSE 0 END) as full_items')
            ->selectRaw('SUM(CASE WHEN received_qty > 0 AND received_qty < ordered_qty THEN 1 ELSE 0 END) as partial_items')
            ->selectRaw('SUM(CASE WHEN received_qty = 0 AND ordered_qty > 0 THEN 1 ELSE 0 END) as pending_items')
            ->first();

        $oldStatus = DB::table('purchase_orders')->where('id', $poId)->value('status');
        $etaDate = DB::table('purchase_orders')->where('id', $poId)->value('eta_date');

        $totalItems = (int) ($summary->total_items ?? 0);
        $fullItems = (int) ($summary->full_items ?? 0);
        $partialItems = (int) ($summary->partial_items ?? 0);
        $pendingItems = (int) ($summary->pending_items ?? 0);

        $newStatus = self::PO_STATUS_FULL;

        if ($totalItems === 0) {
            $newStatus = self::PO_STATUS_FULL;
        } elseif ($fullItems === $totalItems) {
            $newStatus = self::PO_STATUS_FULL;
        } elseif ($partialItems > 0 || ($fullItems > 0 && $pendingItems > 0)) {
            $newStatus = self::PO_STATUS_PARTIAL;
        } elseif ($pendingItems === $totalItems) {
            if ($etaDate && $etaDate < $currentDate) {
                $newStatus = self::PO_STATUS_DELAYED;
            } else {
                $newStatus = self::PO_STATUS_PARTIAL;
            }
        }

        $po = DB::table('purchase_orders')->where('id', $poId)->first();
        if ($po && $po->status === self::PO_STATUS_CANCELLED) {
            $newStatus = self::PO_STATUS_CANCELLED;
        }

        if ($oldStatus !== $newStatus) {
            DB::table('purchase_orders')->where('id', $poId)->update([
                'status' => $newStatus,
                'updated_at' => now(),
                'updated_by' => $userId,
            ]);

            self::pushPoStatus(
                $poId,
                $oldStatus,
                $newStatus,
                $userId,
                'Status auto-update: Full/Partial/Delayed based on receipt & ETA.'
            );
        }

        return $newStatus;
    }

    public static function refreshAllPoStatuses(): int
    {
        $poIds = DB::table('purchase_orders')
            ->where('status', '!=', self::PO_STATUS_CANCELLED)
            ->pluck('id');

        $updated = 0;
        foreach ($poIds as $poId) {
            $oldStatus = DB::table('purchase_orders')->where('id', $poId)->value('status');
            self::refreshPoStatusByOutstanding($poId);
            $newStatus = DB::table('purchase_orders')->where('id', $poId)->value('status');
            if ($oldStatus !== $newStatus) {
                $updated++;
            }
        }

        return $updated;
    }

    public static function resolvePoEtaDate(int $poId): ?string
    {
        return DB::table('purchase_order_items')
            ->where('purchase_order_id', $poId)
            ->whereRaw("COALESCE(item_status, '') != '" . DocumentTermCodes::ITEM_CANCELLED . "'")
            ->where('outstanding_qty', '>', 0)
            ->selectRaw('MIN(COALESCE(eta_date, etd_date)) as next_eta_date')
            ->value('next_eta_date');
    }
}
