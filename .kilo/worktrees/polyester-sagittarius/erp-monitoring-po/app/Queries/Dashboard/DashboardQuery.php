<?php

namespace App\Queries\Dashboard;

use App\Support\DocumentTermCodes;
use App\Support\DomainStatus;
use App\Support\StatusQuery;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardQuery
{
    public function summaryMetrics(?int $supplierId, ?string $dateFrom, ?string $dateTo): array
    {
        $base = $this->baseOutstandingQuery($supplierId, $dateFrom, $dateTo);

        return (array) $base
            ->selectRaw('COUNT(DISTINCT po.id) as outstanding_po')
            ->selectRaw('COUNT(poi.id) as outstanding_item')
            ->selectRaw('COALESCE(SUM(poi.ordered_qty), 0) as total_order_qty')
            ->selectRaw('COALESCE(SUM(poi.received_qty), 0) as total_shipped_qty')
            ->selectRaw('COALESCE(SUM(poi.outstanding_qty), 0) as total_outstanding_qty')
            ->first();
    }

    public function baseOutstandingQuery(?int $supplierId, ?string $dateFrom, ?string $dateTo): Builder
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

    public function baseMonitoringPoQuery(?int $supplierId, ?string $dateFrom, ?string $dateTo): Builder
    {
        $currentDateSql = $this->currentDateExpression();
        $activePoSql = StatusQuery::sqlNotEquals('po.status', DomainStatus::GROUP_PO_STATUS, DocumentTermCodes::PO_CLOSED)
            . ' AND '
            . StatusQuery::sqlNotEquals('po.status', DomainStatus::GROUP_PO_STATUS, DocumentTermCodes::PO_CANCELLED);
        $activeItemSql = StatusQuery::sqlNotEquals('poi.item_status', DomainStatus::GROUP_PO_ITEM_STATUS, DocumentTermCodes::ITEM_CANCELLED);

        return DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_order_items as poi', 'poi.purchase_order_id', '=', 'po.id')
            ->when($supplierId, fn ($query) => $query->where('po.supplier_id', $supplierId))
            ->when($dateFrom, fn ($query) => $query->whereDate('po.po_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('po.po_date', '<=', $dateTo))
            ->select(
                'po.id as po_id',
                'po.po_number',
                'po.po_date',
                'po.status as po_status',
                's.supplier_name',
                DB::raw('COALESCE(po.eta_date, MIN(COALESCE(poi.eta_date, poi.etd_date))) as eta_date')
            )
            ->selectRaw('SUM(CASE WHEN ' . $activeItemSql . ' AND poi.outstanding_qty > 0 THEN 1 ELSE 0 END) as outstanding_item_count')
            ->selectRaw('COALESCE(SUM(poi.ordered_qty), 0) as total_order_qty')
            ->selectRaw('COALESCE(SUM(poi.received_qty), 0) as total_shipped_qty')
            ->selectRaw('COALESCE(SUM(poi.outstanding_qty), 0) as total_outstanding_qty')
            ->whereRaw($activePoSql)
            ->groupBy('po.id', 'po.po_number', 'po.po_date', 'po.status', 's.supplier_name', 'po.eta_date');
    }

    public function baseMonitoringItemQuery(?int $supplierId, ?string $dateFrom, ?string $dateTo): Builder
    {
        $currentDateSql = $this->currentDateExpression();
        $activePoSql = StatusQuery::sqlNotEquals('po.status', DomainStatus::GROUP_PO_STATUS, DocumentTermCodes::PO_CLOSED)
            . ' AND '
            . StatusQuery::sqlNotEquals('po.status', DomainStatus::GROUP_PO_STATUS, DocumentTermCodes::PO_CANCELLED);
        $activeItemSql = StatusQuery::sqlNotEquals('poi.item_status', DomainStatus::GROUP_PO_ITEM_STATUS, DocumentTermCodes::ITEM_CANCELLED);

        return DB::table('purchase_order_items as poi')
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
                'poi.etd_date',
                'poi.item_status'
            )
            ->whereRaw($activePoSql)
            ->whereRaw($activeItemSql)
            ->orderByDesc('poi.outstanding_qty')
            ->orderBy('po.po_number')
            ->orderBy('i.item_code');
    }

    private function currentDateExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "date('now')"
            : 'CURDATE()';
    }
}
