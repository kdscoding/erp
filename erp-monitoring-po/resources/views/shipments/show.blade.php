@extends('layouts.erp')
@php($title = 'Shipment Detail')
@php($header = 'Shipment Detail')
@php($headerSubtitle = 'Detail dokumen shipment, supplier, dan line item.')

@php
    $receivingPercent = $receivingPercent ?? 0;
    $totalShippedQty = $totalShippedQty ?? 0;
    $totalReceivedQty = $totalReceivedQty ?? 0;
    $poNumbers = $poNumbers ?? [];
    $shipmentDate = \Carbon\Carbon::parse($shipment->shipment_date)->format('d-m-Y');
    $totalInvoiceAmount = \App\Support\NumberFormatter::trim($lines->sum('invoice_line_total'));
    $lineCount = $lines->count();
@endphp

@push('styles')
<style>
    .shipment-detail .breadcrumb-lemon {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.78rem;
        color: #7a8660;
        margin-bottom: 1rem;
    }
    .shipment-detail .breadcrumb-lemon a {
        color: var(--lemon-green-deep);
        text-decoration: none;
        font-weight: 500;
    }
    .shipment-detail .breadcrumb-lemon a:hover { text-decoration: underline; }
    .shipment-detail .breadcrumb-lemon .breadcrumb-separator { color: #b5c198; font-weight: 400; }
    .shipment-detail .breadcrumb-lemon .breadcrumb-current { color: var(--lemon-ink); font-weight: 700; }

    .shipment-detail .shipment-detail-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .shipment-detail .shipment-detail-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--lemon-ink);
        margin: 0;
        line-height: 1.2;
    }
    .shipment-detail .shipment-detail-title .status-badge { margin-left: 0.6rem; vertical-align: middle; }
    .shipment-detail .shipment-detail-meta {
        font-size: 0.82rem;
        color: #6b7f3a;
        margin-top: 0.25rem;
    }
    .shipment-detail .shipment-detail-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .shipment-detail .shipment-progress-summary {
        border: 1px solid var(--lemon-line);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(48, 66, 24, 0.03);
        padding: 1.1rem 1.25rem;
        margin-bottom: 1.25rem;
    }
    .shipment-detail .shipment-progress-bar {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: #edf1df;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }
    .shipment-detail .shipment-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--lemon-green) 0%, var(--lemon-green-deep) 100%);
    }
    .shipment-detail .shipment-progress-text {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .shipment-detail .shipment-progress-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #304218;
    }
    .shipment-detail .shipment-progress-quantities {
        font-size: 0.82rem;
        color: #5f7331;
    }
    .shipment-detail .shipment-progress-quantities .qty-value {
        font-weight: 700;
        color: var(--lemon-ink);
    }

    .shipment-detail .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.65rem;
        align-items: stretch;
        margin-bottom: 1.25rem;
    }
    .shipment-detail .info-box {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        border: 1px solid var(--lemon-line);
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(48, 66, 24, 0.02);
        padding: 0.8rem 0.95rem;
        height: 100%;
        position: relative;
    }
    .shipment-detail .info-box::before { display: none; }
    .shipment-detail .info-label {
        font-size: 0.69rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #5f7331;
        margin: 0;
    }
    .shipment-detail .info-value {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--lemon-ink);
        line-height: 1.3;
        word-break: break-word;
    }
    .shipment-detail .info-value .badge { white-space: normal; text-align: left; }

    .shipment-detail .ui-surface {
        border: 1px solid var(--lemon-line);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(48, 66, 24, 0.03);
        overflow: hidden;
    }
    .shipment-detail .ui-surface-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 1rem 1.1rem 0.5rem;
    }
    .shipment-detail .ui-surface-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--lemon-ink);
    }
    .shipment-detail .ui-surface-subtitle {
        font-size: 0.78rem;
        color: #7a8660;
        margin-top: 0.15rem;
    }
    .shipment-detail .ui-surface-body { padding: 1rem 1.1rem; }
    .shipment-detail .table-wrap { padding: 0 1.1rem 1.1rem; }

    .shipment-detail .ui-table,
    .shipment-detail .table {
        margin-bottom: 0;
        font-size: 0.8rem;
    }
    .shipment-detail .ui-table thead th,
    .shipment-detail .table thead th {
        background: #f2f6cf;
        border-bottom: 1px solid var(--lemon-line);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #5f7331;
        white-space: nowrap;
        padding: 0.55rem 0.7rem;
        vertical-align: middle;
    }
    .shipment-detail .table td,
    .shipment-detail .table th {
        padding: 0.5rem 0.7rem;
        vertical-align: middle;
        color: var(--lemon-ink);
    }
    .shipment-detail .table-hover tbody tr:hover {
        background: rgba(48, 66, 24, 0.02);
    }
    .shipment-detail .table-totals td {
        background: #f2f6cf;
        font-weight: 700;
    }
    .shipment-detail .doc-number { font-weight: 700; color: #314216; }
    .shipment-detail .doc-meta { font-size: 0.8rem; color: #7a8660; }

    .shipment-detail .related-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 0.6rem;
    }
    .shipment-detail .related-card {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.6rem 0.75rem;
        border: 1px solid var(--lemon-line);
        border-radius: 12px;
        background: #ffffff;
        text-decoration: none;
        color: inherit;
        transition: all 0.15s ease;
        min-height: 54px;
    }
    .shipment-detail .related-card:hover {
        transform: translateY(-1px);
        border-color: #b6cf45;
        box-shadow: 0 4px 12px rgba(48, 66, 24, 0.05);
    }
    .shipment-detail .related-card .r-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: var(--lemon-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        color: var(--lemon-olive);
        flex-shrink: 0;
    }
    .shipment-detail .related-card .r-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--lemon-ink);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .shipment-detail .related-card .r-meta {
        font-size: 0.69rem;
        color: #7a8660;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .shipment-detail .badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
    }
    .shipment-detail .bg-success { background-color: #88b93b !important; color: #fff !important; }
    .shipment-detail .bg-warning { background-color: #f1d93b !important; color: #4b3b07 !important; }
    .shipment-detail .bg-danger { background-color: #e53e3e !important; color: #fff !important; }

    .shipment-detail .empty-placeholder {
        text-align: center;
        padding: 1.5rem;
        color: #7a8660;
        font-size: 0.8rem;
    }

    @media (max-width: 575.98px) {
        .shipment-detail .shipment-detail-header { flex-direction: column; align-items: stretch; }
        .shipment-detail .shipment-detail-actions { justify-content: stretch; }
        .shipment-detail .shipment-detail-actions .btn { flex: 1; }
        .shipment-detail .info-grid { grid-template-columns: 1fr; }
        .shipment-detail .related-grid { grid-template-columns: 1fr; }
        .shipment-detail .shipment-progress-text { flex-direction: column; align-items: flex-start; gap: 0.35rem; }
    }
</style>
@endpush

@section('content')
<div class="shipment-detail page-shell">

    <nav class="breadcrumb-lemon" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('shipments.index') }}">Shipment</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">{{ $shipment->shipment_number }}</span>
    </nav>

    <section class="shipment-detail-header">
        <div>
            <h2 class="shipment-detail-title">
                {{ $shipment->shipment_number }}
                <x-status-badge :status="$shipment->status" scope="shipment" />
            </h2>
            <p class="shipment-detail-meta">{{ $shipment->supplier_name }} &middot; {{ $shipmentDate }}</p>
        </div>
        <div class="shipment-detail-actions">
            <a href="{{ route('shipments.index') }}" class="btn btn-sm btn-light">Back</a>
            @if ($shipment->status === \App\Support\DocumentTermCodes::SHIPMENT_DRAFT)
                <a href="{{ route('shipments.edit', $shipment->id) }}" class="btn btn-sm btn-primary">Edit</a>
            @endif
            @if (in_array($shipment->status, [\App\Support\DocumentTermCodes::SHIPMENT_SHIPPED, \App\Support\DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED]))
                <a href="{{ route('receiving.process', ['supplier_id' => $shipment->supplier_id, 'shipment_id' => $shipment->id, 'document_number' => $shipment->delivery_note_number]) }}" class="btn btn-sm btn-success">Receive</a>
            @endif
        </div>
    </section>

    <section class="shipment-progress-summary">
        <div class="shipment-progress-bar">
            <div class="shipment-progress-fill" style="width: {{ $receivingPercent }}%"></div>
        </div>
        <div class="shipment-progress-text">
            <span class="shipment-progress-label">Receiving Progress &mdash; {{ $receivingPercent }}%</span>
            <span class="shipment-progress-quantities">
                Received <span class="qty-value">{{ \App\Support\NumberFormatter::trim($totalReceivedQty) }}</span>
                &nbsp;/&nbsp; Shipped <span class="qty-value">{{ \App\Support\NumberFormatter::trim($totalShippedQty) }}</span>
                &nbsp;/&nbsp; Open <span class="qty-value">{{ \App\Support\NumberFormatter::trim(max(0, $totalShippedQty - $totalReceivedQty)) }}</span>
            </span>
        </div>
    </section>

    <section class="info-grid">
        <div class="info-box">
            <div class="info-label">Supplier</div>
            <div class="info-value">{{ $shipment->supplier_name }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Status</div>
            <div class="info-value"><x-status-badge :status="$shipment->status" scope="shipment" /></div>
        </div>
        <div class="info-box">
            <div class="info-label">Tanggal Shipment</div>
            <div class="info-value">{{ $shipmentDate }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Delivery Note</div>
            <div class="info-value">{{ $shipment->delivery_note_number ?: '-' }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Invoice</div>
            <div class="info-value">{{ $shipment->invoice_number ?: '-' }}</div>
        </div>
    </section>

    <section class="ui-surface">
        <div class="ui-surface-head">
            <div>
                <h3 class="ui-surface-title">Item Dalam Dokumen</h3>
                <div class="ui-surface-subtitle">{{ $lineCount }} line item(s) &middot; Total Invoice: {{ $totalInvoiceAmount }}</div>
            </div>
        </div>
        <div class="table-wrap table-responsive">
            @if ($lines->isNotEmpty())
                <table class="table table-hover ui-table">
                    <thead>
                        <tr>
                            <th scope="col">PO</th>
                            <th scope="col">Item</th>
                            <th scope="col">Harga Invoice</th>
                            <th scope="col">Subtotal Invoice</th>
                            <th scope="col">Qty Kirim</th>
                            <th scope="col">Qty Terima</th>
                            <th scope="col">Sisa Kirim</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lines as $line)
                            @php($rem = max(0, $line->shipped_qty - $line->received_qty))
                            <tr>
                                <td>{{ $line->po_number }}</td>
                                <td>
                                    <div class="doc-number">{{ $line->item_code }}</div>
                                    <div class="doc-meta">{{ $line->item_name }}</div>
                                </td>
                                <td>{{ $line->invoice_unit_price !== null ? \App\Support\NumberFormatter::trim($line->invoice_unit_price) : '-' }}</td>
                                <td>{{ $line->invoice_line_total !== null ? \App\Support\NumberFormatter::trim($line->invoice_line_total) : '-' }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($line->shipped_qty) }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($line->received_qty) }}</td>
                                <td>
                                    @php($remClass = $rem == 0 ? 'bg-success' : ($rem < ($line->shipped_qty * 0.25) ? 'bg-warning' : 'bg-danger'))
                                    <span class="badge {{ $remClass }}">{{ \App\Support\NumberFormatter::trim($rem) }}</span>
                                </td>
                            </tr>
                        @endforeach
                        <tr class="table-totals">
                            <td colspan="4" class="text-right">Total</td>
                            <td>{{ \App\Support\NumberFormatter::trim($totalShippedQty) }}</td>
                            <td>{{ \App\Support\NumberFormatter::trim($totalReceivedQty) }}</td>
                            <td>{{ \App\Support\NumberFormatter::trim(max(0, $totalShippedQty - $totalReceivedQty)) }}</td>
                        </tr>
                    </tbody>
                </table>
            @else
                <div class="empty-placeholder">
                    <i class="fas fa-box-open" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                    <div>Belum ada item dalam shipment ini.</div>
                </div>
            @endif
        </div>
    </section>

    <section class="ui-surface">
        <div class="ui-surface-head">
            <div>
                <h3 class="ui-surface-title">Dokumen Terkait</h3>
                <div class="ui-surface-subtitle">Quick links ke dokumen dan modul yang berelasi.</div>
            </div>
        </div>
        <div class="ui-surface-body">
            <div class="related-grid">
                @foreach ($poNumbers as $poNum)
                    <a href="{{ route('po.show', $poNum) }}" class="related-card">
                        <div class="r-icon"><i class="fas fa-file-alt"></i></div>
                        <div>
                            <div class="r-title">{{ $poNum }}</div>
                            <div class="r-meta">Purchase Order</div>
                        </div>
                    </a>
                @endforeach
                <a href="{{ route('audit.index', ['module' => 'shipment', 'ref_id' => $shipment->id]) }}" class="related-card">
                    <div class="r-icon"><i class="fas fa-history"></i></div>
                    <div>
                        <div class="r-title">Audit Trail</div>
                        <div class="r-meta">Change history</div>
                    </div>
                </a>
                <a href="{{ route('supplier-performance.index', ['supplier_code' => $shipment->supplier_code ?? '']) }}" class="related-card">
                    <div class="r-icon"><i class="fas fa-truck"></i></div>
                    <div>
                        <div class="r-title">{{ $shipment->supplier_name }}</div>
                        <div class="r-meta">Supplier Performance</div>
                    </div>
                </a>
                <a href="{{ route('tracking.index', ['q' => $shipment->shipment_number]) }}" class="related-card">
                    <div class="r-icon"><i class="fas fa-table"></i></div>
                    <div>
                        <div class="r-title">Tracking</div>
                        <div class="r-meta">Unified tracking</div>
                    </div>
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
