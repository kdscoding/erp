#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${1:-erp-monitoring-po}"

if [ ! -d "$APP_DIR" ] || [ ! -f "$APP_DIR/artisan" ]; then
  echo "[ERROR] project Laravel tidak valid: $APP_DIR"
  exit 1
fi

cd "$APP_DIR"

echo "[1/6] Ensure PO tables (manual PO, multi item, auto ID)..."
cat > database/migrations/2026_01_03_000001_ensure_purchase_order_tables.php <<'MIG'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('po_number')->unique(); // manual input
                $table->date('po_date');
                $table->foreignId('supplier_id')->constrained('suppliers');
                $table->string('status')->default('Draft');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items');
                $table->decimal('ordered_qty', 14, 2)->default(0);
                $table->decimal('received_qty', 14, 2)->default(0);
                $table->decimal('outstanding_qty', 14, 2)->default(0);
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

echo "[2/6] Write PO controller (Select2 item_id, no ETD input)..."
cat > app/Http/Controllers/PurchaseOrderController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $rows = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
            ->select('po.*', 's.supplier_name')
            ->orderByDesc('po.id')
            ->paginate(20);

        return view('po.index', compact('rows'));
    }

    public function create()
    {
        $suppliers = DB::table('suppliers')->orderBy('supplier_name')->get();
        $items = DB::table('items as i')
            ->leftJoin('units as u', 'u.id', '=', 'i.unit_id')
            ->select('i.id', 'i.item_code', 'i.item_name', DB::raw('COALESCE(u.unit_name, "") as unit_name'))
            ->orderBy('i.item_code')
            ->limit(1000)
            ->get();

        return view('po.create', compact('suppliers', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'required|string|max:100|unique:purchase_orders,po_number',
            'po_date' => 'required|date',
            'supplier_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.ordered_qty' => 'required|numeric|min:0.01',
        ], [
            'required' => ':attribute wajib diisi.',
            'items.min' => 'Minimal harus ada 1 item.',
        ]);

        DB::beginTransaction();
        try {
            $poId = DB::table('purchase_orders')->insertGetId([
                'po_number' => $validated['po_number'],
                'po_date' => $validated['po_date'],
                'supplier_id' => $validated['supplier_id'],
                'status' => 'Draft',
                'notes' => $request->input('notes'),
                'created_by' => optional($request->user())->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($validated['items'] as $row) {
                $item = DB::table('items')->where('id', $row['item_id'])->first();
                if (!$item) {
                    throw new \RuntimeException('Item tidak ditemukan ID: ' . $row['item_id']);
                }

                DB::table('purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'item_id' => $item->id,
                    'ordered_qty' => $row['ordered_qty'],
                    'received_qty' => 0,
                    'outstanding_qty' => $row['ordered_qty'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('po.index')->with('success', 'PO berhasil dibuat (manual number + multi item).');
    }
}
PHP

echo "[3/6] Write PO create UI with Select2 + combined kode-uraian + auto unit + qty..."
mkdir -p resources/views/po

cat > resources/views/po/index.blade.php <<'BLADE'
@extends('layouts.erp')
@php($title='Purchase Order')
@php($header='Purchase Order Monitoring')
@section('content')
<div class="mb-3"><a href="{{ route('po.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat PO</a></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0">
<thead><tr><th>PO Number</th><th>PO Date</th><th>Supplier</th><th>Status</th></tr></thead>
<tbody>
@forelse($rows as $r)
<tr><td>{{ $r->po_number }}</td><td>{{ \Carbon\Carbon::parse($r->po_date)->format('d-m-Y') }}</td><td>{{ $r->supplier_name }}</td><td><span class="badge bg-secondary">{{ $r->status }}</span></td></tr>
@empty
<tr><td colspan="4" class="text-center text-muted">Belum ada PO</td></tr>
@endforelse
</tbody>
</table></div></div>
@endsection
BLADE

cat > resources/views/po/create.blade.php <<'BLADE'
@extends('layouts.erp')
@php($title='Buat PO')
@php($header='Create Purchase Order (Manual)')
@section('content')
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>.select2-container{width:100%!important}</style>

<div class="card card-primary card-outline">
  <div class="card-header"><h3 class="card-title">Header PO</h3></div>
  <div class="card-body">
    <form method="POST" action="{{ route('po.store') }}" id="po-form">@csrf
      <div class="row g-3 mb-3">
        <div class="col-md-3"><label class="form-label">PO Number (Manual)</label><input class="form-control" name="po_number" value="{{ old('po_number') }}" required></div>
        <div class="col-md-3"><label class="form-label">Tanggal PO</label><input type="date" class="form-control" name="po_date" value="{{ old('po_date') }}" required></div>
        <div class="col-md-6"><label class="form-label">Supplier</label>
          <select name="supplier_id" class="form-select" required>
            @foreach($suppliers as $s)<option value="{{ $s->id }}" {{ old('supplier_id')==$s->id?'selected':'' }}>{{ $s->supplier_name }}</option>@endforeach
          </select>
        </div>
      </div>

      <hr>
      <h5 class="mb-2">Detail Item PO</h5>
      <p class="text-muted">Item dipilih via Select2 (Kode + Uraian jadi satu), satuan otomatis muncul, lalu isi Qty.</p>

      <div class="table-responsive mb-3">
        <table class="table table-bordered" id="po-items-table">
          <thead>
            <tr>
              <th style="width:55%">Item (Kode - Uraian)</th>
              <th style="width:20%">Satuan</th>
              <th style="width:20%">Qty Order</th>
              <th style="width:5%"></th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="btn-add-item">+ Tambah Item</button>
      <div class="mb-3"><label class="form-label">Catatan</label><input class="form-control" name="notes" value="{{ old('notes') }}"></div>
      <button class="btn btn-success">Simpan PO</button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
  const items = @json($items);
  const tbody = document.querySelector('#po-items-table tbody');
  const addBtn = document.getElementById('btn-add-item');

  function optionHtml() {
    return '<option value="">-- Pilih Item --</option>' + items.map(i =>
      `<option value="${i.id}" data-unit="${i.unit_name || ''}">${i.item_code} - ${i.item_name}</option>`
    ).join('');
  }

  function rowTemplate(idx) {
    return `
      <tr>
        <td>
          <select class="form-select item-select" name="items[${idx}][item_id]" required>
            ${optionHtml()}
          </select>
        </td>
        <td><input class="form-control item-unit" readonly></td>
        <td><input type="number" step="0.01" min="0.01" class="form-control" name="items[${idx}][ordered_qty]" value="1" required></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-remove">x</button></td>
      </tr>
    `;
  }

  function reindex() {
    [...tbody.querySelectorAll('tr')].forEach((tr, idx) => {
      tr.querySelector('.item-select').setAttribute('name', `items[${idx}][item_id]`);
      tr.querySelector('input[type="number"]').setAttribute('name', `items[${idx}][ordered_qty]`);
    });
  }

  function bindRow(tr) {
    const sel = tr.querySelector('.item-select');
    const unit = tr.querySelector('.item-unit');

    $(sel).select2({
      width: '100%',
      placeholder: '-- Pilih Item --'
    });

    $(sel).on('change', function () {
      const selected = this.options[this.selectedIndex];
      unit.value = selected ? (selected.dataset.unit || '') : '';
    });
  }

  function addRow() {
    const idx = tbody.querySelectorAll('tr').length;
    tbody.insertAdjacentHTML('beforeend', rowTemplate(idx));
    bindRow(tbody.lastElementChild);
  }

  addBtn.addEventListener('click', addRow);

  tbody.addEventListener('click', (e) => {
    if (e.target.classList.contains('btn-remove')) {
      if (tbody.querySelectorAll('tr').length === 1) return;
      const tr = e.target.closest('tr');
      const sel = tr.querySelector('.item-select');
      if (sel) $(sel).select2('destroy');
      tr.remove();
      reindex();
    }
  });

  addRow();
})();
</script>
@endsection
BLADE

echo "[4/6] Keep unified routes..."
# routes maintained by latest step scripts, but rewrite to keep complete menu modules
cat > routes/web.php <<'PHP'
<?php

use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TraceabilityController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');

    Route::get('/masters/units', [UnitController::class, 'index'])->name('units.index');
    Route::post('/masters/units', [UnitController::class, 'store'])->name('units.store');
    Route::get('/masters/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::post('/masters/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    Route::get('/masters/plants', [PlantController::class, 'index'])->name('plants.index');
    Route::post('/masters/plants', [PlantController::class, 'store'])->name('plants.store');
    Route::get('/masters/items', [ItemController::class, 'index'])->name('items.index');
    Route::post('/masters/items', [ItemController::class, 'store'])->name('items.store');

    Route::get('/po', [PurchaseOrderController::class, 'index'])->name('po.index');
    Route::get('/po/create', [PurchaseOrderController::class, 'create'])->name('po.create');
    Route::post('/po', [PurchaseOrderController::class, 'store'])->name('po.store');

    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');

    Route::get('/receiving', [GoodsReceiptController::class, 'index'])->name('receiving.index');
    Route::post('/receiving', [GoodsReceiptController::class, 'store'])->name('receiving.store');

    Route::get('/traceability', [TraceabilityController::class, 'index'])->name('traceability.index');
    Route::get('/reports/outstanding', [ReportController::class, 'outstanding'])->name('reports.outstanding');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
PHP

echo "[5/6] Run migration..."
php artisan migrate

echo "[6/6] Step 3 updated: Select2 + item(kode+uraian) 1 field + auto satuan + qty, tanpa ETD input."
