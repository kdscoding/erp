<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $currentDateSql = $this->currentDateExpression();
        $supplierId = $request->integer('supplier_id');
        $dateFrom = $request->date('date_from')?->format('Y-m-d');
        $dateTo = $request->date('date_to')?->format('Y-m-d');

        $suppliers = DB::table('suppliers')
            ->orderBy('supplier_name')
            ->get(['id', 'supplier_name']);

        $metrics = [
            'open_po' => DB::table('purchase_orders')
                ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
                ->when($dateFrom, fn ($query) => $query->whereDate('po_date', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('po_date', '<=', $dateTo))
                ->whereNotIn('status', ['Closed', 'Cancelled'])
                ->count(),

            'overdue_po' => DB::table('purchase_order_items')
                ->join('purchase_orders as po', 'po.id', '=', 'purchase_order_items.purchase_order_id')
                ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
                ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
                ->whereNotNull('etd_date')
                ->whereRaw("DATE(etd_date) < {$currentDateSql}")
                ->where('outstanding_qty', '>', 0)
                ->where('purchase_order_items.item_status', '!=', 'Cancelled')
                ->count(),

            'shipped_today' => DB::table('shipments')
                ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
                ->whereRaw("DATE(shipment_date) = {$currentDateSql}")
                ->count(),

            'received_today' => DB::table('goods_receipts')
                ->join('purchase_orders as po', 'po.id', '=', 'goods_receipts.purchase_order_id')
                ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
                ->whereRaw("DATE(receipt_date) = {$currentDateSql}")
                ->count(),

            'late_po' => DB::table('purchase_orders')
                ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
                ->when($dateFrom, fn ($query) => $query->whereDate('po_date', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('po_date', '<=', $dateTo))
                ->where('status', 'Late')
                ->count(),

            'suppliers' => DB::table('suppliers')
                ->where('status', true)
                ->count(),

            'at_risk_items' => DB::table('purchase_order_items as poi')
                ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
                ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
                ->where('poi.outstanding_qty', '>', 0)
                ->whereNotNull('poi.etd_date')
                ->whereRaw("DATE(poi.etd_date) < {$currentDateSql}")
                ->whereNotIn('po.status', ['Closed', 'Cancelled'])
                ->count(),
        ];

        $supplierDelay = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->select('s.supplier_name')
            ->selectRaw('COUNT(poi.id) as late_item_count')
            ->selectRaw('COUNT(DISTINCT po.id) as late_po_count')
            ->selectRaw('MIN(poi.etd_date) as oldest_late_etd')
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->where('poi.item_status', '!=', 'Cancelled')
            ->where('poi.outstanding_qty', '>', 0)
            ->whereNotNull('poi.etd_date')
            ->whereRaw("DATE(poi.etd_date) < {$currentDateSql}")
            ->groupBy('s.supplier_name')
            ->orderByDesc('late_item_count')
            ->orderByDesc('late_po_count')
            ->orderBy('oldest_late_etd')
            ->limit(5)
            ->get();

        $poMonitoringSummary = DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_order_items as poi', 'poi.purchase_order_id', '=', 'po.id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->select('po.id as po_id', 'po.po_number', 'po.status as po_status', 's.supplier_name')
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NULL THEN 1 ELSE 0 END) as waiting_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) >= {$currentDateSql} THEN 1 ELSE 0 END) as confirmed_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) < {$currentDateSql} THEN 1 ELSE 0 END) as late_items")
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
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
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
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) >= {$currentDateSql} AND poi.item_status != 'Cancelled' THEN 1 ELSE 0 END) as confirmed_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) < {$currentDateSql} AND poi.item_status != 'Cancelled' THEN 1 ELSE 0 END) as late_items")
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->groupBy('po.id', 'po.po_number', 'po.po_date', 'po.status', 's.supplier_name', 'po.eta_date')
            ->orderBy('po_eta_date')
            ->limit(8)
            ->get();

        $recentReceivings = DB::table('goods_receipts as gr')
            ->join('purchase_orders as po', 'po.id', '=', 'gr.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('gr.receipt_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('gr.receipt_date', '<=', $dateTo))
            ->select('gr.id', 'gr.gr_number', 'gr.receipt_date', 'po.po_number', 's.supplier_name')
            ->orderByDesc('gr.id')
            ->limit(8)
            ->get();

        $onTimeItems = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->select('po.po_number', 'i.item_code', 'i.item_name', 'poi.etd_date', 'poi.outstanding_qty', 's.supplier_name')
            ->where('poi.outstanding_qty', '>', 0)
            ->whereNotNull('poi.etd_date')
            ->whereRaw("DATE(poi.etd_date) >= {$currentDateSql}")
            ->where('poi.item_status', '!=', 'Cancelled')
            ->orderBy('poi.etd_date')
            ->limit(8)
            ->get();

        $atRiskItems = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->select('po.po_number', 'i.item_code', 'i.item_name', 'poi.etd_date', 'poi.outstanding_qty', 's.supplier_name')
            ->where('poi.outstanding_qty', '>', 0)
            ->whereNotNull('poi.etd_date')
            ->whereRaw("DATE(poi.etd_date) < {$currentDateSql}")
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->orderBy('poi.etd_date')
            ->limit(8)
            ->get();

        $itemMonitoringList = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
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
                WHEN DATE(poi.etd_date) < {$currentDateSql} THEN 'Late'
                ELSE 'Confirmed'
            END as monitoring_status")
            ->selectRaw("CASE 
                WHEN poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 'Sudah diterima sebagian'
                WHEN poi.outstanding_qty <= 0 THEN 'Selesai'
                WHEN poi.etd_date IS NULL THEN 'Belum ada konfirmasi supplier'
                WHEN DATE(poi.etd_date) < {$currentDateSql} THEN 'Terlambat dari ETD'
                ELSE 'Sudah dikonfirmasi supplier'
            END as monitoring_note")
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->where('poi.item_status', '!=', 'Cancelled')
            ->orderByRaw("CASE 
                WHEN poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 1
                WHEN poi.etd_date IS NULL THEN 2
                WHEN DATE(poi.etd_date) < {$currentDateSql} THEN 3
                ELSE 4
            END")
            ->orderBy('po.po_number')
            ->orderBy('i.item_code')
            ->limit(5)
            ->get();

        $statusBreakdown = [
            'Waiting' => (int) $poMonitoringSummary->sum('waiting_items'),
            'Confirmed' => (int) $poMonitoringSummary->sum('confirmed_items'),
            'Late' => (int) $poMonitoringSummary->sum('late_items'),
            'Partial' => (int) $poMonitoringSummary->sum('partial_items'),
            'Closed' => (int) $poMonitoringSummary->sum('closed_items'),
        ];

        $etdHealth = [
            'At-Risk' => (int) $atRiskItems->count(),
            'On-Time' => (int) $onTimeItems->count(),
        ];

        $supplierRiskChart = [
            'labels' => $supplierDelay->pluck('supplier_name')->values()->all(),
            'late_items' => $supplierDelay->pluck('late_item_count')->map(fn ($value) => (int) $value)->values()->all(),
            'late_pos' => $supplierDelay->pluck('late_po_count')->map(fn ($value) => (int) $value)->values()->all(),
        ];

        return view('dashboard', compact(
            'metrics',
            'suppliers',
            'supplierId',
            'dateFrom',
            'dateTo',
            'statusBreakdown',
            'etdHealth',
            'supplierRiskChart',
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
        [$poMonitoringSummary, $itemMonitoringList] = $this->monitoringData();

        return view('monitoring', compact('itemMonitoringList', 'poMonitoringSummary'));
    }

    public function exportMonitoringExcel(): Response
    {
        [$poMonitoringSummary, $itemMonitoringList] = $this->monitoringData();

        $content = view('monitoring-export', [
            'poMonitoringSummary' => $poMonitoringSummary,
            'itemMonitoringList' => $itemMonitoringList,
            'generatedAt' => now(),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="monitoring-po-item-' . now()->format('Ymd-His') . '.xls"',
        ]);
    }

    private function monitoringData(): array
    {
        $currentDateSql = $this->currentDateExpression();

        $poMonitoringSummary = DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_order_items as poi', 'poi.purchase_order_id', '=', 'po.id')
            ->select('po.id as po_id', 'po.po_number', 'po.status as po_status', 's.supplier_name')
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NULL THEN 1 ELSE 0 END) as waiting_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) >= {$currentDateSql} THEN 1 ELSE 0 END) as confirmed_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) < {$currentDateSql} THEN 1 ELSE 0 END) as late_items")
            ->selectRaw("SUM(CASE WHEN COALESCE(poi.item_status, '') != 'Cancelled' AND poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 1 ELSE 0 END) as partial_items")
            ->selectRaw("SUM(CASE WHEN poi.item_status = '" . \App\Support\DocumentTermCodes::ITEM_CLOSED . "' THEN 1 ELSE 0 END) as closed_items")
            ->selectRaw("SUM(CASE WHEN poi.item_status = '" . \App\Support\DocumentTermCodes::ITEM_FORCE_CLOSED . "' THEN 1 ELSE 0 END) as force_closed_items")
            ->where('po.status', '!=', 'Cancelled')
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
                WHEN poi.item_status = '" . \App\Support\DocumentTermCodes::ITEM_FORCE_CLOSED . "' THEN '" . \App\Support\DocumentTermCodes::ITEM_FORCE_CLOSED . "'
                WHEN poi.outstanding_qty <= 0 THEN 'Closed'
                WHEN poi.received_qty > 0 THEN 'Partial'
                WHEN poi.etd_date IS NULL THEN 'Waiting'
                WHEN DATE(poi.etd_date) < {$currentDateSql} THEN 'Late'
                ELSE 'Confirmed'
            END as monitoring_status")
            ->selectRaw("CASE 
                WHEN poi.item_status = '" . \App\Support\DocumentTermCodes::ITEM_FORCE_CLOSED . "' THEN 'Outstanding dihentikan secara manual'
                WHEN poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 'Sudah diterima sebagian'
                WHEN poi.outstanding_qty <= 0 THEN 'Selesai'
                WHEN poi.etd_date IS NULL THEN 'Belum ada konfirmasi supplier'
                WHEN DATE(poi.etd_date) < {$currentDateSql} THEN 'Terlambat dari ETD'
                ELSE 'Sudah dikonfirmasi supplier'
            END as monitoring_note")
            ->where('po.status', '!=', 'Cancelled')
            ->where('poi.item_status', '!=', 'Cancelled')
            ->orderByRaw("CASE 
                WHEN poi.item_status = '" . \App\Support\DocumentTermCodes::ITEM_FORCE_CLOSED . "' THEN 1
                WHEN poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 1
                WHEN DATE(poi.etd_date) < {$currentDateSql} THEN 2
                WHEN poi.etd_date IS NULL THEN 3
                ELSE 4
            END")
            ->orderBy('po.po_number')
            ->orderBy('i.item_code')
            ->limit(15)
            ->get();

        return [$poMonitoringSummary, $itemMonitoringList];
    }

    private function currentDateExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "date('now')"
            : 'CURDATE()';
    }

}
