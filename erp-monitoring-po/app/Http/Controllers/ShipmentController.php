<?php

namespace App\Http\Controllers;

use App\Support\ErpFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    private const SHIPPABLE_PO_STATUSES = ['PO Issued', 'Confirmed', 'Shipped', 'Partial'];

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

        $items = $this->candidateItemsBaseQuery()
            ->whereIn('poi.id', $selectedIds)
            ->lockForUpdate()
            ->get();

        if ($items->count() !== $selectedIds->count()) {
            return back()->withInput()->with('error', 'Sebagian item tidak lagi tersedia untuk dibuat shipment.');
        }

        if ($items->pluck('supplier_id')->unique()->count() !== 1) {
            return back()->withInput()->with('error', 'Semua item shipment harus berasal dari supplier yang sama.');
        }

        $supplierId = (int) $items->first()->supplier_id;

        $linePayloads = [];
        foreach ($items as $item) {
            $qty = (float) ($request->input("shipped_qty.{$item->purchase_order_item_id}") ?? 0);
            if ($qty <= 0) {
                return back()->withInput()->with('error', 'Qty kirim harus diisi untuk setiap item yang dipilih.');
            }

            if ($qty > (float) $item->available_to_ship_qty) {
                return back()->withInput()->with('error', "Qty kirim untuk {$item->item_code} melebihi sisa qty yang masih bisa dialokasikan.");
            }

            $linePayloads[] = [
                'purchase_order_item_id' => $item->purchase_order_item_id,
                'purchase_order_id' => $item->purchase_order_id,
                'shipped_qty' => $qty,
            ];
        }

        $userId = optional($request->user())->id;
        $number = ErpFlow::generateNumber('SHP', 'shipments', 'shipment_number');
        $remark = trim(implode(' | ', array_filter([
            ! empty($v['po_reference_missing']) ? 'Dokumen supplier tidak mencantumkan nomor PO.' : null,
            $v['supplier_remark'] ?? null,
        ]))) ?: null;

        DB::transaction(function () use ($linePayloads, $number, $remark, $supplierId, $userId, $v, $request) {
            $shipmentId = DB::table('shipments')->insertGetId([
                'purchase_order_id' => $linePayloads[0]['purchase_order_id'],
                'supplier_id' => $supplierId,
                'shipment_number' => $number,
                'shipment_date' => $v['shipment_date'],
                'delivery_note_number' => $v['delivery_note_number'],
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
        });

        $request->session()->forget('shipment_selected_items');
        $request->session()->forget('shipment_shipped_qty');

        return back()->with('success', 'Shipment dokumen supplier tersimpan dengan status Draft.');
    }

    public function markShipped(string $id, Request $request)
    {
        $shipment = DB::table('shipments')->where('id', $id)->firstOrFail();
        if ($shipment->status !== 'Draft') {
            return back()->with('error', 'Hanya shipment Draft yang bisa dikonfirmasi menjadi Shipped.');
        }

        $linePoIds = DB::table('shipment_items as si')
            ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
            ->where('si.shipment_id', $shipment->id)
            ->pluck('poi.purchase_order_id')
            ->map(fn ($poId) => (int) $poId)
            ->unique()
            ->values();

        if ($linePoIds->isEmpty()) {
            return back()->with('error', 'Shipment belum memiliki item yang bisa dikirim.');
        }

        $userId = optional($request->user())->id;

        DB::transaction(function () use ($shipment, $linePoIds, $userId) {
            DB::table('shipments')->where('id', $shipment->id)->update([
                'status' => 'Shipped',
                'updated_at' => now(),
            ]);

            $purchaseOrders = DB::table('purchase_orders')
                ->whereIn('id', $linePoIds)
                ->lockForUpdate()
                ->get();

            foreach ($purchaseOrders as $po) {
                DB::table('purchase_orders')->where('id', $po->id)->update([
                    'status' => 'Shipped',
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);

                if ($po->status !== 'Shipped') {
                    ErpFlow::pushPoStatus((int) $po->id, $po->status, 'Shipped', $userId, 'Shipment '.$shipment->shipment_number.' dikonfirmasi berangkat.');
                }
            }
        });

        return back()->with('success', 'Shipment berhasil dikonfirmasi menjadi Shipped.');
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
            ->havingRaw('(poi.outstanding_qty - COALESCE(SUM(CASE WHEN sh_alloc.id IS NOT NULL THEN si.shipped_qty - si.received_qty ELSE 0 END), 0)) > 0')
            ->selectRaw('(poi.outstanding_qty - COALESCE(SUM(CASE WHEN sh_alloc.id IS NOT NULL THEN si.shipped_qty - si.received_qty ELSE 0 END), 0)) as available_to_ship_qty');
    }
}
