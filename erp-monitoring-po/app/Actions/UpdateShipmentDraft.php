<?php

namespace App\Actions;

use App\Support\DocumentTermCodes;
use App\Support\ErpFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateShipmentDraft
{
    public function handle(string $id, array $validated, ?int $userId, Request $request): void
    {
        $deliveryNote = trim((string) $validated['delivery_note_number']);
        $invoiceNumber = trim((string) ($validated['invoice_number'] ?? '')) ?: null;

        DB::transaction(function () use ($id, $validated, $deliveryNote, $invoiceNumber, $userId, $request) {
            $shipment = DB::table('shipments')->where('id', $id)->lockForUpdate()->firstOrFail();

            if ($shipment->status !== DocumentTermCodes::SHIPMENT_DRAFT) {
                throw ValidationException::withMessages([
                    'shipment' => 'Hanya draft shipment yang masih bisa diubah.',
                ]);
            }

            $supplierId = (int) $shipment->supplier_id;

            $supplier = DB::table('suppliers')
                ->where('id', $supplierId)
                ->where('status', 1)
                ->first();

            if (! $supplier) {
                throw ValidationException::withMessages([
                    'shipment' => 'Supplier tidak ditemukan atau tidak aktif.',
                ]);
            }

            $supplierCode = trim((string) $supplier->supplier_code);

            $duplicateShipment = DB::table('shipments')
                ->where('supplier_id', $shipment->supplier_id)
                ->where('id', '!=', $shipment->id)
                ->whereRaw('LOWER(TRIM(delivery_note_number)) = ?', [mb_strtolower($deliveryNote)])
                ->where('status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
                ->lockForUpdate()
                ->first();

            if ($duplicateShipment) {
                throw ValidationException::withMessages([
                    'delivery_note_number' => "Delivery note {$deliveryNote} sudah dipakai oleh shipment {$duplicateShipment->shipment_number}.",
                ]);
            }

            if ($invoiceNumber) {
                $duplicateInvoice = DB::table('shipments')
                    ->where('supplier_id', $shipment->supplier_id)
                    ->where('id', '!=', $shipment->id)
                    ->whereRaw('LOWER(TRIM(invoice_number)) = ?', [mb_strtolower($invoiceNumber)])
                    ->where('status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
                    ->lockForUpdate()
                    ->first();

                if ($duplicateInvoice) {
                    throw ValidationException::withMessages([
                        'invoice_number' => "Invoice {$invoiceNumber} sudah dipakai oleh shipment {$duplicateInvoice->shipment_number}.",
                    ]);
                }
            }

            $currentLines = DB::table('shipment_items')
                ->where('shipment_id', (int) $shipment->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('shipment_item_id');

            $keptLines = collect($validated['shipment_items'])
                ->filter(fn($line) => ($line['keep'] ?? null) === '1')
                ->values();

            if ($keptLines->isEmpty()) {
                throw ValidationException::withMessages([
                    'shipment_items' => 'Minimal satu item harus dipertahankan di draft shipment.',
                ]);
            }

            foreach ($keptLines as $line) {
                $existing = $currentLines->get((int) $line['id']);

                if (!$existing) {
                    throw ValidationException::withMessages([
                        'shipment_items' => 'Ada item draft yang tidak valid.',
                    ]);
                }

                $item = DB::table('items')
                    ->where('id', DB::table('purchase_order_items')->where('id', $existing->purchase_order_item_id)->value('item_id'))
                    ->where('active', 1)
                    ->first();

                if (! $item) {
                    throw ValidationException::withMessages([
                        'shipment_items' => "Item pada line {$line['id']} tidak ditemukan atau tidak aktif.",
                    ]);
                }

                $poItem = DB::table('purchase_order_items as poi')
                    ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                    ->select('poi.outstanding_qty', 'po.status as po_status')
                    ->where('poi.id', $existing->purchase_order_item_id)
                    ->first();

                if (! $poItem) {
                    throw ValidationException::withMessages([
                        'shipment_items' => "PO item pada line {$line['id']} tidak ditemukan.",
                    ]);
                }

                if (! in_array($poItem->po_status, [
                    DocumentTermCodes::PO_ISSUED,
                    DocumentTermCodes::PO_OPEN,
                    DocumentTermCodes::PO_LATE,
                ])) {
                    throw ValidationException::withMessages([
                        'shipment_items' => "PO pada line {$line['id']} tidak dalam status yang dapat dikirim.",
                    ]);
                }

                if ((float) $poItem->outstanding_qty <= 0) {
                    throw ValidationException::withMessages([
                        'shipment_items' => "PO pada line {$line['id']} tidak memiliki outstanding qty.",
                    ]);
                }

                if ((float) $line['shipped_qty'] > (float) $existing->available_to_ship_qty) {
                    throw ValidationException::withMessages([
                        'shipment_items' => "Qty kirim untuk item pada line {$line['id']} melebihi batas yang masih tersedia.",
                    ]);
                }
            }

            foreach ($keptLines as $line) {
                $invoiceUnitPriceCheck = array_key_exists('invoice_unit_price', $line) && $line['invoice_unit_price'] !== null && $line['invoice_unit_price'] !== ''
                    ? (float) $line['invoice_unit_price']
                    : null;

                if ($invoiceUnitPriceCheck !== null && $invoiceUnitPriceCheck < 0) {
                    throw ValidationException::withMessages([
                        'shipment_items' => 'Harga invoice tidak boleh negatif.',
                    ]);
                }
            }

            DB::table('shipments')->where('id', $shipment->id)->update([
                'shipment_date' => $validated['shipment_date'],
                'delivery_note_number' => $deliveryNote,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $validated['invoice_date'] ?? null,
                'invoice_currency' => $validated['invoice_currency'] ?? null,
                'supplier_remark' => $validated['supplier_remark'] ?? null,
                'updated_at' => now(),
            ]);

            $keptIds = $keptLines->pluck('id')->map(fn($lineId) => (int) $lineId)->all();

            DB::table('shipment_items')
                ->where('shipment_id', $shipment->id)
                ->whereNotIn('id', $keptIds)
                ->delete();

            foreach ($keptLines as $line) {
                $invoiceUnitPrice = array_key_exists('invoice_unit_price', $line) && $line['invoice_unit_price'] !== null && $line['invoice_unit_price'] !== ''
                    ? (float) $line['invoice_unit_price']
                    : null;

                $invoiceLineTotal = $invoiceUnitPrice !== null
                    ? round($invoiceUnitPrice * (float) $line['shipped_qty'], 2)
                    : null;

                DB::table('shipment_items')
                    ->where('id', (int) $line['id'])
                    ->update([
                        'shipped_qty' => (float) $line['shipped_qty'],
                        'invoice_unit_price' => $invoiceUnitPrice,
                        'invoice_line_total' => $invoiceLineTotal,
                        'updated_at' => now(),
                    ]);
            }

            $linePoIds = DB::table('shipment_items as si')
                ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
                ->where('si.shipment_id', $shipment->id)
                ->pluck('poi.purchase_order_id')
                ->map(fn($poId) => (int) $poId)
                ->unique()
                ->values();

            foreach ($linePoIds as $poId) {
                ErpFlow::refreshPoStatusByOutstanding((int) $poId, $userId);
            }

            ErpFlow::audit('shipments', (int) $shipment->id, 'update', $shipment, $validated, $userId, $request->ip());
        });
    }
}
