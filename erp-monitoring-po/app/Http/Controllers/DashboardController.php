<?php

namespace App\Http\Controllers;

use App\Queries\Dashboard\DashboardQuery;
use App\Support\DocumentTermCodes;
use App\Support\DomainStatus;
use App\Support\ErpFlow;
use App\Support\NumberFormatter;
use App\Support\StatusQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardQuery $dashboardQuery)
    {
    }

    public function __invoke(Request $request): View
    {
        $currentDateSql = $this->currentDateExpression();
        $supplierId = $this->resolveSupplierId($request);
        ['date_from' => $dateFrom, 'date_to' => $dateTo] = $this->resolveDateRange($request);
        $savedViews = $this->savedViews();
        $activeSavedView = (string) $request->query('saved_view', 'default');
        $activePoSql = StatusQuery::sqlNotEquals('po.status', DomainStatus::GROUP_PO_STATUS, DocumentTermCodes::PO_CLOSED)
            . ' AND '
            . StatusQuery::sqlNotEquals('po.status', DomainStatus::GROUP_PO_STATUS, DocumentTermCodes::PO_CANCELLED);
        $activeItemSql = StatusQuery::sqlNotEquals('poi.item_status', DomainStatus::GROUP_PO_ITEM_STATUS, DocumentTermCodes::ITEM_CANCELLED);
        $cancelledItemSql = StatusQuery::sqlEquals('poi.item_status', DomainStatus::GROUP_PO_ITEM_STATUS, DocumentTermCodes::ITEM_CANCELLED);
        $poListSql = $this->groupConcatDistinct('po.po_number');
        $supplierListSql = $this->groupConcatDistinct('s.supplier_name');

        $suppliers = DB::table('suppliers')
            ->orderBy('supplier_name')
            ->get(['id', 'supplier_name', 'supplier_code']);

$metrics = [
            'po_full' => DB::table('purchase_orders')
                ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
                ->when($dateFrom, fn ($query) => $query->whereDate('po_date', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('po_date', '<=', $dateTo))
                ->where('status', ErpFlow::PO_STATUS_FULL)
                ->count(),

            'po_partial' => DB::table('purchase_orders')
                ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
                ->when($dateFrom, fn ($query) => $query->whereDate('po_date', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('po_date', '<=', $dateTo))
                ->where('status', ErpFlow::PO_STATUS_PARTIAL)
                ->count(),

            'po_delayed' => DB::table('purchase_orders')
                ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
                ->when($dateFrom, fn ($query) => $query->whereDate('po_date', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('po_date', '<=', $dateTo))
                ->where('status', ErpFlow::PO_STATUS_DELAYED)
                ->count(),

            'open_po' => DB::table('purchase_orders')
                ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
                ->when($dateFrom, fn ($query) => $query->whereDate('po_date', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('po_date', '<=', $dateTo))
                ->when(true, fn ($query) => StatusQuery::whereNotIn(
                    $query,
                    'status',
                    DomainStatus::GROUP_PO_STATUS,
                    [DocumentTermCodes::PO_CLOSED, DocumentTermCodes::PO_CANCELLED]
                ))
                ->count(),

            'overdue_po' => DB::table('purchase_order_items')
                ->join('purchase_orders as po', 'po.id', '=', 'purchase_order_items.purchase_order_id')
                ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
                ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
                ->whereNotNull('etd_date')
                ->whereRaw("DATE(etd_date) < {$currentDateSql}")
                ->where('outstanding_qty', '>', 0)
                ->when(true, fn ($query) => StatusQuery::whereNotEquals(
                    $query,
                    'purchase_order_items.item_status',
                    DomainStatus::GROUP_PO_ITEM_STATUS,
                    DocumentTermCodes::ITEM_CANCELLED
                ))
                ->count(),

            'shipped_today' => DB::table('shipments')
                ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
                ->whereRaw("DATE(shipment_date) = {$currentDateSql}")
                ->count(),

            'received_today' => DB::table('goods_receipts')
                ->leftJoin('goods_receipt_items as gri', 'gri.goods_receipt_id', '=', 'goods_receipts.id')
                ->leftJoin('purchase_order_items as poi', 'poi.id', '=', 'gri.purchase_order_item_id')
                ->leftJoin('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
                ->whereRaw("DATE(receipt_date) = {$currentDateSql}")
                ->distinct('goods_receipts.id')
                ->count('goods_receipts.id'),

            'late_po' => DB::table('purchase_orders')
                ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
                ->when($dateFrom, fn ($query) => $query->whereDate('po_date', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('po_date', '<=', $dateTo))
                ->when(true, fn ($query) => StatusQuery::whereEquals(
                    $query,
                    'status',
                    DomainStatus::GROUP_PO_STATUS,
                    DocumentTermCodes::PO_LATE
                ))
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
                ->when(true, fn ($query) => StatusQuery::whereNotIn(
                    $query,
                    'po.status',
                    DomainStatus::GROUP_PO_STATUS,
                    [DocumentTermCodes::PO_CLOSED, DocumentTermCodes::PO_CANCELLED]
                ))
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
            ->whereRaw($activePoSql)
            ->whereRaw($activeItemSql)
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
            ->selectRaw("SUM(CASE WHEN {$activeItemSql} AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NULL THEN 1 ELSE 0 END) as waiting_items")
            ->selectRaw("SUM(CASE WHEN {$activeItemSql} AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) >= {$currentDateSql} THEN 1 ELSE 0 END) as confirmed_items")
            ->selectRaw("SUM(CASE WHEN {$activeItemSql} AND poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) < {$currentDateSql} THEN 1 ELSE 0 END) as late_items")
            ->selectRaw("SUM(CASE WHEN {$activeItemSql} AND poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 1 ELSE 0 END) as partial_items")
            ->selectRaw("SUM(CASE WHEN {$activeItemSql} AND poi.outstanding_qty <= 0 THEN 1 ELSE 0 END) as closed_items")
            ->whereRaw($activePoSql)
            ->groupBy('po.id', 'po.po_number', 'po.status', 's.supplier_name')
            ->orderBy('po.po_number')
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
            ->selectRaw("SUM(CASE WHEN {$cancelledItemSql} THEN 1 ELSE 0 END) as cancelled_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty > 0 AND poi.received_qty > 0 THEN 1 ELSE 0 END) as partial_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty <= 0 AND {$activeItemSql} THEN 1 ELSE 0 END) as closed_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NULL AND {$activeItemSql} THEN 1 ELSE 0 END) as waiting_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) >= {$currentDateSql} AND {$activeItemSql} THEN 1 ELSE 0 END) as confirmed_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) < {$currentDateSql} AND {$activeItemSql} THEN 1 ELSE 0 END) as late_items")
            ->whereRaw($activePoSql)
            ->groupBy('po.id', 'po.po_number', 'po.po_date', 'po.status', 's.supplier_name', 'po.eta_date')
            ->orderBy('po_eta_date')
            ->limit(8)
            ->get();

        $recentReceivings = DB::table('goods_receipts as gr')
            ->leftJoin('goods_receipt_items as gri', 'gri.goods_receipt_id', '=', 'gr.id')
            ->leftJoin('purchase_order_items as poi', 'poi.id', '=', 'gri.purchase_order_item_id')
            ->leftJoin('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('gr.receipt_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('gr.receipt_date', '<=', $dateTo))
            ->select('gr.id', 'gr.gr_number', 'gr.receipt_date')
            ->selectRaw("{$poListSql} as po_number")
            ->selectRaw("{$supplierListSql} as supplier_name")
            ->groupBy('gr.id', 'gr.gr_number', 'gr.receipt_date')
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
            ->whereRaw($activeItemSql)
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
            ->whereRaw($activePoSql)
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
            END as item_status_label")
            ->selectRaw("CASE
                WHEN poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 'Sudah diterima sebagian'
                WHEN poi.outstanding_qty <= 0 THEN 'Selesai'
                WHEN poi.etd_date IS NULL THEN 'Belum ada konfirmasi supplier'
                WHEN DATE(poi.etd_date) < {$currentDateSql} THEN 'Terlambat dari ETD'
                ELSE 'Sudah dikonfirmasi supplier'
            END as item_status_note")
            ->whereRaw($activePoSql)
            ->whereRaw($activeItemSql)
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

        $statusBreakdownRow = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->where('poi.item_status', '!=', 'Cancelled')
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NULL THEN 1 ELSE 0 END) as waiting_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) >= {$currentDateSql} THEN 1 ELSE 0 END) as confirmed_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) < {$currentDateSql} THEN 1 ELSE 0 END) as late_items")
            ->selectRaw("SUM(CASE WHEN poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 1 ELSE 0 END) as partial_items")
            ->selectRaw("SUM(CASE WHEN poi.outstanding_qty <= 0 THEN 1 ELSE 0 END) as closed_items")
            ->first();

        $statusBreakdown = [
            'Waiting' => (int) ($statusBreakdownRow->waiting_items ?? 0),
            'Confirmed' => (int) ($statusBreakdownRow->confirmed_items ?? 0),
            'Late' => (int) ($statusBreakdownRow->late_items ?? 0),
            'Partial' => (int) ($statusBreakdownRow->partial_items ?? 0),
            'Closed' => (int) ($statusBreakdownRow->closed_items ?? 0),
        ];

        $etdHealth = [
            'At-Risk' => (int) $atRiskItems->count(),
            'On-Time' => (int) $onTimeItems->count(),
        ];

        $supplierEtdHealth = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->where('poi.item_status', '!=', 'Cancelled')
            ->where('poi.outstanding_qty', '>', 0)
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->select('s.supplier_name')
            ->selectRaw("SUM(CASE WHEN poi.etd_date IS NOT NULL AND DATE(poi.etd_date) < {$currentDateSql} THEN 1 ELSE 0 END) as at_risk_items")
            ->selectRaw("SUM(CASE WHEN poi.etd_date IS NOT NULL AND DATE(poi.etd_date) >= {$currentDateSql} THEN 1 ELSE 0 END) as on_time_items")
            ->selectRaw("SUM(CASE WHEN poi.etd_date IS NULL THEN 1 ELSE 0 END) as waiting_etd_items")
            ->selectRaw('COUNT(DISTINCT po.id) as impacted_po')
            ->selectRaw('SUM(poi.outstanding_qty) as outstanding_qty')
            ->selectRaw('MIN(poi.etd_date) as nearest_etd')
            ->groupBy('s.supplier_name')
            ->get()
            ->map(function ($row) {
                $knownEtdItems = (int) $row->at_risk_items + (int) $row->on_time_items;
                $row->at_risk_percent = $knownEtdItems > 0
                    ? round(((int) $row->at_risk_items / $knownEtdItems) * 100, 1)
                    : 0;

                return $row;
            })
            ->sortBy([
                ['at_risk_items', 'desc'],
                ['at_risk_percent', 'desc'],
                ['outstanding_qty', 'desc'],
            ])
            ->values();

        $supplierRiskChart = [
            'labels' => $supplierDelay->pluck('supplier_name')->values()->all(),
            'late_items' => $supplierDelay->pluck('late_item_count')->map(fn ($value) => (int) $value)->values()->all(),
            'late_pos' => $supplierDelay->pluck('late_po_count')->map(fn ($value) => (int) $value)->values()->all(),
        ];

        $statusDetailItems = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->select(
                'po.id as po_id',
                'po.po_number',
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
            END as item_status_label")
            ->where('poi.item_status', '!=', 'Cancelled')
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->orderByRaw("CASE
                WHEN poi.outstanding_qty <= 0 THEN 5
                WHEN poi.received_qty > 0 THEN 3
                WHEN poi.etd_date IS NULL THEN 2
                WHEN DATE(poi.etd_date) < {$currentDateSql} THEN 1
                ELSE 4
            END")
            ->orderBy('po.po_number')
            ->get();

        $supplierFollowupDetails = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->select(
                's.supplier_name',
                'po.id as po_id',
                'po.po_number',
                'i.item_code',
                'i.item_name',
                'poi.outstanding_qty',
                'poi.etd_date'
            )
            ->where('poi.item_status', '!=', 'Cancelled')
            ->where('poi.outstanding_qty', '>', 0)
            ->whereNotNull('poi.etd_date')
            ->whereRaw("DATE(poi.etd_date) < {$currentDateSql}")
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->orderBy('s.supplier_name')
            ->orderBy('poi.etd_date')
            ->limit(60)
            ->get()
            ->groupBy('supplier_name');

        $etaDetailRows = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->select(
                'po.id as po_id',
                'po.po_number',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'poi.outstanding_qty',
                DB::raw('COALESCE(poi.eta_date, poi.etd_date) as promise_date')
            )
            ->where('poi.item_status', '!=', 'Cancelled')
            ->where('poi.outstanding_qty', '>', 0)
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->orderBy('promise_date')
            ->limit(40)
            ->get()
            ->groupBy('po_number');

        $receivingDetailRows = DB::table('goods_receipts as gr')
            ->leftJoin('goods_receipt_items as gri', 'gri.goods_receipt_id', '=', 'gr.id')
            ->leftJoin('purchase_order_items as poi', 'poi.id', '=', 'gri.purchase_order_item_id')
            ->leftJoin('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('shipments as sh', 'sh.id', '=', 'gr.shipment_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('gr.receipt_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('gr.receipt_date', '<=', $dateTo))
            ->select(
                'gr.id',
                'gr.gr_number',
                'gr.receipt_date',
                'sh.shipment_number',
                'sh.delivery_note_number'
            )
            ->selectRaw("{$poListSql} as po_number")
            ->selectRaw("{$supplierListSql} as supplier_name")
            ->groupBy('gr.id', 'gr.gr_number', 'gr.receipt_date', 'sh.shipment_number', 'sh.delivery_note_number')
            ->orderByDesc('gr.id')
            ->limit(20)
            ->get();

        $statusDetailGroups = $statusDetailItems->groupBy('item_status_label');
        $latePoRows = $openPoList->filter(fn ($row) => (int) ($row->late_items ?? 0) > 0)->values();

        $shipmentTodayRows = DB::table('shipments as sh')
            ->leftJoin('suppliers as s', 's.id', '=', 'sh.supplier_id')
            ->when($supplierId, fn ($query) => $query->where('sh.supplier_id', $supplierId))
            ->select(
                'sh.id',
                'sh.shipment_number',
                'sh.shipment_date',
                'sh.delivery_note_number',
                'sh.status',
                's.supplier_name'
            )
            ->whereRaw("DATE(sh.shipment_date) = {$currentDateSql}")
            ->orderByDesc('sh.id')
            ->limit(20)
            ->get();

        $receivingTodayRows = DB::table('goods_receipts as gr')
            ->leftJoin('goods_receipt_items as gri', 'gri.goods_receipt_id', '=', 'gr.id')
            ->leftJoin('purchase_order_items as poi', 'poi.id', '=', 'gri.purchase_order_item_id')
            ->leftJoin('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('shipments as sh', 'sh.id', '=', 'gr.shipment_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->select(
                'gr.id',
                'gr.gr_number',
                'gr.receipt_date',
                'sh.shipment_number'
            )
            ->selectRaw("{$poListSql} as po_number")
            ->selectRaw("{$supplierListSql} as supplier_name")
            ->whereRaw("DATE(gr.receipt_date) = {$currentDateSql}")
            ->groupBy('gr.id', 'gr.gr_number', 'gr.receipt_date', 'sh.shipment_number')
            ->orderByDesc('gr.id')
            ->limit(20)
            ->get();

        $actionCenter = [
            'items_need_etd_update' => DB::table('purchase_order_items as poi')
                ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->join('items as i', 'i.id', '=', 'poi.item_id')
                ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
                ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
                ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
                ->whereRaw($activePoSql)
                ->whereRaw($activeItemSql)
                ->where('poi.outstanding_qty', '>', 0)
                ->whereNull('poi.etd_date')
                ->select('po.id as po_id', 'po.po_number', 's.supplier_name', 'i.item_code', 'i.item_name', 'poi.outstanding_qty')
                ->orderByDesc('poi.outstanding_qty')
                ->orderBy('po.po_number')
                ->limit(6)
                ->get(),
            'incoming_this_week' => DB::table('purchase_order_items as poi')
                ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->join('items as i', 'i.id', '=', 'poi.item_id')
                ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
                ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
                ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
                ->whereNotIn('po.status', ['Closed', 'Cancelled'])
                ->where('poi.item_status', '!=', 'Cancelled')
                ->where('poi.outstanding_qty', '>', 0)
                ->whereNotNull('poi.etd_date')
                ->whereBetween('poi.etd_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                ->select('po.id as po_id', 'po.po_number', 's.supplier_name', 'i.item_code', 'i.item_name', 'poi.etd_date', 'poi.outstanding_qty')
                ->orderBy('poi.etd_date')
                ->limit(6)
                ->get(),
            'partial_receiving_queue' => DB::table('shipment_items as si')
                ->join('shipments as sh', 'sh.id', '=', 'si.shipment_id')
                ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
                ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
                ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
                ->join('items as i', 'i.id', '=', 'poi.item_id')
                ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
                ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
                ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
                ->whereIn('sh.status', ['Shipped', 'Partial Received'])
                ->whereRaw('(si.shipped_qty - si.received_qty) > 0')
                ->select(
                    'sh.id as shipment_id',
                    'sh.shipment_number',
                    'sh.delivery_note_number',
                    'po.po_number',
                    's.supplier_name',
                    'i.item_code',
                    'i.item_name'
                )
                ->selectRaw('(si.shipped_qty - si.received_qty) as shipment_outstanding_qty')
                ->orderByDesc('shipment_outstanding_qty')
                ->orderBy('sh.shipment_date')
                ->limit(6)
                ->get(),
        ];

        $chartStatusBreakdown = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->where('poi.item_status', '!=', 'Cancelled')
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->selectRaw("CASE
                WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NULL THEN 'Menunggu ETD'
                WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) >= {$currentDateSql} THEN 'Terkonfirmasi'
                WHEN poi.outstanding_qty > 0 AND poi.received_qty = 0 AND poi.etd_date IS NOT NULL AND DATE(poi.etd_date) < {$currentDateSql} THEN 'Terlambat'
                WHEN poi.received_qty > 0 AND poi.outstanding_qty > 0 THEN 'Parsial'
                WHEN poi.outstanding_qty <= 0 THEN 'Selesai'
                ELSE 'Lainnya'
            END as status_label")
            ->selectRaw('COUNT(poi.id) as item_count')
            ->groupBy('status_label')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->status_label => (int) $row->item_count])
            ->toArray();

        $chartSupplierDelay = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('items as i', 'i.id', '=', 'poi.item_id')
            ->leftJoin('item_categories as ic', 'ic.id', '=', 'i.category_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->where('poi.outstanding_qty', '>', 0)
            ->whereNotNull('poi.etd_date')
            ->whereRaw("DATE(poi.etd_date) < {$currentDateSql}")
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->where('poi.item_status', '!=', 'Cancelled')
            ->select('s.supplier_name')
            ->selectRaw('COUNT(poi.id) as late_item_count')
            ->selectRaw('SUM(poi.outstanding_qty) as outstanding_qty')
            ->selectRaw('ic.category_name as category_name')
            ->groupBy('s.supplier_name', 'ic.category_name')
            ->orderByDesc('late_item_count')
            ->limit(8)
            ->get();

        $chartMonthlyTrend = DB::table('purchase_orders as po')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->when(config('database.default') === 'sqlite', function ($query) {
                $query->selectRaw("strftime('%Y-%m', po.po_date) as month_key");
            }, function ($query) {
                $query->selectRaw("DATE_FORMAT(po.po_date, '%Y-%m') as month_key");
            })
            ->selectRaw("COUNT(DISTINCT po.id) as po_count")
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->limit(6)
            ->get()
            ->mapWithKeys(fn ($row) => [$row->month_key => (int) $row->po_count])
            ->toArray();

        $chartPoStatusDist = DB::table('purchase_orders as po')
            ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->whereNotIn('po.status', ['Closed', 'Cancelled'])
            ->select('status')
            ->selectRaw('COUNT(*) as po_count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->status => (int) $row->po_count])
            ->toArray();

        $chartShipmentStatusDist = DB::table('shipments as sh')
            ->when($supplierId, fn ($query) => $query->where('sh.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('sh.shipment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('sh.shipment_date', '<=', $dateTo))
            ->whereNotIn('sh.status', [DocumentTermCodes::SHIPMENT_CANCELLED])
            ->select('status')
            ->selectRaw('COUNT(*) as shipment_count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->status => (int) $row->shipment_count])
            ->toArray();

        $chartShipmentMonthlyTrend = DB::table('shipments as sh')
            ->when($supplierId, fn ($query) => $query->where('sh.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('sh.shipment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('sh.shipment_date', '<=', $dateTo))
            ->whereNotIn('sh.status', [DocumentTermCodes::SHIPMENT_CANCELLED])
            ->when(config('database.default') === 'sqlite', function ($query) {
                $query->selectRaw("strftime('%Y-%m', sh.shipment_date) as month_key");
            }, function ($query) {
                $query->selectRaw("DATE_FORMAT(sh.shipment_date, '%Y-%m') as month_key");
            })
            ->selectRaw('COUNT(DISTINCT sh.id) as shipment_count')
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->limit(6)
            ->get()
            ->mapWithKeys(fn ($row) => [$row->month_key => (int) $row->shipment_count])
            ->toArray();

        $receivingMonthlyTrend = DB::table('goods_receipts as gr')
            ->join('purchase_orders as po', 'po.id', '=', 'gr.purchase_order_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('gr.receipt_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('gr.receipt_date', '<=', $dateTo))
            ->when(config('database.default') === 'sqlite', function ($query) {
                $query->selectRaw("strftime('%Y-%m', gr.receipt_date) as month_key");
            }, function ($query) {
                $query->selectRaw("DATE_FORMAT(gr.receipt_date, '%Y-%m') as month_key");
            })
            ->selectRaw('COUNT(DISTINCT gr.id) as receipt_count')
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->limit(6)
            ->get()
            ->mapWithKeys(fn ($row) => [$row->month_key => (int) $row->receipt_count])
            ->toArray();

        return view('dashboard', compact(
            'metrics',
            'suppliers',
            'supplierId',
            'dateFrom',
            'dateTo',
            'statusBreakdown',
            'etdHealth',
            'supplierEtdHealth',
            'supplierRiskChart',
            'statusDetailItems',
            'statusDetailGroups',
            'supplierFollowupDetails',
            'etaDetailRows',
            'receivingDetailRows',
            'latePoRows',
            'shipmentTodayRows',
            'receivingTodayRows',
            'savedViews',
            'activeSavedView',
            'actionCenter',
            'supplierDelay',
            'poMonitoringSummary',
            'openPoList',
            'recentReceivings',
            'atRiskItems',
            'onTimeItems',
            'itemMonitoringList',
            'chartStatusBreakdown',
            'chartSupplierDelay',
            'chartMonthlyTrend',
            'chartPoStatusDist',
            'chartShipmentStatusDist',
            'chartShipmentMonthlyTrend',
            'receivingMonthlyTrend'
        ));
    }

    public function monitoring(Request $request): View
    {
        $supplierId = $this->resolveSupplierId($request);
        ['date_from' => $dateFrom, 'date_to' => $dateTo] = $this->resolveDateRange($request);
        $monitoringMode = in_array((string) $request->query('mode', 'po'), ['po', 'item'], true)
            ? (string) $request->query('mode', 'po')
            : 'po';

        $suppliers = DB::table('suppliers')
            ->orderBy('supplier_name')
            ->get(['id', 'supplier_name', 'supplier_code']);

        $summaryMetrics = $this->dashboardQuery->summaryMetrics($supplierId, $dateFrom, $dateTo);

        $outstandingPoRows = $this->dashboardQuery->baseMonitoringPoQuery($supplierId, $dateFrom, $dateTo)
            ->select(
                'po.id as po_id',
                'po.po_number',
                'po.po_date',
                'po.status as po_status',
                's.supplier_name',
                DB::raw('COALESCE(po.eta_date, MIN(COALESCE(poi.eta_date, poi.etd_date))) as eta_date')
            )
            ->selectRaw('SUM(CASE WHEN ' . StatusQuery::sqlNotEquals('poi.item_status', DomainStatus::GROUP_PO_ITEM_STATUS, DocumentTermCodes::ITEM_CANCELLED) . ' AND poi.outstanding_qty > 0 THEN 1 ELSE 0 END) as outstanding_item_count')
            ->selectRaw('COALESCE(SUM(poi.ordered_qty), 0) as total_order_qty')
            ->selectRaw('COALESCE(SUM(poi.received_qty), 0) as total_shipped_qty')
            ->selectRaw('COALESCE(SUM(poi.outstanding_qty), 0) as total_outstanding_qty')
            ->groupBy('po.id', 'po.po_number', 'po.po_date', 'po.status', 's.supplier_name', 'po.eta_date')
            ->orderByDesc('total_outstanding_qty')
            ->orderBy('po.po_number')
            ->get();

        $outstandingItemRows = $this->dashboardQuery->baseMonitoringItemQuery($supplierId, $dateFrom, $dateTo)
            ->orderByDesc('poi.outstanding_qty')
            ->orderBy('po.po_number')
            ->orderBy('i.item_code')
            ->get();

        return view('monitoring', compact(
            'suppliers',
            'supplierId',
            'dateFrom',
            'dateTo',
            'monitoringMode',
            'summaryMetrics',
            'outstandingPoRows',
            'outstandingItemRows'
        ));
    }

    public function exportMonitoringExcel(Request $request): Response
    {
        $supplierId = $request->integer('supplier_id');
        ['date_from' => $dateFrom, 'date_to' => $dateTo] = $this->resolveDateRange($request);

        $summaryMetrics = $this->dashboardQuery->summaryMetrics($supplierId, $dateFrom, $dateTo);

        $outstandingPoRows = $this->dashboardQuery->baseMonitoringPoQuery($supplierId, $dateFrom, $dateTo)
            ->select(
                'po.id as po_id',
                'po.po_number',
                'po.po_date',
                'po.status as po_status',
                's.supplier_name',
                DB::raw('COALESCE(po.eta_date, MIN(COALESCE(poi.eta_date, poi.etd_date))) as eta_date')
            )
            ->selectRaw('SUM(CASE WHEN poi.item_status != \'Cancelled\' AND poi.outstanding_qty > 0 THEN 1 ELSE 0 END) as outstanding_item_count')
            ->selectRaw('COALESCE(SUM(poi.ordered_qty), 0) as total_order_qty')
            ->selectRaw('COALESCE(SUM(poi.received_qty), 0) as total_shipped_qty')
            ->selectRaw('COALESCE(SUM(poi.outstanding_qty), 0) as total_outstanding_qty')
            ->groupBy('po.id', 'po.po_number', 'po.po_date', 'po.status', 's.supplier_name', 'po.eta_date')
            ->orderByDesc('total_outstanding_qty')
            ->orderBy('po.po_number')
            ->get();

        $outstandingItemRows = $this->dashboardQuery->baseMonitoringItemQuery($supplierId, $dateFrom, $dateTo)
            ->orderByDesc('poi.outstanding_qty')
            ->orderBy('po.po_number')
            ->orderBy('i.item_code')
            ->get();

        $content = view('monitoring-export', [
            'summaryMetrics' => $summaryMetrics,
            'outstandingPoRows' => $outstandingPoRows,
            'outstandingItemRows' => $outstandingItemRows,
            'generatedAt' => now(),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="monitoring-po-' . now()->format('Ymd-His') . '.xls"',
        ]);
    }

    public function summaryPo(Request $request): View
    {
        $supplierId = $request->integer('supplier_id');
        ['date_from' => $dateFrom, 'date_to' => $dateTo] = $this->resolveDateRange($request);

        $suppliers = DB::table('suppliers')
            ->orderBy('supplier_name')
            ->get(['id', 'supplier_name']);

        $summaryMetrics = $this->dashboardQuery->summaryMetrics($supplierId, $dateFrom, $dateTo);

        $outstandingPoRows = $this->dashboardQuery->baseOutstandingQuery($supplierId, $dateFrom, $dateTo)
            ->select(
                'po.id as po_id',
                'po.po_number',
                'po.po_date',
                'po.status as po_status',
                's.supplier_name',
                DB::raw('COALESCE(po.eta_date, MIN(COALESCE(poi.eta_date, poi.etd_date))) as eta_date')
            )
            ->selectRaw('COUNT(poi.id) as outstanding_item_count')
            ->selectRaw('SUM(poi.ordered_qty) as total_order_qty')
            ->selectRaw('SUM(poi.received_qty) as total_shipped_qty')
            ->selectRaw('SUM(poi.outstanding_qty) as total_outstanding_qty')
            ->groupBy('po.id', 'po.po_number', 'po.po_date', 'po.status', 's.supplier_name', 'po.eta_date')
            ->orderByDesc('total_outstanding_qty')
            ->orderBy('po.po_number')
            ->get();

        return view('summary-po', compact(
            'suppliers',
            'supplierId',
            'dateFrom',
            'dateTo',
            'summaryMetrics',
            'outstandingPoRows'
        ));
    }

    public function exportSummaryPoExcel(Request $request): Response
    {
        $supplierId = $request->integer('supplier_id');
        ['date_from' => $dateFrom, 'date_to' => $dateTo] = $this->resolveDateRange($request);

        $summaryMetrics = $this->dashboardQuery->summaryMetrics($supplierId, $dateFrom, $dateTo);

        $outstandingPoRows = $this->dashboardQuery->baseOutstandingQuery($supplierId, $dateFrom, $dateTo)
            ->select(
                'po.id as po_id',
                'po.po_number',
                'po.po_date',
                'po.status as po_status',
                's.supplier_name',
                DB::raw('COALESCE(po.eta_date, MIN(COALESCE(poi.eta_date, poi.etd_date))) as eta_date')
            )
            ->selectRaw('COUNT(poi.id) as outstanding_item_count')
            ->selectRaw('SUM(poi.ordered_qty) as total_order_qty')
            ->selectRaw('SUM(poi.received_qty) as total_shipped_qty')
            ->selectRaw('SUM(poi.outstanding_qty) as total_outstanding_qty')
            ->groupBy('po.id', 'po.po_number', 'po.po_date', 'po.status', 's.supplier_name', 'po.eta_date')
            ->orderByDesc('total_outstanding_qty')
            ->orderBy('po.po_number')
            ->get();

        $content = view('summary-po-export', [
            'summaryMetrics' => $summaryMetrics,
            'outstandingPoRows' => $outstandingPoRows,
            'generatedAt' => now(),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="summary-po-' . now()->format('Ymd-His') . '.xls"',
        ]);
    }

    public function summaryItem(Request $request): View
    {
        $supplierId = $request->integer('supplier_id');
        ['date_from' => $dateFrom, 'date_to' => $dateTo] = $this->resolveDateRange($request);

        $suppliers = DB::table('suppliers')
            ->orderBy('supplier_name')
            ->get(['id', 'supplier_name']);

        $summaryMetrics = $this->dashboardQuery->summaryMetrics($supplierId, $dateFrom, $dateTo);

        $outstandingItemRows = $this->dashboardQuery->baseOutstandingQuery($supplierId, $dateFrom, $dateTo)
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->select(
                'po.id as po_id',
                'po.po_number',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'poi.ordered_qty',
                'poi.received_qty',
                'poi.outstanding_qty',
                'poi.etd_date'
            )
            ->orderByDesc('poi.outstanding_qty')
            ->orderBy('po.po_number')
            ->orderBy('i.item_code')
            ->paginate(50)
            ->withQueryString();

        return view('summary-item', compact(
            'suppliers',
            'supplierId',
            'dateFrom',
            'dateTo',
            'summaryMetrics',
            'outstandingItemRows'
        ));
    }

    public function exportSummaryItemExcel(Request $request): Response
    {
        $supplierId = $request->integer('supplier_id');
        ['date_from' => $dateFrom, 'date_to' => $dateTo] = $this->resolveDateRange($request);

        $summaryMetrics = $this->dashboardQuery->summaryMetrics($supplierId, $dateFrom, $dateTo);

        $outstandingItemRows = $this->dashboardQuery->baseOutstandingQuery($supplierId, $dateFrom, $dateTo)
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->select(
                'po.id as po_id',
                'po.po_number',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'poi.ordered_qty',
                'poi.received_qty',
                'poi.outstanding_qty',
                'poi.etd_date'
            )
            ->orderByDesc('poi.outstanding_qty')
            ->orderBy('po.po_number')
            ->orderBy('i.item_code')
            ->get();

        $content = view('summary-item-export', [
            'summaryMetrics' => $summaryMetrics,
            'outstandingItemRows' => $outstandingItemRows,
            'generatedAt' => now(),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="summary-item-' . now()->format('Ymd-His') . '.xls"',
        ]);
    }

    public function tracking(Request $request): View
    {
        $supplierId = $this->resolveSupplierId($request);
        ['date_from' => $dateFrom, 'date_to' => $dateTo] = $this->resolveDateRange($request);
        $currentDateSql = $this->currentDateExpression();

        $suppliers = DB::table('suppliers')
            ->orderBy('supplier_name')
            ->get(['id', 'supplier_name', 'supplier_code']);

        $filterSupplierId = $supplierId;
        $filterDateFrom = $dateFrom;
        $filterDateTo = $dateTo;
        $filterPoStatus = $request->query('po_status', 'all');
        $filterItemStatus = $request->query('item_status', 'all');
        $filterCategory = $request->query('category_id', 'all');

        $currentDateSql = ErpFlow::currentDateExpression();

        $activePoSql = StatusQuery::sqlNotEquals('po.status', DomainStatus::GROUP_PO_STATUS, DocumentTermCodes::PO_CLOSED)
            . ' AND '
            . StatusQuery::sqlNotEquals('po.status', DomainStatus::GROUP_PO_STATUS, DocumentTermCodes::PO_CANCELLED);
        $activeItemSql = StatusQuery::sqlNotEquals('poi.item_status', DomainStatus::GROUP_PO_ITEM_STATUS, DocumentTermCodes::ITEM_CANCELLED);

        $categories = DB::table('item_categories')
            ->where('is_active', true)
            ->orderBy('category_name')
            ->get(['id', 'category_name']);

                $itemRows = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->leftJoin('item_categories as ic', 'ic.id', '=', 'i.category_id')
            ->leftJoin('shipment_items as si', 'si.purchase_order_item_id', '=', 'poi.id')
            ->leftJoin('shipments as sh', 'sh.id', '=', 'si.shipment_id')
            ->when($filterSupplierId, fn ($q) => $q->where('po.supplier_id', $filterSupplierId))
            ->when($filterDateFrom, fn ($q) => $q->whereDate('po.po_date', '>=', $filterDateFrom))
            ->when($filterDateTo, fn ($q) => $q->whereDate('po.po_date', '<=', $filterDateTo))
            ->when($filterPoStatus !== 'all', fn ($q) => $q->where('po.status', $filterPoStatus))
            ->when($filterItemStatus !== 'all', fn ($q) => $q->where('poi.item_status', $filterItemStatus))
            ->when($filterCategory !== 'all', fn ($q) => $q->where('i.category_id', $filterCategory))
            ->whereRaw($activePoSql)
            ->whereRaw($activeItemSql)
            ->select(
                'po.id as po_id',
                'po.po_number',
                'po.po_date',
                'po.status as po_status',
                's.supplier_name',
                's.supplier_code',
                'i.item_code',
                'i.item_name',
                'ic.category_name',
                'ic.id as category_id',
                'poi.id as item_id',
                'poi.ordered_qty',
                'poi.received_qty',
                'poi.outstanding_qty',
                'poi.item_status',
                'poi.etd_date',
                'si.shipped_qty as item_shipped_qty',
                'si.received_qty as item_received_qty',
                'sh.shipment_number',
                'sh.shipment_date',
                'sh.delivery_note_number',
            )
            ->orderBy('po.po_number')
            ->orderBy('i.item_code')
            ->get()
            ->map(fn ($row) => [
                'po_id' => $row->po_id,
                'po_number' => $row->po_number,
                'po_date' => $row->po_date ? Carbon::parse($row->po_date)->format('d-m-Y') : null,
                'po_date_raw' => $row->po_date,
                'po_status' => $row->po_status,
                'item_id' => $row->item_id,
                'item_code' => $row->item_code,
                'item_name' => $row->item_name,
                'item_category_id' => $row->category_id,
                'item_category_name' => $row->category_name ?? 'Tanpa Kategori',
                'item_status' => $row->item_status,
                'etd_date' => $row->etd_date,
                'supplier_name' => $row->supplier_name,
                'supplier_code' => $row->supplier_code ?? '-',
                'ordered_qty' => (float) ($row->ordered_qty ?? 0),
                'shipped_qty' => (float) ($row->item_shipped_qty ?? 0),
                'received_qty' => (float) ($row->item_received_qty ?? 0),
                'outstanding_qty' => (float) ($row->outstanding_qty ?? 0),
                'stage' => $row->po_status,
                'ref' => $row->po_number,
                'ref_type' => 'po.show',
                'ref_param' => $row->po_number,
                'shipment_number' => $row->shipment_number ?? null,
                'shipment_date' => $row->shipment_date ? Carbon::parse($row->shipment_date)->format('d-m-Y') : null,
                'delivery_note_number' => $row->delivery_note_number ?? null,
            ])
            ->groupBy('item_id');

        $itemRows = $itemRows->map(function ($group) use ($currentDateSql) {
            $first = (object) $group->first();
            $shipments = [];
            foreach ($group as $row) {
                $row = (object) $row;
                if ($row->shipment_number) {
                    $shipments[] = [
                        'shipment_number' => $row->shipment_number,
                        'shipment_date' => $row->shipment_date ? Carbon::parse($row->shipment_date)->format('d-m-Y') : null,
                        'delivery_note_number' => $row->delivery_note_number ?? null,
                        'shipped_qty' => (float) ($row->item_shipped_qty ?? 0),
                        'received_qty' => (float) ($row->item_received_qty ?? 0),
                        'remaining_qty' => ($row->item_shipped_qty ?? 0) - ($row->item_received_qty ?? 0),
                    ];
                }
            }

            // Compute monitoring_status matching PO page logic
            $monitoringStatus = match (true) {
                $first->item_status === DocumentTermCodes::ITEM_CANCELLED => DocumentTermCodes::ITEM_CANCELLED,
                $first->item_status === DocumentTermCodes::ITEM_FORCE_CLOSED => DocumentTermCodes::ITEM_FORCE_CLOSED,
                (float) $first->outstanding_qty <= 0 => DocumentTermCodes::ITEM_CLOSED,
                (float) $first->received_qty > 0 => DocumentTermCodes::ITEM_PARTIAL,
                empty($first->etd_date) => DocumentTermCodes::ITEM_WAITING,
                Carbon::parse($first->etd_date)->format('Y-m-d') < Carbon::today()->format('Y-m-d') => DocumentTermCodes::ITEM_LATE,
                default => DocumentTermCodes::ITEM_CONFIRMED,
            };

            $orderedQty = (float) ($first->ordered_qty ?? 0);
            $receivedQty = (float) ($first->item_received_qty ?? 0);
            $outstandingQty = (float) ($first->outstanding_qty ?? 0);
            $shippedQty = (float) ($first->item_shipped_qty ?? 0);

            $shipmentProgress = match (true) {
                $outstandingQty <= 0 => 'Fully Shipped',
                $shippedQty > 0 || $receivedQty > 0 => 'Partially Shipped',
                default => 'Not Shipped',
            };

            $progressClass = match ($shipmentProgress) {
                'Fully Shipped' => 'progress-fully',
                'Partially Shipped' => 'progress-partial',
                'Not Shipped' => 'progress-none',
                default => 'progress-none',
            };

            $progressPercent = $orderedQty > 0
                ? min(100, (int) round(($receivedQty / $orderedQty) * 100))
                : 0;

            return [
                'po_id' => $first->po_id,
                'po_number' => $first->po_number,
                'po_date' => $first->po_date,
                'po_status' => $first->po_status,
                'item_id' => $first->item_id,
                'item_code' => $first->item_code,
                'item_name' => $first->item_name,
                'item_category_id' => $first->item_category_id ?? null,
                'item_category_name' => $first->item_category_name ?? 'Tanpa Kategori',
                'item_status' => $first->item_status,
                'monitoring_status' => $monitoringStatus,
                'supplier_name' => $first->supplier_name,
                'supplier_code' => $first->supplier_code ?? '-',
                'ordered_qty' => $orderedQty,
                'shipped_qty' => $shippedQty,
                'received_qty' => $receivedQty,
                'outstanding_qty' => $outstandingQty,
                'shipment_progress' => $shipmentProgress,
                'progress_class' => $progressClass,
                'progress_percent' => $progressPercent,
                'stage' => $first->po_status,
                'stage_class' => match ($first->po_status) {
                    'PO Issued' => 'stage-confirmed',
                    'Open' => 'stage-waiting',
                    'Late' => 'stage-late',
                    'Closed' => 'stage-closed',
                    'Cancelled' => 'stage-cancelled',
                    'Full' => 'stage-closed',
                    'Partial' => 'stage-partial',
                    'Delayed' => 'stage-late',
                    default => 'stage-waiting',
                },
                'ref' => $first->po_number,
                'ref_type' => 'po.show',
                'ref_param' => $first->po_number,
                'shipments' => $shipments,
            ];
        });

        return view('tracking', compact(
            'suppliers',
            'categories',
            'filterSupplierId',
            'filterDateFrom',
            'filterDateTo',
            'filterPoStatus',
            'filterItemStatus',
            'filterCategory',
            'itemRows'
        ));
    }

private function resolveDateRange(Request $request): array
    {
        $today = Carbon::today();
        $savedView = (string) $request->query('saved_view', 'default');

        if ($savedView === 'at-risk-today') {
            return [
                'date_from' => $request->date('date_from')?->format('Y-m-d') ?? $today->copy()->subDays(14)->format('Y-m-d'),
                'date_to' => $request->date('date_to')?->format('Y-m-d') ?? $today->format('Y-m-d'),
            ];
        }

        if ($savedView === 'incoming-this-week') {
            return [
                'date_from' => $request->date('date_from')?->format('Y-m-d') ?? $today->format('Y-m-d'),
                'date_to' => $request->date('date_to')?->format('Y-m-d') ?? $today->copy()->addDays(7)->format('Y-m-d'),
            ];
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            return [
                'date_from' => $request->date('date_from')?->format('Y-m-d'),
                'date_to' => $request->date('date_to')?->format('Y-m-d'),
            ];
        }

        return [
            'date_from' => null,
            'date_to' => null,
        ];
    }

    private function savedViews(): array
    {
        return [
            [
                'key' => 'default',
                'label' => 'Default',
                'description' => 'Ringkasan umum outstanding dan risiko operasional.',
            ],
            [
                'key' => 'at-risk-today',
                'label' => 'At-Risk Hari Ini',
                'description' => 'Fokus pada item yang terlambat dan perlu follow up cepat.',
            ],
            [
                'key' => 'incoming-this-week',
                'label' => 'Incoming Minggu Ini',
                'description' => 'Lihat item dengan ETD paling dekat dalam tujuh hari ke depan.',
            ],
        ];
    }

    private function currentDateExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "date('now')"
            : 'CURDATE()';
    }

    private function resolveSupplierId(Request $request): ?int
    {
        $supplierCode = trim((string) $request->query('supplier_code', ''));
        if ($supplierCode !== '') {
            $supplierId = DB::table('suppliers')
                ->when(
                    is_numeric($supplierCode),
                    fn ($query) => $query->where('supplier_code', $supplierCode)->orWhere('id', (int) $supplierCode),
                    fn ($query) => $query->where('supplier_code', $supplierCode)
                )
                ->value('id');

            return $supplierId !== null ? (int) $supplierId : null;
        }

        return $request->filled('supplier_id') ? $request->integer('supplier_id') : null;
    }

    private function dateDiffExpression(string $endDateColumn, string $startDateColumn): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(julianday({$endDateColumn}) - julianday({$startDateColumn}) AS INTEGER)"
            : "DATEDIFF({$endDateColumn}, {$startDateColumn})";
    }

    private function groupConcatDistinct(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "GROUP_CONCAT(DISTINCT {$column})"
            : "GROUP_CONCAT(DISTINCT {$column} ORDER BY {$column} SEPARATOR ', ')";
    }
}
