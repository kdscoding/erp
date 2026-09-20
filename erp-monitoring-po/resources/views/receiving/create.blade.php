@extends('layouts.erp')

@php($title = 'Receiving Entry - ' . $selectedShipment->shipment_number)
@php($header = 'Receiving Entry')
@php($headerSubtitle = 'Input qty fisik untuk shipment: ' . $selectedShipment->shipment_number)

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
            <section class="ui-surface h-100">
                <div class="ui-surface-head">
                    <h3 class="ui-surface-title mb-0">Informasi Shipment</h3>
                </div>
                <div class="ui-surface-body">
                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-label">Shipment</div>
                            <div class="info-value">{{ $selectedShipment->shipment_number }}</div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Delivery Note</div>
                            <div class="info-value">{{ $selectedShipment->delivery_note_number ?: '-' }}</div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Invoice</div>
                            <div class="info-value">{{ $selectedShipment->invoice_number ?: '-' }}</div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Supplier</div>
                            <div class="info-value">{{ $selectedShipment->supplier_name }}</div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-12 col-md-6">
            <section class="ui-surface h-100">
                <div class="ui-surface-head">
                    <h3 class="ui-surface-title mb-0">Form Receiving</h3>
                    <div class="ui-surface-subtitle">Isi qty yang benar-benar datang di gudang.</div>
                </div>
                <div class="ui-surface-body">
                    <form method="POST" action="{{ route('receiving.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="shipment_id" value="{{ $selectedShipment->id }}">
                        <input type="hidden" name="supplier_id" value="{{ request('supplier_id') }}">
                        <input type="hidden" name="search_document_number" value="{{ request('document_number') }}">
                        <input type="hidden" name="keyword" value="{{ request('keyword') }}">

                        <div class="filter-grid px-0 pt-0 pb-3">
                            <div class="span-6">
                                <label class="field-label">Tanggal Terima</label>
                                <input type="date" name="receipt_date" class="form-control form-control-sm"
                                    value="{{ old('receipt_date', now()->format('Y-m-d')) }}" required>
                            </div>

                            <div class="span-6">
                                <label class="field-label">No Dokumen Receiving</label>
                                <input type="text" name="document_number" class="form-control form-control-sm"
                                    value="{{ old('document_number', $selectedShipment->delivery_note_number) }}" required>
                            </div>
                        </div>

                        <div class="filter-grid px-0 pt-0 pb-3">
                            <div class="span-6">
                                <label class="field-label">Lampiran</label>
                                <input type="file" name="attachment" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf">
                            </div>

                            <div class="span-6">
                                <label class="field-label">Catatan</label>
                                <input type="text" name="note" class="form-control form-control-sm" value="{{ old('note') }}" placeholder="Opsional">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered ui-table">
                                <thead>
                                    <tr>
                                        <th>PO</th>
                                        <th>Item</th>
                                        <th>Harga PO</th>
                                        <th>Harga Invoice</th>
                                        <th>Total Invoice</th>
                                        <th>Qty Dikirim</th>
                                        <th>Sudah Diterima</th>
                                        <th>Sisa Bisa Diterima</th>
                                        <th style="min-width: 150px;">Qty Diterima Sekarang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shipmentItems as $item)
                                        <tr>
                                            <td>{{ $item->po_number }}</td>
                                            <td>
                                                <div class="doc-number">{{ $item->item_code }}</div>
                                                <div class="doc-meta">{{ $item->item_name }}</div>
                                            </td>
                                            <td>{{ $item->unit_price !== null ? \App\Support\NumberFormatter::trim($item->unit_price) : '-' }}</td>
                                            <td>{{ $item->invoice_unit_price !== null ? \App\Support\NumberFormatter::trim($item->invoice_unit_price) : '-' }}</td>
                                            <td>{{ $item->invoice_line_total !== null ? \App\Support\NumberFormatter::trim($item->invoice_line_total) : '-' }}</td>
                                            <td>{{ \App\Support\NumberFormatter::trim($item->shipped_qty) }}</td>
                                            <td>{{ \App\Support\NumberFormatter::trim($item->shipment_received_qty) }}</td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    {{ \App\Support\NumberFormatter::trim($item->shipment_outstanding_qty) }}
                                                </span>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" max="{{ $item->shipment_outstanding_qty }}"
                                                    name="received_qty[{{ $item->shipment_item_id }}]"
                                                    value="{{ old('received_qty.' . $item->shipment_item_id) }}"
                                                    class="form-control form-control-sm"
                                                    placeholder="Isi jika datang">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('receiving.pending', ['supplier_id' => request('supplier_id'), 'document_number' => request('document_number'), 'keyword' => request('keyword')]) }}"
                                class="btn btn-outline-secondary btn-sm">Kembali</a>
                            <button type="submit" class="btn btn-success btn-sm">Posting Receiving</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection