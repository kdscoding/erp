<?php

namespace App\Actions;

use App\Support\DocumentTermCodes;
use App\Support\DomainStatus;
use App\Support\ErpFlow;
use App\Support\TermCatalog;
use Illuminate\Support\Facades\DB;

class CreatePurchaseOrder
{
  public function handle(array $validated, ?int $userId = null, ?string $ip = null, ?string $notes = null): int
  {
    return DB::transaction(function () use ($validated, $userId, $ip, $notes) {
      $poNumber = ($validated['po_number'] ?? null)
        ?: ErpFlow::generateNumber('PO', 'purchase_orders', 'po_number');

      $poId = DB::table('purchase_orders')->insertGetId([
        'po_number'   => $poNumber,
        'po_date'     => $validated['po_date'],
        'supplier_id' => $validated['supplier_id'],
        'notes'       => $notes,
        'created_by'  => $userId,
        'updated_by'  => $userId,
        'created_at'  => now(),
        'updated_at'  => now(),
      ] + DomainStatus::payload(DomainStatus::GROUP_PO_STATUS, 'status', DocumentTermCodes::PO_ISSUED));

      foreach ($validated['items'] as $row) {
        DB::table('purchase_order_items')->insert([
          'purchase_order_id' => $poId,
          'item_id'           => $row['item_id'],
          'ordered_qty'       => $row['ordered_qty'],
          'received_qty'      => 0,
          'outstanding_qty'   => $row['ordered_qty'],
          'unit_price'        => $row['unit_price'] ?? null,
          'remarks'           => $row['remarks'] ?? null,
          'created_at'        => now(),
          'updated_at'        => now(),
        ] + DomainStatus::payload(DomainStatus::GROUP_PO_ITEM_STATUS, 'item_status', DocumentTermCodes::ITEM_WAITING));
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
        DocumentTermCodes::PO_ISSUED,
        $userId,
        TermCatalog::label(
          DocumentTermCodes::GROUP_PO_HISTORY_NOTE,
          DocumentTermCodes::NOTE_RELEASED_NEW_PO,
          'Released new PO'
        )
      );

      ErpFlow::audit(
        'purchase_orders',
        (int) $poId,
        'create',
        null,
        [
          'status'      => DocumentTermCodes::PO_ISSUED,
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
