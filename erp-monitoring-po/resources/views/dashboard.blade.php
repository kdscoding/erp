@extends('layouts.erp')

@php($title = 'Dashboard Outstanding PO')
@php($header = 'Executive Dashboard')
@php($headerSubtitle = 'Dashboard diringkas agar fokus ke prioritas harian, risiko supplier, dan action center.')

@section('content')
    <style>
        .dashboard-grid { display:grid; gap:1rem; }
        .dashboard-hero {
            padding: 1.2rem;
            border: 1px solid rgba(111, 150, 40, .12);
            border-radius: 20px;
            background: radial-gradient(circle at top right, rgba(241, 217, 59, .26), transparent 24%), linear-gradient(135deg, rgba(255,255,255,.98), rgba(244,248,219,.98));
            box-shadow: 0 14px 28px rgba(111,150,40,.05);
        }
        .dashboard-hero h2 { margin: .6rem 0 .35rem; font-size: 1.45rem; line-height: 1.1; color: #2d3d15; }
        .dashboard-muted { font-size: .84rem; color: #728058; }
        .saved-views { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.75rem; margin-top:1rem; }
        .saved-view-card { display:block; padding:.9rem 1rem; border-radius:16px; border:1px solid rgba(111,150,40,.12); background:rgba(255,255,255,.92); color:#314216; text-decoration:none; }
        .saved-view-card.active { border-color:#b9d044; background:linear-gradient(135deg,#fffde8,#eef7d2); }
        .saved-view-title { font-size:.82rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#5e7230; }
        .saved-view-note { margin-top:.3rem; font-size:.8rem; color:#728058; }
        .kpi-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; }
        .kpi-card { padding:1rem; border-radius:18px; border:1px solid rgba(111,150,40,.12); background:linear-gradient(135deg,#fff,#eef7d2); box-shadow:0 12px 24px rgba(111,150,40,.04); }
        .kpi-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; color:#7a8660; }
        .kpi-value { font-size:1.7rem; font-weight:800; color:#314216; line-height:1; margin-top:.3rem; }
        .panel-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .stack-list { display:grid; gap:.75rem; }
        .stack-card { padding:.9rem; border-radius:14px; border:1px solid rgba(111,150,40,.1); background:linear-gradient(135deg,rgba(255,255,255,.98),rgba(247,248,234,.96)); }
        .stack-title { font-weight:800; color:#314216; }
        .stack-meta { font-size:.8rem; color:#728058; }
        .action-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem; }
        .action-card { padding:1rem; border-radius:16px; border:1px solid rgba(111,150,40,.1); background:linear-gradient(135deg,rgba(255,255,255,.98),rgba(247,248,234,.96)); }
        .action-title { font-size:.92rem; font-weight:800; color:#314216; }
        .action-meta { font-size:.8rem; color:#728058; margin-top:.2rem; }
        .action-links { display:grid; gap:.55rem; margin-top:.75rem; }
        .action-link { display:block; padding:.75rem .85rem; border-radius:12px; border:1px solid #e4eabc; background:#fbfcf3; color:#314216; text-decoration:none; }
        @media (max-width: 1199.98px) { .kpi-grid, .saved-views, .panel-grid, .action-grid { grid-template-columns:1fr 1fr; } }
        @media (max-width: 767.98px) { .kpi-grid, .saved-views, .panel-grid, .action-grid { grid-template-columns:1fr; } }
    </style>

    <div class="dashboard-grid">
        <section class="dashboard-hero">
            <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
                <div>
                    <div class="bc-chip">Executive Overview</div>
                    <h2>Dashboard disederhanakan agar user langsung tahu risiko, supplier bermasalah, dan tindakan berikutnya.</h2>
                    <div class="dashboard-muted">Detail penuh sekarang diarahkan ke Monitoring Hub, Supplier Performance, dan Traceability.</div>
                </div>
                <div class="page-actions">
                    <a href="{{ route('monitoring.index', request()->query()) }}" class="btn btn-sm btn-light">Monitoring Hub</a>
                    <a href="{{ route('supplier-performance.index', request()->query()) }}" class="btn btn-sm btn-light">Supplier Performance</a>
                </div>
            </div>

            <form method="GET" class="filter-grid px-0 pb-0">
                <div class="span-6">
                    <label class="field-label">Supplier</label>
                    <select name="supplier_id" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected($supplierId === (int) $supplier->id)>{{ $supplier->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="span-2">
                    <label class="field-label">PO Dari</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                </div>
                <div class="span-2">
                    <label class="field-label">PO Sampai</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                </div>
                <div class="span-1"><button class="btn btn-primary btn-sm w-100">Apply</button></div>
                <div class="span-1"><a href="{{ route('dashboard') }}" class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>

            <div class="saved-views">
                @foreach ($savedViews as $view)
                    <a href="{{ route('dashboard', array_filter(['saved_view' => $view['key'], 'supplier_id' => $supplierId])) }}"
                        class="saved-view-card {{ $activeSavedView === $view['key'] ? 'active' : '' }}">
                        <div class="saved-view-title">{{ $view['label'] }}</div>
                        <div class="saved-view-note">{{ $view['description'] }}</div>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="kpi-grid">
            <article class="kpi-card"><div class="kpi-label">Open PO</div><div class="kpi-value">{{ $metrics['open_po'] }}</div></article>
            <article class="kpi-card"><div class="kpi-label">At-Risk Items</div><div class="kpi-value">{{ $metrics['at_risk_items'] }}</div></article>
            <article class="kpi-card"><div class="kpi-label">Shipment Hari Ini</div><div class="kpi-value">{{ $metrics['shipped_today'] }}</div></article>
            <article class="kpi-card"><div class="kpi-label">Receiving Hari Ini</div><div class="kpi-value">{{ $metrics['received_today'] }}</div></article>
        </section>

        <section class="panel-grid">
            <article class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Top Delayed Suppliers</h3>
                        <div class="ui-surface-subtitle">Supplier dengan item terlambat terbanyak pada filter saat ini.</div>
                    </div>
                </div>
                <div class="ui-surface-body">
                    <div class="stack-list">
                        @forelse($supplierDelay as $row)
                            <div class="stack-card">
                                <div class="stack-title">{{ $row->supplier_name }}</div>
                                <div class="stack-meta">{{ $row->late_item_count }} item terlambat | {{ $row->late_po_count }} PO terdampak</div>
                                <div class="stack-meta">@if ($row->oldest_late_etd) ETD tertua {{ \Carbon\Carbon::parse($row->oldest_late_etd)->format('d-m-Y') }} @else Belum ada ETD referensi @endif</div>
                            </div>
                        @empty
                            <div class="text-muted">Belum ada supplier yang terlambat.</div>
                        @endforelse
                    </div>
                </div>
            </article>

            <article class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Monitoring Shortcut</h3>
                        <div class="ui-surface-subtitle">Gunakan layar monitoring khusus untuk membaca detail, bukan dari dashboard.</div>
                    </div>
                </div>
                <div class="ui-surface-body">
                    <div class="stack-list">
                        <a href="{{ route('monitoring.index', array_filter(request()->query() + ['mode' => 'po'])) }}" class="action-link">
                            <div class="stack-title">Monitoring Hub · PO View</div>
                            <div class="stack-meta">Ringkasan outstanding per purchase order.</div>
                        </a>
                        <a href="{{ route('monitoring.index', array_filter(request()->query() + ['mode' => 'item'])) }}" class="action-link">
                            <div class="stack-title">Monitoring Hub · Item View</div>
                            <div class="stack-meta">Detail outstanding per item untuk follow up operasional.</div>
                        </a>
                        <a href="{{ route('traceability.index', request()->query()) }}" class="action-link">
                            <div class="stack-title">Traceability</div>
                            <div class="stack-meta">Investigasi timeline PO, shipment, dan receiving.</div>
                        </a>
                    </div>
                </div>
            </article>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Action Center</h3>
                    <div class="ui-surface-subtitle">Inbox tugas lintas modul yang tetap menjadi fokus utama dashboard.</div>
                </div>
            </div>
            <div class="ui-surface-body">
                <div class="action-grid">
                    <div class="action-card">
                        <div class="action-title">Items Need ETD Update</div>
                        <div class="action-meta">Item outstanding tanpa ETD yang perlu konfirmasi supplier.</div>
                        <div class="action-links">
                            @forelse($actionCenter['items_need_etd_update'] as $row)
                                <a href="{{ route('po.show', $row->po_id) }}" class="action-link">
                                    <div class="stack-title">{{ $row->po_number }} · {{ $row->item_code }}</div>
                                    <div class="stack-meta">{{ $row->supplier_name }} | Outstanding {{ \App\Support\NumberFormatter::trim($row->outstanding_qty) }}</div>
                                </a>
                            @empty
                                <div class="text-muted">Tidak ada item tanpa ETD pada filter ini.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="action-card">
                        <div class="action-title">Incoming This Week</div>
                        <div class="action-meta">Item dengan ETD tujuh hari ke depan untuk kesiapan receiving.</div>
                        <div class="action-links">
                            @forelse($actionCenter['incoming_this_week'] as $row)
                                <a href="{{ route('po.show', $row->po_id) }}" class="action-link">
                                    <div class="stack-title">{{ $row->po_number }} · {{ $row->item_code }}</div>
                                    <div class="stack-meta">{{ $row->supplier_name }} | ETD {{ \Carbon\Carbon::parse($row->etd_date)->format('d-m-Y') }}</div>
                                </a>
                            @empty
                                <div class="text-muted">Belum ada incoming item minggu ini.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="action-card">
                        <div class="action-title">Partial Receiving Queue</div>
                        <div class="action-meta">Shipment aktif yang masih punya sisa qty untuk diterima.</div>
                        <div class="action-links">
                            @forelse($actionCenter['partial_receiving_queue'] as $row)
                                <a href="{{ route('receiving.process', ['shipment_id' => $row->shipment_id]) }}" class="action-link">
                                    <div class="stack-title">{{ $row->shipment_number }} · {{ $row->item_code }}</div>
                                    <div class="stack-meta">{{ $row->supplier_name }} | Sisa {{ \App\Support\NumberFormatter::trim($row->shipment_outstanding_qty) }}</div>
                                </a>
                            @empty
                                <div class="text-muted">Tidak ada queue receiving parsial.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
