<?php

namespace App\Actions;

use App\Queries\Receiving\ShipmentReceivingQuery;
use App\Support\DocumentTermCodes;
use App\Support\DomainStatus;
use App\Support\ErpFlow;
use App\Support\PurchaseOrderItemStatusResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PostReceiptDocument
{
    public function __construct(
        private ShipmentReceivingQuery $shipmentReceivingQuery,
        private PurchaseOrderItemStatusResolver $purchaseOrderItemStatusResolver
    ) {
    }

    public function handle(array $validated, ?int $userId, Request $request): int
    {
        $lineInputs = collect($validated['received_qty'])
            ->mapWithKeys(fn ($qty, $shipmentItemId) => [(int) $shipmentItemId => (float) $qty])
            ->filter(fn ($qty) => $qty > 0);

        if ($lineInputs->isEmpty()) {
            throw ValidationException::withMessages([
                'received_qty' => 'Isi minimal satu qty terima untuk memproses receiving.',
            ]);
        }

        $allowOverReceipt = (bool) DB::table('settings')->where('key', 'allow_over_receipt')->value('value');
        $storedPath = null;

        DB::beginTransaction();
        try {
            $shipment = DB::table('shipments')->where('id', $validated['shipment_id'])->lockForUpdate()->firstOrFail();

            if (! in_array($shipment->status, [
                DocumentTermCodes::SHIPMENT_SHIPPED,
                DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED,
            ], true)) {
                throw new \RuntimeException('Receiving hanya bisa diproses untuk shipment yang sudah berstatus Shipped atau Partial Received.');
            }

            $shipmentItems = $this->shipmentReceivingQuery->itemsForShipmentBuilder((int) $shipment->id)
                ->whereIn('si.id', $lineInputs->keys()->all())
                ->lockForUpdate()
                ->orderBy('po.po_number')
                ->orderBy('i.item_code')
                ->get()
                ->keyBy('shipment_item_id');

            if ($shipmentItems->count() !== $lineInputs->count()) {
                throw new \RuntimeException('Sebagian item shipment tidak valid untuk diproses.');
            }

            $grId = null;
            foreach ($lineInputs as $shipmentItemId => $receivedQty) {
                $item = $shipmentItems->get($shipmentItemId);
                $shipmentRemaining = max(0, (float) $item->shipped_qty - (float) $item->shipment_received_qty);

                if ($receivedQty > $shipmentRemaining && ! $allowOverReceipt) {
                    throw new \RuntimeException("Qty menerima untuk {$item->item_code} melebihi sisa kiriman.");
                }

                if ($receivedQty > (float) $item->outstanding_qty && ! $allowOverReceipt) {
                    throw new \RuntimeException("Qty menerima untuk {$item->item_code} melebihi outstanding PO.");
                }

                $poItem = DB::table('purchase_order_items')->where('id', $item->purchase_order_item_id)->lockForUpdate()->firstOrFail();
                $po = DB::table('purchase_orders')->where('id', $poItem->purchase_order_id)->lockForUpdate()->firstOrFail();

                if (! $grId) {
                    $grId = DB::table('goods_receipts')->insertGetId([
                        'gr_number' => ErpFlow::generateNumber('GR', 'goods_receipts', 'gr_number'),
                        'receipt_date' => $validated['receipt_date'],
                        'purchase_order_id' => $poItem->purchase_order_id,
                        'shipment_id' => $shipment->id,
                        'warehouse_id' => $po->warehouse_id,
                        'received_by' => $userId,
                        'document_number' => $validated['document_number'],
                        'remark' => $validated['note'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ] + DomainStatus::payload(DomainStatus::GROUP_GOODS_RECEIPT_STATUS, 'status', DocumentTermCodes::GR_POSTED));
                }

                DB::table('goods_receipt_items')->insert([
                    'goods_receipt_id' => $grId,
                    'shipment_item_id' => $shipmentItemId,
                    'purchase_order_item_id' => $poItem->id,
                    'received_qty' => $receivedQty,
                    'qty_variance' => (float) $poItem->ordered_qty - $receivedQty,
                    'accepted_qty' => $receivedQty,
                    'remark' => $validated['note'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $newReceived = (float) $poItem->received_qty + $receivedQty;
                $newOutstanding = max(0, (float) $poItem->ordered_qty - $newReceived);

                DB::table('purchase_order_items')->where('id', $poItem->id)->update([
                    'received_qty' => $newReceived,
                    'outstanding_qty' => $newOutstanding,
                    'updated_at' => now(),
                ] + DomainStatus::payload(
                    DomainStatus::GROUP_PO_ITEM_STATUS,
                    'item_status',
                    $this->purchaseOrderItemStatusResolver->resolve(
                        $newReceived,
                        $newOutstanding,
                        $poItem->etd_date
                    )
                ));

                DB::table('shipment_items')->where('id', $shipmentItemId)->update([
                    'received_qty' => (float) $item->shipment_received_qty + $receivedQty,
                    'updated_at' => now(),
                ]);

                ErpFlow::refreshPoStatusByOutstanding((int) $poItem->purchase_order_id, $userId);
            }

            if ($request->hasFile('attachment') && $grId) {
                $storedPath = $request->file('attachment')->store('attachments/receiving', 'public');
                DB::table('attachments')->insert([
                    'module' => 'goods_receipts',
                    'record_id' => $grId,
                    'file_path' => $storedPath,
                    'file_name' => basename($storedPath),
                    'uploaded_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->refreshShipmentStatus((int) $shipment->id);
            ErpFlow::audit('goods_receipts', $grId, 'create', null, $validated, $userId, $request->ip());
            DB::commit();

            return $grId;
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $e;
        }
    }

    private function refreshShipmentStatus(int $shipmentId): void
    {
        $shipmentSummary = DB::table('shipment_items')
            ->where('shipment_id', $shipmentId)
            ->selectRaw('SUM(CASE WHEN received_qty > 0 THEN 1 ELSE 0 END) as received_lines')
            ->selectRaw('SUM(CASE WHEN received_qty >= shipped_qty THEN 1 ELSE 0 END) as completed_lines')
            ->selectRaw('COUNT(*) as total_lines')
            ->first();

        $shipmentStatus = DocumentTermCodes::SHIPMENT_SHIPPED;
        if ((int) ($shipmentSummary->completed_lines ?? 0) === (int) ($shipmentSummary->total_lines ?? 0)) {
            $shipmentStatus = DocumentTermCodes::SHIPMENT_RECEIVED;
        } elseif ((int) ($shipmentSummary->received_lines ?? 0) > 0) {
            $shipmentStatus = DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED;
        }

        DB::table('shipments')->where('id', $shipmentId)->update([
            'updated_at' => now(),
        ] + DomainStatus::payload(DomainStatus::GROUP_SHIPMENT_STATUS, 'status', $shipmentStatus));
    }
}
