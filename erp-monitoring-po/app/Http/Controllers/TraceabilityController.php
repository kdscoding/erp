<?php

namespace App\Http\Controllers;

use App\Queries\Traceability\TraceabilityIndexQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TraceabilityController extends Controller
{
    public function index(Request $request, TraceabilityIndexQuery $traceabilityIndexQuery): View
    {
        $rows = $traceabilityIndexQuery->get($request);

        return view('traceability.index', compact('rows'));
    }
}
