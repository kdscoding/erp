<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function index()
    {
        $rows = DB::table('shipments as sh')
            ->join('purchase_orders as po', 'po.id', '=', 'sh.purchase_order_id')
            ->select('sh.*', 'po.po_number')
            ->orderByDesc('sh.id')
            ->paginate(20);
        $pos = DB::table('purchase_orders')->orderByDesc('id')->limit(200)->get();
        return view('shipments.index', compact('rows', 'pos'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'purchase_order_id' => 'required|integer',
            'shipment_date' => 'required|date',
        ], ['required' => ':attribute wajib diisi.']);

        $number = 'SHP-' . now()->format('Ymd') . '-' . str_pad((string)(DB::table('shipments')->count()+1), 4, '0', STR_PAD_LEFT);

        DB::table('shipments')->insert([
            'purchase_order_id' => $v['purchase_order_id'],
            'shipment_number' => $number,
            'shipment_date' => $v['shipment_date'],
            'eta_date' => $request->eta_date,
            'delivery_note_number' => $request->delivery_note_number,
            'supplier_remark' => $request->supplier_remark,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_orders')->where('id', $v['purchase_order_id'])->update(['status' => 'Shipped', 'updated_at' => now()]);

        return back()->with('success', 'Shipment tersimpan.');
    }
}
