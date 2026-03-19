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
        $hasReceived = DB::table('purchase_order_items')->where('purchase_order_id', $poId)->where('received_qty', '>', 0)->exists();
        $hasOutstanding = DB::table('purchase_order_items')->where('purchase_order_id', $poId)->where('outstanding_qty', '>', 0)->exists();

        $newStatus = $hasOutstanding ? ($hasReceived ? 'Partial Received' : 'Shipped') : 'Closed';
        $oldStatus = DB::table('purchase_orders')->where('id', $poId)->value('status');

        if ($oldStatus !== $newStatus) {
            DB::table('purchase_orders')->where('id', $poId)->update([
                'status' => $newStatus,
                'updated_at' => now(),
                'updated_by' => $userId,
            ]);
            self::pushPoStatus($poId, $oldStatus, $newStatus, $userId, 'Status auto-update dari transaksi receiving.');
        }

        return $newStatus;
    }
}
