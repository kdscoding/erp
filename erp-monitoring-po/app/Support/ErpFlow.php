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
            ->selectRaw("SUM(CASE WHEN item_status = 'Cancelled' THEN 1 ELSE 0 END) cancelled_items")
            ->selectRaw('SUM(CASE WHEN outstanding_qty > 0 THEN 1 ELSE 0 END) outstanding_items')
            ->selectRaw('SUM(CASE WHEN received_qty > 0 THEN 1 ELSE 0 END) received_items')
            ->first();

        $oldStatus = DB::table('purchase_orders')->where('id', $poId)->value('status');

        $newStatus = 'PO Issued';
        if ((int) ($summary->total_items ?? 0) > 0 && (int) ($summary->cancelled_items ?? 0) === (int) ($summary->total_items ?? 0)) {
            $newStatus = 'Cancelled';
        } elseif ((int) ($summary->outstanding_items ?? 0) === 0) {
            $newStatus = 'Closed';
        } elseif ((int) ($summary->received_items ?? 0) > 0) {
            $newStatus = 'Partial';
        } elseif (DB::table('purchase_order_items')->where('purchase_order_id', $poId)->whereNotNull('etd_date')->exists()) {
            $newStatus = 'Confirmed';
        }

        if ($oldStatus !== $newStatus) {
            DB::table('purchase_orders')->where('id', $poId)->update([
                'status' => $newStatus,
                'updated_at' => now(),
                'updated_by' => $userId,
            ]);
            self::pushPoStatus($poId, $oldStatus, $newStatus, $userId, 'Status auto-update berdasarkan outstanding item.');
        }

        return $newStatus;
    }
}
