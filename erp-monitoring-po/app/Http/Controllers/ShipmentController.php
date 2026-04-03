<?php

namespace App\Http\Controllers;

use App\Support\DocumentTermCodes;
use App\Support\DomainStatus;
use App\Support\ErpFlow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ShipmentController extends Controller
{
    private const SHIPPABLE_PO_STATUSES = [
        DocumentTermCodes::PO_ISSUED,
        DocumentTermCodes::PO_OPEN,
        DocumentTermCodes::PO_LATE,
    ];

    public function index(Request $request): View
    {
        $viewMode = (string) ($request->route()->defaults['view'] ?? $request->get('view', 'worklist'));
        $syncSelection = $request->boolean('sync_selection');

        if ($request->boolean('clear_selection')) {
            $request->session()->forget('shipment_selected_items');
            $request->session()->forget('shipment_shipped_qty');
            $request->session()->forget('shipment_invoice_unit_price');
        }

        if ($syncSelection || $request->has('shipped_qty')) {
            $request->session()->put(
                'shipment_shipped_qty',
                collect($request->input('shipped_qty', []))
                    ->mapWithKeys(fn($qty, $itemId) => [(int) $itemId => (float) $qty])
                    ->all()
            );
        }

        if ($syncSelection || $request->has('invoice_unit_price')) {
            $request->session()->put(
                'shipment_invoice_unit_price',
                collect($request->input('invoice_unit_price', []))
                    ->mapWithKeys(function ($price, $itemId) {
                        $normalized = ($price === null || $price === '') ? null : (float) $price;
                        return [(int) $itemId => $normalized];
                    })
                    ->all()
            );
        }

        if ($syncSelection || $request->has('selected_items')) {
            $request->session()->put(
                'shipment_selected_items',
                collect($request->input('selected_items', []))
                    ->map(fn($id) => (int) $id)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all()
            );
        }

        $selectedItemIds = collect(
            ($syncSelection || $request->has('selected_items'))
                ? $request->input('selected_items', [])
                : $request->session()->get('shipment_selected_items', [])
        )
            ->map(fn($id) => (int) $id)
            ->filter()
            ->values();

        $hasSearch = $request->filled('supplier_id')
            || $request->filled('keyword')
            || $selectedItemIds->isNotEmpty();

        $activeRows = $this->shipmentWorklistBaseQuery()
            ->when(
                $request->filled('supplier_id'),
                fn($q) => $q->where('sh.supplier_id', $request->integer('supplier_id'))
            )
            ->when(
                $request->filled('delivery_note_number'),
                fn($q) => $q->where('sh.delivery_note_number', 'like', '%' . $request->string('delivery_note_number') . '%')
            )
            ->when(
                $request->filled('invoice_number'),
                fn($q) => $q->where('sh.invoice_number', 'like', '%' . $request->string('invoice_number') . '%')
            )
            ->when(
                $request->filled('keyword'),
                function ($q) use ($request) {
                    $keyword = '%' . $request->string('keyword') . '%';
                    $q->where(function ($inner) use ($keyword) {
                        $inner->where('sh.shipment_number', 'like', $keyword)
                            ->orWhere('sh.delivery_note_number', 'like', $keyword)
                            ->orWhere('sh.invoice_number', 'like', $keyword)
                            ->orWhere('s.supplier_name', 'like', $keyword)
                            ->orWhere('anchor_s.supplier_name', 'like', $keyword)
                            ->orWhere('po.po_number', 'like', $keyword);
                    });
                }
            )
            ->when(
                $request->filled('status'),
                fn($q) => $q->where('sh.status', $request->string('status'))
            )
            ->whereIn('sh.status', [
                DocumentTermCodes::SHIPMENT_DRAFT,
                DocumentTermCodes::SHIPMENT_SHIPPED,
                DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED,
            ])
            ->orderByRaw("
                CASE sh.status
                    WHEN '" . DocumentTermCodes::SHIPMENT_DRAFT . "' THEN 1
                    WHEN '" . DocumentTermCodes::SHIPMENT_SHIPPED . "' THEN 2
                    WHEN '" . DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED . "' THEN 3
                    ELSE 9
                END
            ")
            ->orderByDesc('sh.shipment_date')
            ->orderByDesc('sh.id')
            ->paginate(20, ['*'], 'active_page')
            ->withQueryString();

        $archiveRows = $this->shipmentWorklistBaseQuery()
            ->when(
                $request->filled('supplier_id'),
                fn($q) => $q->where('sh.supplier_id', $request->integer('supplier_id'))
            )
            ->when(
                $request->filled('delivery_note_number'),
                fn($q) => $q->where('sh.delivery_note_number', 'like', '%' . $request->string('delivery_note_number') . '%')
            )
            ->when(
                $request->filled('invoice_number'),
                fn($q) => $q->where('sh.invoice_number', 'like', '%' . $request->string('invoice_number') . '%')
            )
            ->when(
                $request->filled('keyword'),
                function ($q) use ($request) {
                    $keyword = '%' . $request->string('keyword') . '%';
                    $q->where(function ($inner) use ($keyword) {
                        $inner->where('sh.shipment_number', 'like', $keyword)
                            ->orWhere('sh.delivery_note_number', 'like', $keyword)
                            ->orWhere('sh.invoice_number', 'like', $keyword)
                            ->orWhere('s.supplier_name', 'like', $keyword)
                            ->orWhere('anchor_s.supplier_name', 'like', $keyword)
                            ->orWhere('po.po_number', 'like', $keyword);
                    });
                }
            )
            ->when(
                $request->filled('status'),
                fn($q) => $q->where('sh.status', $request->string('status'))
            )
            ->whereIn('sh.status', [
                DocumentTermCodes::SHIPMENT_RECEIVED,
                DocumentTermCodes::SHIPMENT_CANCELLED,
            ])
            ->orderByDesc('sh.shipment_date')
            ->orderByDesc('sh.id')
            ->paginate(20, ['*'], 'archive_page')
            ->withQueryString();

        $selectedItems = $selectedItemIds->isNotEmpty()
            ? $this->candidateItemsBaseQuery()->whereIn('poi.id', $selectedItemIds)->get()
            : collect();

        $selectedSupplierId = $selectedItems->isNotEmpty()
            ? (int) $selectedItems->first()->supplier_id
            : null;

        $draftQuantities = collect($request->session()->get('shipment_shipped_qty', []))
            ->mapWithKeys(fn($qty, $itemId) => [(int) $itemId => (float) $qty]);

        $draftInvoicePrices = collect($request->session()->get('shipment_invoice_unit_price', []))
            ->mapWithKeys(fn($price, $itemId) => [(int) $itemId => $price === null ? null : (float) $price]);

        $selectedItemIds = $selectedItems
            ->pluck('purchase_order_item_id')
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        $candidateItems = $hasSearch
            ? $this->candidateItemsQuery($request)
            ->when($selectedSupplierId, fn($query) => $query->where('po.supplier_id', $selectedSupplierId))
            ->when(! empty($selectedItemIds), fn($query) => $query->whereNotIn('poi.id', $selectedItemIds))
            ->orderBy('s.supplier_name')
            ->orderBy('po.po_number')
            ->orderBy('i.item_code')
            ->limit(100)
            ->get()
            : collect();

        $splitShipmentBoard = $selectedItems->isNotEmpty()
            ? $this->shipmentAllocationBoardQuery($selectedItems->pluck('purchase_order_item_id')->all())->get()->groupBy('purchase_order_item_id')
            : collect();

        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get(['id', 'supplier_name']);

        return view('shipments.index', compact(
            'activeRows',
            'archiveRows',
            'suppliers',
            'candidateItems',
            'selectedItems',
            'selectedItemIds',
            'hasSearch',
            'selectedSupplierId',
            'draftQuantities',
            'draftInvoicePrices',
            'splitShipmentBoard',
            'viewMode'
        ));
    }

    public function show(string $id): View
    {
        $shipment = $this->shipmentHeaderQuery()
            ->where('sh.id', $id)
            ->firstOrFail();

        $lines = $this->shipmentLineQuery((int) $shipment->id)->get();

        return view('shipments.show', compact('shipment', 'lines'));
    }

    public function edit(string $id): View
    {
        $shipment = $this->shipmentHeaderQuery()
            ->where('sh.id', $id)
            ->firstOrFail();

        abort_if($shipment->status !== DocumentTermCodes::SHIPMENT_DRAFT, 404);

        $lines = $this->shipmentLineQuery((int) $shipment->id)->get();
        $splitShipmentBoard = $lines->isNotEmpty()
            ? $this->shipmentAllocationBoardQuery($lines->pluck('purchase_order_item_id')->all(), (int) $shipment->id)->get()->groupBy('purchase_order_item_id')
            : collect();

        return view('shipments.edit', compact('shipment', 'lines', 'splitShipmentBoard'));
    }

    public function store(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'shipment_date' => 'required|date',
            'delivery_note_number' => 'required|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
            'invoice_currency' => 'nullable|string|max:10',
            'supplier_remark' => 'nullable|string|max:500',
            'po_reference_missing' => ['nullable', Rule::in(['1'])],
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'integer|exists:purchase_order_items,id',
            'shipped_qty' => 'required|array',
            'invoice_unit_price' => 'nullable|array',
        ], ['required' => ':attribute wajib diisi.']);

        $selectedIds = collect($v['selected_items'])->map(fn($id) => (int) $id)->unique()->values();
        $userId = optional($request->user())->id;

        $remark = trim(implode(' | ', array_filter([
            !empty($v['po_reference_missing']) ? 'Dokumen supplier tidak mencantumkan nomor PO.' : null,
            $v['supplier_remark'] ?? null,
        ]))) ?: null;

        $deliveryNote = trim((string) $v['delivery_note_number']);
        $invoiceNumber = trim((string) ($v['invoice_number'] ?? '')) ?: null;
        $shipmentId = null;

        $shipmentId = DB::transaction(function () use ($selectedIds, $request, $deliveryNote, $invoiceNumber, $remark, $userId, $v) {
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
                'shipment_date' => $v['shipment_date'],
                'delivery_note_number' => $deliveryNote,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $v['invoice_date'] ?? null,
                'invoice_currency' => $v['invoice_currency'] ?? null,
                'supplier_remark' => $remark,
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ] + DomainStatus::payload(DomainStatus::GROUP_SHIPMENT_STATUS, 'status', DocumentTermCodes::SHIPMENT_DRAFT));

            $lineRows = collect($linePayloads)->map(fn($line) => [
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
                ->map(fn($poId) => (int) $poId)
                ->unique()
                ->values();

            foreach ($poIds as $poId) {
                ErpFlow::refreshPoStatusByOutstanding($poId, $userId);
            }

            ErpFlow::audit('shipments', $shipmentId, 'create', null, [
                'shipment' => [
                    'shipment_date' => $v['shipment_date'],
                    'delivery_note_number' => $deliveryNote,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => $v['invoice_date'] ?? null,
                    'invoice_currency' => $v['invoice_currency'] ?? null,
                    'supplier_remark' => $remark,
                    'status' => DocumentTermCodes::SHIPMENT_DRAFT,
                ],
                'lines' => $linePayloads,
            ], $userId, $request->ip());

            return $shipmentId;
        });

        $request->session()->forget('shipment_selected_items');
        $request->session()->forget('shipment_shipped_qty');
        $request->session()->forget('shipment_invoice_unit_price');
        $request->session()->flash('shipment_builder_reset', true);

        return redirect()->route('shipments.index', [
            'focus' => $shipmentId,
        ])->with('success', 'Shipment tersimpan dengan status Draft.');
    }

    public function update(string $id, Request $request): RedirectResponse
    {
        $v = $request->validate([
            'shipment_date' => 'required|date',
            'delivery_note_number' => 'required|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
            'invoice_currency' => 'nullable|string|max:10',
            'supplier_remark' => 'nullable|string|max:500',
            'shipment_items' => 'required|array|min:1',
            'shipment_items.*.id' => 'required|integer|exists:shipment_items,id',
            'shipment_items.*.shipped_qty' => 'required|numeric|min:0.01',
            'shipment_items.*.invoice_unit_price' => 'nullable|numeric|min:0',
            'shipment_items.*.keep' => ['nullable', Rule::in(['1'])],
        ]);

        $deliveryNote = trim((string) $v['delivery_note_number']);
        $invoiceNumber = trim((string) ($v['invoice_number'] ?? '')) ?: null;
        $userId = optional($request->user())->id;

        DB::transaction(function () use ($id, $v, $deliveryNote, $invoiceNumber, $userId, $request) {
            $shipment = DB::table('shipments')->where('id', $id)->lockForUpdate()->firstOrFail();

            if ($shipment->status !== DocumentTermCodes::SHIPMENT_DRAFT) {
                throw ValidationException::withMessages([
                    'shipment' => 'Hanya draft shipment yang masih bisa diubah.',
                ]);
            }

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

            $currentLines = $this->shipmentLineQuery((int) $shipment->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('shipment_item_id');

            $keptLines = collect($v['shipment_items'])
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

                $maxQty = (float) $existing->available_to_ship_qty;

                if ((float) $line['shipped_qty'] > $maxQty) {
                    throw ValidationException::withMessages([
                        'shipment_items' => "Qty kirim untuk {$existing->item_code} melebihi batas yang masih tersedia.",
                    ]);
                }
            }

            DB::table('shipments')->where('id', $shipment->id)->update([
                'shipment_date' => $v['shipment_date'],
                'delivery_note_number' => $deliveryNote,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $v['invoice_date'] ?? null,
                'invoice_currency' => $v['invoice_currency'] ?? null,
                'supplier_remark' => $v['supplier_remark'] ?? null,
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

            ErpFlow::audit('shipments', (int) $shipment->id, 'update', $shipment, $v, $userId, $request->ip());
        });

        return redirect()->route('shipments.edit', $id)->with('success', 'Draft shipment berhasil diperbarui.');
    }

    public function markShipped(string $id, Request $request): RedirectResponse
    {
        $userId = optional($request->user())->id;
        $shipment = null;

        $shipment = DB::transaction(function () use ($id, $userId, $request) {
            $shipment = DB::table('shipments')->where('id', $id)->lockForUpdate()->firstOrFail();

            if ($shipment->status !== DocumentTermCodes::SHIPMENT_DRAFT) {
                throw ValidationException::withMessages([
                    'shipment' => 'Hanya shipment Draft yang bisa dikonfirmasi menjadi Shipped.',
                ]);
            }

            $duplicateShipment = DB::table('shipments')
                ->where('supplier_id', $shipment->supplier_id)
                ->where('id', '!=', $shipment->id)
                ->whereRaw('LOWER(TRIM(delivery_note_number)) = ?', [mb_strtolower(trim((string) $shipment->delivery_note_number))])
                ->where('status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
                ->lockForUpdate()
                ->first();

            if ($duplicateShipment) {
                throw ValidationException::withMessages([
                    'shipment' => "Delivery note {$shipment->delivery_note_number} sudah dipakai oleh shipment {$duplicateShipment->shipment_number}.",
                ]);
            }

            if ($shipment->invoice_number) {
                $duplicateInvoice = DB::table('shipments')
                    ->where('supplier_id', $shipment->supplier_id)
                    ->where('id', '!=', $shipment->id)
                    ->whereRaw('LOWER(TRIM(invoice_number)) = ?', [mb_strtolower(trim((string) $shipment->invoice_number))])
                    ->where('status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
                    ->lockForUpdate()
                    ->first();

                if ($duplicateInvoice) {
                    throw ValidationException::withMessages([
                        'shipment' => "Invoice {$shipment->invoice_number} sudah dipakai oleh shipment {$duplicateInvoice->shipment_number}.",
                    ]);
                }
            }

            $linePoIds = DB::table('shipment_items as si')
                ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
                ->where('si.shipment_id', $shipment->id)
                ->pluck('poi.purchase_order_id')
                ->map(fn($poId) => (int) $poId)
                ->unique()
                ->values();

            if ($linePoIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'shipment' => 'Shipment belum memiliki item yang bisa dikirim.',
                ]);
            }

            DB::table('shipments')->where('id', $shipment->id)->update([
                'updated_at' => now(),
            ] + DomainStatus::payload(DomainStatus::GROUP_SHIPMENT_STATUS, 'status', DocumentTermCodes::SHIPMENT_SHIPPED));

            foreach ($linePoIds as $poId) {
                ErpFlow::refreshPoStatusByOutstanding((int) $poId, $userId);
            }

            ErpFlow::audit(
                'shipments',
                (int) $shipment->id,
                'mark_shipped',
                ['status' => DocumentTermCodes::SHIPMENT_DRAFT],
                ['status' => DocumentTermCodes::SHIPMENT_SHIPPED],
                $userId,
                $request->ip()
            );

            return $shipment;
        });

        return redirect()->route('receiving.process', [
            'supplier_id' => $shipment->supplier_id,
            'shipment_id' => $shipment->id,
            'document_number' => $shipment->delivery_note_number,
        ])->with('success', 'Shipment berhasil dikonfirmasi menjadi Shipped.');
    }

    public function cancelDraft(string $id, Request $request): RedirectResponse
    {
        $shipment = DB::table('shipments')->where('id', $id)->firstOrFail();

        if ($shipment->status !== DocumentTermCodes::SHIPMENT_DRAFT) {
            return back()->with('error', 'Hanya shipment Draft yang bisa dibatalkan.');
        }

        DB::transaction(function () use ($shipment, $request) {
            DB::table('shipments')->where('id', $shipment->id)->update([
                'updated_at' => now(),
            ] + DomainStatus::payload(DomainStatus::GROUP_SHIPMENT_STATUS, 'status', DocumentTermCodes::SHIPMENT_CANCELLED));

            $linePoIds = DB::table('shipment_items as si')
                ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
                ->where('si.shipment_id', $shipment->id)
                ->pluck('poi.purchase_order_id')
                ->map(fn($poId) => (int) $poId)
                ->unique()
                ->values();

            foreach ($linePoIds as $poId) {
                ErpFlow::refreshPoStatusByOutstanding((int) $poId, optional($request->user())->id);
            }

            ErpFlow::audit(
                'shipments',
                (int) $shipment->id,
                'cancel',
                $shipment,
                ['status' => DocumentTermCodes::SHIPMENT_CANCELLED],
                optional($request->user())->id,
                $request->ip()
            );
        });

        return back()->with('success', 'Draft shipment berhasil dibatalkan.');
    }

    public function exportDraftExcel(string $id)
    {
        $shipment = $this->shipmentHeaderQuery()
            ->where('sh.id', $id)
            ->firstOrFail();

        abort_if($shipment->status !== DocumentTermCodes::SHIPMENT_DRAFT, 404);

        $lines = $this->shipmentLineQuery((int) $shipment->id)->get();

        $spreadsheet = new Spreadsheet();

        $headerSheet = $spreadsheet->getActiveSheet();
        $headerSheet->setTitle('HEADER');

        $headerColumns = [
            'shipment_number',
            'shipment_date',
            'supplier_name',
            'delivery_note_number',
            'invoice_number',
            'invoice_date',
            'invoice_currency',
            'supplier_remark',
            'status',
        ];

        foreach ($headerColumns as $index => $column) {
            $headerSheet->setCellValueByColumnAndRow($index + 1, 1, $column);
        }

        $headerValues = [
            $shipment->shipment_number ?? '',
            $shipment->shipment_date ?? '',
            $shipment->supplier_name ?? '',
            $shipment->delivery_note_number ?? '',
            $shipment->invoice_number ?? '',
            $shipment->invoice_date ?? '',
            $shipment->invoice_currency ?? '',
            $shipment->supplier_remark ?? '',
            $shipment->status ?? '',
        ];

        foreach ($headerValues as $index => $value) {
            $headerSheet->setCellValueByColumnAndRow($index + 1, 2, $value);
        }

        $lineSheet = $spreadsheet->createSheet();
        $lineSheet->setTitle('LINES');

        $lineColumns = [
            'shipment_item_id',
            'purchase_order_item_id',
            'po_number',
            'item_code',
            'item_name',
            'po_unit_price',
            'shipped_qty',
            'invoice_unit_price',
            'invoice_line_total',
            'keep',
        ];

        foreach ($lineColumns as $index => $column) {
            $lineSheet->setCellValueByColumnAndRow($index + 1, 1, $column);
        }

        $rowNumber = 2;
        foreach ($lines as $line) {
            $row = [
                $line->shipment_item_id ?? '',
                $line->purchase_order_item_id ?? '',
                $line->po_number ?? '',
                $line->item_code ?? '',
                $line->item_name ?? '',
                $line->po_unit_price ?? '',
                $line->shipped_qty ?? '',
                $line->invoice_unit_price ?? '',
                $line->invoice_line_total ?? '',
                1,
            ];

            foreach ($row as $index => $value) {
                $lineSheet->setCellValueByColumnAndRow($index + 1, $rowNumber, $value);
            }

            $rowNumber++;
        }

        foreach ([$headerSheet, $lineSheet] as $sheet) {
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
            }
        }

        $filename = 'shipment-draft-' . $shipment->shipment_number . '.xlsx';
        $tempPath = storage_path('app/temp/' . uniqid('shipment_export_', true) . '.xlsx');

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0775, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function downloadDraftTemplate()
    {
        $spreadsheet = new Spreadsheet();

        $headerSheet = $spreadsheet->getActiveSheet();
        $headerSheet->setTitle('HEADER');

        $headerColumns = [
            'shipment_number',
            'shipment_date',
            'supplier_name',
            'delivery_note_number',
            'invoice_number',
            'invoice_date',
            'invoice_currency',
            'supplier_remark',
            'status',
        ];

        foreach ($headerColumns as $index => $column) {
            $headerSheet->setCellValueByColumnAndRow($index + 1, 1, $column);
        }

        $headerValues = [
            'SHP-XXXXX',
            now()->format('Y-m-d'),
            '',
            '',
            '',
            '',
            'IDR',
            '',
            DocumentTermCodes::SHIPMENT_DRAFT,
        ];

        foreach ($headerValues as $index => $value) {
            $headerSheet->setCellValueByColumnAndRow($index + 1, 2, $value);
        }

        $lineSheet = $spreadsheet->createSheet();
        $lineSheet->setTitle('LINES');

        $lineColumns = [
            'shipment_item_id',
            'purchase_order_item_id',
            'po_number',
            'item_code',
            'item_name',
            'po_unit_price',
            'shipped_qty',
            'invoice_unit_price',
            'invoice_line_total',
            'keep',
        ];

        foreach ($lineColumns as $index => $column) {
            $lineSheet->setCellValueByColumnAndRow($index + 1, 1, $column);
        }

        foreach ([$headerSheet, $lineSheet] as $sheet) {
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
            }
        }

        $filename = 'shipment-draft-template.xlsx';
        $tempPath = storage_path('app/temp/' . uniqid('shipment_template_', true) . '.xlsx');

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0775, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function importDraftExcel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipment_id' => 'required|integer|exists:shipments,id',
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $shipment = DB::table('shipments')->where('id', $validated['shipment_id'])->firstOrFail();

        if ($shipment->status !== DocumentTermCodes::SHIPMENT_DRAFT) {
            return back()->with('error', 'Import hanya diperbolehkan untuk shipment Draft.');
        }

        $parsed = $this->parseShipmentDraftSpreadsheet($request->file('file')->getRealPath());

        DB::transaction(function () use ($shipment, $parsed, $request) {
            $lockedShipment = DB::table('shipments')->where('id', $shipment->id)->lockForUpdate()->firstOrFail();

            $header = $parsed['header'];
            $lines = collect($parsed['lines'])
                ->filter(fn($row) => (string) ($row['keep'] ?? '1') === '1')
                ->values();

            if ($lines->isEmpty()) {
                throw ValidationException::withMessages([
                    'file' => 'Minimal satu line harus keep=1.',
                ]);
            }

            if (!empty($header['shipment_number']) && trim((string) $header['shipment_number']) !== trim((string) $lockedShipment->shipment_number)) {
                throw ValidationException::withMessages([
                    'file' => 'Shipment number pada file tidak sesuai dengan draft target.',
                ]);
            }

            $supplierName = (string) (
                DB::table('shipments as sh')
                ->leftJoin('suppliers as s', 's.id', '=', 'sh.supplier_id')
                ->where('sh.id', $lockedShipment->id)
                ->value('s.supplier_name') ?? ''
            );

            if (!empty($header['supplier_name']) && trim((string) $header['supplier_name']) !== trim($supplierName)) {
                throw ValidationException::withMessages([
                    'file' => 'Supplier pada file tidak sesuai dengan shipment draft.',
                ]);
            }

            $deliveryNote = $this->nullableString($header['delivery_note_number'] ?? null) ?: (string) $lockedShipment->delivery_note_number;
            $invoiceNumber = $this->nullableString($header['invoice_number'] ?? null);
            $invoiceCurrency = $this->nullableString($header['invoice_currency'] ?? null);
            $supplierRemark = $this->nullableString($header['supplier_remark'] ?? null);
            $shipmentDate = $this->nullableString($header['shipment_date'] ?? null) ?: (string) $lockedShipment->shipment_date;
            $invoiceDate = $this->nullableString($header['invoice_date'] ?? null);

            if (!$deliveryNote) {
                throw ValidationException::withMessages([
                    'file' => 'delivery_note_number wajib diisi pada sheet HEADER.',
                ]);
            }

            $duplicateShipment = DB::table('shipments')
                ->where('supplier_id', $lockedShipment->supplier_id)
                ->where('id', '!=', $lockedShipment->id)
                ->whereRaw('LOWER(TRIM(delivery_note_number)) = ?', [mb_strtolower($deliveryNote)])
                ->where('status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
                ->lockForUpdate()
                ->first();

            if ($duplicateShipment) {
                throw ValidationException::withMessages([
                    'file' => "Delivery note {$deliveryNote} sudah dipakai oleh shipment {$duplicateShipment->shipment_number}.",
                ]);
            }

            if ($invoiceNumber) {
                $duplicateInvoice = DB::table('shipments')
                    ->where('supplier_id', $lockedShipment->supplier_id)
                    ->where('id', '!=', $lockedShipment->id)
                    ->whereRaw('LOWER(TRIM(invoice_number)) = ?', [mb_strtolower($invoiceNumber)])
                    ->where('status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
                    ->lockForUpdate()
                    ->first();

                if ($duplicateInvoice) {
                    throw ValidationException::withMessages([
                        'file' => "Invoice {$invoiceNumber} sudah dipakai oleh shipment {$duplicateInvoice->shipment_number}.",
                    ]);
                }
            }

            DB::table('shipments')->where('id', $lockedShipment->id)->update([
                'shipment_date' => $shipmentDate,
                'delivery_note_number' => $deliveryNote,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invoiceDate,
                'invoice_currency' => $invoiceCurrency,
                'supplier_remark' => $supplierRemark,
                'updated_at' => now(),
            ]);

            $currentLines = DB::table('shipment_items as si')
                ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
                ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->join('items as i', 'i.id', '=', 'poi.item_id')
                ->leftJoin('shipment_items as other_si', function ($join) use ($lockedShipment) {
                    $join->on('other_si.purchase_order_item_id', '=', 'poi.id')
                        ->where('other_si.shipment_id', '!=', $lockedShipment->id);
                })
                ->leftJoin('shipments as other_sh', function ($join) {
                    $join->on('other_sh.id', '=', 'other_si.shipment_id')
                        ->where('other_sh.status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED);
                })
                ->where('si.shipment_id', $lockedShipment->id)
                ->select(
                    'si.id as shipment_item_id',
                    'si.shipment_id',
                    'si.purchase_order_item_id',
                    'si.shipped_qty as current_shipped_qty',
                    'si.received_qty',
                    'poi.purchase_order_id',
                    'poi.outstanding_qty',
                    'poi.unit_price as po_unit_price',
                    'i.item_code'
                )
                ->selectRaw('COALESCE(SUM(CASE WHEN other_sh.id IS NOT NULL THEN other_si.shipped_qty - other_si.received_qty ELSE 0 END), 0) as other_open_shipment_qty')
                ->groupBy(
                    'si.id',
                    'si.shipment_id',
                    'si.purchase_order_item_id',
                    'si.shipped_qty',
                    'si.received_qty',
                    'poi.purchase_order_id',
                    'poi.outstanding_qty',
                    'poi.unit_price',
                    'i.item_code'
                )
                ->get()
                ->keyBy('shipment_item_id');

            $keptIds = [];

            foreach ($lines as $row) {
                $shipmentItemId = (int) ($row['shipment_item_id'] ?? 0);

                if ($shipmentItemId <= 0) {
                    throw ValidationException::withMessages([
                        'file' => 'Ada shipment_item_id yang kosong atau tidak valid di sheet LINES.',
                    ]);
                }

                $currentLine = $currentLines->get($shipmentItemId);

                if (!$currentLine) {
                    throw ValidationException::withMessages([
                        'file' => 'Ada shipment_item_id yang tidak cocok dengan draft target: ' . $shipmentItemId,
                    ]);
                }

                $qty = (float) ($row['shipped_qty'] ?? 0);
                $invoiceUnitPrice = $this->nullableNumber($row['invoice_unit_price'] ?? null);

                if ($qty <= 0) {
                    throw ValidationException::withMessages([
                        'file' => "Qty kirim untuk shipment_item_id {$shipmentItemId} harus lebih besar dari 0.",
                    ]);
                }

                $availableToShipQty = (float) $currentLine->outstanding_qty - (float) $currentLine->other_open_shipment_qty;

                if ($qty > $availableToShipQty) {
                    throw ValidationException::withMessages([
                        'file' => "Qty kirim untuk item {$currentLine->item_code} melebihi qty tersedia.",
                    ]);
                }

                if ($invoiceUnitPrice !== null && $invoiceUnitPrice < 0) {
                    throw ValidationException::withMessages([
                        'file' => "Harga invoice untuk item {$currentLine->item_code} tidak boleh negatif.",
                    ]);
                }

                $invoiceLineTotal = $invoiceUnitPrice !== null
                    ? round($qty * $invoiceUnitPrice, 2)
                    : null;

                DB::table('shipment_items')
                    ->where('id', $shipmentItemId)
                    ->update([
                        'shipped_qty' => $qty,
                        'invoice_unit_price' => $invoiceUnitPrice,
                        'invoice_line_total' => $invoiceLineTotal,
                        'updated_at' => now(),
                    ]);

                $keptIds[] = $shipmentItemId;
            }

            DB::table('shipment_items')
                ->where('shipment_id', $lockedShipment->id)
                ->whereNotIn('id', $keptIds)
                ->delete();

            $poIds = DB::table('shipment_items as si')
                ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
                ->where('si.shipment_id', $lockedShipment->id)
                ->pluck('poi.purchase_order_id')
                ->map(fn($poId) => (int) $poId)
                ->unique()
                ->values();

            foreach ($poIds as $poId) {
                ErpFlow::refreshPoStatusByOutstanding((int) $poId, optional($request->user())->id);
            }

            ErpFlow::audit(
                'shipments',
                (int) $lockedShipment->id,
                'import_excel',
                $lockedShipment,
                [
                    'header' => $header,
                    'lines' => $lines->values()->all(),
                ],
                optional($request->user())->id,
                $request->ip()
            );
        });

        return redirect()->route('shipments.edit', $validated['shipment_id'])
            ->with('success', 'Draft shipment berhasil diperbarui dari Excel.');
    }

    private function shipmentWorklistBaseQuery()
    {
        $poNumbersExpression = $this->groupConcatPoNumbersExpression();

        return DB::table('shipments as sh')
            ->leftJoin('suppliers as s', 's.id', '=', 'sh.supplier_id')
            ->leftJoin('purchase_orders as anchor_po', 'anchor_po.id', '=', 'sh.purchase_order_id')
            ->leftJoin('suppliers as anchor_s', 'anchor_s.id', '=', 'anchor_po.supplier_id')
            ->leftJoin('shipment_items as si', 'si.shipment_id', '=', 'sh.id')
            ->leftJoin('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
            ->leftJoin('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->select(
                'sh.*',
                DB::raw('COALESCE(s.supplier_name, anchor_s.supplier_name) as supplier_name'),
                DB::raw('COUNT(DISTINCT si.id) as line_count'),
                DB::raw('COUNT(DISTINCT po.id) as po_count'),
                DB::raw($poNumbersExpression . ' as po_numbers'),
                DB::raw('COALESCE(SUM(si.shipped_qty),0) as total_shipped_qty'),
                DB::raw('COALESCE(SUM(si.received_qty),0) as total_received_qty'),
                DB::raw('COALESCE(SUM(si.shipped_qty - si.received_qty),0) as total_open_qty')
            )
            ->groupBy(
                'sh.id',
                'sh.purchase_order_id',
                'sh.supplier_id',
                'sh.shipment_number',
                'sh.shipment_date',
                'sh.delivery_note_number',
                'sh.invoice_number',
                'sh.invoice_date',
                'sh.invoice_currency',
                'sh.supplier_remark',
                'sh.status',
                'sh.created_by',
                'sh.created_at',
                'sh.updated_at',
                's.supplier_name',
                'anchor_s.supplier_name'
            );
    }

    private function groupConcatPoNumbersExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "GROUP_CONCAT(DISTINCT po.po_number)"
            : "GROUP_CONCAT(DISTINCT po.po_number ORDER BY po.po_number SEPARATOR ', ')";
    }

    private function candidateItemsQuery(Request $request)
    {
        return $this->candidateItemsBaseQuery()
            ->when($request->filled('supplier_id'), fn($q) => $q->where('po.supplier_id', $request->integer('supplier_id')))
            ->when($request->filled('keyword'), function ($q) use ($request) {
                $keyword = '%' . $request->string('keyword') . '%';
                $q->where(function ($inner) use ($keyword) {
                    $inner->where('po.po_number', 'like', $keyword)
                        ->orWhere('i.item_code', 'like', $keyword)
                        ->orWhere('i.item_name', 'like', $keyword)
                        ->orWhere('s.supplier_name', 'like', $keyword);
                });
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
            ->selectRaw('COALESCE(SUM(CASE WHEN sh_alloc.id IS NOT NULL THEN si.shipped_qty - si.received_qty ELSE 0 END), 0) as open_shipment_qty')
            ->whereIn('po.status', self::SHIPPABLE_PO_STATUSES)
            ->where('poi.outstanding_qty', '>', 0)
            ->where('poi.item_status', '!=', DocumentTermCodes::ITEM_CANCELLED)
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
            )
            ->selectRaw('(poi.outstanding_qty - COALESCE(SUM(CASE WHEN sh_alloc.id IS NOT NULL THEN si.shipped_qty - si.received_qty ELSE 0 END), 0)) as available_to_ship_qty');
    }

    private function shipmentHeaderQuery()
    {
        return DB::table('shipments as sh')
            ->leftJoin('suppliers as s', 's.id', '=', 'sh.supplier_id')
            ->select('sh.*', 's.supplier_name');
    }

    private function shipmentLineQuery(int $shipmentId)
    {
        return DB::table('shipment_items as si')
            ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->leftJoin('shipment_items as other_si', function ($join) use ($shipmentId) {
                $join->on('other_si.purchase_order_item_id', '=', 'poi.id')
                    ->where('other_si.shipment_id', '!=', $shipmentId);
            })
            ->leftJoin('shipments as other_sh', function ($join) {
                $join->on('other_sh.id', '=', 'other_si.shipment_id')
                    ->where('other_sh.status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED);
            })
            ->where('si.shipment_id', $shipmentId)
            ->select(
                'si.id as shipment_item_id',
                'si.shipped_qty',
                'si.received_qty',
                'si.invoice_unit_price',
                'si.invoice_line_total',
                'poi.id as purchase_order_item_id',
                'poi.outstanding_qty',
                'poi.unit_price as po_unit_price',
                'po.po_number',
                'i.item_code',
                'i.item_name'
            )
            ->selectRaw('COALESCE(SUM(CASE WHEN other_sh.id IS NOT NULL THEN other_si.shipped_qty - other_si.received_qty ELSE 0 END), 0) as other_open_shipment_qty')
            ->groupBy(
                'si.id',
                'si.shipped_qty',
                'si.received_qty',
                'si.invoice_unit_price',
                'si.invoice_line_total',
                'poi.id',
                'poi.outstanding_qty',
                'poi.unit_price',
                'po.po_number',
                'i.item_code',
                'i.item_name'
            )
            ->selectRaw('(poi.outstanding_qty - COALESCE(SUM(CASE WHEN other_sh.id IS NOT NULL THEN other_si.shipped_qty - other_si.received_qty ELSE 0 END), 0)) as available_to_ship_qty');
    }

    private function shipmentAllocationBoardQuery(array $purchaseOrderItemIds, ?int $excludeShipmentId = null)
    {
        return DB::table('shipment_items as si')
            ->join('shipments as sh', 'sh.id', '=', 'si.shipment_id')
            ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->whereIn('si.purchase_order_item_id', $purchaseOrderItemIds)
            ->where('sh.status', '!=', DocumentTermCodes::SHIPMENT_CANCELLED)
            ->when($excludeShipmentId, fn($query) => $query->where('si.shipment_id', '!=', $excludeShipmentId))
            ->select(
                'si.purchase_order_item_id',
                'si.shipment_id',
                'sh.shipment_number',
                'sh.shipment_date',
                'sh.delivery_note_number',
                'sh.status',
                'po.po_number',
                'i.item_code',
                'i.item_name',
                'poi.outstanding_qty',
                'si.shipped_qty',
                'si.received_qty'
            )
            ->selectRaw('(si.shipped_qty - si.received_qty) as open_qty')
            ->orderBy('sh.shipment_date')
            ->orderBy('sh.id');
    }

    private function parseShipmentDraftSpreadsheet(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);

        $headerSheet = $spreadsheet->getSheetByName('HEADER');
        $lineSheet = $spreadsheet->getSheetByName('LINES');

        if (!$headerSheet || !$lineSheet) {
            throw ValidationException::withMessages([
                'file' => 'File Excel wajib memiliki sheet HEADER dan LINES.',
            ]);
        }

        $headerRows = $headerSheet->toArray(null, true, true, false);
        $lineRows = $lineSheet->toArray(null, true, true, false);

        $header = $this->extractSpreadsheetAssocRow($headerRows, 'HEADER');
        $lines = $this->extractSpreadsheetAssocRows($lineRows, 'LINES');

        return [
            'header' => $header,
            'lines' => $lines,
        ];
    }

    private function extractSpreadsheetAssocRow(array $rows, string $sheetName): array
    {
        $rows = array_values(array_filter($rows, function ($row) {
            return is_array($row) && count(array_filter($row, fn($value) => $value !== null && $value !== '')) > 0;
        }));

        if (count($rows) < 2) {
            throw ValidationException::withMessages([
                'file' => "Sheet {$sheetName} tidak valid. Minimal harus ada 2 baris: header dan value.",
            ]);
        }

        $header = array_map(fn($value) => trim((string) $value), $rows[0]);
        $values = array_pad($rows[1], count($header), null);

        return array_combine($header, $values);
    }

    private function extractSpreadsheetAssocRows(array $rows, string $sheetName): array
    {
        $rows = array_values(array_filter($rows, function ($row) {
            return is_array($row) && count(array_filter($row, fn($value) => $value !== null && $value !== '')) > 0;
        }));

        if (count($rows) < 2) {
            throw ValidationException::withMessages([
                'file' => "Sheet {$sheetName} tidak valid. Minimal harus ada 2 baris: header dan data.",
            ]);
        }

        $header = array_map(fn($value) => trim((string) $value), $rows[0]);
        $dataRows = [];

        foreach (array_slice($rows, 1) as $row) {
            $padded = array_pad($row, count($header), null);

            if (!array_filter($padded, fn($value) => $value !== null && $value !== '')) {
                continue;
            }

            $dataRows[] = array_combine($header, $padded);
        }

        return $dataRows;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function nullableNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
