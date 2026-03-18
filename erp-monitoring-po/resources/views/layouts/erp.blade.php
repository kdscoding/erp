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
