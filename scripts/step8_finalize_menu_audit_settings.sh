#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${1:-erp-monitoring-po}"
if [ ! -d "$APP_DIR" ] || [ ! -f "$APP_DIR/artisan" ]; then
  echo "[ERROR] project Laravel tidak valid: $APP_DIR"
  exit 1
fi
cd "$APP_DIR"

echo "[1/5] Ensure audit/settings data structures..."
cat > database/migrations/2026_01_06_000001_ensure_audit_settings_tables.php <<'MIG'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('module')->nullable();
                $table->unsignedBigInteger('record_id')->nullable();
                $table->string('action')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
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

echo "[2/5] Write controllers + views..."
cat > app/Http/Controllers/SettingsController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $allowOver = DB::table('settings')->where('key', 'allow_over_receipt')->value('value') ?? '0';
        return view('settings.index', compact('allowOver'));
    }

    public function update(Request $request)
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'allow_over_receipt'],
            ['value' => $request->boolean('allow_over_receipt') ? '1' : '0', 'updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', 'Settings berhasil disimpan.');
    }
}
PHP

cat > app/Http/Controllers/AuditTrailController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AuditTrailController extends Controller
{
    public function index()
    {
        $rows = DB::table('audit_logs')->orderByDesc('id')->paginate(50);
        return view('audit.index', compact('rows'));
    }
}
PHP

mkdir -p resources/views/settings resources/views/audit
cat > resources/views/settings/index.blade.php <<'BLADE'
@extends('layouts.erp')
@php($title='Settings')
@php($header='System Settings')
@section('content')
<div class="card card-primary card-outline">
  <div class="card-body">
    <form method="POST" action="{{ route('settings.update') }}">@csrf
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" value="1" name="allow_over_receipt" id="allow_over_receipt" {{ $allowOver == '1' ? 'checked' : '' }}>
        <label class="form-check-label" for="allow_over_receipt">Izinkan Over Receipt</label>
      </div>
      <button class="btn btn-primary">Simpan</button>
    </form>
  </div>
</div>
@endsection
BLADE

cat > resources/views/audit/index.blade.php <<'BLADE'
@extends('layouts.erp')
@php($title='Audit Trail')
@php($header='Audit Trail')
@section('content')
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>Waktu</th><th>Module</th><th>Aksi</th><th>User</th><th>IP</th></tr></thead><tbody>@forelse($rows as $r)<tr><td>{{ $r->created_at }}</td><td>{{ $r->module }}</td><td>{{ $r->action }}</td><td>{{ $r->user_id }}</td><td>{{ $r->ip_address }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted">Belum ada audit log</td></tr>@endforelse</tbody></table></div></div>
@endsection
BLADE

echo "[3/5] Rewrite ERP layout menu (guaranteed full menu update)..."
cat > resources/views/layouts/erp.blade.php <<'BLADE'
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'ERP Monitoring PO' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>.main-sidebar{background-color:#1f2d3d!important}.content-wrapper{background:#f4f6f9}</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li></ul>
    <ul class="navbar-nav ms-auto">
        <li class="nav-item d-flex align-items-center me-3 text-muted small">{{ auth()->user()->email ?? 'Guest' }}</li>
        @auth
        <li class="nav-item"><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary">Logout</button></form></li>
        @endauth
    </ul>
</nav>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('dashboard') }}" class="brand-link"><span class="brand-text font-weight-light">DIGITALISASI PO RECEIVING</span></a>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="nav-icon fas fa-gauge"></i><p>Dashboard</p></a></li>

                <li class="nav-header">MASTER DATA</li>
                <li class="nav-item"><a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><i class="nav-icon fas fa-truck-field"></i><p>Master Supplier</p></a></li>
                <li class="nav-item"><a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}"><i class="nav-icon fas fa-tags"></i><p>Master Item</p></a></li>
                <li class="nav-item"><a href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}"><i class="nav-icon fas fa-ruler-combined"></i><p>Master Unit</p></a></li>
                <li class="nav-item"><a href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}"><i class="nav-icon fas fa-warehouse"></i><p>Master Warehouse</p></a></li>
                <li class="nav-item"><a href="{{ route('plants.index') }}" class="nav-link {{ request()->routeIs('plants.*') ? 'active' : '' }}"><i class="nav-icon fas fa-industry"></i><p>Master Plant</p></a></li>

                <li class="nav-header">TRANSAKSI</li>
                <li class="nav-item"><a href="{{ route('po.index') }}" class="nav-link {{ request()->routeIs('po.*') ? 'active' : '' }}"><i class="nav-icon fas fa-file-invoice"></i><p>Purchase Order</p></a></li>
                <li class="nav-item"><a href="{{ route('shipments.index') }}" class="nav-link {{ request()->routeIs('shipments.*') ? 'active' : '' }}"><i class="nav-icon fas fa-ship"></i><p>Shipment Tracking</p></a></li>
                <li class="nav-item"><a href="{{ route('receiving.index') }}" class="nav-link {{ request()->routeIs('receiving.*') ? 'active' : '' }}"><i class="nav-icon fas fa-box-open"></i><p>Goods Receiving</p></a></li>

                <li class="nav-header">MONITORING</li>
                <li class="nav-item"><a href="{{ route('traceability.index') }}" class="nav-link {{ request()->routeIs('traceability.*') ? 'active' : '' }}"><i class="nav-icon fas fa-magnifying-glass"></i><p>Traceability</p></a></li>
                <li class="nav-item"><a href="{{ route('reports.outstanding') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="nav-icon fas fa-chart-column"></i><p>Reports</p></a></li>

                <li class="nav-header">KONFIGURASI</li>
                <li class="nav-item"><a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="nav-icon fas fa-gears"></i><p>Settings</p></a></li>
                <li class="nav-item"><a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}"><i class="nav-icon fas fa-clock-rotate-left"></i><p>Audit Trail</p></a></li>
            </ul>
        </nav>
    </div>
</aside>

<div class="content-wrapper">
    <section class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0">{{ $header ?? 'ERP Monitoring' }}</h1></div></div></div></section>
    <section class="content"><div class="container-fluid">@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif @yield('content')</div></section>
</div>

<footer class="main-footer text-sm"><strong>ERP Monitoring PO</strong> - Internal System</footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
BLADE

echo "[4/5] Migrate + seed settings default"
php artisan migrate
php artisan tinker --execute="\Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(['key'=>'allow_over_receipt'],['value'=>'0','created_at'=>now(),'updated_at'=>now()]);"

echo "[5/5] Step 8 complete. Menu full update applied."
