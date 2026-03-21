<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Labeling internal Monitoring' }}</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('lemon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('lemon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('lemon/favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('lemon/favicon.ico') }}">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <style>
        :root{
            --lemon-yellow:#f1d93b;
            --lemon-yellow-soft:#fff4a8;
            --lemon-green:#9ecb3c;
            --lemon-green-deep:#6f9628;
            --lemon-ink:#304218;
            --lemon-olive:#566d2a;
            --lemon-bg:#f7f8ea;
            --lemon-bg-soft:#fcfced;
            --lemon-line:#dfe6b8;
            --lemon-line-strong:#ccd88a;
            --lemon-accent:#e8f18a;
        }
        body{
            font-size:12.5px;
            background:
                radial-gradient(circle at top left, rgba(255, 225, 85, 0.14), transparent 22%),
                radial-gradient(circle at top right, rgba(158, 203, 60, 0.12), transparent 20%),
                var(--lemon-bg);
            color:var(--lemon-ink)
        }
        a{color:var(--lemon-green-deep)}
        a:hover{color:#59781e}
        .main-header.navbar{
            background:linear-gradient(90deg,#738f27,#9ecb3c 55%,#d8e85b);
            color:#21300b;
            border-bottom:1px solid rgba(86,109,42,.12)
        }
        .main-header .nav-link,.main-header .small{color:#21300b!important;text-shadow:none}
        .main-sidebar{background:linear-gradient(180deg,#354a18 0%,#2a3a13 100%)!important}
        .brand-link{background:linear-gradient(90deg,#2d4111,#425d18)!important;border-bottom:1px solid rgba(255,255,255,.12)!important;padding:.7rem .9rem}
        .brand-link .brand-image{float:none;max-height:34px;margin-left:0;margin-right:.55rem;margin-top:0;opacity:.95}
        .brand-text{color:#fff!important;font-weight:700!important;font-size:13px;letter-spacing:.3px;display:inline-flex;flex-direction:column;line-height:1.1;text-align:left}
        .brand-text small{font-size:10px;font-weight:500;letter-spacing:.6px;color:#dceca7}
        .content-wrapper{background:transparent}
        .content-header{padding:.6rem .5rem .2rem}
        .content-header h1{font-size:17px;font-weight:700;color:var(--lemon-ink)}
        .bc-sub{font-size:11px;color:#6f7e48}
        .bc-ribbon{background:#f9fbcf;border:1px solid var(--lemon-line-strong);border-radius:999px;padding:4px 10px;font-size:11px;color:var(--lemon-olive)}
        .card{border:1px solid var(--lemon-line);box-shadow:0 .4rem 1rem rgba(119,136,60,.06);border-radius:10px;background:rgba(255,255,255,.92)}
        .card-header{background:linear-gradient(180deg,var(--lemon-bg-soft),#f4f7d8);border-bottom:1px solid var(--lemon-line);padding:.6rem .85rem}
        .card-title{font-size:12px;font-weight:700;color:var(--lemon-olive);text-transform:uppercase;letter-spacing:.2px}
        .table thead th{background:#f2f6cf;border-bottom:1px solid var(--lemon-line);font-size:10.8px;text-transform:uppercase;letter-spacing:.4px;color:#5f7331}
        .table td,.table th{padding:.42rem .55rem;vertical-align:middle}
        .table-hover tbody tr:hover{background:rgba(241,217,59,.08)}
        .btn{font-size:12px;padding:.32rem .58rem}
        .badge{font-size:10.5px}
        .btn-primary{background:linear-gradient(135deg,#bfd730 0%,#8fc63f 100%);border-color:#8eb93a;color:#23300d;font-weight:700}
        .btn-primary:hover,.btn-primary:focus{background:linear-gradient(135deg,#b1cc22 0%,#82ba34 100%);border-color:#799d2d;color:#1f290d}
        .btn-outline-primary{border-color:#9ecb3c;color:#6d8d1f}
        .btn-outline-primary:hover,.btn-outline-primary:focus{background:#9ecb3c;border-color:#8fb832;color:#22300c}
        .btn-success{background:linear-gradient(135deg,#9ecb3c 0%,#7ead31 100%);border-color:#70992b;color:#22300c}
        .btn-success:hover,.btn-success:focus{background:linear-gradient(135deg,#93c035 0%,#729f2c 100%);border-color:#658c24;color:#1e290b}
        .btn-warning{background:linear-gradient(135deg,#f3de59 0%,#e9c73d 100%);border-color:#d6b330;color:#4a3908}
        .btn-warning:hover,.btn-warning:focus{background:linear-gradient(135deg,#ecd548 0%,#ddb72e 100%);border-color:#c39f22;color:#412f05}
        .btn-light{background:#fffef1;border-color:#dce6b2;color:#5b6e27}
        .btn-light:hover,.btn-light:focus{background:#f8f7df;border-color:#cfdc9b;color:#4d5e1f}
        .form-control,.form-select,.custom-select{
            border-color:#dfe6b8;
            background:#fffef8;
            color:var(--lemon-ink)
        }
        .form-control:focus,.form-select:focus,.custom-select:focus{
            border-color:#b6cf45;
            box-shadow:0 0 0 .12rem rgba(182,207,69,.18);
            background:#fff
        }
        .input-group-text{background:#f7f8dd;border-color:#dfe6b8;color:#73822b}
        .page-link{color:#6d8d1f;border-color:#dfe6b8;background:#fffef5}
        .page-item.active .page-link{background:#9ecb3c;border-color:#8eb93a;color:#21300b}
        .page-link:hover{color:#59781e;background:#f6f8dc;border-color:#d2dd9e}
        .alert-success{background:#eef8d9;border-color:#d5e8a5;color:#48611a}
        .alert-danger{background:#fff2ee;border-color:#efc8bd;color:#8f3f2b}
        .alert-warning{background:#fff8d8;border-color:#ead78f;color:#7e6618}
        .alert-info{background:#f4fbdf;border-color:#dce8a7;color:#5c7130}
        .nav-sidebar .nav-link{font-size:12.5px;padding:.42rem .65rem;color:#e9f1ff!important}
        .nav-sidebar .nav-link.active{background:linear-gradient(90deg,#e4ef7b,#bfd730)!important;color:#21300b!important;font-weight:700}
        .nav-sidebar .nav-link:hover{background:rgba(232,241,138,.15)!important}
        .nav-header{font-size:10px;color:#dceca7!important;letter-spacing:.7px;text-transform:uppercase;font-weight:700}
        .footer-note{font-size:11px;color:#dceca7}
        .bc-chip{background:rgba(255,255,255,.45);border:1px solid rgba(72,97,22,.18);padding:2px 9px;border-radius:99px;font-size:10.5px;color:#354817;font-weight:700}
        .bg-primary{background-color:#9ecb3c!important;color:#21300b!important}
        .bg-success{background-color:#88b93b!important}
        .bg-warning{background-color:#f1d93b!important;color:#4b3b07!important}
        .bg-secondary{background-color:#9aa57a!important}
        .text-muted{color:#7a8660!important}
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
        <a href="{{ route('dashboard') }}" class="brand-link d-flex align-items-center">
            <img src="{{ asset('lemon/apple-touch-icon.png') }}" alt="LEMON Logo" class="brand-image img-circle elevation-2">
            <span class="brand-text">
                <span>LEMON</span>
                <small>Internal Monitoring</small>
            </span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                    <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="nav-icon fas fa-gauge"></i><p>Dashboard</p></a></li>
                    @if ($currentUser?->hasAnyRole(['administrator', 'staff']))
                    <li class="nav-header">Referensi Master</li>
                    <li class="nav-item"><a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><i class="nav-icon fas fa-truck"></i><p>Data Supplier</p></a></li>
                    <li class="nav-item"><a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}"><i class="nav-icon fas fa-tags"></i><p>Data Barang</p></a></li>
                    <li class="nav-item"><a href="{{ route('item-categories.index') }}" class="nav-link {{ request()->routeIs('item-categories.*') ? 'active' : '' }}"><i class="nav-icon fas fa-layer-group"></i><p>Kategori Barang</p></a></li>
                    <li class="nav-item"><a href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}"><i class="nav-icon fas fa-ruler"></i><p>Data Satuan</p></a></li>
                    <li class="nav-item"><a href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}"><i class="nav-icon fas fa-warehouse"></i><p>Data Gudang</p></a></li>
                    <li class="nav-item"><a href="{{ route('plants.index') }}" class="nav-link {{ request()->routeIs('plants.*') ? 'active' : '' }}"><i class="nav-icon fas fa-industry"></i><p>Data Plant</p></a></li>
                    @endif
                    @if ($currentUser?->hasAnyRole(['administrator', 'staff', 'supervisor']))
                    <li class="nav-header">Dokumen</li>
                    <li class="nav-item"><a href="{{ route('po.index') }}" class="nav-link {{ request()->routeIs('po.*') ? 'active' : '' }}"><i class="nav-icon fas fa-file-alt"></i><p>Dokumen PO</p></a></li>
                    @endif
                    @if ($currentUser?->hasAnyRole(['administrator', 'staff']))
                    <li class="nav-item"><a href="{{ route('shipments.process') }}" class="nav-link {{ request()->routeIs('shipments.process') || (request()->routeIs('shipments.index') && request('view', 'draft') !== 'history') ? 'active' : '' }}"><i class="nav-icon fas fa-ship"></i><p>Proses Shipment</p></a></li>
                    <li class="nav-item"><a href="{{ route('shipments.history') }}" class="nav-link {{ request()->routeIs('shipments.history') || (request()->routeIs('shipments.index') && request('view') === 'history') || request()->routeIs('shipments.show') || request()->routeIs('shipments.edit') ? 'active' : '' }}"><i class="nav-icon fas fa-clock-rotate-left"></i><p>Riwayat Shipment</p></a></li>
                    <li class="nav-item"><a href="{{ route('receiving.process') }}" class="nav-link {{ request()->routeIs('receiving.process') || request()->routeIs('receiving.index') ? 'active' : '' }}"><i class="nav-icon fas fa-box-open"></i><p>Proses Receiving</p></a></li>
                    <li class="nav-item"><a href="{{ route('receiving.history') }}" class="nav-link {{ request()->routeIs('receiving.history') || request()->routeIs('receiving.show') ? 'active' : '' }}"><i class="nav-icon fas fa-receipt"></i><p>Riwayat GR</p></a></li>
                    @endif
                    @if ($currentUser?->hasAnyRole(['administrator', 'staff', 'supervisor']))
                    <li class="nav-header">Monitoring & Audit</li>
                    <li class="nav-item"><a href="{{ route('monitoring') }}" class="nav-link {{ request()->routeIs('monitoring') ? 'active' : '' }}"><i class="nav-icon fas fa-eye"></i><p>Monitoring Item</p></a></li>
                    <li class="nav-item"><a href="{{ route('traceability.index') }}" class="nav-link {{ request()->routeIs('traceability.*') ? 'active' : '' }}"><i class="nav-icon fas fa-search"></i><p>Traceability</p></a></li>
                    <li class="nav-item"><a href="{{ route('reports.outstanding') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="nav-icon fas fa-chart-bar"></i><p>Laporan Outstanding</p></a></li>
                    <li class="nav-item"><a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}"><i class="nav-icon fas fa-history"></i><p>Audit Trail</p></a></li>
                    @endif
                    @if ($currentUser?->hasRole('administrator'))
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
                        <h1 class="m-0">{{ $header ?? 'LEMON Internal Monitoring' }}</h1>
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
                                <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer"
        style="background:#314216;border-top:none;"><span class="footer-note"><strong>LEMON Internal Monitoring</strong> -
        Tema lemon untuk kebutuhan operasional internal</span></footer>
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
