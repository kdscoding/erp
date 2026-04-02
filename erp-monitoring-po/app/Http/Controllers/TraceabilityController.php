<?php

namespace App\Http\Controllers;

use App\Queries\Traceability\TraceabilityIndexQuery;
use App\Support\DocumentTermCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TraceabilityController extends Controller
{
    public function index(Request $request, TraceabilityIndexQuery $traceabilityIndexQuery): View
    {
        $rows = $traceabilityIndexQuery->get($request);
        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get(['id', 'supplier_name']);
        $itemStatuses = DocumentTermCodes::poItemStatuses();

        return view('traceability.index', compact('rows', 'suppliers', 'itemStatuses'));
    }
}
