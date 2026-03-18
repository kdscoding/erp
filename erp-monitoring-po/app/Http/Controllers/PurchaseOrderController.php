<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $rows = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('po.*', 's.supplier_name')
            ->orderByDesc('po.id')
            ->paginate(20);

        return view('po.index', compact('rows'));
    }

    public function create()
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'required|string|max:100|unique:purchase_orders,po_number',
            'po_date' => 'required|date',
            'supplier_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.ordered_qty' => 'required|numeric|min:0.01',
        ], [
            'required' => ':attribute wajib diisi.',
            'items.min' => 'Minimal harus ada 1 item.',
        ]);

        DB::beginTransaction();
        try {
            $poId = DB::table('purchase_orders')->insertGetId([
                'po_number' => $validated['po_number'],
                'po_date' => $validated['po_date'],
                'supplier_id' => $validated['supplier_id'],
                'status' => 'Draft',
                'notes' => $request->input('notes'),
                'created_by' => optional($request->user())->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($validated['items'] as $row) {
                $item = DB::table('items')->where('id', $row['item_id'])->first();
                if (!$item) {
                    throw new \RuntimeException('Item tidak ditemukan ID: ' . $row['item_id']);
                }

                DB::table('purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'item_id' => $item->id,
                    'ordered_qty' => $row['ordered_qty'],
                    'received_qty' => 0,
                    'outstanding_qty' => $row['ordered_qty'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('po.index')->with('success', 'PO berhasil dibuat (manual number + multi item).');
    }
}
