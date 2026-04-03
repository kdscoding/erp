<?php

namespace App\Http\Controllers;

use App\Support\DocumentTermCodes;
use App\Support\DomainStatus;
use App\Support\StatusQuery;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
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
            'itemMonitoringList'
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

        $summaryMetrics = $this->summaryMetrics($supplierId, $dateFrom, $dateTo);

        $outstandingPoRows = $this->baseMonitoringPoQuery($supplierId, $dateFrom, $dateTo)
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

        $outstandingItemRows = $this->baseMonitoringItemQuery($supplierId, $dateFrom, $dateTo)
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

    public function supplierPerformance(Request $request): View
    {
        $supplierId = $this->resolveSupplierId($request);
        ['date_from' => $dateFrom, 'date_to' => $dateTo] = $this->resolveDateRange($request);
        $currentDateSql = $this->currentDateExpression();
        $shipmentToReceivingDaysSql = $this->dateDiffExpression('gr.receipt_date', 'sh.shipment_date');

        $suppliers = DB::table('suppliers')
            ->orderBy('supplier_name')
            ->get(['id', 'supplier_name']);

        $receiptSummary = DB::table('goods_receipt_items as gri')
            ->join('goods_receipts as gr', 'gr.id', '=', 'gri.goods_receipt_id')
            ->select('gri.purchase_order_item_id')
            ->selectRaw('MAX(gr.receipt_date) as last_receipt_date')
            ->selectRaw('MIN(gr.receipt_date) as first_receipt_date')
            ->selectRaw('COUNT(DISTINCT gr.id) as gr_count')
            ->groupBy('gri.purchase_order_item_id');

        $shipmentLeadSummary = DB::table('shipment_items as si')
            ->join('shipments as sh', 'sh.id', '=', 'si.shipment_id')
            ->leftJoin('goods_receipt_items as gri', 'gri.shipment_item_id', '=', 'si.id')
            ->leftJoin('goods_receipts as gr', 'gr.id', '=', 'gri.goods_receipt_id')
            ->join('purchase_order_items as poi', 'poi.id', '=', 'si.purchase_order_item_id')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->whereNotNull('gr.receipt_date')
            ->select('po.supplier_id')
            ->selectRaw("AVG({$shipmentToReceivingDaysSql}) as avg_shipment_to_receiving_days")
            ->selectRaw('COUNT(DISTINCT si.id) as shipment_line_with_receipt')
            ->groupBy('po.supplier_id');

        $supplierScorecard = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoinSub($receiptSummary, 'receipt_summary', function ($join) {
                $join->on('receipt_summary.purchase_order_item_id', '=', 'poi.id');
            })
            ->leftJoinSub($shipmentLeadSummary, 'shipment_lead_summary', function ($join) {
                $join->on('shipment_lead_summary.supplier_id', '=', 'po.supplier_id');
            })
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->when(true, fn ($query) => StatusQuery::whereNotEquals(
                $query,
                'poi.item_status',
                DomainStatus::GROUP_PO_ITEM_STATUS,
                DocumentTermCodes::ITEM_CANCELLED
            ))
            ->select('po.supplier_id', 's.supplier_name')
            ->selectRaw('COUNT(DISTINCT po.id) as total_po')
            ->selectRaw('COUNT(poi.id) as total_items')
            ->selectRaw('SUM(CASE WHEN poi.received_qty > 0 THEN 1 ELSE 0 END) as received_items')
            ->selectRaw('SUM(CASE WHEN poi.outstanding_qty <= 0 THEN 1 ELSE 0 END) as closed_items')
            ->selectRaw("SUM(CASE
                WHEN poi.outstanding_qty <= 0
                    AND receipt_summary.last_receipt_date IS NOT NULL
                    AND DATE(receipt_summary.last_receipt_date) <= COALESCE(DATE(poi.eta_date), DATE(poi.etd_date), DATE(receipt_summary.last_receipt_date))
                THEN 1 ELSE 0 END) as on_time_full_items")
            ->selectRaw("SUM(CASE
                WHEN poi.outstanding_qty > 0
                    AND poi.etd_date IS NOT NULL
                    AND DATE(poi.etd_date) < {$currentDateSql}
                THEN 1 ELSE 0 END) as delayed_open_items")
            ->selectRaw('MIN(CASE WHEN poi.outstanding_qty > 0 THEN poi.etd_date ELSE NULL END) as nearest_open_etd')
            ->selectRaw('COALESCE(MAX(shipment_lead_summary.avg_shipment_to_receiving_days), 0) as avg_shipment_to_receiving_days')
            ->groupBy('po.supplier_id', 's.supplier_name')
            ->orderBy('s.supplier_name')
            ->get()
            ->map(function ($row) {
                $denominator = max((int) $row->received_items, 1);
                $row->otif_percent = (int) $row->received_items > 0
                    ? round(((int) $row->on_time_full_items / $denominator) * 100, 1)
                    : 0.0;
                $row->delay_rate = (int) $row->total_items > 0
                    ? round(((int) $row->delayed_open_items / (int) $row->total_items) * 100, 1)
                    : 0.0;

                return $row;
            })
            ->sortByDesc('otif_percent')
            ->values();

        $performanceMetrics = [
            'supplier_count' => (int) $supplierScorecard->count(),
            'received_items' => (int) $supplierScorecard->sum('received_items'),
            'on_time_full_items' => (int) $supplierScorecard->sum('on_time_full_items'),
            'overall_otif_percent' => (int) $supplierScorecard->sum('received_items') > 0
                ? round(((int) $supplierScorecard->sum('on_time_full_items') / (int) $supplierScorecard->sum('received_items')) * 100, 1)
                : 0.0,
            'avg_shipment_to_receiving_days' => $supplierScorecard->count() > 0
                ? round((float) $supplierScorecard->avg('avg_shipment_to_receiving_days'), 1)
                : 0.0,
        ];

        $topDelayedSuppliers = $supplierScorecard
            ->sortByDesc('delayed_open_items')
            ->values()
            ->take(5);

        $bestOtiFSuppliers = $supplierScorecard
            ->filter(fn ($row) => (int) $row->received_items > 0)
            ->sortByDesc('otif_percent')
            ->values()
            ->take(5);

        return view('supplier-performance', compact(
            'suppliers',
            'supplierId',
            'dateFrom',
            'dateTo',
            'performanceMetrics',
            'supplierScorecard',
            'topDelayedSuppliers',
            'bestOtiFSuppliers'
        ));
    }

    public function exportMonitoringExcel(Request $request): Response
    {
        $supplierId = $request->integer('supplier_id');
        ['date_from' => $dateFrom, 'date_to' => $dateTo] = $this->resolveDateRange($request);

        $summaryMetrics = $this->summaryMetrics($supplierId, $dateFrom, $dateTo);

        $outstandingPoRows = $this->baseMonitoringPoQuery($supplierId, $dateFrom, $dateTo)
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

        $outstandingItemRows = $this->baseMonitoringItemQuery($supplierId, $dateFrom, $dateTo)
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

        $summaryMetrics = $this->summaryMetrics($supplierId, $dateFrom, $dateTo);

        $outstandingPoRows = $this->baseOutstandingQuery($supplierId, $dateFrom, $dateTo)
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

        $summaryMetrics = $this->summaryMetrics($supplierId, $dateFrom, $dateTo);

        $outstandingPoRows = $this->baseOutstandingQuery($supplierId, $dateFrom, $dateTo)
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

        $summaryMetrics = $this->summaryMetrics($supplierId, $dateFrom, $dateTo);

        $outstandingItemRows = $this->baseOutstandingQuery($supplierId, $dateFrom, $dateTo)
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

        $summaryMetrics = $this->summaryMetrics($supplierId, $dateFrom, $dateTo);

        $outstandingItemRows = $this->baseOutstandingQuery($supplierId, $dateFrom, $dateTo)
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

    private function summaryMetrics(?int $supplierId, ?string $dateFrom, ?string $dateTo): array
    {
        $baseQuery = $this->baseOutstandingQuery($supplierId, $dateFrom, $dateTo);

        return (array) $baseQuery
            ->selectRaw('COUNT(DISTINCT po.id) as outstanding_po')
            ->selectRaw('COUNT(poi.id) as outstanding_item')
            ->selectRaw('COALESCE(SUM(poi.ordered_qty), 0) as total_order_qty')
            ->selectRaw('COALESCE(SUM(poi.received_qty), 0) as total_shipped_qty')
            ->selectRaw('COALESCE(SUM(poi.outstanding_qty), 0) as total_outstanding_qty')
            ->first();
    }

    private function baseOutstandingQuery(?int $supplierId, ?string $dateFrom, ?string $dateTo)
    {
        return DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->when(true, fn ($query) => StatusQuery::whereNotIn(
                $query,
                'po.status',
                DomainStatus::GROUP_PO_STATUS,
                [DocumentTermCodes::PO_CLOSED, DocumentTermCodes::PO_CANCELLED]
            ))
            ->when(true, fn ($query) => StatusQuery::whereNotEquals(
                $query,
                'poi.item_status',
                DomainStatus::GROUP_PO_ITEM_STATUS,
                DocumentTermCodes::ITEM_CANCELLED
            ))
            ->where('poi.outstanding_qty', '>', 0);
    }

    private function baseMonitoringPoQuery(?int $supplierId, ?string $dateFrom, ?string $dateTo)
    {
        return DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_order_items as poi', 'poi.purchase_order_id', '=', 'po.id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo));
    }

    private function baseMonitoringItemQuery(?int $supplierId, ?string $dateFrom, ?string $dateTo)
    {
        return DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->when(true, fn ($query) => StatusQuery::whereNotEquals(
                $query,
                'poi.item_status',
                DomainStatus::GROUP_PO_ITEM_STATUS,
                DocumentTermCodes::ITEM_CANCELLED
            ))
            ->when(true, fn ($query) => StatusQuery::whereNotIn(
                $query,
                'po.status',
                DomainStatus::GROUP_PO_STATUS,
                [DocumentTermCodes::PO_CLOSED, DocumentTermCodes::PO_CANCELLED]
            ))
            ->select(
                'po.id as po_id',
                'po.po_number',
                'po.status as po_status',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'poi.ordered_qty',
                'poi.received_qty',
                'poi.outstanding_qty',
                'poi.etd_date',
                'poi.item_status'
            );
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

        return [
            'date_from' => $request->date('date_from')?->format('Y-m-d') ?? $today->copy()->subMonth()->format('Y-m-d'),
            'date_to' => $request->date('date_to')?->format('Y-m-d') ?? $today->format('Y-m-d'),
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
