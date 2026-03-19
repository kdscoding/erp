<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();

        $metrics = [
            'open_po' => DB::table('purchase_orders')->whereNotIn('status', ['Closed', 'Cancelled'])->count(),
            'overdue_po' => DB::table('purchase_order_items')->whereNotNull('etd_date')->whereDate('etd_date', '<', $today)->where('outstanding_qty', '>', 0)->where('item_status', '!=', 'Cancelled')->count(),
            'shipped_today' => DB::table('shipments')->whereDate('shipment_date', $today)->count(),
            'received_today' => DB::table('goods_receipts')->whereDate('receipt_date', $today)->count(),
            'partial_po' => DB::table('purchase_orders')->where('status', 'Partial')->count(),
            'suppliers' => DB::table('suppliers')->where('status', true)->count(),
            'at_risk_items' => DB::table('purchase_order_items as poi')
                ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->where('poi.outstanding_qty', '>', 0)
                ->whereNotNull('poi.etd_date')
                ->whereDate('poi.etd_date', '<', $today)
                ->whereNotIn('po.status', ['Closed', 'Cancelled'])
                ->count(),
        ];

        $supplierDelay = DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('s.supplier_name', DB::raw('COUNT(*) as late_count'))
            ->whereDate('po.eta_date', '<', $today)
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->groupBy('s.supplier_name')
            ->orderByDesc('late_count')
            ->limit(5)
            ->get();

        $openPoList = DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('po.po_number', 'po.po_date', 'po.status', 's.supplier_name', 'po.eta_date')
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->orderBy('po.eta_date')
            ->limit(8)
            ->get();

        $recentReceivings = DB::table('goods_receipts as gr')
            ->join('purchase_orders as po', 'po.id', '=', 'gr.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('gr.gr_number', 'gr.receipt_date', 'po.po_number', 's.supplier_name')
            ->orderByDesc('gr.id')
            ->limit(8)
            ->get();


        $onTimeItems = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('po.po_number', 'i.item_code', 'i.item_name', 'poi.etd_date', 'poi.outstanding_qty', 's.supplier_name')
            ->where('poi.outstanding_qty', '>', 0)
            ->whereNotNull('poi.etd_date')
            ->whereDate('poi.etd_date', '>=', $today)
            ->where('poi.item_status', '!=', 'Cancelled')
            ->orderBy('poi.etd_date')
            ->limit(8)
            ->get();

        $atRiskItems = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('po.po_number', 'i.item_code', 'i.item_name', 'poi.etd_date', 'poi.outstanding_qty', 's.supplier_name')
            ->where('poi.outstanding_qty', '>', 0)
            ->whereNotNull('poi.etd_date')
            ->whereDate('poi.etd_date', '<', $today)
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->orderBy('poi.etd_date')
            ->limit(8)
            ->get();

        return view('dashboard', compact('metrics', 'supplierDelay', 'openPoList', 'recentReceivings', 'atRiskItems', 'onTimeItems'));
    }
}
