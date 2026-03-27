<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'LEMON Internal Monitoring' }}</title>

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('lemon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('lemon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('lemon/favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('lemon/favicon.ico') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

    <style>
        :root {
            --lemon-yellow: #f1d93b;
            --lemon-yellow-soft: #fff4a8;
            --lemon-green: #9ecb3c;
            --lemon-green-deep: #6f9628;
            --lemon-ink: #304218;
            --lemon-olive: #566d2a;
            --lemon-bg: #f7f8ea;
            --lemon-bg-soft: #fcfced;
            --lemon-line: #dfe6b8;
            --lemon-line-strong: #ccd88a;
            --lemon-accent: #e8f18a;
        }

        body {
            font-size: 12.5px;
            background:
                radial-gradient(circle at top left, rgba(255, 225, 85, 0.10), transparent 22%),
                radial-gradient(circle at top right, rgba(158, 203, 60, 0.08), transparent 20%),
                var(--lemon-bg);
            color: var(--lemon-ink);
        }

        a {
            color: var(--lemon-green-deep);
        }

        a:hover {
            color: #59781e;
            text-decoration: none;
        }

        .content-wrapper {
            background: transparent;
        }

        .content-header {
            padding: .75rem .5rem .35rem;
        }

        .content {
            padding-bottom: 1rem;
        }

        .main-header.navbar {
            background: linear-gradient(90deg, #738f27, #9ecb3c 55%, #d8e85b);
            color: #21300b;
            border-bottom: 1px solid rgba(86, 109, 42, .12);
        }

        .main-header .nav-link,
        .main-header .small {
            color: #21300b !important;
            text-shadow: none;
        }

        .main-sidebar {
            background: linear-gradient(180deg, #354a18 0%, #2a3a13 100%) !important;
        }

        .brand-link {
            background: linear-gradient(90deg, #2d4111, #425d18) !important;
            border-bottom: 1px solid rgba(255, 255, 255, .12) !important;
            padding: .7rem .9rem;
        }

        .brand-link .brand-image {
            float: none;
            max-height: 34px;
            margin-left: 0;
            margin-right: .55rem;
            margin-top: 0;
            opacity: .95;
        }

        .brand-text {
            color: #fff !important;
            font-weight: 700 !important;
            font-size: 13px;
            letter-spacing: .3px;
            display: inline-flex;
            flex-direction: column;
            line-height: 1.1;
            text-align: left;
        }

        .brand-text small {
            font-size: 10px;
            font-weight: 500;
            letter-spacing: .6px;
            color: #dceca7;
        }

        .nav-sidebar .nav-link {
            font-size: 12.5px;
            padding: .42rem .65rem;
            color: #e9f1ff !important;
        }

        .nav-sidebar .nav-link.active {
            background: linear-gradient(90deg, #e4ef7b, #bfd730) !important;
            color: #21300b !important;
            font-weight: 700;
        }

        .nav-sidebar .nav-link:hover {
            background: rgba(232, 241, 138, .15) !important;
        }

        .nav-sidebar .nav-treeview>.nav-item>.nav-link {
            padding-left: 2.45rem;
            font-size: 12.25px;
            color: rgba(233, 241, 255, .88) !important;
            background: transparent !important;
        }

        .nav-sidebar .nav-treeview>.nav-item>.nav-link.active {
            background: rgba(255, 255, 255, .12) !important;
            color: #fff !important;
            font-weight: 700;
        }

        .nav-sidebar .nav-treeview>.nav-item>.nav-link:hover {
            background: rgba(255, 255, 255, .08) !important;
        }

        .nav-sidebar .nav-treeview>.nav-item>.nav-link .nav-icon {
            font-size: .7rem;
            margin-right: .35rem;
        }

        .nav-sidebar .menu-open>.nav-link {
            border-radius: .25rem;
        }

        .nav-header {
            font-size: 10px;
            color: #dceca7 !important;
            letter-spacing: .7px;
            text-transform: uppercase;
            font-weight: 700;
        }

        .page-topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .page-title-wrap {
            min-width: 0;
        }

        .page-title {
            font-size: 1.18rem;
            font-weight: 800;
            color: var(--lemon-ink);
            margin: 0;
            line-height: 1.2;
        }

        .page-subtitle {
            font-size: .84rem;
            color: #6f7e48;
            margin-top: .2rem;
        }

        .page-ribbon {
            background: #f9fbcf;
            border: 1px solid var(--lemon-line-strong);
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
            color: var(--lemon-olive);
            white-space: nowrap;
        }

        .page-shell {
            display: grid;
            gap: 1rem;
        }

        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .page-head-main {
            min-width: 0;
        }

        .page-section-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--lemon-ink);
        }

        .page-section-subtitle {
            margin: .2rem 0 0;
            font-size: .84rem;
            color: #74805f;
        }

        .page-actions {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .summary-chips {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .summary-chip {
            min-width: 130px;
            padding: .78rem .92rem;
            border-radius: 14px;
            border: 1px solid rgba(111, 150, 40, .12);
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 10px 20px rgba(111, 150, 40, .04);
        }

        .summary-chip-label {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #7a8660;
            margin-bottom: .2rem;
        }

        .summary-chip-value {
            font-size: 1.12rem;
            font-weight: 800;
            color: #314216;
            line-height: 1;
        }

        .ui-surface,
        .card {
            border: 1px solid var(--lemon-line);
            border-radius: 18px;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 14px 28px rgba(111, 150, 40, .05);
        }

        .ui-surface-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
            padding: 1rem 1rem 0;
        }

        .ui-surface-title,
        .card-title {
            margin: 0;
            font-size: .98rem;
            font-weight: 800;
            color: #314216;
        }

        .ui-surface-subtitle {
            font-size: .8rem;
            color: #7a8660;
            margin-top: .2rem;
        }

        .ui-surface-body {
            padding: 1rem;
        }

        .card-header {
            background: linear-gradient(180deg, var(--lemon-bg-soft), #f4f7d8);
            border-bottom: 1px solid var(--lemon-line);
            padding: .75rem .95rem;
        }

        .card-body {
            padding: 1rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: .75rem;
            padding: 1rem;
            align-items: end;
        }

        .span-12 { grid-column: span 12; }
        .span-8 { grid-column: span 8; }
        .span-6 { grid-column: span 6; }
        .span-4 { grid-column: span 4; }
        .span-3 { grid-column: span 3; }
        .span-2 { grid-column: span 2; }
        .span-1 { grid-column: span 1; }

        .field-label,
        .form-label {
            display: block;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .02em;
            color: #52603d;
            margin-bottom: .35rem;
        }

        .field-help {
            font-size: .72rem;
            color: #7d866f;
            margin-top: .3rem;
            line-height: 1.3;
        }

        .table-wrap {
            padding: 1rem;
        }

        .ui-table,
        .table {
            margin-bottom: 0;
        }

        .ui-table thead th,
        .table thead th {
            background: #f2f6cf;
            border-bottom: 1px solid var(--lemon-line);
            font-size: .69rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            white-space: nowrap;
            color: #5f7331;
            vertical-align: middle;
        }

        .table td,
        .table th {
            padding: .45rem .55rem;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background: rgba(241, 217, 59, .08);
        }

        .doc-number {
            font-weight: 700;
            color: #314216;
        }

        .doc-meta {
            font-size: .8rem;
            color: #7a8660;
        }

        .action-stack {
            display: flex;
            gap: .35rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .soft-alert {
            border: 1px solid #e7eadf;
            background: #fafcf5;
            border-radius: 14px;
            padding: .85rem 1rem;
            font-size: .82rem;
            color: #566246;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }

        .info-box {
            border: 1px solid #e7eadf;
            border-radius: 14px;
            background: #fafcf5;
            padding: .9rem 1rem;
            height: 100%;
        }

        .info-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #7d866f;
            margin-bottom: .25rem;
        }

        .info-value {
            font-size: .95rem;
            font-weight: 700;
            color: #2f3c1b;
            word-break: break-word;
        }

        .btn {
            font-size: 12px;
            padding: .36rem .62rem;
            border-radius: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #bfd730 0%, #8fc63f 100%);
            border-color: #8eb93a;
            color: #23300d;
            font-weight: 700;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background: linear-gradient(135deg, #b1cc22 0%, #82ba34 100%);
            border-color: #799d2d;
            color: #1f290d;
        }

        .btn-outline-primary {
            border-color: #9ecb3c;
            color: #6d8d1f;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            background: #9ecb3c;
            border-color: #8fb832;
            color: #22300c;
        }

        .btn-success {
            background: linear-gradient(135deg, #9ecb3c 0%, #7ead31 100%);
            border-color: #70992b;
            color: #22300c;
        }

        .btn-success:hover,
        .btn-success:focus {
            background: linear-gradient(135deg, #93c035 0%, #729f2c 100%);
            border-color: #658c24;
            color: #1e290b;
        }

        .btn-light {
            background: #fffef1;
            border-color: #dce6b2;
            color: #5b6e27;
        }

        .btn-light:hover,
        .btn-light:focus {
            background: #f8f7df;
            border-color: #cfdc9b;
            color: #4d5e1f;
        }

        .form-control,
        .form-select,
        .custom-select,
        .form-control-sm,
        .form-select-sm {
            border-color: #dfe6b8;
            background: #fffef8;
            color: var(--lemon-ink);
            min-height: 38px;
            border-radius: 10px;
        }

        .form-control:focus,
        .form-select:focus,
        .custom-select:focus {
            border-color: #b6cf45;
            box-shadow: 0 0 0 .12rem rgba(182, 207, 69, .18);
            background: #fff;
        }

        .input-group-text {
            background: #f7f8dd;
            border-color: #dfe6b8;
            color: #73822b;
        }

        .badge {
            font-size: 10.5px;
        }

        .bg-primary {
            background-color: #9ecb3c !important;
            color: #21300b !important;
        }

        .bg-success {
            background-color: #88b93b !important;
        }

        .bg-warning {
            background-color: #f1d93b !important;
            color: #4b3b07 !important;
        }

        .bg-secondary {
            background-color: #9aa57a !important;
        }

        .page-link {
            color: #6d8d1f;
            border-color: #dfe6b8;
            background: #fffef5;
        }

        .page-item.active .page-link {
            background: #9ecb3c;
            border-color: #8eb93a;
            color: #21300b;
        }

        .page-link:hover {
            color: #59781e;
            background: #f6f8dc;
            border-color: #d2dd9e;
        }

        .alert-success {
            background: #eef8d9;
            border-color: #d5e8a5;
            color: #48611a;
        }

        .alert-danger {
            background: #fff2ee;
            border-color: #efc8bd;
            color: #8f3f2b;
        }

        .alert-warning {
            background: #fff8d8;
            border-color: #ead78f;
            color: #7e6618;
        }

        .alert-info {
            background: #f4fbdf;
            border-color: #dce8a7;
            color: #5c7130;
        }

        .text-muted {
            color: #7a8660 !important;
        }

        .footer-note {
            font-size: 11px;
            color: #dceca7;
        }

        .bc-chip {
            background: rgba(255, 255, 255, .45);
            border: 1px solid rgba(72, 97, 22, .18);
            padding: 2px 9px;
            border-radius: 99px;
            font-size: 10.5px;
            color: #354817;
            font-weight: 700;
        }

        @media (max-width: 991.98px) {
            .span-8,
            .span-6,
            .span-4,
            .span-3,
            .span-2,
            .span-1 {
                grid-column: span 12;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .summary-chip {
                flex: 1 1 calc(50% - .75rem);
                min-width: 0;
            }

            .page-ribbon {
                white-space: normal;
            }
        }

        @media (max-width: 575.98px) {
            .summary-chip {
                flex: 1 1 100%;
            }
        }
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

            $currentRouteName = request()->route()?->getName() ?? '';
            $shipmentView = (string) (request()->route()?->defaults['view'] ?? request('view', 'worklist'));
            $receivingMode = (string) (request()->route()?->defaults['mode'] ?? request('mode', 'process'));

            $shipmentStatus = null;
            if (request()->routeIs('shipments.show') || request()->routeIs('shipments.edit')) {
                $shipmentId = (int) request()->route('id');
                if ($shipmentId > 0) {
                    $shipmentStatus = \Illuminate\Support\Facades\DB::table('shipments')
                        ->where('id', $shipmentId)
                        ->value('status');
                }
            }

            $shipmentMenuOpen = str_starts_with($currentRouteName, 'shipments.');
            $receivingMenuOpen = str_starts_with($currentRouteName, 'receiving.');

            $shipmentWorklistActive =
                (($currentRouteName === 'shipments.index' || $currentRouteName === 'shipments.process') && $shipmentView === 'worklist') ||
                (request()->routeIs('shipments.show') && in_array($shipmentStatus, [
                    \App\Support\DocumentTermCodes::SHIPMENT_SHIPPED,
                    \App\Support\DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED,
                ], true));

            $shipmentDraftActive =
                $currentRouteName === 'shipments.create' ||
                request()->routeIs('shipments.edit') ||
                (($currentRouteName === 'shipments.index' || $currentRouteName === 'shipments.process') && $shipmentView === 'draft') ||
                (request()->routeIs('shipments.show') && $shipmentStatus === \App\Support\DocumentTermCodes::SHIPMENT_DRAFT);

            $shipmentArchiveActive =
                $currentRouteName === 'shipments.history' ||
                (($currentRouteName === 'shipments.index' || $currentRouteName === 'shipments.process') && $shipmentView === 'history') ||
                (request()->routeIs('shipments.show') && in_array($shipmentStatus, [
                    \App\Support\DocumentTermCodes::SHIPMENT_RECEIVED,
                    \App\Support\DocumentTermCodes::SHIPMENT_CANCELLED,
                ], true));

            $receivingProcessActive =
                ($currentRouteName === 'receiving.index' || $currentRouteName === 'receiving.process') && $receivingMode === 'process';

            $receivingHistoryActive =
                $currentRouteName === 'receiving.history' ||
                (request()->routeIs('receiving.index') && $receivingMode === 'history') ||
                request()->routeIs('receiving.show');
        @endphp

        <nav class="main-header navbar navbar-expand">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto align-items-center">
                @auth
                    <li class="nav-item mr-3"><span class="bc-chip">{{ $roleLabel }}</span></li>
                @endauth
                <li class="nav-item mr-3 small">{{ auth()->user()->nik ?? '-' }} | {{ auth()->user()->email ?? 'Guest' }}</li>
                @auth
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-sm btn-light">Logout</button>
                        </form>
                    </li>
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
                    <ul class="nav nav-pills nav-sidebar flex-column"
                        data-widget="treeview"
                        role="menu"
                        data-accordion="false">

                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}"
                                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-gauge"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        @if ($currentUser?->hasAnyRole(['administrator', 'staff']))
                            <li class="nav-header">Master Data</li>

                            <li class="nav-item">
                                <a href="{{ route('suppliers.index') }}"
                                    class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-truck"></i>
                                    <p>Suppliers</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('items.index') }}"
                                    class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-tags"></i>
                                    <p>Items</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('item-categories.index') }}"
                                    class="nav-link {{ request()->routeIs('item-categories.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-layer-group"></i>
                                    <p>Categories</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('units.index') }}"
                                    class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-ruler"></i>
                                    <p>Units</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('warehouses.index') }}"
                                    class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-warehouse"></i>
                                    <p>Warehouses</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('plants.index') }}"
                                    class="nav-link {{ request()->routeIs('plants.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-industry"></i>
                                    <p>Plants</p>
                                </a>
                            </li>
                        @endif

                        @if ($currentUser?->hasAnyRole(['administrator', 'staff', 'supervisor']))
                            <li class="nav-header">Operational</li>

                            <li class="nav-item">
                                <a href="{{ route('po.index') }}"
                                    class="nav-link {{ request()->routeIs('po.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p>Purchase Orders</p>
                                </a>
                            </li>
                        @endif

                        @if ($currentUser?->hasAnyRole(['administrator', 'staff']))
                            <li class="nav-item has-treeview {{ $shipmentMenuOpen ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ $shipmentMenuOpen ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-ship"></i>
                                    <p>
                                        Shipment
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>

                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('shipments.index') }}"
                                            class="nav-link {{ $shipmentWorklistActive ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Worklist</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('shipments.create') }}"
                                            class="nav-link {{ $shipmentDraftActive ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Create Draft</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('shipments.history') }}"
                                            class="nav-link {{ $shipmentArchiveActive ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Archive</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="nav-item has-treeview {{ $receivingMenuOpen ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ $receivingMenuOpen ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-box-open"></i>
                                    <p>
                                        Receiving
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>

                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('receiving.process') }}"
                                            class="nav-link {{ $receivingProcessActive ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Open Receiving</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('receiving.history') }}"
                                            class="nav-link {{ $receivingHistoryActive ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>History</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if ($currentUser?->hasAnyRole(['administrator', 'staff', 'supervisor']))
                            <li class="nav-header">Summary & Reports</li>

                            <li class="nav-item">
                                <a href="{{ route('summary.po') }}"
                                    class="nav-link {{ request()->routeIs('summary.po') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-invoice"></i>
                                    <p>Summary PO</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('summary.item') }}"
                                    class="nav-link {{ request()->routeIs('summary.item') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-boxes-stacked"></i>
                                    <p>Summary Item</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('traceability.index') }}"
                                    class="nav-link {{ request()->routeIs('traceability.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-search"></i>
                                    <p>Traceability</p>
                                </a>
                            </li>

                        @endif

                        @if ($currentUser?->hasRole('administrator'))
                            <li class="nav-header">Administration</li>

                            <li class="nav-item">
                                <a href="{{ route('settings.index') }}"
                                    class="nav-link {{ request()->routeIs('settings.*') && !request()->routeIs('users.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-cogs"></i>
                                    <p>System Parameters</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('users.index') }}"
                                    class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Users</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="page-topbar">
                        <div class="page-title-wrap">
                            <h1 class="page-title">{{ $header ?? 'LEMON Internal Monitoring' }}</h1>
                            <div class="page-subtitle">
                                {{ $headerSubtitle ?? 'Monitoring purchase order, shipment, receiving, dan proses operasional.' }}
                            </div>
                        </div>

                        <div class="page-ribbon">
                            Tanggal Sistem: {{ now()->timezone('Asia/Jakarta')->format('d-m-Y H:i') }} WIB
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

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

        <footer class="main-footer" style="background:#314216;border-top:none;">
            <span class="footer-note">
                <strong>LEMON Internal Monitoring</strong> - Tema lemon untuk kebutuhan operasional internal
            </span>
        </footer>
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
