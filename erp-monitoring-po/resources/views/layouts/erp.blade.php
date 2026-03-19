<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Procurement Monitoring' }}</title>
<<<<<<< ours
<<<<<<< ours
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        :root{--bg:#F8FAFC;--card:#FFFFFF;--text:#1F2937;--muted:#64748B;--line:#E2E8F0;--accent:#6366F1;--success:#34D399;--warning:#FBBF24;--danger:#FB7185;}
=======
=======
>>>>>>> theirs
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        :root{--bg:#F9FAFB;--card:#FFFFFF;--text:#1F2937;--muted:#64748B;--line:#E5E7EB;--accent:#6366F1;--success:#34D399;--warning:#FBBF24;--danger:#FB7185;}
<<<<<<< ours
>>>>>>> theirs
=======
>>>>>>> theirs
        body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:var(--bg);color:var(--text)}
        .main-sidebar{background:#fff!important;border-right:1px solid var(--line)}
        .brand-link{background:#fff!important;border-bottom:1px solid var(--line)!important}
        .brand-text{color:#111827!important;font-weight:700!important}
        .main-header.navbar{background:#fff;border-bottom:1px solid var(--line)}
        .content-wrapper{background:var(--bg)}
        .nav-sidebar .nav-link{color:#334155!important;border-radius:12px;margin:2px 8px;padding:.5rem .7rem}
        .nav-sidebar .nav-link.active{background:#EEF2FF!important;color:#3730A3!important;font-weight:600}
        .nav-header{color:#94A3B8!important;font-size:10px;font-weight:700;letter-spacing:.08em}
<<<<<<< ours
<<<<<<< ours
        .card{background:var(--card);border:1px solid #eef2f7;border-radius:14px;box-shadow:0 8px 30px rgba(15,23,42,.06)}
        .card-header{background:#fff;border-bottom:1px solid #f1f5f9;border-top-left-radius:14px!important;border-top-right-radius:14px!important}
=======
        .card{background:var(--card);border:1px solid var(--line);border-radius:12px;box-shadow:0 6px 20px rgba(15,23,42,.05)}
        .card-header{background:#fff;border-bottom:1px solid var(--line);border-top-left-radius:12px!important;border-top-right-radius:12px!important}
>>>>>>> theirs
=======
        .card{background:var(--card);border:1px solid var(--line);border-radius:12px;box-shadow:0 6px 20px rgba(15,23,42,.05)}
        .card-header{background:#fff;border-bottom:1px solid var(--line);border-top-left-radius:12px!important;border-top-right-radius:12px!important}
>>>>>>> theirs
        .card-title{font-size:13px;font-weight:700;color:#334155}
        .table thead th{background:#F8FAFC;color:#475569;border-bottom:1px solid var(--line);font-size:11px;text-transform:uppercase}
        .table td{vertical-align:middle}
        .btn{border-radius:10px}
        .btn-primary{background:var(--accent);border-color:var(--accent)}
        .main-footer{background:#fff;border-top:1px solid var(--line);color:var(--muted)}
        .helper-text{font-size:11px;color:var(--muted)}
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand">
        <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li></ul>
        <ul class="navbar-nav ms-auto align-items-center">
<<<<<<< ours
<<<<<<< ours
<<<<<<< ours
            <li class="nav-item me-3"><span class="bc-chip">SIMULASI CEISA BEACUKAI - DARK THEME</span></li>
            <li class="nav-item me-3 small">{{ auth()->user()->email ?? 'Guest' }}</li>
            @auth
            <li class="nav-item"><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-light">Logout</button></form></li>
=======
=======
>>>>>>> theirs
=======
>>>>>>> theirs
            <li class="nav-item me-3"><span class="badge" style="background:#EEF2FF;color:#3730A3;">Light UI Mode</span></li>
            <li class="nav-item me-3 small text-muted">{{ auth()->user()->email ?? 'Guest' }}</li>
            @auth
            <li class="nav-item"><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary">Logout</button></form></li>
<<<<<<< ours
<<<<<<< ours
>>>>>>> theirs
=======
>>>>>>> theirs
            @endauth
        </ul>
    </nav>

<<<<<<< ours
<<<<<<< ours
    <aside class="main-sidebar elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link text-center"><span class="brand-text">BC 4.0 MONITORING INTERNAL</span></a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                    <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="nav-icon fas fa-gauge"></i><p>Dashboard</p></a></li>
                    <li class="nav-header">Referensi Master</li>
                    <li class="nav-item"><a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><i class="nav-icon fas fa-truck"></i><p>Data Supplier</p></a></li>
                    <li class="nav-item"><a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}"><i class="nav-icon fas fa-tags"></i><p>Data Barang</p></a></li>
                    <li class="nav-item"><a href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}"><i class="nav-icon fas fa-ruler"></i><p>Data Satuan</p></a></li>
                    <li class="nav-item"><a href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}"><i class="nav-icon fas fa-warehouse"></i><p>Data Gudang</p></a></li>
                    <li class="nav-item"><a href="{{ route('plants.index') }}" class="nav-link {{ request()->routeIs('plants.*') ? 'active' : '' }}"><i class="nav-icon fas fa-industry"></i><p>Data Plant</p></a></li>
                    <li class="nav-header">Dokumen</li>
                    <li class="nav-item"><a href="{{ route('po.index') }}" class="nav-link {{ request()->routeIs('po.*') ? 'active' : '' }}"><i class="nav-icon fas fa-file-alt"></i><p>Dokumen PO</p></a></li>
                    <li class="nav-item"><a href="{{ route('shipments.index') }}" class="nav-link {{ request()->routeIs('shipments.*') ? 'active' : '' }}"><i class="nav-icon fas fa-ship"></i><p>Dokumen Shipment</p></a></li>
                    <li class="nav-item"><a href="{{ route('receiving.index') }}" class="nav-link {{ request()->routeIs('receiving.*') ? 'active' : '' }}"><i class="nav-icon fas fa-box-open"></i><p>Dokumen Receiving</p></a></li>
                    <li class="nav-header">Monitoring & Audit</li>
                    <li class="nav-item"><a href="{{ route('traceability.index') }}" class="nav-link {{ request()->routeIs('traceability.*') ? 'active' : '' }}"><i class="nav-icon fas fa-search"></i><p>Traceability</p></a></li>
                    <li class="nav-item"><a href="{{ route('reports.outstanding') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="nav-icon fas fa-chart-bar"></i><p>Laporan Outstanding</p></a></li>
                    <li class="nav-item"><a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="nav-icon fas fa-cogs"></i><p>Parameter Sistem</p></a></li>
                    <li class="nav-item"><a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}"><i class="nav-icon fas fa-history"></i><p>Audit Trail</p></a></li>
=======
    <aside class="main-sidebar elevation-0">
        <a href="{{ route('dashboard') }}" class="brand-link text-center"><span class="brand-text">Procurement Control</span></a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                    <li class="nav-item"><a title="Ringkasan KPI dan risiko item" href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="nav-icon fas fa-gauge"></i><p>Dashboard</p></a></li>
                    <li class="nav-header">MASTER DATA</li>
                    <li class="nav-item"><a title="Kelola data supplier" href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><i class="nav-icon fas fa-truck"></i><p>Data Supplier</p></a></li>
                    <li class="nav-item"><a title="Kelola data item label/material" href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}"><i class="nav-icon fas fa-tags"></i><p>Data Barang</p></a></li>
                    <li class="nav-item"><a title="Master satuan item" href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}"><i class="nav-icon fas fa-ruler"></i><p>Data Satuan</p></a></li>
                    <li class="nav-item"><a title="Lokasi penerimaan" href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}"><i class="nav-icon fas fa-warehouse"></i><p>Data Gudang</p></a></li>
                    <li class="nav-header">TRANSAKSI</li>
                    <li class="nav-item"><a title="Buat dan monitor Purchase Order" href="{{ route('po.index') }}" class="nav-link {{ request()->routeIs('po.*') ? 'active' : '' }}"><i class="nav-icon fas fa-file-alt"></i><p>Dokumen PO</p></a></li>
                    <li class="nav-item"><a title="Update status pengiriman supplier" href="{{ route('shipments.index') }}" class="nav-link {{ request()->routeIs('shipments.*') ? 'active' : '' }}"><i class="nav-icon fas fa-ship"></i><p>Dokumen Shipment</p></a></li>
                    <li class="nav-item"><a title="Penerimaan barang per item" href="{{ route('receiving.index') }}" class="nav-link {{ request()->routeIs('receiving.*') ? 'active' : '' }}"><i class="nav-icon fas fa-box-open"></i><p>Dokumen Receiving</p></a></li>
=======
    <aside class="main-sidebar elevation-0">
        <a href="{{ route('dashboard') }}" class="brand-link text-center"><span class="brand-text">Procurement Control</span></a>
        <div class="sidebar">
            @php
                $roleSlugs = auth()->check()
                    ? \Illuminate\Support\Facades\DB::table('roles as r')
                        ->join('user_roles as ur', 'ur.role_id', '=', 'r.id')
                        ->where('ur.user_id', auth()->id())
                        ->pluck('r.slug')
                        ->toArray()
                    : [];
                $isAdministrator = in_array('administrator', $roleSlugs, true) || in_array('admin', $roleSlugs, true);
                $isSupervisor = in_array('supervisor', $roleSlugs, true);
                $isReceiver = in_array('receiver', $roleSlugs, true) || in_array('warehouse', $roleSlugs, true);
            @endphp
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                    <li class="nav-item"><a title="Ringkasan KPI dan risiko item" href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="nav-icon fas fa-gauge"></i><p>Dashboard</p></a></li>
                    @if($isAdministrator)
                        <li class="nav-header">MASTER DATA</li>
                        <li class="nav-item"><a title="Kelola data supplier" href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><i class="nav-icon fas fa-truck"></i><p>Data Supplier</p></a></li>
                        <li class="nav-item"><a title="Kelola data item label/material" href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}"><i class="nav-icon fas fa-tags"></i><p>Data Barang</p></a></li>
                        <li class="nav-item"><a title="Master satuan item" href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}"><i class="nav-icon fas fa-ruler"></i><p>Data Satuan</p></a></li>
                        <li class="nav-item"><a title="Lokasi penerimaan" href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}"><i class="nav-icon fas fa-warehouse"></i><p>Data Gudang</p></a></li>
                        <li class="nav-header">ORDER & RECEIVER</li>
                        <li class="nav-item"><a title="Buat dan monitor Purchase Order" href="{{ route('po.index') }}" class="nav-link {{ request()->routeIs('po.*') ? 'active' : '' }}"><i class="nav-icon fas fa-file-alt"></i><p>Dokumen PO</p></a></li>
                        <li class="nav-item"><a title="Update status pengiriman supplier" href="{{ route('shipments.index') }}" class="nav-link {{ request()->routeIs('shipments.*') ? 'active' : '' }}"><i class="nav-icon fas fa-ship"></i><p>Dokumen Shipment</p></a></li>
                        <li class="nav-item"><a title="Penerimaan barang per item" href="{{ route('receiving.index') }}" class="nav-link {{ request()->routeIs('receiving.*') ? 'active' : '' }}"><i class="nav-icon fas fa-box-open"></i><p>Dokumen Receiving</p></a></li>
                        <li class="nav-item"><a title="Pengaturan sistem dan user management" href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="nav-icon fas fa-users-cog"></i><p>User Management</p></a></li>
                    @endif
                    @if($isReceiver)
                        <li class="nav-header">RECEIVER</li>
                        <li class="nav-item"><a title="Input penerimaan barang per item" href="{{ route('receiving.index') }}" class="nav-link {{ request()->routeIs('receiving.*') ? 'active' : '' }}"><i class="nav-icon fas fa-dolly-flatbed"></i><p>Input Receiving</p></a></li>
                    @endif
                    @if($isSupervisor)
                        <li class="nav-header">SUPERVISOR</li>
                        <li class="nav-item"><a title="Monitoring KPI item-level (read only)" href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="nav-icon fas fa-chart-line"></i><p>KPI Monitoring</p></a></li>
                    @endif
>>>>>>> theirs
                    <li class="nav-header">MONITORING</li>
                    <li class="nav-item"><a title="Lacak histori PO sampai receiving" href="{{ route('traceability.index') }}" class="nav-link {{ request()->routeIs('traceability.*') ? 'active' : '' }}"><i class="nav-icon fas fa-search"></i><p>Traceability</p></a></li>
                    <li class="nav-item"><a title="Laporan item/PO outstanding" href="{{ route('reports.outstanding') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="nav-icon fas fa-chart-bar"></i><p>Laporan Outstanding</p></a></li>
                    <li class="nav-item"><a title="Riwayat semua perubahan data" href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}"><i class="nav-icon fas fa-history"></i><p>Audit Trail</p></a></li>
<<<<<<< ours
>>>>>>> theirs
=======
>>>>>>> theirs
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
<<<<<<< ours
<<<<<<< ours
                <div class="row g-2 align-items-center mb-1">
                    <div class="col-md-7">
                        <h1 class="m-0">{{ $header ?? 'Portal Dokumen BC 4.0 Internal' }}</h1>
                        <div class="bc-sub">Monitoring Purchase Order, Shipment, dan Receiving Material Label</div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <span class="bc-ribbon">Tanggal Sistem: {{ now()->timezone('Asia/Jakarta')->format('d-m-Y H:i') }} WIB</span>
                    </div>
                </div>
=======
                <h1 class="m-0" style="font-size:24px;font-weight:700;">{{ $header ?? 'Procurement Dashboard' }}</h1>
                <div class="helper-text">{{ $helper ?? 'Pantau status PO dan pergerakan item secara real-time.' }}</div>
>>>>>>> theirs
=======
                <h1 class="m-0" style="font-size:24px;font-weight:700;">{{ $header ?? 'Procurement Dashboard' }}</h1>
                <div class="helper-text">{{ $helper ?? 'Pantau status PO dan pergerakan item secara real-time.' }}</div>
>>>>>>> theirs
=======
            @endauth
        </ul>
    </nav>

    <aside class="main-sidebar elevation-0">
        <a href="{{ route('dashboard') }}" class="brand-link text-center"><span class="brand-text">Procurement Control</span></a>
        <div class="sidebar">
            @php
                $roleSlugs = auth()->check()
                    ? \Illuminate\Support\Facades\DB::table('roles as r')
                        ->join('user_roles as ur', 'ur.role_id', '=', 'r.id')
                        ->where('ur.user_id', auth()->id())
                        ->pluck('r.slug')
                        ->toArray()
                    : [];
                $isAdministrator = in_array('administrator', $roleSlugs, true) || in_array('admin', $roleSlugs, true);
                $isSupervisor = in_array('supervisor', $roleSlugs, true);
                $isReceiver = in_array('receiver', $roleSlugs, true) || in_array('warehouse', $roleSlugs, true);
            @endphp
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                    <li class="nav-item"><a title="Ringkasan KPI dan risiko item" href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="nav-icon fas fa-gauge"></i><p>Dashboard</p></a></li>
                    @if($isAdministrator)
                        <li class="nav-header">MASTER DATA</li>
                        <li class="nav-item"><a title="Kelola data supplier" href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><i class="nav-icon fas fa-truck"></i><p>Data Supplier</p></a></li>
                        <li class="nav-item"><a title="Kelola data item label/material" href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}"><i class="nav-icon fas fa-tags"></i><p>Data Barang</p></a></li>
                        <li class="nav-item"><a title="Master satuan item" href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}"><i class="nav-icon fas fa-ruler"></i><p>Data Satuan</p></a></li>
                        <li class="nav-item"><a title="Lokasi penerimaan" href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}"><i class="nav-icon fas fa-warehouse"></i><p>Data Gudang</p></a></li>
                        <li class="nav-header">ORDER & RECEIVER</li>
                        <li class="nav-item"><a title="Buat dan monitor Purchase Order" href="{{ route('po.index') }}" class="nav-link {{ request()->routeIs('po.*') ? 'active' : '' }}"><i class="nav-icon fas fa-file-alt"></i><p>Dokumen PO</p></a></li>
                        <li class="nav-item"><a title="Update status pengiriman supplier" href="{{ route('shipments.index') }}" class="nav-link {{ request()->routeIs('shipments.*') ? 'active' : '' }}"><i class="nav-icon fas fa-ship"></i><p>Dokumen Shipment</p></a></li>
                        <li class="nav-item"><a title="Penerimaan barang per item" href="{{ route('receiving.index') }}" class="nav-link {{ request()->routeIs('receiving.*') ? 'active' : '' }}"><i class="nav-icon fas fa-box-open"></i><p>Dokumen Receiving</p></a></li>
                        <li class="nav-item"><a title="Pengaturan sistem dan user management" href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="nav-icon fas fa-users-cog"></i><p>User Management</p></a></li>
                    @endif
                    @if($isReceiver)
                        <li class="nav-header">RECEIVER</li>
                        <li class="nav-item"><a title="Input penerimaan barang per item" href="{{ route('receiving.index') }}" class="nav-link {{ request()->routeIs('receiving.*') ? 'active' : '' }}"><i class="nav-icon fas fa-dolly-flatbed"></i><p>Input Receiving</p></a></li>
                    @endif
                    @if($isSupervisor)
                        <li class="nav-header">SUPERVISOR</li>
                        <li class="nav-item"><a title="Monitoring KPI item-level (read only)" href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="nav-icon fas fa-chart-line"></i><p>KPI Monitoring</p></a></li>
                    @endif
                    <li class="nav-header">MONITORING</li>
                    <li class="nav-item"><a title="Lacak histori PO sampai receiving" href="{{ route('traceability.index') }}" class="nav-link {{ request()->routeIs('traceability.*') ? 'active' : '' }}"><i class="nav-icon fas fa-search"></i><p>Traceability</p></a></li>
                    <li class="nav-item"><a title="Laporan item/PO outstanding" href="{{ route('reports.outstanding') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="nav-icon fas fa-chart-bar"></i><p>Laporan Outstanding</p></a></li>
                    <li class="nav-item"><a title="Riwayat semua perubahan data" href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}"><i class="nav-icon fas fa-history"></i><p>Audit Trail</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="m-0" style="font-size:24px;font-weight:700;">{{ $header ?? 'Procurement Dashboard' }}</h1>
                <div class="helper-text">{{ $helper ?? 'Pantau status PO dan pergerakan item secara real-time.' }}</div>
>>>>>>> theirs
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
<<<<<<< ours
<<<<<<< ours
<<<<<<< ours
                @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
=======
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div> @endif
>>>>>>> theirs
=======
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
>>>>>>> theirs
                @yield('content')
            </div>
        </section>
    </div>
<<<<<<< ours

<<<<<<< ours
    <footer class="main-footer"
        style="background:#2a2340;border-top:none;"><span class="footer-note"><strong>Portal BC 4.0 Internal</strong> -
        Tampilan terinspirasi CEISA untuk kebutuhan operasional internal</span></footer>
    =======
    <footer class="main-footer"><strong>Procurement Control</strong> - Light UI Operational Dashboard</footer>
    >>>>>>> theirs
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    </body>

=======
=======
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @yield('content')
            </div>
        </section>
    </div>
>>>>>>> theirs

    <footer class="main-footer"><strong>Procurement Control</strong> - Light UI Operational Dashboard</footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
>>>>>>> theirs
</html>
