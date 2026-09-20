#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${1:-erp-monitoring-po}"

if [ ! -d "$APP_DIR" ] || [ ! -f "$APP_DIR/artisan" ]; then
  echo "[ERROR] project Laravel tidak valid: $APP_DIR"
  exit 1
fi

cd "$APP_DIR"

echo "[1/6] Review schema existing (MySQL information_schema)..."
php artisan tinker --execute="echo 'DB: '.config('database.default').PHP_EOL;" >/dev/null 2>&1 || true

cat > database/migrations/2026_01_02_000001_ensure_master_data_columns.php <<'MIG'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {
                if (!Schema::hasColumn('units', 'unit_code')) $table->string('unit_code')->nullable();
                if (!Schema::hasColumn('units', 'unit_name')) $table->string('unit_name')->nullable();
            });
        }

        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $table) {
                if (!Schema::hasColumn('suppliers', 'supplier_code')) $table->string('supplier_code')->nullable();
                if (!Schema::hasColumn('suppliers', 'supplier_name')) $table->string('supplier_name')->nullable();
                if (!Schema::hasColumn('suppliers', 'status')) $table->boolean('status')->default(true);
            });
        }

        if (Schema::hasTable('warehouses')) {
            Schema::table('warehouses', function (Blueprint $table) {
                if (!Schema::hasColumn('warehouses', 'warehouse_code')) $table->string('warehouse_code')->nullable();
                if (!Schema::hasColumn('warehouses', 'warehouse_name')) $table->string('warehouse_name')->nullable();
            });
        }

        if (Schema::hasTable('plants')) {
            Schema::table('plants', function (Blueprint $table) {
                if (!Schema::hasColumn('plants', 'plant_code')) $table->string('plant_code')->nullable();
                if (!Schema::hasColumn('plants', 'plant_name')) $table->string('plant_name')->nullable();
            });
        }

        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (!Schema::hasColumn('items', 'item_code')) $table->string('item_code')->nullable();
                if (!Schema::hasColumn('items', 'item_name')) $table->string('item_name')->nullable();
                if (!Schema::hasColumn('items', 'active')) $table->boolean('active')->default(true);
            });
        }
    }

    public function down(): void
    {
        // no-op
    }
};
MIG

echo "[2/6] Update MasterDataSeeder (safe for existing DB)..."
cat > database/seeders/MasterDataSeeder.php <<'PHP'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('units')->updateOrInsert(['unit_code' => 'PCS'], ['unit_name' => 'Pieces', 'updated_at' => now(), 'created_at' => now()]);
        DB::table('warehouses')->updateOrInsert(['warehouse_code' => 'WH-01'], ['warehouse_name' => 'Main Warehouse', 'location' => 'Plant A', 'updated_at' => now(), 'created_at' => now()]);
        DB::table('plants')->updateOrInsert(['plant_code' => 'PLT-A'], ['plant_name' => 'Plant A', 'updated_at' => now(), 'created_at' => now()]);

        for ($i=1; $i<=10; $i++) {
            DB::table('suppliers')->updateOrInsert(
                ['supplier_code' => sprintf('SUP%03d', $i)],
                ['supplier_name' => 'Supplier '.$i, 'status' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
PHP

echo "[3/6] Create Supplier module (controller/view/routes)..."
cat > app/Http/Controllers/SupplierController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = DB::table('suppliers')->orderByDesc('id')->paginate(20);
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_code' => 'required',
            'supplier_name' => 'required',
        ], ['required' => ':attribute wajib diisi.']);

        DB::table('suppliers')->updateOrInsert([
            'supplier_code' => $validated['supplier_code'],
        ], [
            'supplier_name' => $validated['supplier_name'],
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'status' => true,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier tersimpan.');
    }
}
PHP

mkdir -p resources/views/suppliers
cat > resources/views/suppliers/index.blade.php <<'BLADE'
<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Master Supplier</h2></x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <form method="POST" action="{{ route('suppliers.store') }}" class="bg-white p-4 rounded shadow grid grid-cols-1 md:grid-cols-4 gap-3">
                @csrf
                <input class="border rounded px-3 py-2" name="supplier_code" placeholder="Kode Supplier" required>
                <input class="border rounded px-3 py-2" name="supplier_name" placeholder="Nama Supplier" required>
                <input class="border rounded px-3 py-2" name="email" placeholder="Email">
                <button class="bg-blue-600 text-white rounded px-4 py-2">Simpan</button>
            </form>
            <div class="bg-white p-4 rounded shadow">
                <table class="w-full text-sm border">
                    <thead><tr><th class="border p-2">Kode</th><th class="border p-2">Nama</th></tr></thead>
                    <tbody>
                    @foreach($suppliers as $s)
                        <tr><td class="border p-2">{{ $s->supplier_code }}</td><td class="border p-2">{{ $s->supplier_name }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
BLADE

cat > routes/web.php <<'PHP'
<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
PHP

echo "[4/6] Run migration safely..."
php artisan migrate

echo "[5/6] Seed master defaults..."
php artisan db:seed --class=MasterDataSeeder

echo "[6/6] Step 2 finished. Open /suppliers"
