@extends('layouts.erp')

@php($title = 'Dashboard')
@php($header = 'Executive Dashboard')
@php($headerSubtitle = 'Fokus ke item yang butuh tindakan hari ini, incoming schedule, dan receiving yang tertunda.')

@section('content')
    <style>
        .dashboard-grid { display:grid; gap:1rem; }
        .dashboard-hero {
            padding: 1rem 1.25rem;
            border-radius: 14px;
            border: 1px solid rgba(111,150,40,.1);
            background: linear-gradient(135deg, rgba(255,255,255,.98), rgba(244,248,219,.92));
            box-shadow: 0 6px 16px rgba(111,150,40,.03);
        }
        .dashboard-hero h2 { margin:.25rem 0 .15rem; font-size:1.15rem; color:#2d3d15; }
        .dashboard-muted { font-size:.8rem; color:#728058; }
        .saved-pills { display:flex; flex-wrap:wrap; gap:.45rem; margin-top:.5rem; }
        .saved-pill {
            padding:.3rem .65rem;
            border-radius: 999px;
            border: 1px solid rgba(111,150,40,.18);
            background: rgba(255,255,255,.9);
            color: #314216;
            text-decoration: none;
            font-size: .76rem;
            font-weight: 600;
            transition: all .15s ease;
        }
        .saved-pill:hover { background: #eef7d2; border-color: #b9d044; }
        .saved-pill.active { background: linear-gradient(135deg,#fffde8,#eef7d2); border-color: #b9d044; color: #3f520f; }
        .filter-inline {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: .5rem;
            align-items: end;
            margin-top: .5rem;
        }
        .filter-inline .field-label { font-size: .7rem; color: #5e7230; font-weight: 600; margin-bottom: .15rem; display: block; }
        .quick-links { display:flex; gap:.5rem; flex-wrap:wrap; margin-top:.2rem; }
        .quick-link {
            padding:.4rem .75rem;
            border-radius: 10px;
            border: 1px solid #e4eabc;
            background: #fbfcf3;
            color: #314216;
            text-decoration: none;
            font-size: .78rem;
            font-weight: 600;
            transition: all .15s ease;
        }
        .quick-link:hover { background: #eef7d2; border-color: #b9d044; }
        .kpi-section-title { font-size:.75rem; text-transform:uppercase; letter-spacing:.07em; color:#5e7230; font-weight:700; margin-bottom:.4rem; }
        .kpi-grid { display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap:.85rem; }
        .kpi-card {
            padding: .85rem .9rem;
            border-radius: 12px;
            border: 1px solid rgba(111,150,40,.1);
            background: linear-gradient(135deg, #fff, #eef7d2);
            box-shadow: 0 4px 10px rgba(111,150,40,.02);
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform .12s ease, box-shadow .12s ease;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(111,150,40,.05); }
        .kpi-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; color: #7a8660; font-weight: 700; }
        .kpi-value { font-size: 1.45rem; font-weight: 800; color: #2d3d15; line-height: 1; margin-top: .2rem; }
        .kpi-hint { font-size: .7rem; color: #728058; margin-top: .15rem; }
        .action-section-head { font-size:.85rem; font-weight:800; color:#2d3d15; margin-bottom:.5rem; }
        .action-grid { display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap:.85rem; }
        .action-card {
            padding: .9rem;
            border-radius: 12px;
            border: 1px solid rgba(111,150,40,.1);
            background: linear-gradient(135deg, rgba(255,255,255,.98), rgba(247,248,234,.94));
        }
        .action-header { display:flex; justify-content:space-between; align-items:center; gap:.4rem; margin-bottom:.4rem; }
        .action-title { font-size: .84rem; font-weight: 800; color: #2d3d15; }
        .action-count {
            font-size: .66rem;
            font-weight: 700;
            padding: .12rem .45rem;
            border-radius: 999px;
            background: rgba(111,150,40,.12);
            color: #4a5e2a;
            white-space: nowrap;
        }
        .action-meta { font-size: .76rem; color: #728058; margin-bottom: .4rem; }
        .action-list { display:grid; gap: .4rem; }
        .action-item {
            display:block;
            padding:.5rem .6rem;
            border-radius: 10px;
            border: 1px solid #e4eabc;
            background: #fbfcf3;
            color: #314216;
            text-decoration: none;
            font-size: .78rem;
            transition: background .12s ease;
        }
        .action-item:hover { background: #eef7d2; }
        .action-item-title { font-weight: 600; color: #2d3d15; }
        .action-item-meta { font-size: .7rem; color: #728058; margin-top: .1rem; }
        .empty-state { font-size: .8rem; color: #8a9470; padding: .4rem 0; }

        @media (max-width: 991.98px) {
            .kpi-grid, .action-grid, .filter-inline { grid-template-columns: 1fr 1fr; }
            .filter-inline { grid-template-columns: 1fr 1fr 1fr; }
        }
        @media (max-width: 575.98px) {
            .kpi-grid, .action-grid, .filter-inline { grid-template-columns: 1fr; }
        }
    </style>

    <div class="dashboard-grid">
        <section class="dashboard-hero">
            <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.6rem;">
                <div>
                    <div class="bc-chip">Executive Overview</div>
                    <h2>Ringkasan prioritas harian dan risiko supplier.</h2>
                    <div class="dashboard-muted">Detail penuh tersedia di Monitoring Hub, Supplier Performance, dan Traceability.</div>
                </div>
                <div class="quick-links">
                    <a href="{{ route('monitoring.index', request()->query()) }}" class="quick-link">Monitoring Hub</a>
                    <a href="{{ route('supplier-performance.index', request()->query()) }}" class="quick-link">Supplier Performance</a>
                    <a href="{{ route('traceability.index', request()->query()) }}" class="quick-link">Traceability</a>
                </div>
            </div>

            <form method="GET" class="filter-inline">
                <div>
                    <label class="field-label">Supplier</label>
                    <select name="supplier_id" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected($supplierId === (int) $supplier->id)>{{ $supplier->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">PO Dari</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                </div>
                <div>
                    <label class="field-label">PO Sampai</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                </div>
                <div><button class="btn btn-primary btn-sm w-100">Apply Filter</button></div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
                <div style="display:flex; align-items:end;">
                    <a href="{{ route('dashboard', array_filter(['saved_view' => 'custom'])) }}" class="btn btn-outline-secondary btn-sm w-100">+ View Baru</a>
                </div>
            </form>

            <div class="saved-pills">
                @foreach ($savedViews as $view)
                    <a href="{{ route('dashboard', array_filter(['saved_view' => $view['key'], 'supplier_id' => $supplierId])) }}"
                       class="saved-pill {{ $activeSavedView === $view['key'] ? 'active' : '' }}">
                        {{ $view['label'] }}
                    </a>
                @endforeach
            </div>
        </section>

        <section>
            <div class="kpi-section-title">Key Metrics</div>
            <div class="kpi-grid">
                <a href="{{ route('monitoring.index', array_filter(request()->query() + ['mode' => 'po'])) }}" class="kpi-card">
                    <div class="kpi-label">Outstanding PO</div>
                    <div class="kpi-value">{{ $metrics['open_po'] }}</div>
                    <div class="kpi-hint">PO aktif (bukan Closed/Cancelled)</div>
                </a>
                <a href="{{ route('monitoring.index', array_filter(request()->query() + ['mode' => 'item'])) }}" class="kpi-card">
                    <div class="kpi-label">At-Risk Items</div>
                    <div class="kpi-value">{{ $metrics['at_risk_items'] }}</div>
                    <div class="kpi-hint">Item outstanding lewat ETD</div>
                </a>
                <a href="{{ route('shipments.index', array_filter(request()->query())) }}" class="kpi-card">
                    <div class="kpi-label">Shipment Hari Ini</div>
                    <div class="kpi-value">{{ $metrics['shipped_today'] }}</div>
                    <div class="kpi-hint">Dokumen shipment tanggal {{ \Carbon\Carbon::today()->format('d-m-Y') }}</div>
                </a>
                <a href="{{ route('receiving.index', array_filter(request()->query())) }}" class="kpi-card">
                    <div class="kpi-label">Receiving Hari Ini</div>
                    <div class="kpi-value">{{ $metrics['received_today'] }}</div>
                    <div class="kpi-hint">GR diposting tanggal {{ \Carbon\Carbon::today()->format('d-m-Y') }}</div>
                </a>
            </div>
        </section>

        <section>
            <div class="action-section-head">Action Center</div>
            <div class="action-grid">
                <article class="action-card">
                    <div class="action-header">
                        <div class="action-title">Items Need ETD Update</div>
                        <div class="action-count">{{ $actionCenter['items_need_etd_update']->count() }} item</div>
                    </div>
                    <div class="action-meta">Item outstanding tanpa konfirmasi ETD dari supplier.</div>
                    <div class="action-list">
                        @forelse($actionCenter['items_need_etd_update'] as $row)
                            <a href="{{ route('po.show', $row->po_number) }}" class="action-item">
                                <div class="action-item-title">{{ $row->po_number }} · {{ $row->item_code }}</div>
                                <div class="action-item-meta">{{ $row->supplier_name }} | Outstanding {{ \App\Support\NumberFormatter::trim($row->outstanding_qty) }}</div>
                            </a>
                        @empty
                            <div class="empty-state">Semua item sudah punya ETD.</div>
                        @endforelse
                    </div>
                </article>

                <article class="action-card">
                    <div class="action-header">
                        <div class="action-title">Incoming Minggu Ini</div>
                        <div class="action-count">{{ $actionCenter['incoming_this_week']->count() }} item</div>
                    </div>
                    <div class="action-meta">Item dengan ETD dalam 7 hari ke depan.</div>
                    <div class="action-list">
                        @forelse($actionCenter['incoming_this_week'] as $row)
                            <a href="{{ route('po.show', $row->po_number) }}" class="action-item">
                                <div class="action-item-title">{{ $row->po_number }} · {{ $row->item_code }}</div>
                                <div class="action-item-meta">{{ $row->supplier_name }} | ETD {{ \Carbon\Carbon::parse($row->etd_date)->format('d-m-Y') }}</div>
                            </a>
                        @empty
                            <div class="empty-state">Tidak ada incoming minggu ini.</div>
                        @endforelse
                    </div>
                </article>

                <article class="action-card">
                    <div class="action-header">
                        <div class="action-title">Partial Receiving Queue</div>
                        <div class="action-count">{{ $actionCenter['partial_receiving_queue']->count() }} shipment</div>
                    </div>
                    <div class="action-meta">Shipment aktif yang masih punya sisa qty untuk diterima.</div>
                    <div class="action-list">
                        @forelse($actionCenter['partial_receiving_queue'] as $row)
                            <a href="{{ route('receiving.process', ['shipment_id' => $row->shipment_id]) }}" class="action-item">
                                <div class="action-item-title">{{ $row->shipment_number }} · {{ $row->item_code }}</div>
                                <div class="action-item-meta">{{ $row->supplier_name }} | Sisa {{ \App\Support\NumberFormatter::trim($row->shipment_outstanding_qty) }}</div>
                            </a>
                        @empty
                            <div class="empty-state">Tidak ada queue receiving parsial.</div>
                        @endforelse
                    </div>
                </article>
            </div>
        </section>
    </div>
@endsection
