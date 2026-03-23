<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class ErpFlow
{
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
            'to_status' => $to,
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
        $currentDateSql = self::currentDateExpression();

        $summary = DB::table('purchase_order_items')
            ->where('purchase_order_id', $poId)
            ->selectRaw('COUNT(*) total_items')
            ->selectRaw("SUM(CASE WHEN COALESCE(item_status, '') = '" . DocumentTermCodes::ITEM_CANCELLED . "' THEN 1 ELSE 0 END) cancelled_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(item_status, '') != '" . DocumentTermCodes::ITEM_CANCELLED . "' THEN 1 ELSE 0 END) active_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(item_status, '') != '" . DocumentTermCodes::ITEM_CANCELLED . "' AND received_qty = 0 AND outstanding_qty > 0 AND etd_date IS NULL THEN 1 ELSE 0 END) pure_waiting_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(item_status, '') != '" . DocumentTermCodes::ITEM_CANCELLED . "' AND received_qty = 0 AND outstanding_qty > 0 AND etd_date IS NOT NULL THEN 1 ELSE 0 END) items_with_etd")
            ->selectRaw("SUM(CASE WHEN COALESCE(item_status, '') != '" . DocumentTermCodes::ITEM_CANCELLED . "' AND received_qty = 0 AND outstanding_qty > 0 AND etd_date IS NOT NULL AND DATE(etd_date) < {$currentDateSql} THEN 1 ELSE 0 END) overdue_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(item_status, '') != '" . DocumentTermCodes::ITEM_CANCELLED . "' AND received_qty > 0 AND outstanding_qty > 0 THEN 1 ELSE 0 END) partial_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(item_status, '') != '" . DocumentTermCodes::ITEM_CANCELLED . "' AND outstanding_qty <= 0 THEN 1 ELSE 0 END) closed_items")
            ->first();

        $allocationSummary = DB::table('purchase_order_items as poi')
            ->leftJoin('shipment_items as si', 'si.purchase_order_item_id', '=', 'poi.id')
            ->leftJoin('shipments as sh', function ($join) {
                $join->on('sh.id', '=', 'si.shipment_id')
                    ->where('sh.status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED);
            })
            ->where('poi.purchase_order_id', $poId)
            ->whereRaw("COALESCE(poi.item_status, '') != '" . DocumentTermCodes::ITEM_CANCELLED . "'")
            ->selectRaw('COUNT(DISTINCT CASE WHEN sh.id IS NOT NULL THEN poi.id END) as allocated_items')
            ->first();

        $oldStatus = DB::table('purchase_orders')->where('id', $poId)->value('status');
        $nextEtaDate = self::resolvePoEtaDate($poId);

        $totalItems = (int) ($summary->total_items ?? 0);
        $cancelledItems = (int) ($summary->cancelled_items ?? 0);
        $activeItems = (int) ($summary->active_items ?? 0);
        $pureWaitingItems = (int) ($summary->pure_waiting_items ?? 0);
        $itemsWithEtd = (int) ($summary->items_with_etd ?? 0);
        $overdueItems = (int) ($summary->overdue_items ?? 0);
        $partialItems = (int) ($summary->partial_items ?? 0);
        $closedItems = (int) ($summary->closed_items ?? 0);
        $allocatedItems = (int) ($allocationSummary->allocated_items ?? 0);

        $newStatus = DocumentTermCodes::PO_ISSUED;

        if ($totalItems > 0 && $cancelledItems === $totalItems) {
            $newStatus = DocumentTermCodes::PO_CANCELLED;
        } elseif ($activeItems > 0 && $closedItems === $activeItems) {
            $newStatus = DocumentTermCodes::PO_CLOSED;
        } elseif ($overdueItems > 0) {
            $newStatus = DocumentTermCodes::PO_LATE;
        } elseif (
            $activeItems > 0 &&
            (
                $itemsWithEtd > 0 ||
                $allocatedItems > 0 ||
                $partialItems > 0 ||
                $closedItems > 0
            )
        ) {
            $newStatus = DocumentTermCodes::PO_OPEN;
        } elseif ($activeItems > 0 && $pureWaitingItems === $activeItems) {
            $newStatus = DocumentTermCodes::PO_ISSUED;
        }

        if ($oldStatus !== $newStatus) {
            DB::table('purchase_orders')->where('id', $poId)->update([
                'status' => $newStatus,
                'eta_date' => $nextEtaDate,
                'updated_at' => now(),
                'updated_by' => $userId,
            ]);

            self::pushPoStatus(
                $poId,
                $oldStatus,
                $newStatus,
                $userId,
                'Status auto-update berdasarkan model monitoring header PO.'
            );
        } else {
            DB::table('purchase_orders')->where('id', $poId)->update([
                'eta_date' => $nextEtaDate,
                'updated_at' => now(),
                'updated_by' => $userId,
            ]);
        }

        return $newStatus;
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
