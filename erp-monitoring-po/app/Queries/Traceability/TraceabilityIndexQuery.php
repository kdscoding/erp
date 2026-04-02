<?php

namespace App\Queries\Traceability;

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
            ->orderByDesc('po.po_date')
            ->orderByDesc('poi.id')
            ->get();
    }

    public function baseQuery(): Builder
    {
        return DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->join('items as i', 'i.id', '=', 'poi.item_id')
            ->leftJoin('goods_receipt_items as gri', 'gri.purchase_order_item_id', '=', 'poi.id')
            ->leftJoin('goods_receipts as gr', 'gr.id', '=', 'gri.goods_receipt_id')
            ->select(
                'poi.id as purchase_order_item_id',
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
            ->groupBy(
                'poi.id',
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
}
