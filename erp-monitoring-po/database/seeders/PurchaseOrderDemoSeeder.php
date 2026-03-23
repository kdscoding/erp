<?php

namespace Database\Seeders;

use App\Support\DocumentTermCodes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseOrderDemoSeeder extends Seeder
{
  public function run(): void
  {
    $now = now();

    $this->cleanupExistingDemoData();

    $suppliers = DB::table('suppliers')->orderBy('id')->pluck('id')->all();
    $items = DB::table('items')->orderBy('id')->pluck('id')->all();
    $warehouseId = DB::table('warehouses')->value('id');
    $plantId = DB::table('plants')->value('id');

    if (empty($suppliers) || empty($items) || ! $warehouseId || ! $plantId) {
      return;
    }

    $samples = [
      ['suffix' => '2001', 'po_status' => DocumentTermCodes::PO_ISSUED, 'ordered_qty' => 120, 'received_qty' => 0, 'etd_offset' => null],
      ['suffix' => '2002', 'po_status' => DocumentTermCodes::PO_OPEN, 'ordered_qty' => 150, 'received_qty' => 0, 'etd_offset' => 5],
      ['suffix' => '2003', 'po_status' => DocumentTermCodes::PO_OPEN, 'ordered_qty' => 100, 'received_qty' => 0, 'etd_offset' => 4],
      ['suffix' => '2004', 'po_status' => DocumentTermCodes::PO_OPEN, 'ordered_qty' => 180, 'received_qty' => 0, 'etd_offset' => 2],
      ['suffix' => '2005', 'po_status' => DocumentTermCodes::PO_LATE, 'ordered_qty' => 90, 'received_qty' => 0, 'etd_offset' => -2],
      ['suffix' => '2006', 'po_status' => DocumentTermCodes::PO_LATE, 'ordered_qty' => 160, 'received_qty' => 70, 'etd_offset' => -3],
      ['suffix' => '2007', 'po_status' => DocumentTermCodes::PO_CLOSED, 'ordered_qty' => 110, 'received_qty' => 110, 'etd_offset' => -5],
      ['suffix' => '2008', 'po_status' => DocumentTermCodes::PO_CANCELLED, 'ordered_qty' => 130, 'received_qty' => 0, 'etd_offset' => null],
    ];

    foreach ($samples as $index => $sample) {
      $supplierId = $suppliers[$index % count($suppliers)];
      $itemId = $items[$index % count($items)];
      $orderedQty = (float) $sample['ordered_qty'];
      $receivedQty = (float) $sample['received_qty'];
      $outstandingQty = max(0, $orderedQty - $receivedQty);

      $poId = DB::table('purchase_orders')->insertGetId([
        'po_number' => 'PO-DEMO-' . $sample['suffix'],
        'po_date' => now()->subDays(5 + $index)->toDateString(),
        'supplier_id' => $supplierId,
        'plant_id' => $plantId,
        'warehouse_id' => $warehouseId,
        'currency' => 'IDR',
        'status' => $sample['po_status'],
        'eta_date' => $sample['etd_offset'] !== null ? now()->addDays($sample['etd_offset'] + 3)->toDateString() : null,
        'notes' => 'Demo PO ' . $sample['suffix'],
        'cancel_reason' => $sample['po_status'] === DocumentTermCodes::PO_CANCELLED ? 'Demo cancelled PO' : null,
        'created_at' => $now,
        'updated_at' => $now,
      ]);

      $itemStatus = match ($sample['po_status']) {
        DocumentTermCodes::PO_CANCELLED => DocumentTermCodes::ITEM_CANCELLED,
        DocumentTermCodes::PO_CLOSED => DocumentTermCodes::ITEM_CLOSED,
        default => $receivedQty > 0
          ? ($outstandingQty > 0 ? DocumentTermCodes::ITEM_PARTIAL : DocumentTermCodes::ITEM_CLOSED)
          : ($sample['etd_offset'] === null
            ? DocumentTermCodes::ITEM_WAITING
            : ($sample['etd_offset'] < 0 ? DocumentTermCodes::ITEM_LATE : DocumentTermCodes::ITEM_CONFIRMED)),
      };

      DB::table('purchase_order_items')->insert([
        'purchase_order_id' => $poId,
        'item_id' => $itemId,
        'ordered_qty' => $orderedQty,
        'received_qty' => $receivedQty,
        'outstanding_qty' => $outstandingQty,
        'item_status' => $itemStatus,
        'unit_price' => 1500 + ($index * 125),
        'eta_date' => $sample['etd_offset'] !== null ? now()->addDays($sample['etd_offset'] + 3)->toDateString() : null,
        'etd_date' => $sample['etd_offset'] !== null ? now()->addDays($sample['etd_offset'])->toDateString() : null,
        'cancel_reason' => $itemStatus === DocumentTermCodes::ITEM_CANCELLED ? 'Demo cancelled item' : null,
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
    }
  }

  private function cleanupExistingDemoData(): void
  {
    $poIds = DB::table('purchase_orders')
      ->where('po_number', 'like', 'PO-DEMO-%')
      ->pluck('id');

    if ($poIds->isEmpty()) {
      return;
    }

    $shipmentIds = DB::table('shipments')->whereIn('purchase_order_id', $poIds)->pluck('id');
    $goodsReceiptIds = DB::table('goods_receipts')
      ->whereIn('purchase_order_id', $poIds)
      ->orWhereIn('shipment_id', $shipmentIds)
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

    DB::table('po_status_histories')->whereIn('purchase_order_id', $poIds)->delete();
    DB::table('purchase_order_items')->whereIn('purchase_order_id', $poIds)->delete();
    DB::table('purchase_orders')->whereIn('id', $poIds)->delete();
  }
}
