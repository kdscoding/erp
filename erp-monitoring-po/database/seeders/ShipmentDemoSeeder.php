<?php

namespace Database\Seeders;

use App\Support\DocumentTermCodes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShipmentDemoSeeder extends Seeder
{
  public function run(): void
  {
    $now = now();

    $poRows = DB::table('purchase_orders')
      ->where('po_number', 'like', 'PO-DEMO-%')
      ->orderBy('id')
      ->get();

    foreach ($poRows as $po) {
      $poItem = DB::table('purchase_order_items')
        ->where('purchase_order_id', $po->id)
        ->first();

      if (! $poItem) {
        continue;
      }

      $suffix = str_replace('PO-DEMO-', '', $po->po_number);

      $shipmentMap = match ($po->status) {
        DocumentTermCodes::PO_OPEN => [
          '2003' => [DocumentTermCodes::SHIPMENT_DRAFT, 60, 1825.50],
          '2004' => [DocumentTermCodes::SHIPMENT_SHIPPED, 180, 1950.00],
        ],
        DocumentTermCodes::PO_LATE => [
          '2006' => [DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED, 120, 2100.75],
        ],
        DocumentTermCodes::PO_CLOSED => [
          '2007' => [DocumentTermCodes::SHIPMENT_RECEIVED, 110, 1675.00],
        ],
        default => [],
      };

      if (! isset($shipmentMap[$suffix])) {
        continue;
      }

      [$shipmentStatus, $shipmentQty, $invoiceUnitPrice] = $shipmentMap[$suffix];

      $shipmentId = DB::table('shipments')->insertGetId([
        'purchase_order_id' => $po->id,
        'supplier_id' => $po->supplier_id,
        'shipment_number' => 'SHP-DEMO-' . $suffix,
        'shipment_date' => now()->subDays(2)->toDateString(),
        'delivery_note_number' => 'SJ-DEMO-' . $suffix,
        'invoice_number' => 'INV-DEMO-' . $suffix,
        'invoice_date' => now()->subDays(2)->toDateString(),
        'invoice_currency' => 'IDR',
        'supplier_remark' => 'Demo shipment ' . $suffix,
        'status' => $shipmentStatus,
        'created_at' => $now,
        'updated_at' => $now,
      ]);

      DB::table('shipment_items')->insert([
        'shipment_id' => $shipmentId,
        'purchase_order_item_id' => $poItem->id,
        'shipped_qty' => (float) $shipmentQty,
        'received_qty' => (float) $poItem->received_qty,
        'invoice_unit_price' => $invoiceUnitPrice,
        'invoice_line_total' => round($shipmentQty * $invoiceUnitPrice, 2),
        'note' => 'Demo shipment item ' . $suffix,
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }
  }
}
