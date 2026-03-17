#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${1:-erp-monitoring-po}"
if [ ! -d "$APP_DIR" ] || [ ! -f "$APP_DIR/artisan" ]; then
  echo "[ERROR] project Laravel tidak valid: $APP_DIR"
  exit 1
fi
cd "$APP_DIR"

echo "[1/5] Write Traceability + Report controller..."
cat > app/Http/Controllers/TraceabilityController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TraceabilityController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->leftJoin('shipments as sh', 'sh.purchase_order_id', '=', 'po.id')
            ->leftJoin('goods_receipts as gr', 'gr.purchase_order_id', '=', 'po.id')
            ->select('po.po_number', 'po.po_date', 'po.status', 's.supplier_name', 'sh.shipment_number', 'sh.shipment_date', 'gr.gr_number', 'gr.receipt_date')
            ->orderByDesc('po.id');

        if ($request->filled('po_number')) {
            $query->where('po.po_number', 'like', '%' . $request->po_number . '%');
        }

        $rows = $query->paginate(30);
        return view('traceability.index', compact('rows'));
    }
}
PHP

cat > app/Http/Controllers/ReportController.php <<'PHP'
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
PHP

echo "[2/5] Write views..."
mkdir -p resources/views/traceability resources/views/reports
cat > resources/views/traceability/index.blade.php <<'BLADE'
@extends('layouts.erp')
@php($title='Traceability')
@php($header='Traceability PO -> Shipment -> Receiving')
@section('content')
<div class="card card-outline card-primary mb-3"><div class="card-body">
<form method="GET" class="row g-2">
<div class="col-md-4"><input name="po_number" class="form-control" placeholder="Cari PO Number" value="{{ request('po_number') }}"></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Cari</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>PO</th><th>Tgl PO</th><th>Supplier</th><th>Shipment</th><th>GR</th><th>Status</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->po_number }}</td><td>{{ $r->po_date }}</td><td>{{ $r->supplier_name }}</td><td>{{ $r->shipment_number }} / {{ $r->shipment_date }}</td><td>{{ $r->gr_number }} / {{ $r->receipt_date }}</td><td>{{ $r->status }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
BLADE

cat > resources/views/reports/outstanding.blade.php <<'BLADE'
@extends('layouts.erp')
@php($title='Outstanding PO')
@php($header='Laporan Outstanding PO')
@section('content')
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>PO</th><th>Tgl</th><th>Supplier</th><th>Status</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->po_number }}</td><td>{{ $r->po_date }}</td><td>{{ $r->supplier_name }}</td><td>{{ $r->status }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
BLADE

echo "[3/5] Extend routes..."
python - <<'PY'
from pathlib import Path
p=Path('routes/web.php')
s=p.read_text()
if 'TraceabilityController' not in s:
    s=s.replace('use App\\Http\\Controllers\\SupplierController;','use App\\Http\\Controllers\\SupplierController;\nuse App\\Http\\Controllers\\TraceabilityController;\nuse App\\Http\\Controllers\\ReportController;')
block="""
    Route::get('/traceability', [TraceabilityController::class, 'index'])->name('traceability.index');
    Route::get('/reports/outstanding', [ReportController::class, 'outstanding'])->name('reports.outstanding');
"""
if "Route::get('/traceability'" not in s:
    s=s.replace("    Route::get('/receiving', [GoodsReceiptController::class, 'index'])->name('receiving.index');\n    Route::post('/receiving', [GoodsReceiptController::class, 'store'])->name('receiving.store');\n", "    Route::get('/receiving', [GoodsReceiptController::class, 'index'])->name('receiving.index');\n    Route::post('/receiving', [GoodsReceiptController::class, 'store'])->name('receiving.store');\n"+block)
p.write_text(s)
PY

echo "[4/5] Migrate (no new migration in this step, safe run)..."
php artisan migrate

echo "[5/5] Step 6 complete. Open /traceability and /reports/outstanding"
