<?php

namespace App\Queries\PurchaseOrders;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderIndexQuery
{
    public function base(Request $request): Builder
    {
        return DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('po.*', 's.supplier_name')
            ->when($request->filled('status'), fn (Builder $query) => $query->where('po.status', trim((string) $request->input('status'))))
            ->when($request->filled('supplier_id'), fn (Builder $query) => $query->where('po.supplier_id', (int) $request->input('supplier_id')))
            ->orderByDesc('po.id');
    }
}
