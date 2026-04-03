<?php

namespace App\Http\Controllers;

use App\Queries\Receiving\ReceivingHistoryQuery;
use App\Queries\Receiving\ShipmentReceivingQuery;
use App\Support\DocumentTermCodes;
use App\Support\DomainStatus;
use App\Support\ErpFlow;
use App\Support\PurchaseOrderItemStatusResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    public function index(
        Request $request,
        ReceivingHistoryQuery $receivingHistoryQuery,
        ShipmentReceivingQuery $shipmentReceivingQuery
    ): View
    {
        $mode = $request->route('mode', 'process');
        $clearSelection = $request->boolean('clear_selection');

        $rows = $receivingHistoryQuery->paginate($request);
        $shipmentDocuments = $shipmentReceivingQuery->documents($request);

        $selectedShipmentId = $request->integer('shipment_id');
        if ($mode === 'process' && ! $selectedShipmentId && ! $clearSelection && $shipmentDocuments->isNotEmpty()) {
            $selectedShipmentId = (int) $shipmentDocuments->first()->id;
        }

        $selectedShipment = $selectedShipmentId
            ? $shipmentDocuments->firstWhere('id', $selectedShipmentId)
            : null;

        if ($mode === 'process' && $selectedShipmentId) {
            $shipmentDocuments = $shipmentDocuments
                ->reject(fn($document) => (int) $document->id === $selectedShipmentId)
                ->values();
        }

        $shipmentItems = $mode === 'process' && $selectedShipmentId
            ? $shipmentReceivingQuery->itemsForShipment($selectedShipmentId)
            : collect();

        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get(['id', 'supplier_name']);

        return view('receiving.index', compact(
            'rows',
            'shipmentDocuments',
            'shipmentItems',
            'selectedShipment',
            'suppliers',
            'mode'
        ));
    }

    public function show(string $id): View
    {
        $receipt = DB::table('goods_receipts as gr')
            ->join('purchase_orders as po', 'po.id', '=', 'gr.purchase_order_id')
            ->leftJoin('shipments as sh', 'sh.id', '=', 'gr.shipment_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('warehouses as w', 'w.id', '=', 'gr.warehouse_id')
            ->leftJoin('users as u', 'u.id', '=', 'gr.received_by')
            ->select(
                'gr.*',
                'po.po_number',
                'po.status as po_status',
                's.supplier_name',
                'sh.shipment_number',
                'sh.delivery_note_number',
                'sh.invoice_number',
                'sh.invoice_date',
                'sh.invoice_currency',
                'sh.status as shipment_status',
                'w.warehouse_name',
                'u.name as receiver_name'
            )
            ->where('gr.id', $id)
            ->firstOrFail();

        $items = DB::table('goods_receipt_items as gri')
            ->join('purchase_order_items as poi', 'poi.id', '=', 'gri.purchase_order_item_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->leftJoin('units as u', 'u.id', '=', 'i.unit_id')
            ->leftJoin('shipment_items as si', 'si.id', '=', 'gri.shipment_item_id')
            ->select(
                'gri.*',
                'poi.ordered_qty',
                'poi.unit_price as po_unit_price',
                'poi.received_qty as total_po_received_qty',
                'poi.outstanding_qty',
                'i.item_code',
                'i.item_name',
                'u.unit_name',
                'si.shipped_qty',
                'si.invoice_unit_price',
                'si.invoice_line_total'
            )
            ->where('gri.goods_receipt_id', $id)
            ->orderBy('gri.id')
            ->get();

        return view('receiving.show', compact('receipt', 'items'));
    }

    public function cancel(
        string $id,
        Request $request,
        PurchaseOrderItemStatusResolver $purchaseOrderItemStatusResolver
    ): RedirectResponse
    {
        $validated = $request->validate([
            'cancel_reason' => 'required|string|max:1000',
        ], [
            'cancel_reason.required' => 'Alasan pembatalan GR wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            $receipt = DB::table('goods_receipts')->where('id', $id)->lockForUpdate()->firstOrFail();

            if ($receipt->status !== DocumentTermCodes::GR_POSTED) {
                throw new \RuntimeException('Hanya GR berstatus Posted yang bisa dibatalkan.');
            }

            $receiptItems = DB::table('goods_receipt_items')
                ->where('goods_receipt_id', $receipt->id)
                ->lockForUpdate()
                ->get();

            if ($receiptItems->isEmpty()) {
                throw new \RuntimeException('GR tidak memiliki item untuk dibatalkan.');
            }

            foreach ($receiptItems as $receiptItem) {
                $poItem = DB::table('purchase_order_items')->where('id', $receiptItem->purchase_order_item_id)->lockForUpdate()->firstOrFail();
                $shipmentItem = $receiptItem->shipment_item_id
                    ? DB::table('shipment_items')->where('id', $receiptItem->shipment_item_id)->lockForUpdate()->first()
                    : null;

                $newPoReceived = max(0, (float) $poItem->received_qty - (float) $receiptItem->received_qty);
                $newOutstanding = max(0, (float) $poItem->ordered_qty - $newPoReceived);
                $newItemStatus = $purchaseOrderItemStatusResolver->resolve(
                    $newPoReceived,
                    $newOutstanding,
                    $poItem->etd_date
                );

                DB::table('purchase_order_items')->where('id', $poItem->id)->update([
                    'received_qty' => $newPoReceived,
                    'outstanding_qty' => $newOutstanding,
                    'updated_at' => now(),
                ] + DomainStatus::payload(DomainStatus::GROUP_PO_ITEM_STATUS, 'item_status', $newItemStatus));

                if ($shipmentItem) {
                    DB::table('shipment_items')->where('id', $shipmentItem->id)->update([
                        'received_qty' => max(0, (float) $shipmentItem->received_qty - (float) $receiptItem->received_qty),
                        'updated_at' => now(),
                    ]);
                }
            }

            $poIds = $receiptItems->pluck('purchase_order_item_id')
                ->map(fn($itemId) => (int) DB::table('purchase_order_items')->where('id', $itemId)->value('purchase_order_id'))
                ->filter()
                ->unique()
                ->values();

            foreach ($poIds as $poId) {
                ErpFlow::refreshPoStatusByOutstanding((int) $poId, optional($request->user())->id);
            }

            if ($receipt->shipment_id) {
                $this->refreshShipmentStatus((int) $receipt->shipment_id);
            }

            DB::table('goods_receipts')->where('id', $receipt->id)->update([
                'cancel_reason' => $validated['cancel_reason'],
                'cancelled_by' => optional($request->user())->id,
                'cancelled_at' => now(),
                'updated_at' => now(),
            ] + DomainStatus::payload(DomainStatus::GROUP_GOODS_RECEIPT_STATUS, 'status', DocumentTermCodes::GR_CANCELLED));

            ErpFlow::audit('goods_receipts', (int) $receipt->id, 'cancel', $receipt, [
                'status' => DocumentTermCodes::GR_CANCELLED,
                'cancel_reason' => $validated['cancel_reason'],
            ], optional($request->user())->id, $request->ip());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('receiving.show', $id)->with('success', 'Goods Receipt berhasil dibatalkan dan qty receiving telah dikembalikan.');
    }

    public function store(
        Request $request,
        ShipmentReceivingQuery $shipmentReceivingQuery,
        PurchaseOrderItemStatusResolver $purchaseOrderItemStatusResolver
    ): RedirectResponse
    {
        if ($request->filled('shipment_id')) {
            return $this->storeDocumentReceiving(
                $request,
                $shipmentReceivingQuery,
                $purchaseOrderItemStatusResolver
            );
        }

        return $this->storeSingleLineReceiving(
            $request,
            $purchaseOrderItemStatusResolver
        );
    }

    private function storeDocumentReceiving(
        Request $request,
        ShipmentReceivingQuery $shipmentReceivingQuery,
        PurchaseOrderItemStatusResolver $purchaseOrderItemStatusResolver
    ): RedirectResponse
    {
        $v = $request->validate([
            'shipment_id' => 'required|integer|exists:shipments,id',
            'receipt_date' => 'required|date',
            'document_number' => 'required|string|max:100',
            'note' => 'nullable|string|max:500',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'received_qty' => 'required|array',
            'received_qty.*' => 'nullable|numeric|min:0',
        ]);

        $lineInputs = collect($v['received_qty'])
            ->mapWithKeys(fn($qty, $shipmentItemId) => [(int) $shipmentItemId => (float) $qty])
            ->filter(fn($qty) => $qty > 0);

        if ($lineInputs->isEmpty()) {
            throw ValidationException::withMessages([
                'received_qty' => 'Isi minimal satu qty terima untuk memproses receiving.',
            ]);
        }

        $allowOverReceipt = (bool) DB::table('settings')->where('key', 'allow_over_receipt')->value('value');
        $storedPath = null;

        DB::beginTransaction();
        try {
            $shipment = DB::table('shipments')->where('id', $v['shipment_id'])->lockForUpdate()->firstOrFail();

            if (! in_array($shipment->status, [
                DocumentTermCodes::SHIPMENT_SHIPPED,
                DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED,
            ], true)) {
                throw new \RuntimeException('Receiving hanya bisa diproses untuk shipment yang sudah berstatus Shipped atau Partial Received.');
            }

            $shipmentItems = $shipmentReceivingQuery->itemsForShipmentBuilder((int) $shipment->id)
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
                        'receipt_date' => $v['receipt_date'],
                        'purchase_order_id' => $poItem->purchase_order_id,
                        'shipment_id' => $shipment->id,
                        'warehouse_id' => $po->warehouse_id,
                        'received_by' => optional($request->user())->id,
                        'document_number' => $v['document_number'],
                        'remark' => $v['note'] ?? null,
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
                    'remark' => $v['note'] ?? null,
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
                    $purchaseOrderItemStatusResolver->resolve(
                        $newReceived,
                        $newOutstanding,
                        $poItem->etd_date
                    )
                ));

                DB::table('shipment_items')->where('id', $shipmentItemId)->update([
                    'received_qty' => (float) $item->shipment_received_qty + $receivedQty,
                    'updated_at' => now(),
                ]);

                ErpFlow::refreshPoStatusByOutstanding((int) $poItem->purchase_order_id, optional($request->user())->id);
            }

            if ($request->hasFile('attachment') && $grId) {
                $storedPath = $request->file('attachment')->store('attachments/receiving', 'public');
                DB::table('attachments')->insert([
                    'module' => 'goods_receipts',
                    'record_id' => $grId,
                    'file_path' => $storedPath,
                    'file_name' => basename($storedPath),
                    'uploaded_by' => optional($request->user())->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->refreshShipmentStatus((int) $shipment->id);
            ErpFlow::audit('goods_receipts', $grId, 'create', null, $v, optional($request->user())->id, $request->ip());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('receiving.process', [
            'shipment_id' => $v['shipment_id'],
            'supplier_id' => $request->input('supplier_id'),
            'document_number' => $request->input('search_document_number', $request->input('document_number')),
        ])->with('success', 'Goods Receipt berhasil diposting untuk dokumen shipment terpilih.');
    }

    private function storeSingleLineReceiving(
        Request $request,
        PurchaseOrderItemStatusResolver $purchaseOrderItemStatusResolver
    ): RedirectResponse
    {
        $v = $request->validate([
            'shipment_item_id' => 'required|integer|exists:shipment_items,id',
            'receipt_date' => 'required|date',
            'received_qty' => 'required|numeric|min:0.01',
            'accepted_qty' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
            'document_number' => 'required|string|max:100',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ], [
            'required' => ':attribute wajib diisi.',
            'received_qty.min' => 'Qty terima minimal 0.01.',
        ]);

        $allowOverReceipt = (bool) DB::table('settings')->where('key', 'allow_over_receipt')->value('value');
        $storedPath = null;

        DB::beginTransaction();
        try {
            $shipmentItem = DB::table('shipment_items')->where('id', $v['shipment_item_id'])->lockForUpdate()->firstOrFail();
            $poItem = DB::table('purchase_order_items')->where('id', $shipmentItem->purchase_order_item_id)->lockForUpdate()->firstOrFail();
            $po = DB::table('purchase_orders')->where('id', $poItem->purchase_order_id)->lockForUpdate()->firstOrFail();
            $shipment = DB::table('shipments')->where('id', $shipmentItem->shipment_id)->lockForUpdate()->firstOrFail();

            if (! in_array($shipment->status, [
                DocumentTermCodes::SHIPMENT_SHIPPED,
                DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED,
            ], true)) {
                throw new \RuntimeException('Receiving hanya bisa diproses untuk shipment yang sudah berstatus Shipped atau Partial Received.');
            }

            if (in_array($po->status, [
                DocumentTermCodes::PO_CANCELLED,
                DocumentTermCodes::PO_CLOSED,
            ], true)) {
                throw new \RuntimeException('PO dengan status ini tidak dapat diproses receiving.');
            }

            if ($poItem->item_status === DocumentTermCodes::ITEM_CANCELLED) {
                throw new \RuntimeException('Item sudah dibatalkan. Receiving tidak dapat diproses.');
            }

            $shipmentRemaining = max(0, (float) $shipmentItem->shipped_qty - (float) $shipmentItem->received_qty);
            if ($v['received_qty'] > $shipmentRemaining && ! $allowOverReceipt) {
                throw new \RuntimeException('Qty melebihi sisa qty pada shipment item.');
            }

            if ($v['received_qty'] > $poItem->outstanding_qty && ! $allowOverReceipt) {
                throw new \RuntimeException('Qty melebihi outstanding dan konfigurasi over-receipt tidak diizinkan.');
            }

            $grId = DB::table('goods_receipts')->insertGetId([
                'gr_number' => ErpFlow::generateNumber('GR', 'goods_receipts', 'gr_number'),
                'receipt_date' => $v['receipt_date'],
                'purchase_order_id' => $poItem->purchase_order_id,
                'shipment_id' => $shipment->id,
                'warehouse_id' => $po->warehouse_id,
                'received_by' => optional($request->user())->id,
                'document_number' => $v['document_number'],
                'remark' => $v['note'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ] + DomainStatus::payload(DomainStatus::GROUP_GOODS_RECEIPT_STATUS, 'status', DocumentTermCodes::GR_POSTED));

            $acceptedQty = $v['accepted_qty'] ?? $v['received_qty'];
            $variance = (float) $poItem->ordered_qty - (float) $v['received_qty'];

            DB::table('goods_receipt_items')->insert([
                'goods_receipt_id' => $grId,
                'shipment_item_id' => $shipmentItem->id,
                'purchase_order_item_id' => $poItem->id,
                'received_qty' => $v['received_qty'],
                'qty_variance' => $variance,
                'accepted_qty' => $acceptedQty,
                'remark' => $v['note'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($request->hasFile('attachment')) {
                $storedPath = $request->file('attachment')->store('attachments/receiving', 'public');
                DB::table('attachments')->insert([
                    'module' => 'goods_receipts',
                    'record_id' => $grId,
                    'file_path' => $storedPath,
                    'file_name' => basename($storedPath),
                    'uploaded_by' => optional($request->user())->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $newReceived = (float) $poItem->received_qty + (float) $v['received_qty'];
            $newOutstanding = max(0, (float) $poItem->ordered_qty - $newReceived);

            DB::table('purchase_order_items')->where('id', $poItem->id)->update([
                'received_qty' => $newReceived,
                'outstanding_qty' => $newOutstanding,
                'updated_at' => now(),
            ] + DomainStatus::payload(
                DomainStatus::GROUP_PO_ITEM_STATUS,
                'item_status',
                $purchaseOrderItemStatusResolver->resolve(
                    $newReceived,
                    $newOutstanding,
                    $poItem->etd_date
                )
            ));

            DB::table('shipment_items')->where('id', $shipmentItem->id)->update([
                'received_qty' => (float) $shipmentItem->received_qty + (float) $v['received_qty'],
                'updated_at' => now(),
            ]);

            ErpFlow::refreshPoStatusByOutstanding((int) $poItem->purchase_order_id, optional($request->user())->id);
            $this->refreshShipmentStatus((int) $shipment->id);
            ErpFlow::audit('goods_receipts', $grId, 'create', null, $v, optional($request->user())->id, $request->ip());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Goods Receipt item berhasil diposting.');
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
