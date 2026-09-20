@extends('layouts.erp')

@php($title = 'Pending Receiving')
@php($header = 'Pending Receiving')
@php($headerSubtitle = 'Daftar shipment yang siap diterima gudang. Pilih dokumen untuk memulai proses receiving.')

@section('content')
    <section class="ui-surface mb-3">
        <div class="ui-surface-head">
            <div>
                <h3 class="ui-surface-title">Filter Shipment</h3>
                <div class="ui-surface-subtitle">Cari dokumen supplier yang siap diterima gudang.</div>
            </div>
        </div>
        <div class="ui-surface-body">
            <form method="GET" action="{{ route('receiving.pending') }}" class="filter-grid">
                <div class="span-3">
                    <label class="field-label">Supplier</label>
                    <select name="supplier_id" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>
                                {{ $supplier->supplier_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="span-3">
                    <label class="field-label">Delivery Note</label>
                    <input type="text" name="document_number" value="{{ request('document_number') }}"
                        class="form-control form-control-sm" placeholder="No surat jalan supplier">
                </div>

                <div class="span-4">
                    <label class="field-label">Cari Shipment / PO / Invoice</label>
                    <input type="text" name="keyword" value="{{ request('keyword') }}"
                        class="form-control form-control-sm" placeholder="Shipment, PO, invoice, supplier">
                </div>

                <div class="span-1">
                    <button class="btn btn-primary btn-sm w-100">Apply</button>
                </div>

                <div class="span-1">
                    <a href="{{ route('receiving.pending') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>
        </div>
    </section>

    <section class="summary-chips mb-3">
        <div class="summary-chip">
            <div class="summary-chip-label">Total Dokumen</div>
            <div class="summary-chip-value">{{ $shipmentDocuments->count() }}</div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">Siap Diproses</div>
            <div class="summary-chip-value">{{ $shipmentDocuments->whereNotIn('status', ['Closed', 'Cancelled'])->count() }}</div>
        </div>
        <div class="summary-chip">
            <div class="summary-chip-label">Total Outstanding Qty</div>
            <div class="summary-chip-value">{{ \App\Support\NumberFormatter::trim($shipmentDocuments->sum('outstanding_qty')) }}</div>
        </div>
    </section>

    <section class="ui-surface">
        <div class="ui-surface-head">
            <div>
                <h3 class="ui-surface-title">Shipment Worklist</h3>
                <div class="ui-surface-subtitle">Klik "Mulai Receiving" untuk memproses qty fisik yang datang di gudang.</div>
            </div>
        </div>
        <div class="table-wrap table-responsive">
            @if ($shipmentDocuments->isNotEmpty())
                <table class="table table-hover ui-table">
                    <thead>
                        <tr>
                            <th>Shipment</th>
                            <th>Supplier</th>
                            <th>Delivery Note</th>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>PO</th>
                            <th>Line</th>
                            <th>Sisa Kiriman</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($shipmentDocuments as $document)
                            <tr>
                                <td>
                                    <div class="doc-number">{{ $document->shipment_number }}</div>
                                    <div class="doc-meta"><x-status-badge :status="$document->status" scope="shipment" /></div>
                                </td>
                                <td>{{ $document->supplier_name }}</td>
                                <td>{{ $document->delivery_note_number ?: '-' }}</td>
                                <td>{{ $document->invoice_number ?: '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($document->shipment_date)->format('d-m-Y') }}</td>
                                <td>{{ $document->po_count }}</td>
                                <td>{{ $document->line_count }}</td>
                                <td>{{ \App\Support\NumberFormatter::trim($document->outstanding_qty) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('receiving.create', ['shipment' => $document->id, 'supplier_id' => request('supplier_id'), 'document_number' => request('document_number'), 'keyword' => request('keyword')]) }}"
                                        class="btn btn-sm btn-primary">Mulai Receiving</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-box-open fa-2x mb-2 opacity-25"></i>
                    <p>Belum ada dokumen shipment yang siap diterima.</p>
                    <p class="small">Pastikan shipment berstatus <strong>Shipped</strong> atau <strong>Partial Received</strong> dan masih memiliki sisa qty.</p>
                </div>
            @endif
        </div>
    </section>
@endsection