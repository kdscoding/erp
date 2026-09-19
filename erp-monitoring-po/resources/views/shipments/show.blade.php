@extends('layouts.erp')
@php($title = 'Shipment Detail')
@php($header = 'Shipment Detail')
@php($headerSubtitle = 'Detail dokumen shipment dan line item.')

@php($receivingPercent = isset($receivingPercent) ? $receivingPercent : 0)
@php($timeline = isset($timeline) ? $timeline : [])
@php($poNumbers = isset($poNumbers) ? $poNumbers : [])
@php($receiptLinks = isset($receiptLinks) ? $receiptLinks : [])

@push('styles')
<style>
    .timeline { display: flex; flex-direction: column; gap: 0; padding: 0; margin: 0; list-style: none; }
    .timeline-item { display: flex; gap: .75rem; padding-bottom: .75rem; position: relative; }
    .timeline-item:not(:last-child)::before { content: ''; position: absolute; left: 15px; top: 28px; bottom: 0; width: 2px; background: var(--lemon-line); }
    .timeline-item.completed:not(:last-child)::before { background: var(--lemon-green); }
    .timeline-icon { width: 32px; height: 32px; border-radius: 50%; background: var(--lemon-bg); border: 2px solid var(--lemon-line); display: flex; align-items: center; justify-content: center; font-size: .82rem; flex-shrink: 0; z-index: 1; }
    .timeline-item.completed .timeline-icon { background: var(--lemon-green); border-color: var(--lemon-green); color: #fff; }
    .timeline-item.active .timeline-icon { background: var(--lemon-yellow); border-color: var(--lemon-yellow); color: var(--lemon-ink); }
    .timeline-content { flex: 1; }
    .timeline-title { font-weight: 700; font-size: .84rem; color: var(--lemon-ink); }
    .timeline-meta { font-size: .76rem; color: #7a8660; }
    .timeline-desc { font-size: .78rem; color: #52603d; margin-top: 2px; }
    .donut-wrapper { display: flex; justify-content: center; align-items: center; flex-direction: column; }
    .donut-chart { width: 110px; height: 110px; border-radius: 50%; display: flex; align-items: center; justify-content: center; position: relative; }
    .donut-inner { width: 72px; height: 72px; border-radius: 50%; background: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .donut-value { font-size: 1.2rem; font-weight: 800; color: var(--lemon-ink); line-height: 1; }
    .donut-label { font-size: .62rem; color: #7a8660; text-transform: uppercase; letter-spacing: .04em; margin-top: 2px; }
    .breadcrumb-nav { display: flex; align-items: center; gap: .35rem; font-size: .76rem; color: #7a8660; margin-bottom: .5rem; }
    .breadcrumb-nav a { color: var(--lemon-green-deep); text-decoration: none; }
    .breadcrumb-nav a:hover { text-decoration: underline; }
    .breadcrumb-nav .sep { color: #b5c198; }
    .timeline-toggle { cursor: pointer; user-select: none; }
    .timeline-section { display: none; }
    .timeline-section.open { display: block; }
    .related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: .5rem; }
    .related-card { border: 1px solid var(--lemon-line); border-radius: 8px; background: #fffef8; padding: .5rem .7rem; display: flex; justify-content: space-between; align-items: center; gap: .5rem; }
    .related-card .r-title { font-weight: 700; font-size: .78rem; color: var(--lemon-ink); }
    .related-card .r-meta { font-size: .68rem; color: #7a8660; }
    .related-card .r-icon { width: 28px; height: 28px; border-radius: 6px; background: var(--lemon-bg); display: flex; align-items: center; justify-content: center; font-size: .78rem; color: var(--lemon-olive); flex-shrink: 0; }
</style>
@endpush

@section('content')
    <div class="page-shell">

        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Home</a>
            <span class="sep">/</span>
            <a href="{{ route('shipments.index') }}">Shipment</a>
            <span class="sep">/</span>
            <span>{{ $shipment->shipment_number }}</span>
        </nav>

        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">{{ $shipment->shipment_number }}</h2>
                <p class="page-section-subtitle">Detail dokumen shipment, supplier, dan line item.</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('shipments.index') }}" class="btn btn-sm btn-light">Back</a>
                @if ($shipment->status === \App\Support\DocumentTermCodes::SHIPMENT_DRAFT)
                    <a href="{{ route('shipments.edit', $shipment->id) }}" class="btn btn-sm btn-primary">Edit</a>
                @endif
                @if (in_array($shipment->status, [\App\Support\DocumentTermCodes::SHIPMENT_SHIPPED, \App\Support\DocumentTermCodes::SHIPMENT_PARTIAL_RECEIVED]))
                    <a href="{{ route('receiving.process', ['supplier_id' => $shipment->supplier_id, 'shipment_id' => $shipment->id, 'document_number' => $shipment->delivery_note_number]) }}" class="btn btn-sm btn-success">Receive</a>
                @endif
            </div>
        </section>

        <section class="info-grid">
            <div class="info-box"><div class="info-label">Supplier</div><div class="info-value">{{ $shipment->supplier_name }}</div></div>
            <div class="info-box"><div class="info-label">Status</div><div class="info-value"><x-status-badge :status="$shipment->status" scope="shipment" /></div></div>
            <div class="info-box"><div class="info-label">Tanggal</div><div class="info-value">{{ \Carbon\Carbon::parse($shipment->shipment_date)->format('d-m-Y') }}</div></div>
            <div class="info-box"><div class="info-label">Delivery Note</div><div class="info-value">{{ $shipment->delivery_note_number ?: '-' }}</div></div>
            <div class="info-box"><div class="info-label">Invoice</div><div class="info-value">{{ $shipment->invoice_number ?: '-' }}</div></div>
            <div class="info-box">
                <div class="donut-wrapper">
                    <div class="donut-chart" style="background: conic-gradient(var(--lemon-green) 0% {{ $receivingPercent }}%, var(--lemon-line) {{ $receivingPercent }}% 100%)">
                        <div class="donut-inner">
                            <div class="donut-value">{{ $receivingPercent }}%</div>
                            <div class="donut-label">Received</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ui-surface mt-3">
            <div class="ui-surface-head">
                <div><h3 class="ui-surface-title">Item Dalam Dokumen</h3></div>
            </div>
            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
                    <thead><tr><th>PO</th><th>Item</th><th>Harga Invoice</th><th>Total Invoice</th><th>Qty Dikirim</th><th>Sudah Diterima</th><th>Sisa</th></tr></thead>
                    <tbody>
                        @foreach ($lines as $line)
                            <tr>
                                <td>{{ $line->po_number }}</td>
                                <td><div class="doc-number">{{ $line->item_code }}</div><div class="doc-meta">{{ $line->item_name }}</div></td>
                                <td>{{ $line->invoice_unit_price !== null ? \App\Support\NumberFormatter::trim($line->invoice_unit_price) : '-' }}</td>
                                <td>{{ $line->invoice_line_total !== null ? \App\Support\NumberFormatter::trim($line->invoice_line_total) : '-' }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($line->shipped_qty) }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($line->received_qty) }}</td>
                                <td>
                                    @php($rem = max(0, $line->shipped_qty - $line->received_qty))
                                    <span class="badge {{ $rem == 0 ? 'bg-success' : ($rem < ($line->shipped_qty * 0.25) ? 'bg-warning text-dark' : 'bg-danger') }}">{{ \App\Support\NumberFormatter::trim($rem) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ui-surface mt-3">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title timeline-toggle" onclick="this.nextElementSibling.classList.toggle('open')">
                        <i class="fas fa-chevron-down mr-1"></i> Timeline &amp; Related Documents
                    </h3>
                </div>
            </div>
            <div class="ui-surface-body timeline-section">
                @if (!empty($timeline))
                    <ul class="timeline mb-3">
                        @foreach ($timeline as $event)
                            <li class="timeline-item {{ $event['completed'] ? 'completed' : 'active' }}">
                                <div class="timeline-icon"><i class="fas {{ $event['icon'] ?? 'fa-circle' }}"></i></div>
                                <div class="timeline-content">
                                    <div class="timeline-title">{{ $event['title'] }}</div>
                                    <div class="timeline-meta">{{ $event['date'] ?? '' }}</div>
                                    <div class="timeline-desc">{{ $event['description'] ?? '' }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @if (!empty($poNumbers) || !empty($receiptLinks))
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($poNumbers as $poNum)
                            <div class="related-card"><div><div class="r-title">{{ $poNum }}</div><div class="r-meta">Purchase Order</div></div><div class="r-icon"><i class="fas fa-file-alt"></i></div></div>
                        @endforeach
                        @foreach ($receiptLinks as $link)
                            <div class="related-card"><div><div class="r-title">{{ $link['number'] ?? '' }}</div><div class="r-meta">Goods Receipt</div></div><div class="r-icon"><i class="fas fa-box-open"></i></div></div>
                        @endforeach
                        <a href="{{ route('audit.index', ['module' => 'shipment', 'ref_id' => $shipment->id]) }}" class="related-card" style="text-decoration:none;color:inherit">
                            <div><div class="r-title">Audit Trail</div><div class="r-meta">Change history</div></div><div class="r-icon"><i class="fas fa-history"></i></div>
                        </a>
                        <a href="{{ route('supplier-performance.index', ['supplier_code' => $shipment->supplier_code ?? '']) }}" class="related-card" style="text-decoration:none;color:inherit">
                            <div><div class="r-title">{{ $shipment->supplier_name }}</div><div class="r-meta">Supplier Performance</div></div><div class="r-icon"><i class="fas fa-truck"></i></div>
                        </a>
                        <a href="{{ route('tracking.index', ['q' => $shipment->shipment_number]) }}" class="related-card" style="text-decoration:none;color:inherit">
                            <div><div class="r-title">Tracking</div><div class="r-meta">Unified tracking</div></div><div class="r-icon"><i class="fas fa-table"></i></div>
                        </a>
                    </div>
                @endif
            </div>
        </section>

    </div>

    <script>
        function navigateToDetail(id) { window.location.href = '{{ route('shipments.show', ':id') }}'.replace(':id', id); }
    </script>
@endsection