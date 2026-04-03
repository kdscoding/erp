<?php

namespace App\Queries\Traceability;

use App\Support\DomainStatus;
use App\Support\StatusQuery;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TraceabilityIndexQuery
{
    public function get(Request $request): Collection
    {
        return $this->baseQuery()
            ->when(
                $request->filled('po_number'),
                fn (Builder $query) => $query->where('po.po_number', 'like', '%' . trim((string) $request->input('po_number')) . '%')
            )
            ->when(
                $request->filled('supplier_id'),
                fn (Builder $query) => $query->where('po.supplier_id', (int) $request->input('supplier_id'))
            )
            ->when(
                $request->filled('item_keyword'),
                function (Builder $query) use ($request) {
                    $keyword = '%' . trim((string) $request->input('item_keyword')) . '%';

                    $query->where(function (Builder $inner) use ($keyword) {
                        $inner->where('i.item_code', 'like', $keyword)
                            ->orWhere('i.item_name', 'like', $keyword);
                    });
                }
            )
            ->when(
                $request->filled('item_status'),
                fn (Builder $query) => StatusQuery::whereEquals(
                    $query,
                    'poi.item_status',
                    DomainStatus::GROUP_PO_ITEM_STATUS,
                    trim((string) $request->input('item_status'))
                )
            )
            ->orderByDesc('po.po_date')
            ->orderByDesc('poi.id')
            ->get();
    }

    public function baseQuery(): Builder
    {
        $shipmentNumberAggregate = $this->groupConcatExpression('sh.shipment_number');
        $deliveryNoteAggregate = $this->groupConcatExpression('sh.delivery_note_number');

        return DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->leftJoin('shipment_items as si', 'si.purchase_order_item_id', '=', 'poi.id')
            ->leftJoin('shipments as sh', 'sh.id', '=', 'si.shipment_id')
            ->leftJoin('goods_receipt_items as gri', 'gri.purchase_order_item_id', '=', 'poi.id')
            ->leftJoin('goods_receipts as gr', 'gr.id', '=', 'gri.goods_receipt_id')
            ->select(
                'poi.id as purchase_order_item_id',
                'po.id as po_id',
                'po.supplier_id',
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
            ->selectRaw('MIN(sh.shipment_date) as first_shipment_date')
            ->selectRaw('MAX(sh.shipment_date) as last_shipment_date')
            ->selectRaw('COUNT(DISTINCT si.id) as shipment_count')
            ->selectRaw("{$shipmentNumberAggregate} as shipment_numbers")
            ->selectRaw("{$deliveryNoteAggregate} as delivery_note_numbers")
            ->selectRaw('MIN(gr.receipt_date) as first_receipt_date')
            ->selectRaw('MAX(gr.receipt_date) as last_receipt_date')
            ->selectRaw('COUNT(gri.id) as receipt_count')
            ->groupBy(
                'poi.id',
                'po.id',
                'po.supplier_id',
                'po.po_number',
                'po.po_date',
                'po.status',
                's.supplier_name',
                'i.item_code',
                'i.item_name',
                'poi.ordered_qty',
                'poi.received_qty',
                'poi.outstanding_qty',
                'poi.etd_date',
                'poi.item_status',
                'poi.cancel_reason'
            );
    }

    private function groupConcatExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "GROUP_CONCAT(DISTINCT {$column})"
            : "GROUP_CONCAT(DISTINCT {$column} ORDER BY {$column} SEPARATOR ', ')";
    }
}
