<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class ErpFlow
{
    public static function generateNumber(string $prefix, string $table, string $column): string
    {
        $date = now()->format('Ymd');
        $last = DB::table($table)
            ->whereDate('created_at', now()->toDateString())
            ->where($column, 'like', $prefix.'-'.$date.'-%')
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
        $summary = DB::table('purchase_order_items')
            ->where('purchase_order_id', $poId)
            ->selectRaw('COUNT(*) total_items')
            ->selectRaw("SUM(CASE WHEN COALESCE(item_status, '') = 'Cancelled' THEN 1 ELSE 0 END) cancelled_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(item_status, '') != 'Cancelled' THEN 1 ELSE 0 END) active_items")
            ->selectRaw('SUM(CASE WHEN outstanding_qty > 0 THEN 1 ELSE 0 END) outstanding_items')
            ->selectRaw('SUM(CASE WHEN received_qty > 0 THEN 1 ELSE 0 END) received_items')
            ->selectRaw("SUM(CASE WHEN COALESCE(item_status, '') != 'Cancelled' AND outstanding_qty > 0 AND etd_date IS NOT NULL THEN 1 ELSE 0 END) confirmed_open_items")
            ->first();

        $shipmentCoverage = DB::table('purchase_order_items as poi')
            ->where('poi.purchase_order_id', $poId)
            ->whereRaw("COALESCE(poi.item_status, '') != 'Cancelled'")
            ->where('poi.outstanding_qty', '>', 0)
            ->selectRaw('COUNT(*) as open_item_count')
            ->selectRaw('SUM(CASE WHEN COALESCE((
                SELECT SUM(si2.shipped_qty)
                FROM shipment_items si2
                INNER JOIN shipments sh2 ON sh2.id = si2.shipment_id
                WHERE si2.purchase_order_item_id = poi.id
                  AND sh2.status IN (\'Shipped\', \'Partial Received\', \'Received\')
            ), 0) >= poi.outstanding_qty THEN 1 ELSE 0 END) as fully_allocated_open_items')
            ->first();

        $oldStatus = DB::table('purchase_orders')->where('id', $poId)->value('status');
        $nextEtaDate = self::resolvePoEtaDate($poId);

        $newStatus = 'PO Issued';
        if ((int) ($summary->total_items ?? 0) > 0 && (int) ($summary->cancelled_items ?? 0) === (int) ($summary->total_items ?? 0)) {
            $newStatus = 'Cancelled';
        } elseif ((int) ($summary->active_items ?? 0) > 0 && (int) ($summary->outstanding_items ?? 0) === 0) {
            $newStatus = 'Closed';
        } elseif ((int) ($summary->received_items ?? 0) > 0) {
            $newStatus = 'Partial';
        } elseif (
            (int) ($shipmentCoverage->open_item_count ?? 0) > 0 &&
            (int) ($shipmentCoverage->fully_allocated_open_items ?? 0) === (int) ($shipmentCoverage->open_item_count ?? 0)
        ) {
            $newStatus = 'Shipped';
        } elseif ((int) ($summary->confirmed_open_items ?? 0) > 0) {
            $newStatus = 'Confirmed';
        }

        if ($oldStatus !== $newStatus) {
            DB::table('purchase_orders')->where('id', $poId)->update([
                'status' => $newStatus,
                'eta_date' => $nextEtaDate,
                'updated_at' => now(),
                'updated_by' => $userId,
            ]);
            self::pushPoStatus($poId, $oldStatus, $newStatus, $userId, 'Status auto-update berdasarkan outstanding item.');
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
            ->whereRaw("COALESCE(item_status, '') != 'Cancelled'")
            ->where('outstanding_qty', '>', 0)
            ->selectRaw('MIN(COALESCE(eta_date, etd_date)) as next_eta_date')
            ->value('next_eta_date');
    }
}
