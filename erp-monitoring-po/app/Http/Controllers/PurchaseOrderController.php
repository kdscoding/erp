<?php

namespace App\Http\Controllers;

use App\Actions\CreatePurchaseOrder;
use App\Support\DocumentTermCodes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $rows = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('po.*', 's.supplier_name')
            ->when($request->filled('status'), fn($q) => $q->where('po.status', $request->string('status')))
            ->when($request->filled('supplier_id'), fn($q) => $q->where('po.supplier_id', $request->integer('supplier_id')))
            ->orderByDesc('po.id')
            ->paginate(20)
            ->withQueryString();

        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get(['id', 'supplier_name']);

        return view('po.index', compact('rows', 'suppliers'));
    }

    public function create(): View
    {
        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get();
        $items = DB::table('items as i')
            ->leftJoin('units as u', 'u.id', '=', 'i.unit_id')
            ->select('i.id', 'i.item_code', 'i.item_name', DB::raw('COALESCE(u.unit_name, "") as unit_name'))
            ->orderBy('i.item_code')
            ->limit(1000)
            ->get();

        return view('po.create', compact('suppliers', 'items'));
    }

    public function show(string $id): View
    {
        $currentDateSql = $this->currentDateExpression();

        $po = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('plants as p', 'p.id', '=', 'po.plant_id')
            ->leftJoin('warehouses as w', 'w.id', '=', 'po.warehouse_id')
            ->select('po.*', 's.supplier_name', 'p.plant_name', 'w.warehouse_name')
            ->where('po.id', $id)
            ->firstOrFail();

        $items = DB::table('purchase_order_items as poi')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->leftJoin('units as u', 'u.id', '=', 'i.unit_id')
            ->select('poi.*', 'i.item_code', 'i.item_name', 'u.unit_name')
            ->selectRaw("CASE
                WHEN poi.item_status = '" . DocumentTermCodes::ITEM_CANCELLED . "' THEN '" . DocumentTermCodes::ITEM_CANCELLED . "'
                WHEN poi.outstanding_qty <= 0 THEN '" . DocumentTermCodes::ITEM_CLOSED . "'
                WHEN poi.received_qty > 0 THEN '" . DocumentTermCodes::ITEM_PARTIAL . "'
                WHEN poi.etd_date IS NULL THEN '" . DocumentTermCodes::ITEM_WAITING . "'
                WHEN DATE(poi.etd_date) < {$currentDateSql} THEN '" . DocumentTermCodes::ITEM_LATE . "'
                ELSE '" . DocumentTermCodes::ITEM_CONFIRMED . "'
            END as monitoring_status")
            ->where('poi.purchase_order_id', $id)
            ->orderBy('poi.id')
            ->get();

        $poIsFinal = in_array($po->status, [
            DocumentTermCodes::PO_CLOSED,
            DocumentTermCodes::PO_CANCELLED,
        ], true);

        $items = $items->map(function ($item) use ($poIsFinal) {
            $itemIsFinal = in_array($item->monitoring_status, [
                DocumentTermCodes::ITEM_CLOSED,
                DocumentTermCodes::ITEM_CANCELLED,
            ], true);

            $item->can_update_etd = ! $poIsFinal && ! $itemIsFinal;
            $item->can_cancel = ! $poIsFinal && ! $itemIsFinal && (float) $item->received_qty <= 0;
            $item->can_force_close = ! $poIsFinal && ! $itemIsFinal && (float) $item->outstanding_qty > 0;

            return $item;
        });

        $trackingRows = DB::table('purchase_order_items as poi')
            ->leftJoin('shipment_items as si', 'si.purchase_order_item_id', '=', 'poi.id')
            ->leftJoin('shipments as sh', 'sh.id', '=', 'si.shipment_id')
            ->leftJoin('goods_receipt_items as gri', function ($join) {
                $join->on('gri.purchase_order_item_id', '=', 'poi.id')
                    ->on('gri.shipment_item_id', '=', 'si.id');
            })
            ->leftJoin('goods_receipts as gr', 'gr.id', '=', 'gri.goods_receipt_id')
            ->where('poi.purchase_order_id', $id)
            ->select(
                'poi.id as purchase_order_item_id',
                'si.id as shipment_item_id',
                'si.shipped_qty',
                'si.received_qty as shipment_received_qty',
                'sh.id as shipment_id',
                'sh.shipment_number',
                'sh.shipment_date',
                'sh.delivery_note_number',
                'sh.status as shipment_status',
                'gr.id as goods_receipt_id',
                'gr.gr_number',
                'gr.receipt_date',
                'gr.document_number as gr_document_number',
                'gr.status as gr_status',
                'gri.received_qty as gr_received_qty',
                'gri.accepted_qty',
                'gri.rejected_qty'
            )
            ->orderBy('sh.shipment_date')
            ->orderBy('sh.id')
            ->orderBy('gr.receipt_date')
            ->orderBy('gr.id')
            ->get()
            ->groupBy('purchase_order_item_id');

        $items = $items->map(function ($item) use ($trackingRows) {
            $rows = collect($trackingRows->get($item->id, []))
                ->filter(fn($row) => $row->shipment_id || $row->goods_receipt_id)
                ->values();

            $item->tracking_rows = $rows
                ->groupBy(fn($row) => $row->shipment_item_id ?: 'no-shipment')
                ->map(function ($shipmentRows) {
                    $base = $shipmentRows->first();

                    return (object) [
                        'shipment_id' => $base->shipment_id,
                        'shipment_item_id' => $base->shipment_item_id,
                        'shipment_number' => $base->shipment_number,
                        'shipment_date' => $base->shipment_date,
                        'delivery_note_number' => $base->delivery_note_number,
                        'shipment_status' => $base->shipment_status,
                        'shipped_qty' => $base->shipped_qty,
                        'shipment_received_qty' => $base->shipment_received_qty,
                        'gr_rows' => $shipmentRows
                            ->filter(fn($row) => $row->goods_receipt_id)
                            ->map(fn($row) => (object) [
                                'goods_receipt_id' => $row->goods_receipt_id,
                                'gr_number' => $row->gr_number,
                                'receipt_date' => $row->receipt_date,
                                'gr_document_number' => $row->gr_document_number,
                                'gr_status' => $row->gr_status,
                                'gr_received_qty' => $row->gr_received_qty,
                                'accepted_qty' => $row->accepted_qty,
                                'rejected_qty' => $row->rejected_qty,
                            ])
                            ->values(),
                    ];
                })
                ->values();

            return $item;
        });

        $itemSummary = [
            'total' => $items->count(),
            'waiting' => $items->where('monitoring_status', DocumentTermCodes::ITEM_WAITING)->count(),
            'confirmed' => $items->where('monitoring_status', DocumentTermCodes::ITEM_CONFIRMED)->count(),
            'late' => $items->where('monitoring_status', DocumentTermCodes::ITEM_LATE)->count(),
            'partial' => $items->where('monitoring_status', DocumentTermCodes::ITEM_PARTIAL)->count(),
            'closed' => $items->where('monitoring_status', DocumentTermCodes::ITEM_CLOSED)->count(),
            'cancelled' => $items->where('monitoring_status', DocumentTermCodes::ITEM_CANCELLED)->count(),
        ];

        $itemSummary['active'] = $itemSummary['total'] - $itemSummary['cancelled'];
        $itemSummary['progress_label'] = match (true) {
            $itemSummary['active'] === 0 => 'Semua item dibatalkan',
            $itemSummary['partial'] > 0 && ($itemSummary['waiting'] > 0 || $itemSummary['confirmed'] > 0 || $itemSummary['late'] > 0) => 'Receiving berjalan, masih ada item belum selesai',
            $itemSummary['partial'] > 0 => 'Receiving parsial',
            $itemSummary['confirmed'] > 0 && ($itemSummary['waiting'] > 0 || $itemSummary['late'] > 0) => 'Konfirmasi supplier masih campuran',
            $itemSummary['late'] > 0 => 'Ada item overdue ETD',
            $itemSummary['confirmed'] > 0 => 'Seluruh item aktif sudah terkonfirmasi',
            $itemSummary['waiting'] === $itemSummary['active'] => 'Semua item aktif masih waiting',
            $itemSummary['closed'] === $itemSummary['active'] => 'Semua item selesai',
            default => 'Perlu review manual',
        };

        $histories = DB::table('po_status_histories as h')
            ->leftJoin('users as u', 'u.id', '=', 'h.changed_by')
            ->where('h.purchase_order_id', $id)
            ->orderByDesc('h.id')
            ->select('h.*', 'u.name as changed_by_name')
            ->get();

        $poCanCancel = ! $poIsFinal;

        return view('po.show', compact('po', 'items', 'itemSummary', 'histories', 'poCanCancel', 'poIsFinal'));
    }

    public function store(Request $request, CreatePurchaseOrder $createPurchaseOrder): RedirectResponse
    {
        $validated = $request->validate([
            'po_number' => 'nullable|string|max:100|unique:purchase_orders,po_number',
            'po_date' => 'required|date',
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.ordered_qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.remarks' => 'nullable|string|max:500',
        ], [
            'required' => ':attribute wajib diisi.',
            'items.min' => 'Minimal harus ada 1 item.',
        ]);

        try {
            $createPurchaseOrder->handle(
                $validated,
                optional($request->user())->id,
                $request->ip(),
                $request->input('notes')
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('po.index')
            ->with('success', 'PO berhasil dibuat dengan status ' . DocumentTermCodes::PO_ISSUED . '.');
    }

    public function updateItemSchedule(Request $request, string $itemId): RedirectResponse
    {
        $item = DB::table('purchase_order_items')->where('id', $itemId)->firstOrFail();
        $poStatus = DB::table('purchase_orders')->where('id', $item->purchase_order_id)->value('status');

        $v = $request->validate([
            'etd_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:500',
        ]);

        if (! $this->canUpdateItemSchedule($item, $poStatus)) {
            return back()->with('error', 'ETD hanya bisa diubah untuk item aktif pada PO yang belum final.');
        }

        $newStatus = $item->outstanding_qty <= 0
            ? DocumentTermCodes::ITEM_CLOSED
            : (($item->received_qty > 0)
                ? DocumentTermCodes::ITEM_PARTIAL
                : (($v['etd_date'] ?? null)
                    ? DocumentTermCodes::ITEM_CONFIRMED
                    : DocumentTermCodes::ITEM_WAITING));

        DB::table('purchase_order_items')->where('id', $itemId)->update([
            'etd_date' => $v['etd_date'] ?? null,
            'item_status' => $newStatus,
            'remarks' => $v['remarks'] ?? DB::table('purchase_order_items')->where('id', $itemId)->value('remarks'),
            'updated_at' => now(),
        ]);

        \App\Support\ErpFlow::refreshPoStatusByOutstanding((int) $item->purchase_order_id, optional($request->user())->id);

        \App\Support\ErpFlow::audit(
            'purchase_order_items',
            (int) $itemId,
            'item_schedule_update',
            ['etd_date' => $item->etd_date, 'item_status' => $item->item_status],
            ['etd_date' => $v['etd_date'] ?? null, 'item_status' => $newStatus],
            optional($request->user())->id,
            $request->ip()
        );

        return back()->with('success', 'ETD item berhasil diperbarui.');
    }

    public function cancelItem(Request $request, string $itemId): RedirectResponse
    {
        $validated = $request->validate([
            'cancel_reason' => 'required|string|max:1000',
        ], [
            'cancel_reason.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            $item = DB::table('purchase_order_items')->where('id', $itemId)->lockForUpdate()->firstOrFail();
            $poStatus = DB::table('purchase_orders')->where('id', $item->purchase_order_id)->value('status');

            if (! $this->canCancelItem($item, $poStatus)) {
                throw new \RuntimeException('Cancel item hanya boleh untuk item aktif yang belum pernah diterima dan PO belum final.');
            }

            DB::table('purchase_order_items')->where('id', $itemId)->update([
                'item_status' => DocumentTermCodes::ITEM_CANCELLED,
                'cancel_reason' => $validated['cancel_reason'],
                'outstanding_qty' => 0,
                'updated_at' => now(),
            ]);

            \App\Support\ErpFlow::audit(
                'purchase_order_items',
                (int) $itemId,
                'item_cancelled',
                ['item_status' => $item->item_status, 'cancel_reason' => $item->cancel_reason],
                ['item_status' => DocumentTermCodes::ITEM_CANCELLED, 'cancel_reason' => $validated['cancel_reason']],
                optional($request->user())->id,
                $request->ip()
            );

            \App\Support\ErpFlow::refreshPoStatusByOutstanding((int) $item->purchase_order_id, optional($request->user())->id);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Item berhasil dibatalkan.');
    }

    public function forceCloseItem(Request $request, string $itemId): RedirectResponse
    {
        $validated = $request->validate([
            'cancel_reason' => 'required|string|max:1000',
        ], [
            'cancel_reason.required' => 'Alasan force close wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            $item = DB::table('purchase_order_items')->where('id', $itemId)->lockForUpdate()->firstOrFail();
            $poStatus = DB::table('purchase_orders')->where('id', $item->purchase_order_id)->value('status');

            if (! $this->canForceCloseItem($item, $poStatus)) {
                throw new \RuntimeException('Force close hanya boleh untuk item aktif yang masih memiliki outstanding dan PO belum final.');
            }

            DB::table('purchase_order_items')->where('id', $itemId)->update([
                'item_status' => DocumentTermCodes::ITEM_CANCELLED,
                'cancel_reason' => '[FORCE CLOSE] ' . $validated['cancel_reason'],
                'outstanding_qty' => 0,
                'updated_at' => now(),
            ]);

            \App\Support\ErpFlow::audit(
                'purchase_order_items',
                (int) $itemId,
                'item_force_close',
                ['item_status' => $item->item_status, 'cancel_reason' => $item->cancel_reason],
                ['item_status' => DocumentTermCodes::ITEM_CANCELLED, 'cancel_reason' => '[FORCE CLOSE] ' . $validated['cancel_reason']],
                optional($request->user())->id,
                $request->ip()
            );

            \App\Support\ErpFlow::refreshPoStatusByOutstanding((int) $item->purchase_order_id, optional($request->user())->id);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Force close item berhasil. Status item menjadi Cancelled.');
    }

    public function cancelPo(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'cancel_reason' => 'required|string|max:1000',
        ], [
            'cancel_reason.required' => 'Alasan pembatalan PO wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            $po = DB::table('purchase_orders')->where('id', $id)->lockForUpdate()->firstOrFail();
            $userId = optional($request->user())->id;

            if (! $this->canCancelPo($po)) {
                throw new \RuntimeException('PO yang sudah Closed atau Cancelled tidak dapat dibatalkan lagi.');
            }

            DB::table('purchase_orders')->where('id', $id)->update([
                'status' => DocumentTermCodes::PO_CANCELLED,
                'eta_date' => null,
                'cancel_reason' => $validated['cancel_reason'],
                'updated_at' => now(),
                'updated_by' => $userId,
            ]);

            DB::table('purchase_order_items')
                ->where('purchase_order_id', $id)
                ->where('item_status', '!=', DocumentTermCodes::ITEM_CLOSED)
                ->update([
                    'item_status' => DocumentTermCodes::ITEM_CANCELLED,
                    'cancel_reason' => $validated['cancel_reason'],
                    'outstanding_qty' => 0,
                    'updated_at' => now(),
                ]);

            \App\Support\ErpFlow::pushPoStatus((int) $id, (string) $po->status, DocumentTermCodes::PO_CANCELLED, $userId, $validated['cancel_reason']);
            \App\Support\ErpFlow::audit('purchase_orders', (int) $id, 'po_cancelled', ['status' => $po->status], ['status' => DocumentTermCodes::PO_CANCELLED, 'cancel_reason' => $validated['cancel_reason']], $userId, $request->ip());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'PO berhasil dibatalkan.');
    }

    private function canCancelPo(object $po): bool
    {
        return ! in_array($po->status, [DocumentTermCodes::PO_CLOSED, DocumentTermCodes::PO_CANCELLED], true);
    }

    private function canUpdateItemSchedule(object $item, ?string $poStatus): bool
    {
        return ! in_array($poStatus, [DocumentTermCodes::PO_CLOSED, DocumentTermCodes::PO_CANCELLED], true)
            && ! in_array($item->item_status, [DocumentTermCodes::ITEM_CLOSED, DocumentTermCodes::ITEM_CANCELLED], true);
    }

    private function canCancelItem(object $item, ?string $poStatus): bool
    {
        return ! in_array($poStatus, [DocumentTermCodes::PO_CLOSED, DocumentTermCodes::PO_CANCELLED], true)
            && ! in_array($item->item_status, [DocumentTermCodes::ITEM_CLOSED, DocumentTermCodes::ITEM_CANCELLED], true)
            && (float) $item->received_qty <= 0;
    }

    private function canForceCloseItem(object $item, ?string $poStatus): bool
    {
        return ! in_array($poStatus, [DocumentTermCodes::PO_CLOSED, DocumentTermCodes::PO_CANCELLED], true)
            && ! in_array($item->item_status, [DocumentTermCodes::ITEM_CLOSED, DocumentTermCodes::ITEM_CANCELLED], true)
            && (float) $item->outstanding_qty > 0;
    }

    private function currentDateExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "date('now')"
            : 'CURDATE()';
    }
}
