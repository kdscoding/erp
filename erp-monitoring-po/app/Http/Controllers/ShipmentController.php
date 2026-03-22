<?php

namespace App\Http\Controllers;

use App\Support\ErpFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    private const SHIPPABLE_PO_STATUSES = ['PO Issued', 'Open', 'Late'];

    public function index(Request $request): View
    {
        if ($request->boolean('clear_selection')) {
            $request->session()->forget('shipment_selected_items');
            $request->session()->forget('shipment_shipped_qty');
        }

        if ($request->has('shipped_qty')) {
            $request->session()->put('shipment_shipped_qty', collect($request->input('shipped_qty', []))
                ->mapWithKeys(fn ($qty, $itemId) => [(int) $itemId => (float) $qty])
                ->all());
        }

        if ($request->has('selected_items')) {
            $request->session()->put('shipment_selected_items', collect($request->input('selected_items', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all());
        }

        $selectedItemIds = collect($request->has('selected_items')
            ? $request->input('selected_items', [])
            : $request->session()->get('shipment_selected_items', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();
        $hasSearch = $request->filled('supplier_id') || $request->filled('keyword') || $selectedItemIds->isNotEmpty();

        $rows = DB::table('shipments as sh')
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
                DB::raw("GROUP_CONCAT(DISTINCT po.po_number ORDER BY po.po_number SEPARATOR ', ') as po_numbers")
            )
            ->when($request->filled('delivery_note_number'), fn ($q) => $q->where('sh.delivery_note_number', 'like', '%'.$request->string('delivery_note_number').'%'))
            ->groupBy('sh.id', 'sh.purchase_order_id', 'sh.supplier_id', 'sh.shipment_number', 'sh.shipment_date', 'sh.delivery_note_number', 'sh.supplier_remark', 'sh.status', 'sh.created_by', 'sh.created_at', 'sh.updated_at', 's.supplier_name', 'anchor_s.supplier_name')
            ->orderByDesc('sh.id')
            ->paginate(20);

        $selectedItems = $selectedItemIds->isNotEmpty()
            ? $this->candidateItemsBaseQuery()->whereIn('poi.id', $selectedItemIds)->get()
            : collect();
        $selectedSupplierId = $selectedItems->isNotEmpty() ? (int) $selectedItems->first()->supplier_id : null;
        $draftQuantities = collect($request->session()->get('shipment_shipped_qty', []))
            ->mapWithKeys(fn ($qty, $itemId) => [(int) $itemId => (float) $qty]);

        $candidateItems = $hasSearch
            ? $this->candidateItemsQuery($request)
                ->when($selectedSupplierId, fn ($query) => $query->where('po.supplier_id', $selectedSupplierId))
                ->when($selectedItemIds->isNotEmpty(), fn ($query) => $query->whereNotIn('poi.id', $selectedItemIds))
                ->orderBy('s.supplier_name')
                ->orderBy('po.po_number')
                ->orderBy('i.item_code')
                ->limit(100)
                ->get()
            : collect();

        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get(['id', 'supplier_name']);

        return view('shipments.index', compact('rows', 'suppliers', 'candidateItems', 'selectedItems', 'hasSearch', 'selectedSupplierId', 'draftQuantities'));
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

        abort_if($shipment->status !== 'Draft', 404);

        $lines = $this->shipmentLineQuery((int) $shipment->id)->get();

        return view('shipments.edit', compact('shipment', 'lines'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'shipment_date' => 'required|date',
            'delivery_note_number' => 'required|string|max:100',
            'supplier_remark' => 'nullable|string|max:500',
            'po_reference_missing' => ['nullable', Rule::in(['1'])],
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'integer|exists:purchase_order_items,id',
            'shipped_qty' => 'required|array',
        ], ['required' => ':attribute wajib diisi.']);

        $selectedIds = collect($v['selected_items'])->map(fn ($id) => (int) $id)->unique()->values();
        $userId = optional($request->user())->id;
        $remark = trim(implode(' | ', array_filter([
            ! empty($v['po_reference_missing']) ? 'Dokumen supplier tidak mencantumkan nomor PO.' : null,
            $v['supplier_remark'] ?? null,
        ]))) ?: null;
        $deliveryNote = trim((string) $v['delivery_note_number']);
        $shipmentId = null;

        try {
            $shipmentId = DB::transaction(function () use ($selectedIds, $request, $deliveryNote, $remark, $userId, $v) {
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
                    ->where('status', '!=', 'Cancelled')
                    ->lockForUpdate()
                    ->first();

                if ($duplicateShipment) {
                    $statusLabel = $duplicateShipment->status === 'Draft'
                        ? 'masih berupa Draft'
                        : 'sudah diproses dengan status '.$duplicateShipment->status;

                    throw ValidationException::withMessages([
                        'delivery_note_number' => "Delivery note {$deliveryNote} untuk supplier ini sudah digunakan pada shipment {$duplicateShipment->shipment_number} dan {$statusLabel}.",
                    ]);
                }

                $linePayloads = [];
                foreach ($items as $item) {
                    $qty = (float) ($request->input("shipped_qty.{$item->purchase_order_item_id}") ?? 0);
                    if ($qty <= 0) {
                        throw ValidationException::withMessages([
                            'shipped_qty' => 'Qty kirim harus diisi untuk setiap item yang dipilih.',
                        ]);
                    }

                    if ($qty > (float) $item->available_to_ship_qty) {
                        throw ValidationException::withMessages([
                            'shipped_qty.'.$item->purchase_order_item_id => "Qty kirim untuk {$item->item_code} melebihi sisa qty yang masih bisa dialokasikan.",
                        ]);
                    }

                    $linePayloads[] = [
                        'purchase_order_item_id' => $item->purchase_order_item_id,
                        'purchase_order_id' => $item->purchase_order_id,
                        'shipped_qty' => $qty,
                    ];
                }

                $number = ErpFlow::generateNumber('SHP', 'shipments', 'shipment_number');
                $shipmentId = DB::table('shipments')->insertGetId([
                    'purchase_order_id' => $linePayloads[0]['purchase_order_id'],
                    'supplier_id' => $supplierId,
                    'shipment_number' => $number,
                    'shipment_date' => $v['shipment_date'],
                    'delivery_note_number' => $deliveryNote,
                    'supplier_remark' => $remark,
                    'status' => 'Draft',
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $lineRows = collect($linePayloads)->map(fn ($line) => [
                    'shipment_id' => $shipmentId,
                    'purchase_order_item_id' => $line['purchase_order_item_id'],
                    'shipped_qty' => $line['shipped_qty'],
                    'received_qty' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all();

                DB::table('shipment_items')->insert($lineRows);

                ErpFlow::audit('shipments', $shipmentId, 'create', null, [
                    'shipment' => $v,
                    'lines' => $linePayloads,
                ], $userId, $request->ip());

                return $shipmentId;
            });
        } catch (ValidationException $e) {
            throw $e;
        }

        $request->session()->forget('shipment_selected_items');
        $request->session()->forget('shipment_shipped_qty');
        $request->session()->flash('shipment_builder_reset', true);

        return redirect()->route('shipments.history', [
            'focus' => $shipmentId,
        ])->with('success', 'Shipment dokumen supplier tersimpan dengan status Draft. Lanjutkan dari riwayat shipment untuk review atau tandai barang sudah berangkat.');
    }

    public function markShipped(string $id, Request $request)
    {
        $userId = optional($request->user())->id;
        $shipment = null;

        try {
            $shipment = DB::transaction(function () use ($id, $userId) {
                $shipment = DB::table('shipments')->where('id', $id)->lockForUpdate()->firstOrFail();
                if ($shipment->status !== 'Draft') {
                    throw ValidationException::withMessages([
                        'shipment' => 'Hanya shipment Draft yang bisa dikonfirmasi menjadi Shipped.',
                    ]);
                }

                $duplicateShipment = DB::table('shipments')
                    ->where('supplier_id', $shipment->supplier_id)
                    ->where('id', '!=', $shipment->id)
                    ->whereRaw('LOWER(TRIM(delivery_note_number)) = ?', [mb_strtolower(trim((string) $shipment->delivery_note_number))])
                    ->where('status', '!=', 'Cancelled')
                    ->lockForUpdate()
                    ->first();

                if ($duplicateShipment) {
                    throw ValidationException::withMessages([
                        'shipment' => "Delivery note {$shipment->delivery_note_number} sudah dipakai oleh shipment {$duplicateShipment->shipment_number}.",
                    ]);
                }

                $linePoIds = DB::table('shipment_items as si')
                    ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
                    ->where('si.shipment_id', $shipment->id)
                    ->pluck('poi.purchase_order_id')
                    ->map(fn ($poId) => (int) $poId)
                    ->unique()
                    ->values();

                if ($linePoIds->isEmpty()) {
                    throw ValidationException::withMessages([
                        'shipment' => 'Shipment belum memiliki item yang bisa dikirim.',
                    ]);
                }

                DB::table('shipments')->where('id', $shipment->id)->update([
                    'status' => 'Shipped',
                    'updated_at' => now(),
                ]);

                foreach ($linePoIds as $poId) {
                    ErpFlow::refreshPoStatusByOutstanding((int) $poId, $userId);
                }

                ErpFlow::audit('shipments', (int) $shipment->id, 'mark_shipped', ['status' => 'Draft'], ['status' => 'Shipped'], $userId, request()->ip());

                return $shipment;
            });
        } catch (ValidationException $e) {
            throw $e;
        }

        return redirect()->route('receiving.process', [
            'supplier_id' => $shipment->supplier_id,
            'shipment_id' => $shipment->id,
            'document_number' => $shipment->delivery_note_number,
        ])->with('success', 'Shipment berhasil dikonfirmasi menjadi Shipped. Lanjutkan dengan proses receiving untuk delivery note ini.');
    }

    public function update(string $id, Request $request)
    {
        $v = $request->validate([
            'shipment_date' => 'required|date',
            'delivery_note_number' => 'required|string|max:100',
            'supplier_remark' => 'nullable|string|max:500',
            'shipment_items' => 'required|array|min:1',
            'shipment_items.*.id' => 'required|integer|exists:shipment_items,id',
            'shipment_items.*.shipped_qty' => 'required|numeric|min:0.01',
            'shipment_items.*.keep' => ['nullable', Rule::in(['1'])],
        ]);

        $deliveryNote = trim((string) $v['delivery_note_number']);
        $userId = optional($request->user())->id;

        DB::transaction(function () use ($id, $v, $deliveryNote, $userId, $request) {
            $shipment = DB::table('shipments')->where('id', $id)->lockForUpdate()->firstOrFail();
            if ($shipment->status !== 'Draft') {
                throw ValidationException::withMessages([
                    'shipment' => 'Hanya draft shipment yang masih bisa diubah.',
                ]);
            }

            $duplicateShipment = DB::table('shipments')
                ->where('supplier_id', $shipment->supplier_id)
                ->where('id', '!=', $shipment->id)
                ->whereRaw('LOWER(TRIM(delivery_note_number)) = ?', [mb_strtolower($deliveryNote)])
                ->where('status', '!=', 'Cancelled')
                ->lockForUpdate()
                ->first();

            if ($duplicateShipment) {
                throw ValidationException::withMessages([
                    'delivery_note_number' => "Delivery note {$deliveryNote} sudah dipakai oleh shipment {$duplicateShipment->shipment_number}.",
                ]);
            }

            $currentLines = $this->shipmentLineQuery((int) $shipment->id)->lockForUpdate()->get()->keyBy('shipment_item_id');
            $keptLines = collect($v['shipment_items'])
                ->filter(fn ($line) => ($line['keep'] ?? null) === '1')
                ->values();

            if ($keptLines->isEmpty()) {
                throw ValidationException::withMessages([
                    'shipment_items' => 'Minimal satu item harus dipertahankan di draft shipment.',
                ]);
            }

            foreach ($keptLines as $line) {
                $existing = $currentLines->get((int) $line['id']);
                if (! $existing) {
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
                'supplier_remark' => $v['supplier_remark'] ?? null,
                'updated_at' => now(),
            ]);

            $keptIds = $keptLines->pluck('id')->map(fn ($lineId) => (int) $lineId)->all();
            DB::table('shipment_items')
                ->where('shipment_id', $shipment->id)
                ->whereNotIn('id', $keptIds)
                ->delete();

            foreach ($keptLines as $line) {
                DB::table('shipment_items')
                    ->where('id', (int) $line['id'])
                    ->update([
                        'shipped_qty' => (float) $line['shipped_qty'],
                        'updated_at' => now(),
                    ]);
            }

            ErpFlow::audit('shipments', (int) $shipment->id, 'update', $shipment, $v, $userId, $request->ip());
        });

        return redirect()->route('shipments.edit', $id)->with('success', 'Draft shipment berhasil diperbarui.');
    }

    public function cancelDraft(string $id, Request $request)
    {
        $shipment = DB::table('shipments')->where('id', $id)->firstOrFail();
        if ($shipment->status !== 'Draft') {
            return back()->with('error', 'Hanya shipment Draft yang bisa dibatalkan.');
        }

        DB::table('shipments')->where('id', $shipment->id)->update([
            'status' => 'Cancelled',
            'updated_at' => now(),
        ]);

        ErpFlow::audit('shipments', (int) $shipment->id, 'cancel', $shipment, ['status' => 'Cancelled'], optional($request->user())->id, $request->ip());

        return back()->with('success', 'Draft shipment berhasil dibatalkan.');
    }

    private function candidateItemsQuery(Request $request)
    {
        return $this->candidateItemsBaseQuery()
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('po.supplier_id', $request->integer('supplier_id')))
            ->when($request->filled('keyword'), function ($q) use ($request) {
                $keyword = '%'.$request->string('keyword').'%';
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
                    ->where('sh_alloc.status', '!=', 'Cancelled');
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
                DB::raw('COALESCE(SUM(CASE WHEN sh_alloc.id IS NOT NULL THEN si.shipped_qty - si.received_qty ELSE 0 END), 0) as open_shipment_qty')
            )
            ->whereIn('po.status', self::SHIPPABLE_PO_STATUSES)
            ->where('poi.outstanding_qty', '>', 0)
            ->where('poi.item_status', '!=', 'Cancelled')
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
                'poi.etd_date'
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
                    ->where('other_sh.status', '!=', 'Cancelled');
            })
            ->where('si.shipment_id', $shipmentId)
            ->select(
                'si.id as shipment_item_id',
                'si.shipped_qty',
                'si.received_qty',
                'poi.id as purchase_order_item_id',
                'poi.outstanding_qty',
                'po.po_number',
                'i.item_code',
                'i.item_name'
            )
            ->selectRaw('COALESCE(SUM(CASE WHEN other_sh.id IS NOT NULL THEN other_si.shipped_qty - other_si.received_qty ELSE 0 END), 0) as other_open_shipment_qty')
            ->groupBy('si.id', 'si.shipped_qty', 'si.received_qty', 'poi.id', 'poi.outstanding_qty', 'po.po_number', 'i.item_code', 'i.item_name')
            ->selectRaw('(poi.outstanding_qty - COALESCE(SUM(CASE WHEN other_sh.id IS NOT NULL THEN other_si.shipped_qty - other_si.received_qty ELSE 0 END), 0)) as available_to_ship_qty');
    }
}
