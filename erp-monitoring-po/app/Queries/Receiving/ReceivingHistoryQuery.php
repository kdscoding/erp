<?php

namespace App\Queries\Receiving;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceivingHistoryQuery
{
    public function paginate(Request $request, int $perPage = 20): LengthAwarePaginator
    {
        return DB::table('goods_receipts as gr')
            ->join('purchase_orders as po', 'po.id', '=', 'gr.purchase_order_id')
            ->leftJoin('shipments as sh', 'sh.id', '=', 'gr.shipment_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select(
                'gr.*',
                'po.po_number',
                's.supplier_name',
                'sh.shipment_number',
                'sh.delivery_note_number',
                'sh.invoice_number'
            )
            ->when(
                $request->filled('document_number'),
                fn ($query) => $query->where('gr.document_number', 'like', '%' . $request->string('document_number') . '%')
            )
            ->orderByDesc('gr.id')
            ->paginate($perPage);
    }
}
