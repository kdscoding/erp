<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function outstanding(Request $request): View
    {
        $rows = DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_order_items as poi', function ($join) {
                $join->on('poi.purchase_order_id', '=', 'po.id')
                    ->where('poi.item_status', '!=', 'Cancelled')
                    ->where('poi.outstanding_qty', '>', 0);
            })
            ->select('po.id', 'po.po_number', 'po.po_date', 'po.status', 's.supplier_name')
            ->selectRaw('COALESCE(po.eta_date, MIN(COALESCE(poi.eta_date, poi.etd_date))) as eta_date')
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('po.supplier_id', $request->integer('supplier_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('po.status', $request->string('status')))
            ->when($request->filled('start_date'), fn ($q) => $q->whereDate('po.po_date', '>=', $request->date('start_date')))
            ->when($request->filled('end_date'), fn ($q) => $q->whereDate('po.po_date', '<=', $request->date('end_date')))
            ->groupBy('po.id', 'po.po_number', 'po.po_date', 'po.status', 's.supplier_name', 'po.eta_date')
            ->orderByDesc('po.id')
            ->paginate(30)
            ->withQueryString();

        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get(['id', 'supplier_name']);

        return view('reports.outstanding', compact('rows', 'suppliers'));
    }
}
