<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = DB::table('suppliers')->pluck('id')->all();
        $items = DB::table('items')->limit(50)->pluck('id')->all();
        $warehouseId = DB::table('warehouses')->value('id');
        $plantId = DB::table('plants')->value('id');

        if (empty($suppliers) || empty($items)) {
            return;
        }

        $statuses = ['Approved', 'Shipped', 'Partial Received', 'Closed', 'Submitted'];

        for ($poNo = 1; $poNo <= 20; $poNo++) {
            $status = $statuses[$poNo % count($statuses)];
            $poId = DB::table('purchase_orders')->insertGetId([
                'po_number' => sprintf('PO-DEMO-%04d', $poNo),
                'po_date' => now()->subDays(rand(1, 45))->toDateString(),
                'supplier_id' => $suppliers[array_rand($suppliers)],
                'plant_id' => $plantId,
                'warehouse_id' => $warehouseId,
                'currency' => 'IDR',
                'status' => $status,
                'eta_date' => now()->subDays(rand(-10, 10))->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            for ($line = 1; $line <= 3; $line++) {
                $orderedQty = rand(100, 500);
                $receivedQty = $status === 'Closed' ? $orderedQty : ($status === 'Partial Received' ? rand(10, $orderedQty - 1) : 0);
                $outstanding = $orderedQty - $receivedQty;

                $poItemId = DB::table('purchase_order_items')->insertGetId([
                    'purchase_order_id' => $poId,
                    'item_id' => $items[array_rand($items)],
                    'ordered_qty' => $orderedQty,
                    'received_qty' => $receivedQty,
                    'outstanding_qty' => $outstanding,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($receivedQty > 0 && DB::table('goods_receipts')->count() < 10) {
                    $grId = DB::table('goods_receipts')->insertGetId([
                        'gr_number' => sprintf('GR-DEMO-%04d', DB::table('goods_receipts')->count() + 1),
                        'receipt_date' => now()->subDays(rand(0, 10))->toDateString(),
                        'purchase_order_id' => $poId,
                        'warehouse_id' => $warehouseId,
                        'document_number' => 'SJ-'.rand(1000, 9999),
                        'status' => 'Posted',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('goods_receipt_items')->insert([
                        'goods_receipt_id' => $grId,
                        'purchase_order_item_id' => $poItemId,
                        'item_id' => DB::table('purchase_order_items')->where('id', $poItemId)->value('item_id'),
                        'received_qty' => $receivedQty,
                        'accepted_qty' => $receivedQty,
                        'rejected_qty' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
