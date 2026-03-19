<?php

namespace App\Http\Controllers;

use App\Support\ErpFlow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    private function transitionMap(): array
    {
        return [
            'Draft' => ['Submitted', 'Cancelled'],
            'Submitted' => ['Approved', 'Cancelled', 'On Hold / Discrepancy'],
            'Approved' => ['Sent to Supplier', 'On Hold / Discrepancy', 'Cancelled'],
<<<<<<< ours
<<<<<<< ours
<<<<<<< ours
            'Sent to Supplier' => ['Supplier Confirmed', 'Shipped', 'On Hold / Discrepancy'],
=======
            'Sent to Supplier' => ['Partial Confirmed', 'Supplier Confirmed', 'Shipped', 'On Hold / Discrepancy'],
            'Partial Confirmed' => ['Supplier Confirmed', 'Shipped', 'On Hold / Discrepancy'],
>>>>>>> theirs
=======
            'Sent to Supplier' => ['Partial Confirmed', 'Supplier Confirmed', 'Shipped', 'On Hold / Discrepancy'],
            'Partial Confirmed' => ['Supplier Confirmed', 'Shipped', 'On Hold / Discrepancy'],
>>>>>>> theirs
=======
            'Sent to Supplier' => ['Partial Confirmed', 'Supplier Confirmed', 'Shipped', 'On Hold / Discrepancy'],
            'Partial Confirmed' => ['Supplier Confirmed', 'Shipped', 'On Hold / Discrepancy'],
>>>>>>> theirs
            'Supplier Confirmed' => ['Shipped', 'On Hold / Discrepancy'],
            'Shipped' => ['Partial Received', 'Closed', 'On Hold / Discrepancy'],
            'Partial Received' => ['Closed', 'On Hold / Discrepancy'],
            'On Hold / Discrepancy' => ['Submitted', 'Approved', 'Sent to Supplier', 'Cancelled'],
        ];
    }

    public function index(Request $request): View
    {
        $rows = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('po.*', 's.supplier_name')
            ->when($request->filled('status'), fn ($q) => $q->where('po.status', $request->string('status')))
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('po.supplier_id', $request->integer('supplier_id')))
            ->orderByDesc('po.id')
            ->paginate(20)
            ->withQueryString();
<<<<<<< ours

        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get(['id', 'supplier_name']);

=======

        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get(['id', 'supplier_name']);

>>>>>>> theirs
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

    public function show(int $id): View
    {
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
            ->where('poi.purchase_order_id', $id)
            ->orderBy('poi.id')
            ->get();

        $histories = DB::table('po_status_histories as h')
            ->leftJoin('users as u', 'u.id', '=', 'h.changed_by')
            ->where('h.purchase_order_id', $id)
            ->orderByDesc('h.id')
            ->select('h.*', 'u.name as changed_by_name')
            ->get();

        $allowedTransitions = $this->transitionMap()[trim((string) $po->status)] ?? [];
<<<<<<< ours
<<<<<<< ours
<<<<<<< ours

        return view('po.show', compact('po', 'items', 'histories', 'allowedTransitions'));
=======
=======
>>>>>>> theirs
=======
>>>>>>> theirs
        $totalItems = $items->count();
        $confirmedItems = $items->whereNotNull('etd_date')->count();
        $splitShipment = $items->pluck('etd_date')->filter()->unique()->count() > 2;

        return view('po.show', compact('po', 'items', 'histories', 'allowedTransitions', 'totalItems', 'confirmedItems', 'splitShipment'));
<<<<<<< ours
<<<<<<< ours
>>>>>>> theirs
=======
>>>>>>> theirs
=======
>>>>>>> theirs
    }

    public function store(Request $request): RedirectResponse
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

        $userId = optional($request->user())->id;

        DB::beginTransaction();
        try {
            $poNumber = $validated['po_number'] ?: ErpFlow::generateNumber('PO', 'purchase_orders', 'po_number');
            $poId = DB::table('purchase_orders')->insertGetId([
                'po_number' => $poNumber,
                'po_date' => $validated['po_date'],
                'supplier_id' => $validated['supplier_id'],
                'status' => 'Draft',
                'notes' => $request->input('notes'),
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($validated['items'] as $row) {
                DB::table('purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'item_id' => $row['item_id'],
                    'ordered_qty' => $row['ordered_qty'],
                    'received_qty' => 0,
                    'outstanding_qty' => $row['ordered_qty'],
                    'item_status' => 'Waiting',
<<<<<<< ours
<<<<<<< ours
=======
                    'status_item' => 'Waiting',
>>>>>>> theirs
=======
                    'status_item' => 'Waiting',
>>>>>>> theirs
                    'unit_price' => $row['unit_price'] ?? null,
                    'remarks' => $row['remarks'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            ErpFlow::pushPoStatus($poId, null, 'Draft', $userId, 'PO dibuat.');
            ErpFlow::audit('purchase_orders', $poId, 'create', null, ['status' => 'Draft'], $userId, $request->ip());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('po.index')->with('success', 'PO berhasil dibuat.');
<<<<<<< ours
<<<<<<< ours
    }

    public function transition(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'to_status' => 'required|string|max:50',
            'note' => 'nullable|string|max:500',
        ]);

        $po = DB::table('purchase_orders')->where('id', $id)->firstOrFail();
        $userId = optional($request->user())->id;
        $currentStatus = trim((string) $po->status);
        $targetStatus = trim((string) $validated['to_status']);
        $allowedMap = $this->transitionMap();
        $allowedTransitions = $allowedMap[$currentStatus] ?? [];

        if ($currentStatus === $targetStatus) {
            return back()->with('success', 'Status PO tidak berubah.');
        }

        if (! in_array($targetStatus, $allowedTransitions, true)) {
            $allowedText = empty($allowedTransitions) ? 'tidak ada transisi tersedia' : implode(', ', $allowedTransitions);

            return back()->with('error', "Transisi tidak valid dari {$currentStatus} ke {$targetStatus}. Opsi yang diizinkan: {$allowedText}.");
        }

        DB::table('purchase_orders')->where('id', $id)->update([
            'status' => $targetStatus,
            'approved_by' => $targetStatus === 'Approved' ? $userId : $po->approved_by,
            'approved_at' => $targetStatus === 'Approved' ? now() : $po->approved_at,
            'sent_to_supplier_at' => $targetStatus === 'Sent to Supplier' ? now() : $po->sent_to_supplier_at,
            'updated_by' => $userId,
            'updated_at' => now(),
        ]);

        ErpFlow::pushPoStatus($id, $currentStatus, $targetStatus, $userId, $validated['note'] ?? null);
        ErpFlow::audit('purchase_orders', $id, 'status_change', ['status' => $currentStatus], ['status' => $targetStatus, 'note' => $validated['note'] ?? null], $userId, $request->ip());

        if ($targetStatus === 'Approved') {
            DB::table('po_approvals')->insert([
                'purchase_order_id' => $id,
                'approver_id' => $userId,
                'status' => 'Approved',
                'note' => $validated['note'] ?? null,
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Status PO berhasil diperbarui ke '.$targetStatus.'.');
    }

=======
=======
>>>>>>> theirs
    }

    public function transition(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'to_status' => 'required|string|max:50',
            'note' => 'nullable|string|max:500',
        ]);

        $po = DB::table('purchase_orders')->where('id', $id)->firstOrFail();
        $userId = optional($request->user())->id;
        $currentStatus = trim((string) $po->status);
        $targetStatus = trim((string) $validated['to_status']);
        $allowedMap = $this->transitionMap();
        $allowedTransitions = $allowedMap[$currentStatus] ?? [];

        if ($currentStatus === $targetStatus) {
            return back()->with('success', 'Status PO tidak berubah.');
        }

        if (! in_array($targetStatus, $allowedTransitions, true)) {
            $allowedText = empty($allowedTransitions) ? 'tidak ada transisi tersedia' : implode(', ', $allowedTransitions);

            return back()->with('error', "Transisi tidak valid dari {$currentStatus} ke {$targetStatus}. Opsi yang diizinkan: {$allowedText}.");
        }

        DB::table('purchase_orders')->where('id', $id)->update([
            'status' => $targetStatus,
            'approved_by' => $targetStatus === 'Approved' ? $userId : $po->approved_by,
            'approved_at' => $targetStatus === 'Approved' ? now() : $po->approved_at,
            'sent_to_supplier_at' => $targetStatus === 'Sent to Supplier' ? now() : $po->sent_to_supplier_at,
            'updated_by' => $userId,
            'updated_at' => now(),
        ]);

        ErpFlow::pushPoStatus($id, $currentStatus, $targetStatus, $userId, $validated['note'] ?? null);
        ErpFlow::audit('purchase_orders', $id, 'status_change', ['status' => $currentStatus], ['status' => $targetStatus, 'note' => $validated['note'] ?? null], $userId, $request->ip());

        if ($targetStatus === 'Approved') {
            DB::table('po_approvals')->insert([
                'purchase_order_id' => $id,
                'approver_id' => $userId,
                'status' => 'Approved',
                'note' => $validated['note'] ?? null,
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Status PO berhasil diperbarui ke '.$targetStatus.'.');
    }

<<<<<<< ours
>>>>>>> theirs
=======
>>>>>>> theirs

    public function updateItemSchedule(Request $request, int $itemId): RedirectResponse
    {
        $item = DB::table('purchase_order_items')->where('id', $itemId)->firstOrFail();

        $v = $request->validate([
            'etd_date' => 'nullable|date',
            'eta_date' => 'nullable|date|after_or_equal:etd_date',
<<<<<<< ours
<<<<<<< ours
            'item_status' => 'required|string|in:Waiting,Confirmed,Shipped,Partial Received,Received,On Hold',
            'remarks' => 'nullable|string|max:500',
        ], [
            'item_status.required' => 'Status item wajib dipilih.',
=======
=======
>>>>>>> theirs
            'status_item' => 'required|string|in:Waiting,Confirmed,Shipped,Partial Received,Received,On Hold',
            'remarks' => 'nullable|string|max:500',
        ], [
            'status_item.required' => 'Status item wajib dipilih.',
<<<<<<< ours
>>>>>>> theirs
=======
>>>>>>> theirs
        ]);

        DB::table('purchase_order_items')->where('id', $itemId)->update([
            'etd_date' => $v['etd_date'] ?? null,
            'eta_date' => $v['eta_date'] ?? null,
<<<<<<< ours
<<<<<<< ours
            'item_status' => $v['item_status'],
=======
            'item_status' => $v['status_item'],
            'status_item' => $v['status_item'],
>>>>>>> theirs
=======
            'item_status' => $v['status_item'],
            'status_item' => $v['status_item'],
>>>>>>> theirs
            'remarks' => $v['remarks'] ?? DB::table('purchase_order_items')->where('id', $itemId)->value('remarks'),
            'updated_at' => now(),
        ]);

        ErpFlow::audit('purchase_order_items', $itemId, 'item_schedule_update',
<<<<<<< ours
<<<<<<< ours
            ['etd_date' => $item->etd_date, 'eta_date' => $item->eta_date, 'item_status' => $item->item_status],
            ['etd_date' => $v['etd_date'] ?? null, 'eta_date' => $v['eta_date'] ?? null, 'item_status' => $v['item_status']],
=======
            ['etd_date' => $item->etd_date, 'eta_date' => $item->eta_date, 'status_item' => $item->status_item ?? $item->item_status],
            ['etd_date' => $v['etd_date'] ?? null, 'eta_date' => $v['eta_date'] ?? null, 'status_item' => $v['status_item']],
>>>>>>> theirs
=======
            ['etd_date' => $item->etd_date, 'eta_date' => $item->eta_date, 'status_item' => $item->status_item ?? $item->item_status],
            ['etd_date' => $v['etd_date'] ?? null, 'eta_date' => $v['eta_date'] ?? null, 'status_item' => $v['status_item']],
>>>>>>> theirs
            optional($request->user())->id,
            $request->ip()
        );

<<<<<<< ours
<<<<<<< ours
<<<<<<< ours
=======
=======
>>>>>>> theirs
=======
>>>>>>> theirs
        $poId = (int) $item->purchase_order_id;
        $totalItems = DB::table('purchase_order_items')->where('purchase_order_id', $poId)->count();
        $confirmedItems = DB::table('purchase_order_items')->where('purchase_order_id', $poId)->whereNotNull('etd_date')->count();

        $newPoStatus = null;
        if ($confirmedItems > 0 && $confirmedItems < $totalItems) {
            $newPoStatus = 'Partial Confirmed';
        } elseif ($totalItems > 0 && $confirmedItems === $totalItems) {
            $newPoStatus = 'Supplier Confirmed';
        }

        if ($newPoStatus) {
            $oldPoStatus = DB::table('purchase_orders')->where('id', $poId)->value('status');
            if ($oldPoStatus !== $newPoStatus) {
                DB::table('purchase_orders')->where('id', $poId)->update([
                    'status' => $newPoStatus,
                    'updated_by' => optional($request->user())->id,
                    'updated_at' => now(),
                ]);
                ErpFlow::pushPoStatus($poId, $oldPoStatus, $newPoStatus, optional($request->user())->id, 'Auto update dari konfirmasi ETD item.');
            }
        }

<<<<<<< ours
<<<<<<< ours
>>>>>>> theirs
=======
>>>>>>> theirs
=======
>>>>>>> theirs
        return back()->with('success', 'Jadwal item berhasil diperbarui.');
    }

}
