<?php

namespace Database\Seeders;

use App\Support\DocumentTermCodes;
use App\Support\DomainStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GoodsReceiptDemoSeeder extends Seeder
{
  public function run(): void
  {
    $now = now();
    $warehouseId = DB::table('warehouses')->value('id');

    if (! $warehouseId) {
      return;
    }

    $shipmentRows = DB::table('shipments')
      ->where('shipment_number', 'like', 'SHP-DEMO-%')
      ->orderBy('id')
      ->get();

    foreach ($shipmentRows as $shipment) {
      if (! in_array($shipment->status, [
        DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED,
        DocumentTermCodes::SHIPMENT_RECEIVED,
      ], true)) {
        continue;
      }

      $shipmentItem = DB::table('shipment_items')
        ->where('shipment_id', $shipment->id)
        ->first();

      if (! $shipmentItem || (float) $shipmentItem->received_qty <= 0) {
        continue;
      }

      $poItem = DB::table('purchase_order_items')->where('id', $shipmentItem->purchase_order_item_id)->first();
      if (! $poItem) {
        continue;
      }

      $suffix = str_replace('SHP-DEMO-', '', $shipment->shipment_number);

      $grId = DB::table('goods_receipts')->insertGetId([
        'gr_number' => 'GR-DEMO-' . $suffix,
        'receipt_date' => now()->subDays(1)->toDateString(),
        'purchase_order_id' => $shipment->purchase_order_id,
        'shipment_id' => $shipment->id,
        'warehouse_id' => $warehouseId,
        'document_number' => $shipment->delivery_note_number,
        'remark' => 'Demo receiving ' . $suffix,
        'created_at' => $now,
        'updated_at' => $now,
      ] + DomainStatus::payload(DomainStatus::GROUP_GOODS_RECEIPT_STATUS, 'status', DocumentTermCodes::GR_POSTED));

      DB::table('goods_receipt_items')->insert([
        'goods_receipt_id' => $grId,
        'shipment_item_id' => $shipmentItem->id,
        'purchase_order_item_id' => $poItem->id,
        'received_qty' => (float) $shipmentItem->received_qty,
        'qty_variance' => (float) $shipmentItem->shipped_qty - (float) $shipmentItem->received_qty,
        'accepted_qty' => (float) $shipmentItem->received_qty,
        'remark' => 'Demo receiving ' . $suffix,
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }
  }
}
