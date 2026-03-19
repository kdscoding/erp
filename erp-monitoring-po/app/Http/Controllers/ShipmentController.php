<?php

namespace App\Http\Controllers;

use App\Support\ErpFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    public function index(): View
    {
        $rows = DB::table('shipments as sh')
            ->join('purchase_orders as po', 'po.id', '=', 'sh.purchase_order_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('sh.*', 'po.po_number', 's.supplier_name')
            ->orderByDesc('sh.id')
            ->paginate(20);

        $pos = DB::table('purchase_orders')
            ->whereIn('status', ['Approved', 'Sent to Supplier', 'Supplier Confirmed', 'Shipped', 'Partial Received'])
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('shipments.index', compact('rows', 'pos'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'purchase_order_id' => 'required|integer|exists:purchase_orders,id',
            'shipment_date' => 'required|date',
            'eta_date' => 'nullable|date|after_or_equal:shipment_date',
            'delivery_note_number' => 'nullable|string|max:100',
            'supplier_remark' => 'nullable|string|max:500',
        ], ['required' => ':attribute wajib diisi.']);

        $po = DB::table('purchase_orders')->where('id', $v['purchase_order_id'])->firstOrFail();
        if (! in_array($po->status, ['Approved', 'Sent to Supplier', 'Supplier Confirmed', 'Shipped', 'Partial Received'], true)) {
            return back()->with('error', 'Status PO tidak valid untuk shipment.');
        }

        $userId = optional($request->user())->id;
        $number = ErpFlow::generateNumber('SHP', 'shipments', 'shipment_number');

        DB::table('shipments')->insert([
            'purchase_order_id' => $v['purchase_order_id'],
            'shipment_number' => $number,
            'shipment_date' => $v['shipment_date'],
            'eta_date' => $v['eta_date'] ?? null,
            'delivery_note_number' => $v['delivery_note_number'] ?? null,
            'supplier_remark' => $v['supplier_remark'] ?? null,
            'status' => 'Shipped',
            'created_by' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_orders')->where('id', $v['purchase_order_id'])->update([
            'status' => 'Shipped',
            'eta_date' => $v['eta_date'] ?? $po->eta_date,
            'updated_by' => $userId,
            'updated_at' => now(),
        ]);

        ErpFlow::pushPoStatus((int) $po->id, $po->status, 'Shipped', $userId, 'Shipment '.$number.' dibuat.');
        ErpFlow::audit('shipments', (int) DB::getPdo()->lastInsertId(), 'create', null, $v, $userId, $request->ip());

        return back()->with('success', 'Shipment tersimpan.');
    }
}
