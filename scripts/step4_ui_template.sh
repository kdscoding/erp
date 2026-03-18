#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${1:-erp-monitoring-po}"

if [ ! -d "$APP_DIR" ] || [ ! -f "$APP_DIR/artisan" ]; then
  echo "[ERROR] project Laravel tidak valid: $APP_DIR"
  exit 1
fi

cd "$APP_DIR"

echo "[1/5] Apply AdminLTE-adapted ERP layout..."
mkdir -p resources/views/layouts resources/views/dashboard resources/views/suppliers resources/views/po

cat > resources/views/layouts/erp.blade.php <<'BLADE'
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'ERP Monitoring PO' }}</title>

    <!-- AdminLTE dependencies (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        .brand-link .brand-text { font-size: .9rem; font-weight: 700; }
        .content-wrapper { background: #f4f6f9; }
        .small-box .icon > i { font-size: 2rem; top: 12px; }
        .table th { white-space: nowrap; }
        .main-sidebar { background-color: #1f2d3d !important; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item d-flex align-items-center me-3 text-muted small">
                {{ auth()->user()->email ?? 'Guest' }}
            </li>
            @auth
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="btn btn-sm btn-outline-secondary">Logout</button>
                </form>
            </li>
            @endauth
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light">DIGITALISASI PO RECEIVING</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-gauge"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-truck-field"></i><p>Master Supplier</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('po.index') }}" class="nav-link {{ request()->routeIs('po.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-invoice"></i><p>Purchase Order</p>
                        </a>
                    </li>
                    <li class="nav-header">MODUL BERIKUTNYA</li>
                    <li class="nav-item"><a href="#" class="nav-link disabled"><i class="nav-icon fas fa-ship"></i><p>Shipment Tracking</p></a></li>
                    <li class="nav-item"><a href="#" class="nav-link disabled"><i class="nav-icon fas fa-box-open"></i><p>Goods Receiving</p></a></li>
                    <li class="nav-item"><a href="#" class="nav-link disabled"><i class="nav-icon fas fa-magnifying-glass"></i><p>Traceability</p></a></li>
                    <li class="nav-item"><a href="#" class="nav-link disabled"><i class="nav-icon fas fa-chart-column"></i><p>Reports</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ $header ?? 'ERP Monitoring' }}</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer text-sm">
        <strong>ERP Monitoring PO</strong> - Internal System
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
BLADE

echo "[2/5] Update dashboard to AdminLTE cards..."
cat > resources/views/dashboard.blade.php <<'BLADE'
@extends('layouts.erp')

@php($title='Dashboard ERP')
@php($header='Dashboard Monitoring PO')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ \Illuminate\Support\Facades\DB::table('purchase_orders')->whereNotIn('status', ['Closed','Cancelled'])->count() }}</h3>
                <p>Total Open PO</p>
            </div>
            <div class="icon"><i class="fas fa-file-invoice"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ \Illuminate\Support\Facades\DB::table('purchase_orders')->where('status','Draft')->count() }}</h3>
                <p>Draft PO</p>
            </div>
            <div class="icon"><i class="fas fa-pen"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ \Illuminate\Support\Facades\DB::table('suppliers')->count() }}</h3>
                <p>Total Supplier</p>
            </div>
            <div class="icon"><i class="fas fa-truck"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ \Illuminate\Support\Facades\DB::table('items')->count() }}</h3>
                <p>Total Item</p>
            </div>
            <div class="icon"><i class="fas fa-tags"></i></div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Ringkasan Implementasi</h3></div>
    <div class="card-body">
        <ul class="mb-0">
            <li>Foundation + Auth + Role</li>
            <li>Master Data</li>
            <li>Purchase Order Basic</li>
            <li>UI Template AdminLTE (adapted)</li>
        </ul>
    </div>
</div>
@endsection
BLADE

echo "[3/5] Update supplier and PO pages to AdminLTE components..."
cat > resources/views/suppliers/index.blade.php <<'BLADE'
@extends('layouts.erp')

@php($title='Master Supplier')
@php($header='Master Data Supplier')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header"><h3 class="card-title">Input Supplier</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('suppliers.store') }}" class="row g-2">
            @csrf
            <div class="col-md-2"><input class="form-control" name="supplier_code" placeholder="Kode Supplier" required></div>
            <div class="col-md-4"><input class="form-control" name="supplier_name" placeholder="Nama Supplier" required></div>
            <div class="col-md-3"><input class="form-control" name="email" placeholder="Email"></div>
            <div class="col-md-3"><button class="btn btn-primary w-100">Simpan Supplier</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar Supplier</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap mb-0">
            <thead><tr><th>Kode</th><th>Nama</th><th>Email</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($suppliers as $s)
                <tr>
                    <td>{{ $s->supplier_code }}</td>
                    <td>{{ $s->supplier_name }}</td>
                    <td>{{ $s->email }}</td>
                    <td><span class="badge bg-success">Aktif</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">Belum ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
BLADE

cat > resources/views/po/index.blade.php <<'BLADE'
@extends('layouts.erp')

@php($title='Purchase Order')
@php($header='Purchase Order Monitoring')

@section('content')
<div class="mb-3">
    <a href="{{ route('po.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat PO</a>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar Purchase Order</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap mb-0">
            <thead><tr><th>PO Number</th><th>PO Date</th><th>Supplier</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($rows as $r)
                <tr>
                    <td>{{ $r->po_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->po_date)->format('d-m-Y') }}</td>
                    <td>{{ $r->supplier_name }}</td>
                    <td><span class="badge bg-secondary">{{ $r->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">Belum ada PO</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
BLADE

cat > resources/views/po/create.blade.php <<'BLADE'
@extends('layouts.erp')

@php($title='Buat PO')
@php($header='Create Purchase Order')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header"><h3 class="card-title">Form Purchase Order</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('po.store') }}" class="row g-3">
            @csrf
            <div class="col-md-3"><label class="form-label">Tanggal PO</label><input type="date" class="form-control" name="po_date" required></div>
            <div class="col-md-5">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select" required>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->supplier_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Item</label>
                <select name="item_id" class="form-select" required>
                    @foreach($items as $i)
                    <option value="{{ $i->id }}">{{ $i->item_code }} - {{ $i->item_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Ordered Qty</label><input type="number" step="0.01" class="form-control" name="ordered_qty" required></div>
            <div class="col-md-9"><label class="form-label">Catatan</label><input class="form-control" name="notes"></div>
            <div class="col-12"><button class="btn btn-success">Simpan PO</button></div>
        </form>
    </div>
</div>
@endsection
BLADE

echo "[4/5] Keep current routes/controllers and integrate with new template"
echo "[5/5] Done. AdminLTE-adapted ERP template applied"
