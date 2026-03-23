<?php

namespace App\Actions\PurchaseOrder;

use App\Support\ErpFlow;
use App\Support\PurchaseOrderItemStatus;
use App\Support\PurchaseOrderStatus;
use App\Support\TermCatalog;
use Illuminate\Support\Facades\DB;

class CreatePurchaseOrder
{
  /**
   * @param array{
   *   po_number?: string|null,
   *   po_date: string,
   *   supplier_id: int,
   *   items: array<int, array{
   *     item_id: int,
   *     ordered_qty: int|float|string,
   *     unit_price?: int|float|string|null,
   *     remarks?: string|null
   *   }>
   * } $validated
   */
  public function handle(array $validated, ?int $userId = null, ?string $ip = null, ?string $notes = null): int
  {
    return DB::transaction(function () use ($validated, $userId, $ip, $notes) {
      $poNumber = ($validated['po_number'] ?? null)
        ?: ErpFlow::generateNumber('PO', 'purchase_orders', 'po_number');

      $poId = DB::table('purchase_orders')->insertGetId([
        'po_number'   => $poNumber,
        'po_date'     => $validated['po_date'],
        'supplier_id' => $validated['supplier_id'],
        'status'      => PurchaseOrderStatus::OPEN,
        'notes'       => $notes,
        'created_by'  => $userId,
        'updated_by'  => $userId,
        'created_at'  => now(),
        'updated_at'  => now(),
      ]);

      foreach ($validated['items'] as $row) {
        DB::table('purchase_order_items')->insert([
          'purchase_order_id' => $poId,
          'item_id'           => $row['item_id'],
          'ordered_qty'       => $row['ordered_qty'],
          'received_qty'      => 0,
          'outstanding_qty'   => $row['ordered_qty'],
          'item_status'       => PurchaseOrderItemStatus::WAITING,
          'unit_price'        => $row['unit_price'] ?? null,
          'remarks'           => $row['remarks'] ?? null,
          'created_at'        => now(),
          'updated_at'        => now(),
        ]);
      }

      DB::table('purchase_orders')
        ->where('id', $poId)
        ->update([
          'eta_date'   => ErpFlow::resolvePoEtaDate((int) $poId),
          'updated_at' => now(),
          'updated_by' => $userId,
        ]);

      ErpFlow::pushPoStatus(
        (int) $poId,
        null,
        PurchaseOrderStatus::OPEN,
        $userId,
        TermCatalog::label('po_history_note', 'released_new_po', 'Released new PO')
      );

      ErpFlow::audit(
        'purchase_orders',
        (int) $poId,
        'create',
        null,
        [
          'status'      => PurchaseOrderStatus::OPEN,
          'po_number'   => $poNumber,
          'po_date'     => $validated['po_date'],
          'supplier_id' => $validated['supplier_id'],
        ],
        $userId,
        $ip
      );

      return (int) $poId;
    });
  }
}
