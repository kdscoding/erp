#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${1:-erp-monitoring-po}"
if [ ! -d "$APP_DIR" ] || [ ! -f "$APP_DIR/artisan" ]; then
  echo "[ERROR] project Laravel tidak valid: $APP_DIR"
  exit 1
fi
cd "$APP_DIR"

echo "[1/6] Ensure shipment + receiving columns/tables..."
cat > database/migrations/2026_01_04_000001_ensure_shipment_receiving_tables.php <<'MIG'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('shipments')) {
            Schema::create('shipments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->string('shipment_number')->unique();
                $table->date('shipment_date')->nullable();
                $table->date('eta_date')->nullable();
                $table->string('delivery_note_number')->nullable();
                $table->text('supplier_remark')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('goods_receipts')) {
            Schema::create('goods_receipts', function (Blueprint $table) {
                $table->id();
                $table->string('gr_number')->unique();
                $table->date('receipt_date');
                $table->foreignId('purchase_order_id')->constrained('purchase_orders');
                $table->string('document_number')->nullable();
                $table->text('remark')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('goods_receipt_items')) {
            Schema::create('goods_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
                $table->foreignId('purchase_order_item_id')->constrained('purchase_order_items');
                $table->decimal('received_qty', 14, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // no-op
    }
};
MIG

echo "[2/6] Write controllers..."
cat > app/Http/Controllers/ShipmentController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function index()
    {
        $rows = DB::table('shipments as sh')
            ->join('purchase_orders as po', 'po.id', '=', 'sh.purchase_order_id')
            ->select('sh.*', 'po.po_number')
            ->orderByDesc('sh.id')
            ->paginate(20);
        $pos = DB::table('purchase_orders')->orderByDesc('id')->limit(200)->get();
        return view('shipments.index', compact('rows', 'pos'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'purchase_order_id' => 'required|integer',
            'shipment_date' => 'required|date',
        ], ['required' => ':attribute wajib diisi.']);

        $number = 'SHP-' . now()->format('Ymd') . '-' . str_pad((string)(DB::table('shipments')->count()+1), 4, '0', STR_PAD_LEFT);

        DB::table('shipments')->insert([
            'purchase_order_id' => $v['purchase_order_id'],
            'shipment_number' => $number,
            'shipment_date' => $v['shipment_date'],
            'eta_date' => $request->eta_date,
            'delivery_note_number' => $request->delivery_note_number,
            'supplier_remark' => $request->supplier_remark,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchase_orders')->where('id', $v['purchase_order_id'])->update(['status' => 'Shipped', 'updated_at' => now()]);

        return back()->with('success', 'Shipment tersimpan.');
    }
}
PHP

cat > app/Http/Controllers/GoodsReceiptController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    public function index()
    {
        $rows = DB::table('goods_receipts as gr')
            ->join('purchase_orders as po', 'po.id', '=', 'gr.purchase_order_id')
            ->select('gr.*', 'po.po_number')
            ->orderByDesc('gr.id')
            ->paginate(20);
        $poItems = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->select('poi.*', 'po.po_number')
            ->where('poi.outstanding_qty', '>', 0)
            ->limit(300)
            ->get();
        return view('receiving.index', compact('rows', 'poItems'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'purchase_order_item_id' => 'required|integer',
            'receipt_date' => 'required|date',
            'received_qty' => 'required|numeric|min:0.01',
        ]);

        $poItem = DB::table('purchase_order_items')->where('id', $v['purchase_order_item_id'])->first();
        if (!$poItem) abort(422, 'PO item tidak ditemukan');
        if ($v['received_qty'] > $poItem->outstanding_qty) abort(422, 'Qty melebihi outstanding');

        $grNumber = 'GR-' . now()->format('Ymd') . '-' . str_pad((string)(DB::table('goods_receipts')->count()+1), 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $grId = DB::table('goods_receipts')->insertGetId([
                'gr_number' => $grNumber,
                'receipt_date' => $v['receipt_date'],
                'purchase_order_id' => $poItem->purchase_order_id,
                'document_number' => $request->document_number,
                'remark' => $request->remark,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('goods_receipt_items')->insert([
                'goods_receipt_id' => $grId,
                'purchase_order_item_id' => $poItem->id,
                'received_qty' => $v['received_qty'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newReceived = $poItem->received_qty + $v['received_qty'];
            $newOutstanding = $poItem->ordered_qty - $newReceived;
            DB::table('purchase_order_items')->where('id', $poItem->id)->update([
                'received_qty' => $newReceived,
                'outstanding_qty' => $newOutstanding,
                'updated_at' => now(),
            ]);

            $hasOutstanding = DB::table('purchase_order_items')
                ->where('purchase_order_id', $poItem->purchase_order_id)
                ->where('outstanding_qty', '>', 0)
                ->exists();
            DB::table('purchase_orders')->where('id', $poItem->purchase_order_id)->update([
                'status' => $hasOutstanding ? 'Partial Received' : 'Closed',
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return back()->with('success', 'Goods Receipt tersimpan.');
    }
}
PHP

echo "[3/6] Write views..."
mkdir -p resources/views/shipments resources/views/receiving
cat > resources/views/shipments/index.blade.php <<'BLADE'
@extends('layouts.erp')
@php($title='Shipment Tracking')
@php($header='Shipment Tracking')
@section('content')
<div class="card card-primary card-outline mb-3"><div class="card-body">
<form method="POST" action="{{ route('shipments.store') }}" class="row g-2">@csrf
<div class="col-md-4"><select name="purchase_order_id" class="form-select" required>@foreach($pos as $po)<option value="{{ $po->id }}">{{ $po->po_number }}</option>@endforeach</select></div>
<div class="col-md-3"><input type="date" name="shipment_date" class="form-control" required></div>
<div class="col-md-3"><input type="date" name="eta_date" class="form-control" placeholder="ETA"></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>No Shipment</th><th>PO</th><th>Tgl</th><th>ETA</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->shipment_number }}</td><td>{{ $r->po_number }}</td><td>{{ $r->shipment_date }}</td><td>{{ $r->eta_date }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
BLADE

cat > resources/views/receiving/index.blade.php <<'BLADE'
@extends('layouts.erp')
@php($title='Goods Receiving')
@php($header='Goods Receiving')
@section('content')
<div class="card card-success card-outline mb-3"><div class="card-body">
<form method="POST" action="{{ route('receiving.store') }}" class="row g-2">@csrf
<div class="col-md-5"><select name="purchase_order_item_id" class="form-select" required>@foreach($poItems as $i)<option value="{{ $i->id }}">{{ $i->po_number }} | Item #{{ $i->item_id }} | OS: {{ $i->outstanding_qty }}</option>@endforeach</select></div>
<div class="col-md-2"><input type="date" name="receipt_date" class="form-control" required></div>
<div class="col-md-2"><input type="number" step="0.01" name="received_qty" class="form-control" placeholder="Qty" required></div>
<div class="col-md-3"><button class="btn btn-success w-100">Post GR</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>GR</th><th>PO</th><th>Tgl</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->gr_number }}</td><td>{{ $r->po_number }}</td><td>{{ $r->receipt_date }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
BLADE

echo "[4/6] Update routes..."
cat > routes/web.php <<'PHP'
<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');

    Route::get('/po', [PurchaseOrderController::class, 'index'])->name('po.index');
    Route::get('/po/create', [PurchaseOrderController::class, 'create'])->name('po.create');
    Route::post('/po', [PurchaseOrderController::class, 'store'])->name('po.store');

    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');

    Route::get('/receiving', [GoodsReceiptController::class, 'index'])->name('receiving.index');
    Route::post('/receiving', [GoodsReceiptController::class, 'store'])->name('receiving.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
PHP

echo "[5/6] Apply migration..."
php artisan migrate

echo "[6/6] Step 5 complete. Open /shipments and /receiving"
