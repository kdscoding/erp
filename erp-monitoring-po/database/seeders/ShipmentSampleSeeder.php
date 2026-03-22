<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShipmentSampleSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = DB::table('suppliers')->orderBy('id')->limit(10)->pluck('id')->all();
        $items = DB::table('items')->orderBy('id')->limit(20)->pluck('id')->all();
        $warehouseId = DB::table('warehouses')->value('id');
        $plantId = DB::table('plants')->value('id');
        $now = now();

        if (empty($suppliers) || empty($items) || ! $warehouseId || ! $plantId) {
            return;
        }

        DB::transaction(function () use ($suppliers, $items, $warehouseId, $plantId, $now) {
            $this->cleanupExistingDemoData();

            $samples = [
                ['suffix' => '0001', 'shipment_status' => 'Draft', 'po_status' => 'Open', 'ordered_qty' => 120, 'shipped_qty' => 120, 'received_qty' => 0, 'po_date_offset' => 18, 'shipment_date_offset' => 2, 'etd_offset' => 7],
                ['suffix' => '0002', 'shipment_status' => 'Draft', 'po_status' => 'Open', 'ordered_qty' => 85, 'shipped_qty' => 60, 'received_qty' => 0, 'po_date_offset' => 17, 'shipment_date_offset' => 1, 'etd_offset' => 5],
                ['suffix' => '0003', 'shipment_status' => 'Draft', 'po_status' => 'PO Issued', 'ordered_qty' => 150, 'shipped_qty' => 90, 'received_qty' => 0, 'po_date_offset' => 15, 'shipment_date_offset' => 0, 'etd_offset' => 10],
                ['suffix' => '0004', 'shipment_status' => 'Shipped', 'po_status' => 'Open', 'ordered_qty' => 200, 'shipped_qty' => 200, 'received_qty' => 0, 'po_date_offset' => 13, 'shipment_date_offset' => 3, 'etd_offset' => 2],
                ['suffix' => '0005', 'shipment_status' => 'Shipped', 'po_status' => 'Late', 'ordered_qty' => 140, 'shipped_qty' => 80, 'received_qty' => 0, 'po_date_offset' => 12, 'shipment_date_offset' => 2, 'etd_offset' => -1],
                ['suffix' => '0006', 'shipment_status' => 'Partial Received', 'po_status' => 'Late', 'ordered_qty' => 160, 'shipped_qty' => 160, 'received_qty' => 70, 'po_date_offset' => 10, 'shipment_date_offset' => 5, 'receipt_date_offset' => 3, 'etd_offset' => -2],
                ['suffix' => '0007', 'shipment_status' => 'Partial Received', 'po_status' => 'Late', 'ordered_qty' => 110, 'shipped_qty' => 90, 'received_qty' => 45, 'po_date_offset' => 9, 'shipment_date_offset' => 4, 'receipt_date_offset' => 1, 'etd_offset' => -1],
                ['suffix' => '0008', 'shipment_status' => 'Received', 'po_status' => 'Closed', 'ordered_qty' => 95, 'shipped_qty' => 95, 'received_qty' => 95, 'po_date_offset' => 8, 'shipment_date_offset' => 6, 'receipt_date_offset' => 2, 'etd_offset' => -3],
                ['suffix' => '0009', 'shipment_status' => 'Received', 'po_status' => 'Closed', 'ordered_qty' => 175, 'shipped_qty' => 175, 'received_qty' => 175, 'po_date_offset' => 7, 'shipment_date_offset' => 5, 'receipt_date_offset' => 1, 'etd_offset' => -4],
                ['suffix' => '0010', 'shipment_status' => 'Received', 'po_status' => 'Closed', 'ordered_qty' => 130, 'shipped_qty' => 100, 'received_qty' => 100, 'po_date_offset' => 6, 'shipment_date_offset' => 4, 'receipt_date_offset' => 0, 'etd_offset' => -2],
            ];

            foreach ($samples as $index => $sample) {
                $supplierId = $suppliers[$index % count($suppliers)];
                $itemId = $items[$index % count($items)];
                $orderedQty = (float) $sample['ordered_qty'];
                $receivedQty = (float) $sample['received_qty'];
                $remainingQty = max(0, $orderedQty - $receivedQty);

                $poId = DB::table('purchase_orders')->insertGetId([
                    'po_number' => 'PO-SHP-DEMO-'.$sample['suffix'],
                    'po_date' => now()->subDays($sample['po_date_offset'])->toDateString(),
                    'supplier_id' => $supplierId,
                    'plant_id' => $plantId,
                    'warehouse_id' => $warehouseId,
                    'currency' => 'IDR',
                    'status' => $sample['po_status'],
                    'eta_date' => now()->addDays($sample['etd_offset'] + 5)->toDateString(),
                    'notes' => 'Sample shipment demo '.$sample['suffix'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $poItemId = DB::table('purchase_order_items')->insertGetId([
                    'purchase_order_id' => $poId,
                    'item_id' => $itemId,
                    'ordered_qty' => $orderedQty,
                    'received_qty' => $receivedQty,
                    'outstanding_qty' => $remainingQty,
                    'item_status' => $receivedQty <= 0
                        ? match ($sample['po_status']) {
                            'PO Issued' => 'Waiting',
                            'Late' => 'Late',
                            default => 'Confirmed',
                        }
                        : ($remainingQty > 0 ? 'Partial' : 'Closed'),
                    'eta_date' => now()->addDays($sample['etd_offset'] + 5)->toDateString(),
                    'etd_date' => now()->addDays($sample['etd_offset'])->toDateString(),
                    'remarks' => 'Line sample '.$sample['suffix'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $shipmentId = DB::table('shipments')->insertGetId([
                    'purchase_order_id' => $poId,
                    'supplier_id' => $supplierId,
                    'shipment_number' => 'SHP-DEMO-'.$sample['suffix'],
                    'shipment_date' => now()->subDays($sample['shipment_date_offset'])->toDateString(),
                    'eta_date' => now()->addDays($sample['etd_offset'] + 7)->toDateString(),
                    'delivery_note_number' => 'SJ-DEMO-'.$sample['suffix'],
                    'supplier_remark' => 'Contoh shipment status '.$sample['shipment_status'],
                    'status' => $sample['shipment_status'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $shipmentItemId = DB::table('shipment_items')->insertGetId([
                    'shipment_id' => $shipmentId,
                    'purchase_order_item_id' => $poItemId,
                    'shipped_qty' => (float) $sample['shipped_qty'],
                    'received_qty' => $receivedQty,
                    'note' => 'Shipment item sample '.$sample['suffix'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($receivedQty > 0) {
                    $grId = DB::table('goods_receipts')->insertGetId([
                        'gr_number' => 'GR-SHP-DEMO-'.$sample['suffix'],
                        'receipt_date' => now()->subDays($sample['receipt_date_offset'])->toDateString(),
                        'purchase_order_id' => $poId,
                        'shipment_id' => $shipmentId,
                        'warehouse_id' => $warehouseId,
                        'document_number' => 'SJ-DEMO-'.$sample['suffix'],
                        'remark' => 'Receiving sample shipment '.$sample['suffix'],
                        'status' => 'Posted',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('goods_receipt_items')->insert([
                        'goods_receipt_id' => $grId,
                        'shipment_item_id' => $shipmentItemId,
                        'purchase_order_item_id' => $poItemId,
                        'item_id' => $itemId,
                        'received_qty' => $receivedQty,
                        'qty_variance' => (float) $sample['shipped_qty'] - $receivedQty,
                        'accepted_qty' => $receivedQty,
                        'rejected_qty' => 0,
                        'remark' => 'Receiving sample shipment '.$sample['suffix'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        });
    }

    private function cleanupExistingDemoData(): void
    {
        $shipmentIds = DB::table('shipments')
            ->where('shipment_number', 'like', 'SHP-DEMO-%')
            ->pluck('id');

        $poIds = DB::table('purchase_orders')
            ->where('po_number', 'like', 'PO-SHP-DEMO-%')
            ->pluck('id');

        $goodsReceiptIds = DB::table('goods_receipts')
            ->where(function ($query) use ($shipmentIds, $poIds) {
                $query->where('gr_number', 'like', 'GR-SHP-DEMO-%');

                if ($shipmentIds->isNotEmpty()) {
                    $query->orWhereIn('shipment_id', $shipmentIds);
                }

                if ($poIds->isNotEmpty()) {
                    $query->orWhereIn('purchase_order_id', $poIds);
                }
            })
            ->pluck('id');

        if ($goodsReceiptIds->isNotEmpty()) {
            DB::table('goods_receipt_items')->whereIn('goods_receipt_id', $goodsReceiptIds)->delete();
            DB::table('attachments')
                ->where('module', 'goods_receipts')
                ->whereIn('record_id', $goodsReceiptIds)
                ->delete();
            DB::table('goods_receipts')->whereIn('id', $goodsReceiptIds)->delete();
        }

        if ($shipmentIds->isNotEmpty()) {
            DB::table('shipment_items')->whereIn('shipment_id', $shipmentIds)->delete();
            DB::table('shipments')->whereIn('id', $shipmentIds)->delete();
        }

        if ($poIds->isNotEmpty()) {
            DB::table('po_status_histories')->whereIn('purchase_order_id', $poIds)->delete();
            DB::table('purchase_order_items')->whereIn('purchase_order_id', $poIds)->delete();
            DB::table('purchase_orders')->whereIn('id', $poIds)->delete();
        }
    }
}
