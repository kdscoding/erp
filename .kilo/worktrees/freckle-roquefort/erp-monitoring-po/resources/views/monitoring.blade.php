@extends('layouts.erp')

@php($title = 'Monitoring Hub')
@php($header = 'Monitoring Hub')
@php($headerSubtitle = 'Satu layar utama untuk monitoring outstanding, dengan mode PO View dan Item View.')
@php($monitoringMode = $monitoringMode ?? 'po')

@section('content')
    <style>
        .monitoring-tabs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        .monitoring-tab {
            display: block;
            padding: .9rem 1rem;
            border-radius: 16px;
            border: 1px solid rgba(111, 150, 40, .12);
            background: rgba(255, 255, 255, .94);
            color: #314216;
            text-decoration: none;
        }

        .monitoring-tab.active {
            border-color: #b9d044;
            background: linear-gradient(135deg, #fffde8, #eef7d2);
        }

        .monitoring-tab-title {
            font-size: .84rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #5e7230;
        }

        .monitoring-tab-note {
            margin-top: .3rem;
            font-size: .8rem;
            color: #728058;
        }

        @media (max-width: 767.98px) {
            .monitoring-tabs {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">Monitoring Hub</h2>
                <p class="page-section-subtitle">`Summary PO` dan `Summary Item` sekarang dikonsolidasikan ke satu hub agar
                    user tidak bingung memilih halaman.</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('monitoring.export-excel', request()->query()) }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel"></i> Export Monitoring
                </a>
                <a href="{{ route('traceability.index') }}" class="btn btn-sm btn-light">Buka Traceability</a>
            </div>
        </section>

        <section class="ui-surface">
            <form method="GET" class="filter-grid">
                <input type="hidden" name="mode" value="{{ $monitoringMode }}">
                <div class="span-6">
                    <label class="field-label">Supplier Code</label>
                    <select name="supplier_code" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->supplier_code }}" @selected(request('supplier_code') === $supplier->supplier_code)>{{ $supplier->supplier_code }} - {{ $supplier->supplier_name }}
                            </option>
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
                <div class="span-1"><a href="{{ route('monitoring.index', ['mode' => $monitoringMode]) }}"
                        class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>
        </section>

        <section class="monitoring-tabs">
            <a href="{{ route('monitoring.index', array_filter(request()->query() + ['mode' => 'po'])) }}"
                class="monitoring-tab {{ $monitoringMode === 'po' ? 'active' : '' }}">
                <div class="monitoring-tab-title">PO View</div>
                <div class="monitoring-tab-note">Ringkasan outstanding per purchase order.</div>
            </a>
            <a href="{{ route('monitoring.index', array_filter(request()->query() + ['mode' => 'item'])) }}"
                class="monitoring-tab {{ $monitoringMode === 'item' ? 'active' : '' }}">
                <div class="monitoring-tab-title">Item View</div>
                <div class="monitoring-tab-note">Detail outstanding per item untuk follow up operasional.</div>
            </a>
        </section>

        <section class="summary-chips">
            <div class="summary-chip">
                <div class="summary-chip-label">Outstanding PO</div>
                <div class="summary-chip-value">
                    {{ number_format((float) ($summaryMetrics['outstanding_po'] ?? 0), 0, ',', '.') }}</div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">Outstanding Item</div>
                <div class="summary-chip-value">
                    {{ number_format((float) ($summaryMetrics['outstanding_item'] ?? 0), 0, ',', '.') }}</div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">Total Order</div>
                <div class="summary-chip-value">
                    {{ \App\Support\NumberFormatter::trim($summaryMetrics['total_order_qty'] ?? 0) }}</div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">Total Pengiriman</div>
                <div class="summary-chip-value">
                    {{ \App\Support\NumberFormatter::trim($summaryMetrics['total_shipped_qty'] ?? 0) }}</div>
            </div>
            <div class="summary-chip">
                <div class="summary-chip-label">Total Outstanding</div>
                <div class="summary-chip-value">
                    {{ \App\Support\NumberFormatter::trim($summaryMetrics['total_outstanding_qty'] ?? 0) }}</div>
            </div>
        </section>

        @if ($monitoringMode === 'po')
            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Monitoring Summary Per Purchase Order</h3>
                        <div class="ui-surface-subtitle">Satu baris per PO untuk pembacaan summary outstanding yang cepat.
                        </div>
                    </div>
                </div>
                <div class="table-wrap table-responsive">
                    <table class="table table-hover ui-table">
                        <thead>
                            <tr>
                                <th>PO</th>
                                <th>Supplier</th>
                                <th>Tanggal PO</th>
                                <th>ETA</th>
                                <th>Item Outstanding</th>
                                <th>Total Order</th>
                                <th>Total Pengiriman</th>
                                <th>Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outstandingPoRows as $row)
                                <tr>
                                    <td>
                                        <a href="{{ route('po.show', $row->po_number) }}"
                                            class="doc-number text-decoration-none">{{ $row->po_number }}</a>
                                        <div class="doc-meta">{{ $row->po_status }}</div>
                                    </td>
                                    <td>{{ $row->supplier_name }}</td>
                                    <td>{{ $row->po_date ? \Carbon\Carbon::parse($row->po_date)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>{{ $row->eta_date ? \Carbon\Carbon::parse($row->eta_date)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>{{ number_format((float) $row->outstanding_item_count, 0, ',', '.') }}</td>
                                    <td>{{ \App\Support\NumberFormatter::trim($row->total_order_qty) }}</td>
                                    <td>{{ \App\Support\NumberFormatter::trim($row->total_shipped_qty) }}</td>
                                    <td>{{ \App\Support\NumberFormatter::trim($row->total_outstanding_qty) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada outstanding PO pada filter
                                        ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($monitoringMode === 'item')
            <section class="ui-surface">
                <div class="ui-surface-head">
                    <div>
                        <h3 class="ui-surface-title">Monitoring Detail Per Item</h3>
                        <div class="ui-surface-subtitle">Item-level visibility untuk follow up ETD, shipment, dan receiving.
                        </div>
                    </div>
                </div>
                <div class="table-wrap table-responsive">
                    <table class="table table-hover ui-table">
                        <thead>
                            <tr>
                                <th>PO</th>
                                <th>Supplier</th>
                                <th>Item</th>
                                <th>Status</th>
                                <th>ETD</th>
                                <th>Ordered</th>
                                <th>Received</th>
                                <th>Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outstandingItemRows as $row)
                                <tr>
                                    <td><a href="{{ route('po.show', $row->po_number) }}"
                                            class="doc-number text-decoration-none">{{ $row->po_number }}</a></td>
                                    <td>{{ $row->supplier_name }}</td>
                                    <td>{{ $row->item_code }} - {{ $row->item_name }}</td>
                                    <td><x-status-badge :status="$row->item_status" scope="item" /></td>
                                    <td>{{ $row->etd_date ? \Carbon\Carbon::parse($row->etd_date)->format('d-m-Y') : '-' }}
                                    </td>
                                    <td>{{ \App\Support\NumberFormatter::trim($row->ordered_qty) }}</td>
                                    <td>{{ \App\Support\NumberFormatter::trim($row->received_qty) }}</td>
                                    <td>{{ \App\Support\NumberFormatter::trim($row->outstanding_qty) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada item outstanding pada filter
                                        ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
@endsection
