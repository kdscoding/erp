<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Portal Dokumen BC 4.0 - Internal' }}</title>
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <style>
        :root{--bc-blue:#2c2a4a;--bc-blue-2:#5a3d7a;--bc-bg:#f4f2f8;--bc-line:#d9d2e6;}
        body{font-size:12.5px;background:var(--bc-bg)}
        .main-header.navbar{background:linear-gradient(90deg,var(--bc-blue),var(--bc-blue-2));color:#fff;border-bottom:0}
        .main-header .nav-link,.main-header .small{color:#fff!important;text-shadow:0 1px 0 rgba(0,0,0,.15)}
        .main-sidebar{background:#2a2340!important}
        .brand-link{background:#211a35!important;border-bottom:1px solid rgba(255,255,255,.12)!important;padding:.75rem .9rem}
        .brand-text{color:#fff!important;font-weight:700!important;font-size:13px;letter-spacing:.3px}
        .content-wrapper{background:var(--bc-bg)}
        .content-header{padding:.6rem .5rem .2rem}
        .content-header h1{font-size:17px;font-weight:700;color:#3a2e58}
        .bc-sub{font-size:11px;color:#6a5d87}
        .bc-ribbon{background:#efe9f7;border:1px solid #d6c8e8;border-radius:4px;padding:4px 10px;font-size:11px;color:#5c467f}
        .card{border:1px solid var(--bc-line);box-shadow:none;border-radius:4px}
        .card-header{background:#f8f5fc;border-bottom:1px solid var(--bc-line);padding:.5rem .75rem}
        .card-title{font-size:12px;font-weight:700;color:#4d3b6d;text-transform:uppercase;letter-spacing:.2px}
        .table thead th{background:#ece5f6;border-bottom:1px solid var(--bc-line);font-size:10.8px;text-transform:uppercase;letter-spacing:.4px}
        .table td,.table th{padding:.42rem .55rem;vertical-align:middle}
        .btn{font-size:12px;padding:.32rem .58rem}
        .badge{font-size:10.5px}
        .nav-sidebar .nav-link{font-size:12.5px;padding:.42rem .65rem;color:#e9f1ff!important}
        .nav-sidebar .nav-link.active{background:#ef8f2f!important;color:#1f1a2e!important;font-weight:700}
        .nav-header{font-size:10px;color:#f0e8ff!important;letter-spacing:.7px;text-transform:uppercase;font-weight:700}
        .footer-note{font-size:11px;color:#efe4ff}
        .bc-chip{background:#ffffff33;border:1px solid #ffffff77;padding:2px 9px;border-radius:99px;font-size:10.5px}
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<div class="wrapper">
    @php
        $currentUser = auth()->user();
        $currentUser?->loadMissing('roles');
        $roleSlug = $currentUser?->primaryRoleSlug();
        $roleLabel = match ($roleSlug) {
            'administrator' => 'Administrator',
            'staff' => 'Staff',
            'supervisor' => 'Supervisor',
            default => 'Tanpa Role',
        };
    @endphp
    <nav class="main-header navbar navbar-expand">
        <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li></ul>
        <ul class="navbar-nav ml-auto align-items-center">
            <li class="nav-item mr-3"><span class="bc-chip">SIMULASI CEISA BEACUKAI - DARK THEME</span></li>
            @auth
            <li class="nav-item mr-3"><span class="bc-chip">{{ $roleLabel }}</span></li>
            @endauth
            <li class="nav-item mr-3 small">{{ auth()->user()->nik ?? '-' }} | {{ auth()->user()->email ?? 'Guest' }}</li>
            @auth
            <li class="nav-item"><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-light">Logout</button></form></li>
            @endauth
        </ul>
    </nav>

    <aside class="main-sidebar elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link text-center"><span class="brand-text">BC 4.0 MONITORING INTERNAL</span></a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                    <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="nav-icon fas fa-gauge"></i><p>Dashboard</p></a></li>
                    @if($currentUser?->hasAnyRole(['administrator', 'staff']))
                    <li class="nav-header">Referensi Master</li>
                    <li class="nav-item"><a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><i class="nav-icon fas fa-truck"></i><p>Data Supplier</p></a></li>
                    <li class="nav-item"><a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}"><i class="nav-icon fas fa-tags"></i><p>Data Barang</p></a></li>
                    <li class="nav-item"><a href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}"><i class="nav-icon fas fa-ruler"></i><p>Data Satuan</p></a></li>
                    <li class="nav-item"><a href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}"><i class="nav-icon fas fa-warehouse"></i><p>Data Gudang</p></a></li>
                    <li class="nav-item"><a href="{{ route('plants.index') }}" class="nav-link {{ request()->routeIs('plants.*') ? 'active' : '' }}"><i class="nav-icon fas fa-industry"></i><p>Data Plant</p></a></li>
                    @endif
                    @if($currentUser?->hasAnyRole(['administrator', 'staff', 'supervisor']))
                    <li class="nav-header">Dokumen</li>
                    <li class="nav-item"><a href="{{ route('po.index') }}" class="nav-link {{ request()->routeIs('po.*') ? 'active' : '' }}"><i class="nav-icon fas fa-file-alt"></i><p>Dokumen PO</p></a></li>
                    @endif
                    @if($currentUser?->hasAnyRole(['administrator', 'staff']))
                    <li class="nav-item"><a href="{{ route('shipments.index') }}" class="nav-link {{ request()->routeIs('shipments.*') ? 'active' : '' }}"><i class="nav-icon fas fa-ship"></i><p>Dokumen Shipment</p></a></li>
                    <li class="nav-item"><a href="{{ route('receiving.index') }}" class="nav-link {{ request()->routeIs('receiving.*') ? 'active' : '' }}"><i class="nav-icon fas fa-box-open"></i><p>Dokumen Receiving</p></a></li>
                    @endif
                    @if($currentUser?->hasAnyRole(['administrator', 'staff', 'supervisor']))
                    <li class="nav-header">Monitoring & Audit</li>
                    <li class="nav-item"><a href="{{ route('monitoring') }}" class="nav-link {{ request()->routeIs('monitoring') ? 'active' : '' }}"><i class="nav-icon fas fa-eye"></i><p>Monitoring Item</p></a></li>
                    <li class="nav-item"><a href="{{ route('traceability.index') }}" class="nav-link {{ request()->routeIs('traceability.*') ? 'active' : '' }}"><i class="nav-icon fas fa-search"></i><p>Traceability</p></a></li>
                    <li class="nav-item"><a href="{{ route('reports.outstanding') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="nav-icon fas fa-chart-bar"></i><p>Laporan Outstanding</p></a></li>
                    <li class="nav-item"><a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}"><i class="nav-icon fas fa-history"></i><p>Audit Trail</p></a></li>
                    @endif
                    @if($currentUser?->hasRole('administrator'))
                    <li class="nav-header">Administrasi</li>
                    <li class="nav-item"><a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') && !request()->routeIs('users.*') ? 'active' : '' }}"><i class="nav-icon fas fa-cogs"></i><p>Parameter Sistem</p></a></li>
                    <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="nav-icon fas fa-users"></i><p>Daftar User</p></a></li>
                    @endif
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row g-2 align-items-center mb-1">
                    <div class="col-md-7">
                        <h1 class="m-0">{{ $header ?? 'Portal Dokumen BC 4.0 Internal' }}</h1>
                        <div class="bc-sub">Monitoring Purchase Order, Shipment, dan Receiving Material Label</div>
                    </div>
                    <div class="col-md-5 text-md-right">
                        <span class="bc-ribbon">Tanggal Sistem: {{ now()->timezone('Asia/Jakarta')->format('d-m-Y H:i') }} WIB</span>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div> @endif
                @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div> @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer"
        style="background:#2a2340;border-top:none;"><span class="footer-note"><strong>Portal BC 4.0 Internal</strong> -
        Tampilan terinspirasi CEISA untuk kebutuhan operasional internal</span></footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function() {
            $('table.data-table').each(function() {
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable({
                        paging: false,
                        info: false,
                        ordering: true,
                        language: {
                            search: "Cari:",
                            zeroRecords: "Data tidak ditemukan"
                        }
                    });
                }
            });
        });
    </script>
    </body>

</html>
