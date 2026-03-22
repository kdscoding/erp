<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $metrics = [
            'open_po' => DB::table('purchase_orders')
                ->whereNotIn('status', ['Closed', 'Cancelled'])
                ->count(),

            'overdue_po' => DB::table('purchase_order_items')
                ->whereNotNull('etd_date')
                ->whereRaw('DATE(etd_date) < CURDATE()')
                ->where('outstanding_qty', '>', 0)
                ->where('item_status', '!=', 'Cancelled')
                ->count(),

            'shipped_today' => DB::table('shipments')
                ->whereRaw('DATE(shipment_date) = CURDATE()')
                ->count(),

            'received_today' => DB::table('goods_receipts')
                ->whereRaw('DATE(receipt_date) = CURDATE()')
                ->count(),

            'late_po' => DB::table('purchase_orders')
                ->where('status', 'Late')
                ->count(),

            'suppliers' => DB::table('suppliers')
                ->where('status', true)
                ->count(),

            'at_risk_items' => DB::table('purchase_order_items as poi')
                ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->where('poi.outstanding_qty', '>', 0)
                ->whereNotNull('poi.etd_date')
                ->whereRaw('DATE(poi.etd_date) < CURDATE()')
                ->whereNotIn('po.status', ['Closed', 'Cancelled'])
                ->count(),
        ];

        $supplierDelay = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('s.supplier_name')
            ->selectRaw('COUNT(poi.id) as late_item_count')
            ->selectRaw('COUNT(DISTINCT po.id) as late_po_count')
            ->selectRaw('MIN(poi.etd_date) as oldest_late_etd')
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->where('poi.item_status', '!=', 'Cancelled')
            ->where('poi.outstanding_qty', '>', 0)
            ->whereNotNull('poi.etd_date')
            ->whereRaw('DATE(poi.etd_date) < CURDATE()')
            ->groupBy('s.supplier_name')
            ->orderByDesc('late_item_count')
            ->orderByDesc('late_po_count')
            ->orderBy('oldest_late_etd')
            ->limit(5)
            ->get();

        $poMonitoringSummary = DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_order_items as poi', 'poi.purchase_order_id', '=', 'po.id')
            ->select('po.id as po_id', 'po.po_number', 'po.status as po_status', 's.supplier_name')
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NULL THEN 1 ELSE 0 END) as waiting_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) >= CURDATE() THEN 1 ELSE 0 END) as confirmed_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) < CURDATE() THEN 1 ELSE 0 END) as late_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 1 ELSE 0 END) as partial_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty <= 0 THEN 1 ELSE 0 END) as closed_items")
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->groupBy('po.id', 'po.po_number', 'po.status', 's.supplier_name')
            ->orderBy('po.po_number')
            ->limit(8)
            ->get();

        $openPoList = DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_order_items as poi', 'poi.purchase_order_id', '=', 'po.id')
            ->select(
                'po.id',
                'po.po_number',
                'po.po_date',
                'po.status',
                's.supplier_name',
                DB::raw('COALESCE(po.eta_date, MIN(COALESCE(poi.eta_date, poi.etd_date))) as po_eta_date')
            )
            ->selectRaw("SUM(CASE WHEN poi.item_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty > 0 AND poi.received_qty > 0 THEN 1 ELSE 0 END) as partial_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty <= 0 AND poi.item_status != 'Cancelled' THEN 1 ELSE 0 END) as closed_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NULL AND poi.item_status != 'Cancelled' THEN 1 ELSE 0 END) as waiting_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) >= CURDATE() AND poi.item_status != 'Cancelled' THEN 1 ELSE 0 END) as confirmed_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) < CURDATE() AND poi.item_status != 'Cancelled' THEN 1 ELSE 0 END) as late_items")
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->groupBy('po.id', 'po.po_number', 'po.po_date', 'po.status', 's.supplier_name', 'po.eta_date')
            ->orderBy('po_eta_date')
            ->limit(8)
            ->get();

        $recentReceivings = DB::table('goods_receipts as gr')
            ->join('purchase_orders as po', 'po.id', '=', 'gr.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('gr.id', 'gr.gr_number', 'gr.receipt_date', 'po.po_number', 's.supplier_name')
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
            ->whereRaw('DATE(poi.etd_date) >= CURDATE()')
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
            ->whereRaw('DATE(poi.etd_date) < CURDATE()')
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->orderBy('poi.etd_date')
            ->limit(8)
            ->get();

        $itemMonitoringList = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select(
                'poi.id',
                'po.id as po_id',
                'po.po_number',
                'po.status as po_status',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'poi.ordered_qty',
                'poi.received_qty',
                'poi.outstanding_qty',
                'poi.etd_date'
            )
            ->selectRaw("CASE 
                WHEN poi.item_status = 'Cancelled' THEN 'Cancelled'
                WHEN poi.outstanding_qty <= 0 THEN 'Closed'
                WHEN poi.received_qty > 0 THEN 'Partial'
                WHEN poi.etd_date IS NULL THEN 'Waiting'
                WHEN DATE(poi.etd_date) < CURDATE() THEN 'Late'
                ELSE 'Confirmed'
            END as monitoring_status")
            ->selectRaw("CASE 
                WHEN poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 'Sudah diterima sebagian'
                WHEN poi.outstanding_qty <= 0 THEN 'Selesai'
                WHEN poi.etd_date IS NULL THEN 'Belum ada konfirmasi supplier'
                WHEN DATE(poi.etd_date) < CURDATE() THEN 'Terlambat dari ETD'
                ELSE 'Sudah dikonfirmasi supplier'
            END as monitoring_note")
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->where('poi.item_status', '!=', 'Cancelled')
            ->orderByRaw("CASE 
                WHEN poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 1
                WHEN poi.etd_date IS NULL THEN 2
                WHEN DATE(poi.etd_date) < CURDATE() THEN 3
                ELSE 4
            END")
            ->orderBy('po.po_number')
            ->orderBy('i.item_code')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'metrics',
            'supplierDelay',
            'poMonitoringSummary',
            'openPoList',
            'recentReceivings',
            'atRiskItems',
            'onTimeItems',
            'itemMonitoringList'
        ));
    }

    public function monitoring(): View
    {
        $poMonitoringSummary = DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_order_items as poi', 'poi.purchase_order_id', '=', 'po.id')
            ->select('po.id as po_id', 'po.po_number', 'po.status as po_status', 's.supplier_name')
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NULL THEN 1 ELSE 0 END) as waiting_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) >= CURDATE() THEN 1 ELSE 0 END) as confirmed_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) < CURDATE() THEN 1 ELSE 0 END) as late_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 1 ELSE 0 END) as partial_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty <= 0 THEN 1 ELSE 0 END) as closed_items")
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->groupBy('po.id', 'po.po_number', 'po.status', 's.supplier_name')
            ->orderBy('po.po_number')
            ->get();

        $itemMonitoringList = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select(
                'poi.id',
                'po.id as po_id',
                'po.po_number',
                'po.status as po_status',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'poi.ordered_qty',
                'poi.received_qty',
                'poi.outstanding_qty',
                'poi.etd_date'
            )
            ->selectRaw("CASE 
                WHEN poi.item_status = 'Cancelled' THEN 'Cancelled'
                WHEN poi.outstanding_qty <= 0 THEN 'Closed'
                WHEN poi.received_qty > 0 THEN 'Partial'
                WHEN poi.etd_date IS NULL THEN 'Waiting'
                WHEN DATE(poi.etd_date) < CURDATE() THEN 'Late'
                ELSE 'Confirmed'
            END as monitoring_status")
            ->selectRaw("CASE 
                WHEN poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 'Sudah diterima sebagian'
                WHEN poi.outstanding_qty <= 0 THEN 'Selesai'
                WHEN poi.etd_date IS NULL THEN 'Belum ada konfirmasi supplier'
                WHEN DATE(poi.etd_date) < CURDATE() THEN 'Terlambat dari ETD'
                ELSE 'Sudah dikonfirmasi supplier'
            END as monitoring_note")
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->where('poi.item_status', '!=', 'Cancelled')
            ->orderByRaw("CASE 
                WHEN poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 1
                WHEN poi.etd_date IS NULL THEN 2
                WHEN DATE(poi.etd_date) < CURDATE() THEN 3
                ELSE 4
            END")
            ->orderBy('po.po_number')
            ->orderBy('i.item_code')
            ->get();

        return view('monitoring', compact('itemMonitoringList', 'poMonitoringSummary'));
    }

}
