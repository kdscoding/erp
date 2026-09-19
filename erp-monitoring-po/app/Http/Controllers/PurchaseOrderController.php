<?php

namespace App\Http\Controllers;

use App\Actions\CreatePurchaseOrder;
use App\Imports\PurchaseOrderImport;
use App\Queries\PurchaseOrders\PurchaseOrderDetailQuery;
use App\Queries\PurchaseOrders\PurchaseOrderIndexQuery;
use App\Support\DocumentTermCodes;
use App\Support\DocumentTermStatus;
use App\Support\DomainStatus;
use App\Support\ErpFlow;
use App\Support\PurchaseOrderItemStatusResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PurchaseOrderController extends Controller
{
    public function index(Request $request, PurchaseOrderIndexQuery $purchaseOrderIndexQuery): View
    {
        $baseQuery = $purchaseOrderIndexQuery->base($request);
        $rows = $baseQuery
            ->paginate(20)
            ->withQueryString();

        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get(['id', 'supplier_name', 'supplier_code']);

        $statusCounts = $purchaseOrderIndexQuery->base($request)
            ->get()
            ->groupBy('status')
            ->mapWithKeys(fn ($group, $key) => [
                DomainStatus::legacyValue(DomainStatus::GROUP_PO_STATUS, (string) $key) => $group->count(),
            ])
            ->all();

        $canonicalStatusOrder = [
            DocumentTermCodes::PO_ISSUED,
            DocumentTermCodes::PO_OPEN,
            DocumentTermCodes::PO_LATE,
            DocumentTermCodes::PO_CLOSED,
            DocumentTermCodes::PO_CANCELLED,
            'Full',
            'Partial',
            'Delayed',
        ];

        $summaryChips = collect($canonicalStatusOrder)
            ->map(fn ($status) => [
                'value' => $status,
                'label' => DocumentTermStatus::label(DomainStatus::GROUP_PO_STATUS, $status),
                'count' => $statusCounts[$status] ?? 0,
            ])
            ->prepend([
                'value' => '',
                'label' => 'Total',
                'count' => array_sum($statusCounts),
            ])
            ->values()
            ->all();

        $filterPoNumber = $request->query('po_number', '');
        $filterSupplierCode = $request->query('supplier_code', '');
        $filterDateFrom = $request->query('date_from', '');
        $filterDateTo = $request->query('date_to', '');
        $filterStatus = $request->query('status', '');

        return view('po.index', compact('rows', 'suppliers', 'summaryChips', 'filterPoNumber', 'filterSupplierCode', 'filterDateFrom', 'filterDateTo', 'filterStatus'));
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

    public function searchItems(Request $request)
    {
        $query = trim((string) $request->input('q', ''));

        $items = DB::table('items as i')
            ->leftJoin('units as u', 'u.id', '=', 'i.unit_id')
            ->select('i.id', 'i.item_code', 'i.item_name', DB::raw('COALESCE(u.unit_name, "") as unit_name'))
            ->when($query, fn ($q) => $q->where(function ($sub) use ($query) {
                $sub->where('i.item_code', 'like', '%'.$query.'%')
                    ->orWhere('i.item_name', 'like', '%'.$query.'%');
            }))
            ->orderBy('i.item_code')
            ->limit(100)
            ->get();

        return response()->json($items);
    }

    public function show(string $id, PurchaseOrderDetailQuery $purchaseOrderDetailQuery, Request $request): View
    {
        $data = $purchaseOrderDetailQuery->get($id, $request);

        $sort = $data['sort'] = $request->input('sort', 'item_code');
        $direction = $data['direction'] = $request->input('direction', 'asc');

        $data['suppliers'] = DB::table('suppliers')->orderBy('supplier_name')->get(['id', 'supplier_name', 'supplier_code']);

        return view('po.show', $data);
    }

    public function edit(string $id, PurchaseOrderDetailQuery $purchaseOrderDetailQuery): View
    {
        $data = $purchaseOrderDetailQuery->get($id);

        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get(['id', 'supplier_name', 'supplier_code']);

        return view('po.show', array_merge($data, [
            'suppliers' => $suppliers,
            'editing' => true,
        ]));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'po_number' => 'required|string|max:100|unique:purchase_orders,po_number,'.(int) $id,
            'po_date' => 'required|date',
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'notes' => 'nullable|string|max:500',
        ], [
            'required' => ':attribute wajib diisi.',
            'po_number.unique' => 'Nomor PO sudah digunakan oleh PO lain.',
        ]);

        DB::table('purchase_orders')->where('id', (int) $id)->update([
            'po_number' => $validated['po_number'],
            'po_date' => $validated['po_date'],
            'supplier_id' => $validated['supplier_id'],
            'notes' => $validated['notes'] ?? null,
            'updated_at' => now(),
            'updated_by' => optional($request->user())->id,
        ]);

        ErpFlow::audit('purchase_orders', (int) $id, 'po_header_update',
            ['po_number', 'po_date', 'supplier_id', 'notes'],
            [$validated['po_number'], $validated['po_date'], $validated['supplier_id'], $validated['notes'] ?? null],
            optional($request->user())->id,
            $request->ip()
        );

        return redirect()->route('po.show', $validated['po_number'])
            ->with('success', 'Header PO berhasil diperbarui.');
    }

    public function refreshStatus(string $id, PurchaseOrderDetailQuery $purchaseOrderDetailQuery): RedirectResponse
    {
        $data = $purchaseOrderDetailQuery->get($id);

        ErpFlow::refreshPoStatusByOutstanding((int) $data['po']->id, optional(request()->user())->id);

        return redirect()->route('po.show', $data['po']->po_number)
            ->with('success', 'Status PO berhasil di-refresh.');
    }

    public function exportItemTrackingText(string $id, string $itemId, PurchaseOrderDetailQuery $purchaseOrderDetailQuery)
    {
        $data = $purchaseOrderDetailQuery->get($id);
        $item = $data['items']->firstWhere('id', (int) $itemId);

        abort_if($item === null, 404);

        $content = view('po.exports.tracking-copy', [
            'po' => $data['po'],
            'item' => $item,
            'timeline' => $data['trackingData'][$item->id]['timeline'] ?? [],
            'generatedAt' => now(),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="tracking-copy-'.$item->item_code.'.txt"',
        ]);
    }

    public function exportItemTrackingExcel(string $id, string $itemId, PurchaseOrderDetailQuery $purchaseOrderDetailQuery)
    {
        $data = $purchaseOrderDetailQuery->get($id);
        $item = $data['items']->firstWhere('id', (int) $itemId);

        abort_if($item === null, 404);

        $content = view('po.exports.tracking-tsv', [
            'po' => $data['po'],
            'item' => $item,
            'timeline' => $data['trackingData'][$item->id]['timeline'] ?? [],
            'generatedAt' => now(),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="tracking-'.$item->item_code.'.xls"',
        ]);
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
            'Content-Disposition' => 'attachment; filename="po-monitoring-'.now()->format('Ymd-His').'.xls"',
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
            'Content-Disposition' => 'attachment; filename="po-detail-'.$data['po']->po_number.'.xls"',
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

        if ($request->input('save_and_new')) {
            return redirect()
                ->route('po.create')
                ->with('success', 'PO berhasil dibuat. Buat PO berikutnya.')
                ->withInput([
                    'supplier_id' => $validated['supplier_id'],
                    'po_date' => $validated['po_date'],
                ]);
        }

        return redirect()
            ->route('po.index')
            ->with('success', 'PO berhasil dibuat dengan status '.DocumentTermCodes::PO_ISSUED.'.');
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PO Import');

        $columns = ['po_number', 'po_date', 'supplier_code', 'currency', 'notes', 'item_code', 'ordered_qty', 'unit_price', 'etd_date', 'remarks'];
        foreach ($columns as $index => $col) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $col);
        }
        foreach ($columns as $index => $col) {
            $sheet->setCellValueByColumnAndRow($index + 1, 2, '');
        }
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        $tempPath = storage_path('app/temp/'.uniqid('po_template_', true).'.xlsx');

        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0775, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, 'po-import-template.xlsx')->deleteFileAfterSend(true);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
        ], [
            'file.required' => 'File wajib dipilih.',
        ]);

        $import = new PurchaseOrderImport;

        try {
            $import->handle($request->file('file'));
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = "Import berhasil. {$import->inserted} PO ditambahkan.";

        return redirect()->route('po.index')->with('success', $message);
    }

    public function updateItemSchedule(
        Request $request,
        string $itemId,
        PurchaseOrderItemStatusResolver $purchaseOrderItemStatusResolver
    ): RedirectResponse {
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

        ErpFlow::refreshPoStatusByOutstanding((int) $item->purchase_order_id, optional($request->user())->id);

        $newEta = ErpFlow::resolvePoEtaDate((int) $item->purchase_order_id);
        DB::table('purchase_orders')->where('id', $item->purchase_order_id)->update([
            'eta_date' => $newEta,
            'updated_at' => now(),
        ]);

        ErpFlow::audit(
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
    ): RedirectResponse {
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

                ErpFlow::audit(
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

            ErpFlow::refreshPoStatusByOutstanding((int) $id, optional($request->user())->id);

            $newEta = ErpFlow::resolvePoEtaDate((int) $id);
            DB::table('purchase_orders')->where('id', $id)->update([
                'eta_date' => $newEta,
                'updated_at' => now(),
            ]);
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

            ErpFlow::audit(
                'purchase_order_items',
                (int) $itemId,
                'item_cancelled',
                ['item_status' => $item->item_status, 'cancel_reason' => $item->cancel_reason],
                ['item_status' => DocumentTermCodes::ITEM_CANCELLED, 'cancel_reason' => $validated['cancel_reason']],
                optional($request->user())->id,
                $request->ip()
            );

            ErpFlow::refreshPoStatusByOutstanding((int) $item->purchase_order_id, optional($request->user())->id);
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
                'cancel_reason' => '[FORCE CLOSE] '.$validated['cancel_reason'],
                'outstanding_qty' => 0,
                'updated_at' => now(),
            ] + DomainStatus::payload(DomainStatus::GROUP_PO_ITEM_STATUS, 'item_status', DocumentTermCodes::ITEM_FORCE_CLOSED));

            ErpFlow::audit(
                'purchase_order_items',
                (int) $itemId,
                'item_force_close',
                ['item_status' => $item->item_status, 'cancel_reason' => $item->cancel_reason],
                ['item_status' => DocumentTermCodes::ITEM_FORCE_CLOSED, 'cancel_reason' => '[FORCE CLOSE] '.$validated['cancel_reason']],
                optional($request->user())->id,
                $request->ip()
            );

            ErpFlow::refreshPoStatusByOutstanding((int) $item->purchase_order_id, optional($request->user())->id);
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

            ErpFlow::pushPoStatus((int) $id, (string) $po->status, DocumentTermCodes::PO_CANCELLED, $userId, $validated['cancel_reason']);
            ErpFlow::audit('purchase_orders', (int) $id, 'po_cancelled', ['status' => $po->status], ['status' => DocumentTermCodes::PO_CANCELLED, 'cancel_reason' => $validated['cancel_reason']], $userId, $request->ip());

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
