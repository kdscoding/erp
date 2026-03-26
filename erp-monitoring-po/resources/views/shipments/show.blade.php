@extends('layouts.erp')
@php($title = 'Shipment Detail')
@php($header = 'Shipment Detail')
@php($headerSubtitle = 'Detail dokumen shipment, referensi supplier, dan line item yang ikut terkirim.')

@section('content')
    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">{{ $shipment->shipment_number }}</h2>
                <p class="page-section-subtitle">Buka detail shipment aktif maupun arsip tanpa header dobel dan tanpa CSS lokal.</p>
            </div>

            <div class="page-actions">
                <a href="{{ route('shipments.history') }}" class="btn btn-sm btn-light">Back to Archive</a>
                <a href="{{ route('shipments.index') }}" class="btn btn-sm btn-light">Back to Worklist</a>
                @if ($shipment->status === \App\Support\DocumentTermCodes::SHIPMENT_DRAFT)
                    <a href="{{ route('shipments.edit', $shipment->id) }}" class="btn btn-sm btn-primary">Edit Draft</a>
                    <a href="{{ route('shipments.export-excel', $shipment->id) }}" class="btn btn-sm btn-outline-success">Export Excel</a>
                @endif
            </div>
        </section>

        <section class="info-grid">
            <div class="info-box"><div class="info-label">No Shipment</div><div class="info-value">{{ $shipment->shipment_number }}</div></div>
            <div class="info-box"><div class="info-label">Supplier</div><div class="info-value">{{ $shipment->supplier_name }}</div></div>
            <div class="info-box"><div class="info-label">Status</div><div class="info-value"><x-status-badge :status="$shipment->status" scope="shipment" /></div></div>
            <div class="info-box"><div class="info-label">Tanggal Dokumen</div><div class="info-value">{{ \Carbon\Carbon::parse($shipment->shipment_date)->format('d-m-Y') }}</div></div>
            <div class="info-box"><div class="info-label">Delivery Note</div><div class="info-value">{{ $shipment->delivery_note_number ?: '-' }}</div></div>
            <div class="info-box"><div class="info-label">Invoice</div><div class="info-value">{{ $shipment->invoice_number ?: '-' }}</div></div>
            <div class="info-box"><div class="info-label">Invoice Date</div><div class="info-value">{{ $shipment->invoice_date ? \Carbon\Carbon::parse($shipment->invoice_date)->format('d-m-Y') : '-' }}</div></div>
            <div class="info-box"><div class="info-label">Currency</div><div class="info-value">{{ $shipment->invoice_currency ?: '-' }}</div></div>
            <div class="info-box"><div class="info-label">Catatan</div><div class="info-value">{{ $shipment->supplier_remark ?: '-' }}</div></div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Item Dalam Dokumen Shipment</h3>
                    <div class="ui-surface-subtitle">Harga invoice tetap menjadi referensi dari dokumen shipment, bukan dari receiving.</div>
                </div>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
                    <thead>
                        <tr>
                            <th>PO</th>
                            <th>Item</th>
                            <th>Harga PO</th>
                            <th>Harga Invoice</th>
                            <th>Total Invoice</th>
                            <th>Qty Dikirim</th>
                            <th>Sudah Diterima</th>
                            <th>Sisa Kiriman</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lines as $line)
                            <tr>
                                <td>{{ $line->po_number }}</td>
                                <td><div class="doc-number">{{ $line->item_code }}</div><div class="doc-meta">{{ $line->item_name }}</div></td>
                                <td>{{ $line->po_unit_price !== null ? \App\Support\NumberFormatter::trim($line->po_unit_price) : '-' }}</td>
                                <td>{{ $line->invoice_unit_price !== null ? \App\Support\NumberFormatter::trim($line->invoice_unit_price) : '-' }}</td>
                                <td>{{ $line->invoice_line_total !== null ? \App\Support\NumberFormatter::trim($line->invoice_line_total) : '-' }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($line->shipped_qty) }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($line->received_qty) }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim(max(0, $line->shipped_qty - $line->received_qty)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
