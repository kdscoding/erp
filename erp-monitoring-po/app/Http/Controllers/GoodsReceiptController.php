<?php

namespace App\Http\Controllers;

use App\Support\ErpFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Database\Query\Expression;

class GoodsReceiptController extends Controller
{
    public function index(Request $request): View
    {
        $clearSelection = $request->boolean('clear_selection');

        $rows = DB::table('goods_receipts as gr')
            ->join('purchase_orders as po', 'po.id', '=', 'gr.purchase_order_id')
            ->leftJoin('shipments as sh', 'sh.id', '=', 'gr.shipment_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('gr.*', 'po.po_number', 's.supplier_name', 'sh.shipment_number', 'sh.delivery_note_number')
            ->when($request->filled('document_number'), fn ($q) => $q->where('gr.document_number', 'like', '%'.$request->string('document_number').'%'))
            ->orderByDesc('gr.id')
            ->paginate(20);

        $shipmentDocuments = DB::table('shipments as sh')
            ->join('suppliers as s', 's.id', '=', 'sh.supplier_id')
            ->join('shipment_items as si', 'si.shipment_id', '=', 'sh.id')
            ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->whereIn('sh.status', ['Shipped', 'Partial Received'])
            ->whereRaw('(si.shipped_qty - si.received_qty) > 0')
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('sh.supplier_id', $request->integer('supplier_id')))
            ->when($request->filled('document_number'), fn ($q) => $q->where('sh.delivery_note_number', 'like', '%'.$request->string('document_number').'%'))
            ->when($request->filled('keyword'), function ($q) use ($request) {
                $keyword = '%'.$request->string('keyword').'%';
                $q->where(function ($inner) use ($keyword) {
                    $inner->where('sh.shipment_number', 'like', $keyword)
                        ->orWhere('sh.delivery_note_number', 'like', $keyword)
                        ->orWhere('po.po_number', 'like', $keyword)
                        ->orWhere('s.supplier_name', 'like', $keyword);
                });
            })
            ->select(
                'sh.id',
                'sh.shipment_number',
                'sh.delivery_note_number',
                'sh.shipment_date',
                'sh.status',
                'sh.supplier_id',
                's.supplier_name'
            )
            ->selectRaw('COUNT(DISTINCT si.id) as line_count')
            ->selectRaw('COUNT(DISTINCT po.id) as po_count')
            ->selectRaw('SUM(si.shipped_qty - si.received_qty) as outstanding_qty')
            ->groupBy('sh.id', 'sh.shipment_number', 'sh.delivery_note_number', 'sh.shipment_date', 'sh.status', 'sh.supplier_id', 's.supplier_name')
            ->orderByDesc('sh.id')
            ->get();

        $selectedShipmentId = $request->integer('shipment_id');
        if (! $selectedShipmentId && ! $clearSelection && $shipmentDocuments->isNotEmpty()) {
            $selectedShipmentId = (int) $shipmentDocuments->first()->id;
        }

        $selectedShipment = $selectedShipmentId
            ? $shipmentDocuments->firstWhere('id', $selectedShipmentId)
            : null;

        $shipmentItems = $selectedShipmentId
            ? $this->shipmentItemsQuery()
                ->where('sh.id', $selectedShipmentId)
                ->orderBy('po.po_number')
                ->orderBy('i.item_code')
                ->get()
            : collect();

        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get(['id', 'supplier_name']);

        return view('receiving.index', compact('rows', 'shipmentDocuments', 'shipmentItems', 'selectedShipment', 'suppliers'));
    }

    public function store(Request $request)
    {
        if ($request->filled('shipment_id')) {
            return $this->storeDocumentReceiving($request);
        }

        return $this->storeSingleLineReceiving($request);
    }

    private function storeDocumentReceiving(Request $request)
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
            $shipment = DB::table('shipments')->where('id', $v['shipment_id'])->lockForUpdate()->firstOrFail();
            if (! in_array($shipment->status, ['Shipped', 'Partial Received'], true)) {
                throw new \RuntimeException('Receiving hanya bisa diproses untuk shipment yang sudah berstatus Shipped atau Partial Received.');
            }

            $shipmentItems = $this->shipmentItemsQuery()
                ->where('sh.id', $shipment->id)
                ->whereIn('si.id', $lineInputs->keys()->all())
                ->lockForUpdate()
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
                        'status' => 'Posted',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('goods_receipt_items')->insert([
                    'goods_receipt_id' => $grId,
                    'shipment_item_id' => $shipmentItemId,
                    'purchase_order_item_id' => $poItem->id,
                    'item_id' => $poItem->item_id,
                    'received_qty' => $receivedQty,
                    'qty_variance' => (float) $poItem->ordered_qty - $receivedQty,
                    'note' => $v['note'] ?? null,
                    'accepted_qty' => $receivedQty,
                    'rejected_qty' => 0,
                    'remark' => $v['note'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $newReceived = (float) $poItem->received_qty + $receivedQty;
                $newOutstanding = max(0, (float) $poItem->ordered_qty - $newReceived);

                DB::table('purchase_order_items')->where('id', $poItem->id)->update([
                    'received_qty' => $newReceived,
                    'outstanding_qty' => $newOutstanding,
                    'item_status' => $newOutstanding > 0 ? 'Partial' : 'Closed',
                    'updated_at' => now(),
                ]);

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

        return redirect()->route('receiving.index', [
            'shipment_id' => $v['shipment_id'],
            'supplier_id' => $request->input('supplier_id'),
            'document_number' => $request->input('search_document_number', $request->input('document_number')),
        ])->with('success', 'Goods Receipt berhasil diposting untuk dokumen shipment terpilih.');
    }

    private function storeSingleLineReceiving(Request $request)
    {
        $v = $request->validate([
            'shipment_item_id' => 'required|integer|exists:shipment_items,id',
            'receipt_date' => 'required|date',
            'received_qty' => 'required|numeric|min:0.01',
            'accepted_qty' => 'nullable|numeric|min:0',
            'rejected_qty' => 'nullable|numeric|min:0',
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

            if (! in_array($shipment->status, ['Shipped', 'Partial Received'], true)) {
                throw new \RuntimeException('Receiving hanya bisa diproses untuk shipment yang sudah berstatus Shipped atau Partial Received.');
            }

            if (in_array($po->status, ['Cancelled', 'Closed'], true)) {
                throw new \RuntimeException('PO dengan status ini tidak dapat diproses receiving.');
            }

            if ($poItem->item_status === 'Cancelled') {
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
                'status' => 'Posted',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $acceptedQty = $v['accepted_qty'] ?? $v['received_qty'];
            $rejectedQty = $v['rejected_qty'] ?? max(0, $v['received_qty'] - $acceptedQty);
            $variance = (float) $poItem->ordered_qty - (float) $v['received_qty'];

            DB::table('goods_receipt_items')->insert([
                'goods_receipt_id' => $grId,
                'shipment_item_id' => $shipmentItem->id,
                'purchase_order_item_id' => $poItem->id,
                'item_id' => $poItem->item_id,
                'received_qty' => $v['received_qty'],
                'qty_variance' => $variance,
                'note' => $v['note'] ?? null,
                'accepted_qty' => $acceptedQty,
                'rejected_qty' => $rejectedQty,
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
                'item_status' => $newOutstanding > 0 ? 'Partial' : 'Closed',
                'updated_at' => now(),
            ]);

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

    private function shipmentItemsQuery()
    {
        $currentDateSql = $this->currentDateExpression();

        return DB::table('shipment_items as si')
            ->join('shipments as sh', 'sh.id', '=', 'si.shipment_id')
            ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->leftJoin('goods_receipt_items as gri', 'gri.shipment_item_id', '=', 'si.id')
            ->select(
                'si.id as shipment_item_id',
                'si.shipment_id',
                'si.shipped_qty',
                'si.received_qty as shipment_received_qty',
                'poi.id as purchase_order_item_id',
                'poi.purchase_order_id',
                'poi.ordered_qty',
                'poi.received_qty',
                'poi.outstanding_qty',
                'poi.etd_date',
                'po.po_number',
                'po.status as po_status',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'sh.shipment_number',
                'sh.delivery_note_number',
                'sh.status as shipment_status',
                DB::raw('COALESCE(MAX(gri.created_at), NULL) as last_receipt_at')
            )
            ->selectRaw('(si.shipped_qty - si.received_qty) as shipment_outstanding_qty')
            ->selectRaw("CASE
                WHEN (si.shipped_qty - si.received_qty) <= 0 THEN 'Received'
                WHEN si.received_qty > 0 THEN 'Partial Received'
                WHEN poi.etd_date IS NULL THEN 'Waiting'
                WHEN DATE(poi.etd_date) < {$currentDateSql} THEN 'Late'
                ELSE 'Shipped'
            END as monitoring_status")
            ->whereIn('sh.status', ['Shipped', 'Partial Received'])
            ->whereRaw('(si.shipped_qty - si.received_qty) > 0')
            ->where('poi.item_status', '!=', 'Cancelled')
            ->groupBy(
                'si.id',
                'si.shipment_id',
                'si.shipped_qty',
                'si.received_qty',
                'poi.id',
                'poi.purchase_order_id',
                'poi.ordered_qty',
                'poi.received_qty',
                'poi.outstanding_qty',
                'poi.etd_date',
                'po.po_number',
                'po.status',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'sh.shipment_number',
                'sh.delivery_note_number',
                'sh.status'
            );
    }

    private function currentDateExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "date('now')"
            : 'CURDATE()';
    }

    private function refreshShipmentStatus(int $shipmentId): void
    {
        $shipmentSummary = DB::table('shipment_items')
            ->where('shipment_id', $shipmentId)
            ->selectRaw('SUM(CASE WHEN received_qty > 0 THEN 1 ELSE 0 END) as received_lines')
            ->selectRaw('SUM(CASE WHEN received_qty >= shipped_qty THEN 1 ELSE 0 END) as completed_lines')
            ->selectRaw('COUNT(*) as total_lines')
            ->first();

        $shipmentStatus = 'Shipped';
        if ((int) ($shipmentSummary->completed_lines ?? 0) === (int) ($shipmentSummary->total_lines ?? 0)) {
            $shipmentStatus = 'Received';
        } elseif ((int) ($shipmentSummary->received_lines ?? 0) > 0) {
            $shipmentStatus = 'Partial Received';
        }

        DB::table('shipments')->where('id', $shipmentId)->update([
            'status' => $shipmentStatus,
            'updated_at' => now(),
        ]);
    }
}
