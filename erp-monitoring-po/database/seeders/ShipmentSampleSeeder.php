<?php

namespace Database\Seeders;

use App\Support\PurchaseOrderItemStatus;
use App\Support\PurchaseOrderStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShipmentSampleSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = DB::table('suppliers')->orderBy('id')->limit(6)->pluck('id')->all();
        $items = DB::table('items')->orderBy('id')->limit(12)->pluck('id')->all();
        $warehouseId = DB::table('warehouses')->value('id');
        $plantId = DB::table('plants')->value('id');
        $now = now();

        if (empty($suppliers) || empty($items) || ! $warehouseId || ! $plantId) {
            return;
        }

        DB::transaction(function () use ($suppliers, $items, $warehouseId, $plantId, $now) {
            $this->cleanupExistingDemoData();

            $samples = [
                [
                    'suffix' => '1001',
                    'po_status' => PurchaseOrderStatus::PO_ISSUED,
                    'shipment_status' => null,
                    'ordered_qty' => 120,
                    'received_qty' => 0,
                    'has_etd' => false,
                    'shipment_qty' => 0,
                    'days_ago_po' => 3,
                    'etd_offset' => null,
                ],
                [
                    'suffix' => '1002',
                    'po_status' => PurchaseOrderStatus::OPEN,
                    'shipment_status' => null,
                    'ordered_qty' => 150,
                    'received_qty' => 0,
                    'has_etd' => true,
                    'shipment_qty' => 0,
                    'days_ago_po' => 5,
                    'etd_offset' => 5,
                ],
                [
                    'suffix' => '1003',
                    'po_status' => PurchaseOrderStatus::OPEN,
                    'shipment_status' => 'Draft',
                    'ordered_qty' => 100,
                    'received_qty' => 0,
                    'has_etd' => true,
                    'shipment_qty' => 60,
                    'days_ago_po' => 6,
                    'etd_offset' => 4,
                ],
                [
                    'suffix' => '1004',
                    'po_status' => PurchaseOrderStatus::OPEN,
                    'shipment_status' => 'Shipped',
                    'ordered_qty' => 180,
                    'received_qty' => 0,
                    'has_etd' => true,
                    'shipment_qty' => 180,
                    'days_ago_po' => 8,
                    'etd_offset' => 2,
                ],
                [
                    'suffix' => '1005',
                    'po_status' => PurchaseOrderStatus::LATE,
                    'shipment_status' => null,
                    'ordered_qty' => 90,
                    'received_qty' => 0,
                    'has_etd' => true,
                    'shipment_qty' => 0,
                    'days_ago_po' => 10,
                    'etd_offset' => -2,
                ],
                [
                    'suffix' => '1006',
                    'po_status' => PurchaseOrderStatus::LATE,
                    'shipment_status' => 'Partial Received',
                    'ordered_qty' => 160,
                    'received_qty' => 70,
                    'has_etd' => true,
                    'shipment_qty' => 120,
                    'days_ago_po' => 11,
                    'etd_offset' => -3,
                    'receipt_days_ago' => 1,
                ],
                [
                    'suffix' => '1007',
                    'po_status' => PurchaseOrderStatus::CLOSED,
                    'shipment_status' => 'Received',
                    'ordered_qty' => 110,
                    'received_qty' => 110,
                    'has_etd' => true,
                    'shipment_qty' => 110,
                    'days_ago_po' => 14,
                    'etd_offset' => -5,
                    'receipt_days_ago' => 2,
                ],
                [
                    'suffix' => '1008',
                    'po_status' => PurchaseOrderStatus::CANCELLED,
                    'shipment_status' => null,
                    'ordered_qty' => 130,
                    'received_qty' => 0,
                    'has_etd' => false,
                    'shipment_qty' => 0,
                    'days_ago_po' => 7,
                    'etd_offset' => null,
                ],
            ];

            foreach ($samples as $index => $sample) {
                $supplierId = $suppliers[$index % count($suppliers)];
                $itemId = $items[$index % count($items)];
                $orderedQty = (float) $sample['ordered_qty'];
                $receivedQty = (float) $sample['received_qty'];
                $outstandingQty = max(0, $orderedQty - $receivedQty);

                $poId = DB::table('purchase_orders')->insertGetId([
                    'po_number' => 'PO-DEMO-' . $sample['suffix'],
                    'po_date' => now()->subDays($sample['days_ago_po'])->toDateString(),
                    'supplier_id' => $supplierId,
                    'plant_id' => $plantId,
                    'warehouse_id' => $warehouseId,
                    'currency' => 'IDR',
                    'status' => $sample['po_status'],
                    'eta_date' => $sample['has_etd'] && $sample['etd_offset'] !== null
                        ? now()->addDays($sample['etd_offset'] + 3)->toDateString()
                        : null,
                    'notes' => 'Demo data ' . $sample['suffix'],
                    'cancel_reason' => $sample['po_status'] === PurchaseOrderStatus::CANCELLED ? 'Demo cancelled PO' : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $itemStatus = match ($sample['po_status']) {
                    PurchaseOrderStatus::CANCELLED => PurchaseOrderItemStatus::CANCELLED,
                    PurchaseOrderStatus::CLOSED => PurchaseOrderItemStatus::CLOSED,
                    default => $receivedQty > 0
                        ? ($outstandingQty > 0 ? PurchaseOrderItemStatus::PARTIAL : PurchaseOrderItemStatus::CLOSED)
                        : ($sample['has_etd']
                            ? ($sample['etd_offset'] < 0 ? PurchaseOrderItemStatus::LATE : PurchaseOrderItemStatus::CONFIRMED)
                            : PurchaseOrderItemStatus::WAITING),
                };

                $poItemId = DB::table('purchase_order_items')->insertGetId([
                    'purchase_order_id' => $poId,
                    'item_id' => $itemId,
                    'ordered_qty' => $orderedQty,
                    'received_qty' => $receivedQty,
                    'outstanding_qty' => $outstandingQty,
                    'item_status' => $itemStatus,
                    'eta_date' => $sample['has_etd'] && $sample['etd_offset'] !== null
                        ? now()->addDays($sample['etd_offset'] + 3)->toDateString()
                        : null,
                    'etd_date' => $sample['has_etd'] && $sample['etd_offset'] !== null
                        ? now()->addDays($sample['etd_offset'])->toDateString()
                        : null,
                    'cancel_reason' => $itemStatus === PurchaseOrderItemStatus::CANCELLED ? 'Demo cancelled item' : null,
                    'remarks' => 'Demo line ' . $sample['suffix'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('po_status_histories')->insert([
                    'purchase_order_id' => $poId,
                    'from_status' => null,
                    'to_status' => $sample['po_status'],
                    'changed_by' => null,
                    'changed_at' => $now,
                    'note' => 'Seeded demo data',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if (! empty($sample['shipment_status']) && (float) $sample['shipment_qty'] > 0) {
                    $shipmentId = DB::table('shipments')->insertGetId([
                        'purchase_order_id' => $poId,
                        'supplier_id' => $supplierId,
                        'shipment_number' => 'SHP-DEMO-' . $sample['suffix'],
                        'shipment_date' => now()->subDays(max(1, $sample['days_ago_po'] - 1))->toDateString(),
                        'eta_date' => $sample['has_etd'] && $sample['etd_offset'] !== null
                            ? now()->addDays($sample['etd_offset'] + 5)->toDateString()
                            : null,
                        'delivery_note_number' => 'SJ-DEMO-' . $sample['suffix'],
                        'supplier_remark' => 'Demo shipment ' . $sample['suffix'],
                        'status' => $sample['shipment_status'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $shipmentItemId = DB::table('shipment_items')->insertGetId([
                        'shipment_id' => $shipmentId,
                        'purchase_order_item_id' => $poItemId,
                        'shipped_qty' => (float) $sample['shipment_qty'],
                        'received_qty' => $receivedQty,
                        'note' => 'Demo shipment item ' . $sample['suffix'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    if ($receivedQty > 0) {
                        $grId = DB::table('goods_receipts')->insertGetId([
                            'gr_number' => 'GR-DEMO-' . $sample['suffix'],
                            'receipt_date' => now()->subDays($sample['receipt_days_ago'] ?? 1)->toDateString(),
                            'purchase_order_id' => $poId,
                            'shipment_id' => $shipmentId,
                            'warehouse_id' => $warehouseId,
                            'document_number' => 'SJ-DEMO-' . $sample['suffix'],
                            'remark' => 'Demo receiving ' . $sample['suffix'],
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
                            'qty_variance' => (float) $sample['shipment_qty'] - $receivedQty,
                            'accepted_qty' => $receivedQty,
                            'rejected_qty' => 0,
                            'remark' => 'Demo receiving ' . $sample['suffix'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
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
            ->where('po_number', 'like', 'PO-DEMO-%')
            ->pluck('id');

        $goodsReceiptIds = DB::table('goods_receipts')
            ->where(function ($query) use ($shipmentIds, $poIds) {
                $query->where('gr_number', 'like', 'GR-DEMO-%');

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
            DB::table('attachments')->where('module', 'goods_receipts')->whereIn('record_id', $goodsReceiptIds)->delete();
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
