<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TraceabilityController extends Controller
{
    public function index(Request $request)
    {
        $rows = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->leftJoin('goods_receipt_items as gri', 'gri.purchase_order_item_id', '=', 'poi.id')
            ->leftJoin('goods_receipts as gr', 'gr.id', '=', 'gri.goods_receipt_id')
            ->select(
                'po.po_number',
                'po.po_date',
                'po.status as po_status',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'poi.ordered_qty',
                'poi.received_qty',
                'poi.outstanding_qty',
                'poi.etd_date',
                'poi.item_status',
                'poi.cancel_reason'
            )
            ->selectRaw('MIN(gr.receipt_date) as first_receipt_date')
            ->selectRaw('MAX(gr.receipt_date) as last_receipt_date')
            ->selectRaw('COUNT(gri.id) as receipt_count')
            ->when($request->filled('po_number'), fn ($q) => $q->where('po.po_number', 'like', '%' . $request->po_number . '%'))
            ->groupBy('po.po_number', 'po.po_date', 'po.status', 's.supplier_name', 'i.item_code', 'i.item_name', 'poi.ordered_qty', 'poi.received_qty', 'poi.outstanding_qty', 'poi.etd_date', 'poi.item_status', 'poi.cancel_reason')
            ->orderByDesc('po.po_date')
            ->get();

        return view('traceability.index', compact('rows'));
    }
}
