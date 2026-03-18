<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TraceabilityController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('shipments as sh', 'sh.purchase_order_id', '=', 'po.id')
            ->leftJoin('goods_receipts as gr', 'gr.purchase_order_id', '=', 'po.id')
            ->select('po.po_number', 'po.po_date', 'po.status', 's.supplier_name', 'sh.shipment_number', 'sh.shipment_date', 'gr.gr_number', 'gr.receipt_date')
            ->orderByDesc('po.id');

        if ($request->filled('po_number')) {
            $query->where('po.po_number', 'like', '%' . $request->po_number . '%');
        }

        $rows = $query->paginate(30);
        return view('traceability.index', compact('rows'));
    }
}
