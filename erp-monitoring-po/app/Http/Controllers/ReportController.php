<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function outstanding()
    {
        $rows = DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->whereNotIn('po.status', ['Closed','Cancelled'])
            ->select('po.po_number', 'po.po_date', 'po.status', 's.supplier_name')
            ->orderByDesc('po.id')
            ->paginate(30);

        return view('reports.outstanding', compact('rows'));
    }
}
