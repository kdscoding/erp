<?php

namespace App\Actions;

use App\Support\DocumentTermCodes;
use App\Support\DomainStatus;
use App\Support\ErpFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreShipmentDraft
{
    public function handle(array $validated, ?int $userId, Request $request): int
    {
        $selectedIds = collect($validated['selected_items'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $remark = trim(implode(' | ', array_filter([
            !empty($validated['po_reference_missing']) ? 'Dokumen supplier tidak mencantumkan nomor PO.' : null,
            $validated['supplier_remark'] ?? null,
        ]))) ?: null;

        $deliveryNote = trim((string) $validated['delivery_note_number']);
        $invoiceNumber = trim((string) ($validated['invoice_number'] ?? '')) ?: null;

        return DB::transaction(function () use ($selectedIds, $request, $deliveryNote, $invoiceNumber, $remark, $userId, $validated) {
            $items = $this->candidateItemsBaseQuery()
                ->whereIn('poi.id', $selectedIds)
                ->lockForUpdate()
                ->get();

            if ($items->count() !== $selectedIds->count()) {
                throw ValidationException::withMessages([
                    'selected_items' => 'Sebagian item tidak lagi tersedia untuk dibuat shipment.',
                ]);
            }

            if ($items->pluck('supplier_id')->unique()->count() !== 1) {
                throw ValidationException::withMessages([
                    'selected_items' => 'Semua item shipment harus berasal dari supplier yang sama.',
                ]);
            }

            $supplierId = (int) $items->first()->supplier_id;

            $duplicateShipment = DB::table('shipments')
                ->where('supplier_id', $supplierId)
                ->whereRaw('LOWER(TRIM(delivery_note_number)) = ?', [mb_strtolower($deliveryNote)])
                ->where('status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
                ->lockForUpdate()
                ->first();

            if ($duplicateShipment) {
                $statusLabel = $duplicateShipment->status === DocumentTermCodes::SHIPMENT_DRAFT
                    ? 'masih berupa Draft'
                    : 'sudah diproses dengan status ' . $duplicateShipment->status;

                throw ValidationException::withMessages([
                    'delivery_note_number' => "Delivery note {$deliveryNote} untuk supplier ini sudah digunakan pada shipment {$duplicateShipment->shipment_number} dan {$statusLabel}.",
                ]);
            }

            if ($invoiceNumber) {
                $duplicateInvoice = DB::table('shipments')
                    ->where('supplier_id', $supplierId)
                    ->whereRaw('LOWER(TRIM(invoice_number)) = ?', [mb_strtolower($invoiceNumber)])
                    ->where('status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
                    ->lockForUpdate()
                    ->first();

                if ($duplicateInvoice) {
                    throw ValidationException::withMessages([
                        'invoice_number' => "Invoice {$invoiceNumber} untuk supplier ini sudah dipakai pada shipment {$duplicateInvoice->shipment_number}.",
                    ]);
                }
            }

            $linePayloads = [];
            foreach ($items as $item) {
                $qty = (float) ($request->input("shipped_qty.{$item->purchase_order_item_id}") ?? 0);
                $invoiceUnitPrice = $request->input("invoice_unit_price.{$item->purchase_order_item_id}");
                $invoiceUnitPrice = ($invoiceUnitPrice === null || $invoiceUnitPrice === '') ? null : (float) $invoiceUnitPrice;

                if ($qty <= 0) {
                    throw ValidationException::withMessages([
                        'shipped_qty' => 'Qty kirim harus diisi untuk setiap item yang dipilih.',
                    ]);
                }

                if ($qty > (float) $item->available_to_ship_qty) {
                    throw ValidationException::withMessages([
                        'shipped_qty.' . $item->purchase_order_item_id => "Qty kirim untuk {$item->item_code} melebihi sisa qty yang masih bisa dialokasikan.",
                    ]);
                }

                $linePayloads[] = [
                    'purchase_order_item_id' => $item->purchase_order_item_id,
                    'purchase_order_id' => $item->purchase_order_id,
                    'shipped_qty' => $qty,
                    'invoice_unit_price' => $invoiceUnitPrice,
                    'invoice_line_total' => $invoiceUnitPrice !== null ? round($invoiceUnitPrice * $qty, 2) : null,
                ];
            }

            $number = ErpFlow::generateNumber('SHP', 'shipments', 'shipment_number');

            $shipmentId = DB::table('shipments')->insertGetId([
                'purchase_order_id' => $linePayloads[0]['purchase_order_id'],
                'supplier_id' => $supplierId,
                'shipment_number' => $number,
                'shipment_date' => $validated['shipment_date'],
                'delivery_note_number' => $deliveryNote,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $validated['invoice_date'] ?? null,
                'invoice_currency' => $validated['invoice_currency'] ?? null,
                'supplier_remark' => $remark,
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ] + DomainStatus::payload(DomainStatus::GROUP_SHIPMENT_STATUS, 'status', DocumentTermCodes::SHIPMENT_DRAFT));

            $lineRows = collect($linePayloads)->map(fn ($line) => [
                'shipment_id' => $shipmentId,
                'purchase_order_item_id' => $line['purchase_order_item_id'],
                'shipped_qty' => $line['shipped_qty'],
                'received_qty' => 0,
                'invoice_unit_price' => $line['invoice_unit_price'],
                'invoice_line_total' => $line['invoice_line_total'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

            DB::table('shipment_items')->insert($lineRows);

            $poIds = collect($linePayloads)
                ->pluck('purchase_order_id')
                ->map(fn ($poId) => (int) $poId)
                ->unique()
                ->values();

            foreach ($poIds as $poId) {
                ErpFlow::refreshPoStatusByOutstanding($poId, $userId);
            }

            ErpFlow::audit('shipments', $shipmentId, 'create', null, [
                'shipment' => [
                    'shipment_date' => $validated['shipment_date'],
                    'delivery_note_number' => $deliveryNote,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => $validated['invoice_date'] ?? null,
                    'invoice_currency' => $validated['invoice_currency'] ?? null,
                    'supplier_remark' => $remark,
                    'status' => DocumentTermCodes::SHIPMENT_DRAFT,
                ],
                'lines' => $linePayloads,
            ], $userId, $request->ip());

            return (int) $shipmentId;
        });
    }

    private function candidateItemsBaseQuery()
    {
        return DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->leftJoin('shipment_items as si', 'si.purchase_order_item_id', '=', 'poi.id')
            ->leftJoin('shipments as sh_alloc', function ($join) {
                $join->on('sh_alloc.id', '=', 'si.shipment_id')
                    ->where('sh_alloc.status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED);
            })
            ->select(
                'poi.id as purchase_order_item_id',
                'poi.purchase_order_id',
                'po.supplier_id',
                'po.po_number',
                'po.status as po_status',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'poi.outstanding_qty',
                'poi.etd_date',
                'poi.unit_price'
            )
            ->selectRaw('(poi.outstanding_qty - COALESCE(SUM(CASE WHEN sh_alloc.id IS NOT NULL THEN si.shipped_qty - si.received_qty ELSE 0 END), 0)) as available_to_ship_qty')
            ->whereIn('po.status', [
                DocumentTermCodes::PO_ISSUED,
                DocumentTermCodes::PO_OPEN,
                DocumentTermCodes::PO_LATE,
            ])
            ->where('poi.outstanding_qty', '>', 0)
            ->groupBy(
                'poi.id',
                'poi.purchase_order_id',
                'po.supplier_id',
                'po.po_number',
                'po.status',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'poi.outstanding_qty',
                'poi.etd_date',
                'poi.unit_price'
            );
    }
}
