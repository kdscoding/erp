#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${1:-erp-monitoring-po}"
if [ ! -d "$APP_DIR" ] || [ ! -f "$APP_DIR/artisan" ]; then
  echo "[ERROR] project Laravel tidak valid: $APP_DIR"
  exit 1
fi
cd "$APP_DIR"

echo "[1/6] Ensure supporting master tables..."
cat > database/migrations/2026_01_05_000001_ensure_supporting_master_tables.php <<'MIG'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->string('unit_code')->unique();
                $table->string('unit_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) {
                $table->id();
                $table->string('warehouse_code')->unique();
                $table->string('warehouse_name');
                $table->string('location')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('plants')) {
            Schema::create('plants', function (Blueprint $table) {
                $table->id();
                $table->string('plant_code')->unique();
                $table->string('plant_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->id();
                $table->string('item_code')->unique();
                $table->string('item_name');
                $table->foreignId('unit_id')->nullable()->constrained('units');
                $table->string('category')->nullable();
                $table->boolean('active')->default(true);
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

echo "[2/6] Write controllers for supporting masters..."
cat > app/Http/Controllers/UnitController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    public function index()
    {
        $rows = DB::table('units')->orderByDesc('id')->paginate(20);
        return view('masters.units.index', compact('rows'));
    }

    public function store(Request $request)
    {
        $v = $request->validate(['unit_code' => 'required', 'unit_name' => 'required'], ['required' => ':attribute wajib diisi.']);
        DB::table('units')->updateOrInsert(['unit_code' => $v['unit_code']], ['unit_name' => $v['unit_name'], 'updated_at' => now(), 'created_at' => now()]);
        return back()->with('success', 'Unit tersimpan.');
    }
}
PHP

cat > app/Http/Controllers/WarehouseController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function index()
    {
        $rows = DB::table('warehouses')->orderByDesc('id')->paginate(20);
        return view('masters.warehouses.index', compact('rows'));
    }

    public function store(Request $request)
    {
        $v = $request->validate(['warehouse_code' => 'required', 'warehouse_name' => 'required']);
        DB::table('warehouses')->updateOrInsert(
            ['warehouse_code' => $v['warehouse_code']],
            ['warehouse_name' => $v['warehouse_name'], 'location' => $request->location, 'updated_at' => now(), 'created_at' => now()]
        );
        return back()->with('success', 'Warehouse tersimpan.');
    }
}
PHP

cat > app/Http/Controllers/PlantController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlantController extends Controller
{
    public function index()
    {
        $rows = DB::table('plants')->orderByDesc('id')->paginate(20);
        return view('masters.plants.index', compact('rows'));
    }

    public function store(Request $request)
    {
        $v = $request->validate(['plant_code' => 'required', 'plant_name' => 'required']);
        DB::table('plants')->updateOrInsert(['plant_code' => $v['plant_code']], ['plant_name' => $v['plant_name'], 'updated_at' => now(), 'created_at' => now()]);
        return back()->with('success', 'Plant tersimpan.');
    }
}
PHP

cat > app/Http/Controllers/ItemController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index()
    {
        $rows = DB::table('items as i')
            ->leftJoin('units as u', 'u.id', '=', 'i.unit_id')
            ->select('i.*', 'u.unit_name')
            ->orderByDesc('i.id')
            ->paginate(20);
        $units = DB::table('units')->orderBy('unit_name')->get();
        return view('masters.items.index', compact('rows', 'units'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'item_code' => 'required',
            'item_name' => 'required',
        ]);

        DB::table('items')->updateOrInsert(
            ['item_code' => $v['item_code']],
            [
                'item_name' => $v['item_name'],
                'unit_id' => $request->unit_id,
                'category' => $request->category,
                'active' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('success', 'Item tersimpan.');
    }
}
PHP

echo "[3/6] Write views for supporting masters..."
mkdir -p resources/views/masters/units resources/views/masters/warehouses resources/views/masters/plants resources/views/masters/items

cat > resources/views/masters/units/index.blade.php <<'BLADE'
@extends('layouts.erp')
@php($title='Master Unit')
@php($header='Master Unit of Measure')
@section('content')
<div class="card card-primary card-outline mb-3"><div class="card-body"><form method="POST" action="{{ route('units.store') }}" class="row g-2">@csrf
<div class="col-md-3"><input class="form-control" name="unit_code" placeholder="Kode Unit" required></div>
<div class="col-md-5"><input class="form-control" name="unit_name" placeholder="Nama Unit" required></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover mb-0"><thead><tr><th>Kode</th><th>Nama</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->unit_code }}</td><td>{{ $r->unit_name }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
BLADE

cat > resources/views/masters/warehouses/index.blade.php <<'BLADE'
@extends('layouts.erp')
@php($title='Master Warehouse')
@php($header='Master Warehouse')
@section('content')
<div class="card card-primary card-outline mb-3"><div class="card-body"><form method="POST" action="{{ route('warehouses.store') }}" class="row g-2">@csrf
<div class="col-md-2"><input class="form-control" name="warehouse_code" placeholder="Kode" required></div>
<div class="col-md-4"><input class="form-control" name="warehouse_name" placeholder="Nama" required></div>
<div class="col-md-4"><input class="form-control" name="location" placeholder="Lokasi"></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover mb-0"><thead><tr><th>Kode</th><th>Nama</th><th>Lokasi</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->warehouse_code }}</td><td>{{ $r->warehouse_name }}</td><td>{{ $r->location }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
BLADE

cat > resources/views/masters/plants/index.blade.php <<'BLADE'
@extends('layouts.erp')
@php($title='Master Plant')
@php($header='Master Plant')
@section('content')
<div class="card card-primary card-outline mb-3"><div class="card-body"><form method="POST" action="{{ route('plants.store') }}" class="row g-2">@csrf
<div class="col-md-3"><input class="form-control" name="plant_code" placeholder="Kode Plant" required></div>
<div class="col-md-7"><input class="form-control" name="plant_name" placeholder="Nama Plant" required></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover mb-0"><thead><tr><th>Kode</th><th>Nama</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->plant_code }}</td><td>{{ $r->plant_name }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
BLADE

cat > resources/views/masters/items/index.blade.php <<'BLADE'
@extends('layouts.erp')
@php($title='Master Item')
@php($header='Master Item Label/Material')
@section('content')
<div class="card card-primary card-outline mb-3"><div class="card-body"><form method="POST" action="{{ route('items.store') }}" class="row g-2">@csrf
<div class="col-md-2"><input class="form-control" name="item_code" placeholder="Kode Item" required></div>
<div class="col-md-4"><input class="form-control" name="item_name" placeholder="Nama Item" required></div>
<div class="col-md-3"><select class="form-select" name="unit_id"><option value="">Pilih Unit</option>@foreach($units as $u)<option value="{{ $u->id }}">{{ $u->unit_name }}</option>@endforeach</select></div>
<div class="col-md-2"><input class="form-control" name="category" placeholder="Kategori"></div>
<div class="col-md-1"><button class="btn btn-primary w-100">OK</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover mb-0"><thead><tr><th>Kode</th><th>Nama</th><th>Unit</th><th>Kategori</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->item_code }}</td><td>{{ $r->item_name }}</td><td>{{ $r->unit_name }}</td><td>{{ $r->category }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
BLADE

echo "[4/6] Update routes with supporting master menus..."
python - <<'PY'
from pathlib import Path
p=Path('routes/web.php')
s=p.read_text()
for use in [
'use App\\Http\\Controllers\\UnitController;',
'use App\\Http\\Controllers\\WarehouseController;',
'use App\\Http\\Controllers\\PlantController;',
'use App\\Http\\Controllers\\ItemController;'
]:
    if use not in s:
        s=s.replace('use App\\Http\\Controllers\\SupplierController;', 'use App\\Http\\Controllers\\SupplierController;\n'+use)

inject = """
    Route::get('/masters/units', [UnitController::class, 'index'])->name('units.index');
    Route::post('/masters/units', [UnitController::class, 'store'])->name('units.store');

    Route::get('/masters/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::post('/masters/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');

    Route::get('/masters/plants', [PlantController::class, 'index'])->name('plants.index');
    Route::post('/masters/plants', [PlantController::class, 'store'])->name('plants.store');

    Route::get('/masters/items', [ItemController::class, 'index'])->name('items.index');
    Route::post('/masters/items', [ItemController::class, 'store'])->name('items.store');
"""
if "Route::get('/masters/units'" not in s:
    s=s.replace("    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');\n    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');\n",
                "    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');\n    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');\n"+inject)
p.write_text(s)
PY

echo "[5/6] Migrate + seed supporting defaults..."
php artisan migrate
php artisan db:seed --class=MasterDataSeeder || true

echo "[6/6] Step 7 complete. Menus: units/warehouses/plants/items"
