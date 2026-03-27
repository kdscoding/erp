@extends('layouts.erp')

@php($title = 'Summary Item')
@php($header = 'Summary Outstanding Item')
@php($headerSubtitle = 'Ringkasan outstanding di level item untuk follow up operasional yang lebih presisi.')

@section('content')
    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">Summary Item</h2>
                <p class="page-section-subtitle">Fokus ke item outstanding terbesar. Halaman dipaginasi agar tetap ringan saat data bertambah.</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('summary.po', request()->query()) }}" class="btn btn-sm btn-light">Buka Summary PO</a>
                <a href="{{ route('summary.item.export-excel', request()->query()) }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>
        </section>

        <section class="ui-surface">
            <form method="GET" class="filter-grid">
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
                <div class="span-1">
                    <button class="btn btn-primary btn-sm w-100">Apply</button>
                </div>
                <div class="span-1">
                    <a href="{{ route('summary.item') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>
        </section>

        <section class="summary-chips">
            <div class="summary-chip"><div class="summary-chip-label">Outstanding PO</div><div class="summary-chip-value">{{ number_format((float) ($summaryMetrics['outstanding_po'] ?? 0), 0, ',', '.') }}</div></div>
            <div class="summary-chip"><div class="summary-chip-label">Outstanding Item</div><div class="summary-chip-value">{{ number_format((float) ($summaryMetrics['outstanding_item'] ?? 0), 0, ',', '.') }}</div></div>
            <div class="summary-chip"><div class="summary-chip-label">Total Order</div><div class="summary-chip-value">{{ \App\Support\NumberFormatter::trim($summaryMetrics['total_order_qty'] ?? 0) }}</div></div>
            <div class="summary-chip"><div class="summary-chip-label">Total Pengiriman</div><div class="summary-chip-value">{{ \App\Support\NumberFormatter::trim($summaryMetrics['total_shipped_qty'] ?? 0) }}</div></div>
            <div class="summary-chip"><div class="summary-chip-label">Total Outstanding</div><div class="summary-chip-value">{{ \App\Support\NumberFormatter::trim($summaryMetrics['total_outstanding_qty'] ?? 0) }}</div></div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Outstanding per Item</h3>
                    <div class="ui-surface-subtitle">Menampilkan 50 item per halaman untuk menjaga performa browser dan query.</div>
                </div>
            </div>
            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
                    <thead>
                        <tr>
                            <th>PO</th>
                            <th>Item</th>
                            <th>Supplier</th>
                            <th>ETD</th>
                            <th>Order</th>
                            <th>Pengiriman</th>
                            <th>Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outstandingItemRows as $row)
                            <tr>
                                <td><a href="{{ route('po.show', $row->po_id) }}" class="doc-number text-decoration-none">{{ $row->po_number }}</a></td>
                                <td>{{ $row->item_code }} - {{ $row->item_name }}</td>
                                <td>{{ $row->supplier_name }}</td>
                                <td>{{ $row->etd_date ? \Carbon\Carbon::parse($row->etd_date)->format('d-m-Y') : '-' }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($row->ordered_qty) }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($row->received_qty) }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($row->outstanding_qty) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Tidak ada outstanding item pada filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="ui-surface-body pt-0">
                {{ $outstandingItemRows->links() }}
            </div>
        </section>
    </div>
@endsection
