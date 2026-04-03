<?php

namespace App\Http\Controllers;

use App\Actions\CreatePurchaseOrder;
use App\Queries\PurchaseOrders\PurchaseOrderDetailQuery;
use App\Queries\PurchaseOrders\PurchaseOrderIndexQuery;
use App\Support\DocumentTermCodes;
use App\Support\DomainStatus;
use App\Support\PurchaseOrderItemStatusResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request, PurchaseOrderIndexQuery $purchaseOrderIndexQuery): View
    {
        $rows = $purchaseOrderIndexQuery->base($request)
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

    public function show(string $id, PurchaseOrderDetailQuery $purchaseOrderDetailQuery): View
    {
        $data = $purchaseOrderDetailQuery->get($id);

        return view('po.show', $data);
    }

    public function exportIndexExcel(Request $request, PurchaseOrderIndexQuery $purchaseOrderIndexQuery)
    {
        $rows = $purchaseOrderIndexQuery->base($request)->get();

        $content = view('po.exports.index', [
            'rows' => $rows,
            'generatedAt' => now(),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="po-monitoring-' . now()->format('Ymd-His') . '.xls"',
        ]);
    }

    public function exportDetailExcel(string $id, PurchaseOrderDetailQuery $purchaseOrderDetailQuery)
    {
        $data = $purchaseOrderDetailQuery->get($id);

        $content = view('po.exports.detail', array_merge($data, [
            'generatedAt' => now(),
        ]))->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="po-detail-' . $data['po']->po_number . '.xls"',
        ]);
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

    public function updateItemSchedule(
        Request $request,
        string $itemId,
        PurchaseOrderItemStatusResolver $purchaseOrderItemStatusResolver
    ): RedirectResponse
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

        $newStatus = $purchaseOrderItemStatusResolver->resolve(
            (float) $item->received_qty,
            (float) $item->outstanding_qty,
            $v['etd_date'] ?? null
        );

        DB::table('purchase_order_items')->where('id', $itemId)->update([
            'etd_date' => $v['etd_date'] ?? null,
            'remarks' => $v['remarks'] ?? DB::table('purchase_order_items')->where('id', $itemId)->value('remarks'),
            'updated_at' => now(),
        ] + DomainStatus::payload(DomainStatus::GROUP_PO_ITEM_STATUS, 'item_status', $newStatus));

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

    public function bulkUpdateItemSchedule(
        Request $request,
        string $id,
        PurchaseOrderItemStatusResolver $purchaseOrderItemStatusResolver
    ): RedirectResponse
    {
        $validated = $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'required|integer',
            'etd_date' => 'nullable|date',
            'day_offset' => 'nullable|integer|min:-30|max:30',
        ], [
            'item_ids.required' => 'Pilih minimal satu item untuk bulk update ETD.',
            'item_ids.min' => 'Pilih minimal satu item untuk bulk update ETD.',
        ]);

        $offset = (int) ($validated['day_offset'] ?? 0);
        $baseDate = $validated['etd_date'] ?? null;

        if ($baseDate === null && $offset !== 0) {
            return back()->with('error', 'Isi tanggal ETD dasar jika ingin memakai offset hari.');
        }

        $targetDate = $baseDate
            ? now()->parse($baseDate)->addDays($offset)->toDateString()
            : null;

        DB::beginTransaction();
        try {
            $po = DB::table('purchase_orders')->where('id', $id)->lockForUpdate()->firstOrFail();

            if (! $this->canCancelPo((object) ['status' => $po->status])) {
                throw new \RuntimeException('Bulk update ETD hanya bisa dijalankan untuk PO yang belum final.');
            }

            $items = DB::table('purchase_order_items')
                ->where('purchase_order_id', $id)
                ->whereIn('id', $validated['item_ids'])
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw new \RuntimeException('Item yang dipilih tidak valid untuk PO ini.');
            }

            $updatedCount = 0;

            foreach ($items as $item) {
                if (! $this->canUpdateItemSchedule($item, $po->status)) {
                    continue;
                }

                $newStatus = $purchaseOrderItemStatusResolver->resolve(
                    (float) $item->received_qty,
                    (float) $item->outstanding_qty,
                    $targetDate
                );

                DB::table('purchase_order_items')->where('id', $item->id)->update([
                    'etd_date' => $targetDate,
                    'updated_at' => now(),
                ] + DomainStatus::payload(DomainStatus::GROUP_PO_ITEM_STATUS, 'item_status', $newStatus));

                \App\Support\ErpFlow::audit(
                    'purchase_order_items',
                    (int) $item->id,
                    'item_schedule_bulk_update',
                    ['etd_date' => $item->etd_date, 'item_status' => $item->item_status],
                    ['etd_date' => $targetDate, 'item_status' => $newStatus],
                    optional($request->user())->id,
                    $request->ip()
                );

                $updatedCount++;
            }

            if ($updatedCount === 0) {
                throw new \RuntimeException('Tidak ada item aktif yang bisa diupdate dari pilihan tersebut.');
            }

            \App\Support\ErpFlow::refreshPoStatusByOutstanding((int) $id, optional($request->user())->id);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Bulk update ETD berhasil untuk {$updatedCount} item.");
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
                'cancel_reason' => $validated['cancel_reason'],
                'outstanding_qty' => 0,
                'updated_at' => now(),
            ] + DomainStatus::payload(DomainStatus::GROUP_PO_ITEM_STATUS, 'item_status', DocumentTermCodes::ITEM_CANCELLED));

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
                'cancel_reason' => '[FORCE CLOSE] ' . $validated['cancel_reason'],
                'outstanding_qty' => 0,
                'updated_at' => now(),
            ] + DomainStatus::payload(DomainStatus::GROUP_PO_ITEM_STATUS, 'item_status', DocumentTermCodes::ITEM_FORCE_CLOSED));

            \App\Support\ErpFlow::audit(
                'purchase_order_items',
                (int) $itemId,
                'item_force_close',
                ['item_status' => $item->item_status, 'cancel_reason' => $item->cancel_reason],
                ['item_status' => DocumentTermCodes::ITEM_FORCE_CLOSED, 'cancel_reason' => '[FORCE CLOSE] ' . $validated['cancel_reason']],
                optional($request->user())->id,
                $request->ip()
            );

            \App\Support\ErpFlow::refreshPoStatusByOutstanding((int) $item->purchase_order_id, optional($request->user())->id);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Force close item berhasil. Status item menjadi Force Closed.');
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
                'eta_date' => null,
                'cancel_reason' => $validated['cancel_reason'],
                'updated_at' => now(),
                'updated_by' => $userId,
            ] + DomainStatus::payload(DomainStatus::GROUP_PO_STATUS, 'status', DocumentTermCodes::PO_CANCELLED));

            DB::table('purchase_order_items')
                ->where('purchase_order_id', $id)
                ->whereNotIn('item_status', [DocumentTermCodes::ITEM_CLOSED, DocumentTermCodes::ITEM_FORCE_CLOSED])
                ->update([
                    'cancel_reason' => $validated['cancel_reason'],
                    'outstanding_qty' => 0,
                    'updated_at' => now(),
                ] + DomainStatus::payload(DomainStatus::GROUP_PO_ITEM_STATUS, 'item_status', DocumentTermCodes::ITEM_CANCELLED));

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
            && ! in_array($item->item_status, [DocumentTermCodes::ITEM_CLOSED, DocumentTermCodes::ITEM_FORCE_CLOSED, DocumentTermCodes::ITEM_CANCELLED], true);
    }

    private function canCancelItem(object $item, ?string $poStatus): bool
    {
        return ! in_array($poStatus, [DocumentTermCodes::PO_CLOSED, DocumentTermCodes::PO_CANCELLED], true)
            && ! in_array($item->item_status, [DocumentTermCodes::ITEM_CLOSED, DocumentTermCodes::ITEM_FORCE_CLOSED, DocumentTermCodes::ITEM_CANCELLED], true)
            && (float) $item->received_qty <= 0;
    }

    private function canForceCloseItem(object $item, ?string $poStatus): bool
    {
        return ! in_array($poStatus, [DocumentTermCodes::PO_CLOSED, DocumentTermCodes::PO_CANCELLED], true)
            && ! in_array($item->item_status, [DocumentTermCodes::ITEM_CLOSED, DocumentTermCodes::ITEM_FORCE_CLOSED, DocumentTermCodes::ITEM_CANCELLED], true)
            && (float) $item->outstanding_qty > 0;
    }

}
