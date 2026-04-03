<?php

namespace App\Queries\Receiving;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceivingHistoryQuery
{
    public function paginate(Request $request, int $perPage = 20): LengthAwarePaginator
    {
        $poNumberAggregate = DB::connection()->getDriverName() === 'sqlite'
            ? "GROUP_CONCAT(DISTINCT po.po_number)"
            : "GROUP_CONCAT(DISTINCT po.po_number ORDER BY po.po_number SEPARATOR ', ')";

        return DB::table('goods_receipts as gr')
            ->leftJoin('shipments as sh', 'sh.id', '=', 'gr.shipment_id')
            ->leftJoin('goods_receipt_items as gri', 'gri.goods_receipt_id', '=', 'gr.id')
            ->leftJoin('purchase_order_items as poi', 'poi.id', '=', 'gri.purchase_order_item_id')
            ->leftJoin('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select(
                'gr.*',
                'sh.shipment_number',
                'sh.delivery_note_number',
                'sh.invoice_number'
            )
            ->selectRaw("{$poNumberAggregate} as po_number")
            ->selectRaw('MIN(s.supplier_name) as supplier_name')
            ->when(
                $request->filled('document_number'),
                fn ($query) => $query->where('gr.document_number', 'like', '%' . $request->string('document_number') . '%')
            )
            ->groupBy(
                'gr.id',
                'gr.gr_number',
                'gr.receipt_date',
                'gr.purchase_order_id',
                'gr.shipment_id',
                'gr.warehouse_id',
                'gr.received_by',
                'gr.document_number',
                'gr.remark',
                'gr.status',
                'gr.status_code',
                'gr.cancel_reason',
                'gr.cancelled_by',
                'gr.cancelled_at',
                'gr.created_at',
                'gr.updated_at',
                'sh.shipment_number',
                'sh.delivery_note_number',
                'sh.invoice_number'
            )
            ->orderByDesc('gr.id')
            ->paginate($perPage);
    }
}
