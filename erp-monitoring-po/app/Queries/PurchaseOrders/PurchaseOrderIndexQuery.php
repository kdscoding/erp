<?php

namespace App\Queries\PurchaseOrders;

use App\Support\DomainStatus;
use App\Support\StatusQuery;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderIndexQuery
{
    public function base(Request $request): Builder
    {
        return DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('po.*', 's.supplier_name', 's.supplier_code')
            ->when(
                $request->filled('status'),
                fn (Builder $query) => StatusQuery::whereEquals(
                    $query,
                    'po.status',
                    DomainStatus::GROUP_PO_STATUS,
                    trim((string) $request->input('status'))
                )
            )
            ->when(
                $request->filled('po_number'),
                fn (Builder $query) => $query->where('po.po_number', 'like', '%' . trim((string) $request->input('po_number')) . '%')
            )
            ->when(
                $request->filled('supplier_code'),
                fn (Builder $query) => $query->where('s.supplier_code', trim((string) $request->input('supplier_code')))
            )
            ->when($request->filled('supplier_id'), fn (Builder $query) => $query->where('po.supplier_id', (int) $request->input('supplier_id')))
            ->orderByDesc('po.id');
    }
}
