@extends('layouts.erp')
@php($title = 'Detail Shipment')
@php($header = 'Detail Shipment')

@section('content')
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Dokumen Shipment {{ $shipment->shipment_number }}</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('shipments.history', ['focus' => $shipment->id]) }}" class="btn btn-sm btn-light">Kembali ke
                    Riwayat</a>
                @if ($shipment->status === \App\Support\DocumentTermCodes::SHIPMENT_DRAFT)
                    <a href="{{ route('shipments.edit', $shipment->id) }}" class="btn btn-sm btn-primary">Edit Draft</a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <strong>No Shipment</strong>
                    <div>{{ $shipment->shipment_number }}</div>
                </div>
                <div class="col-md-3">
                    <strong>Supplier</strong>
                    <div>{{ $shipment->supplier_name }}</div>
                </div>
                <div class="col-md-2">
                    <strong>Status</strong>
                    <div><x-status-badge :status="$shipment->status" scope="shipment" /></div>
                </div>
                <div class="col-md-2">
                    <strong>Tanggal Dokumen</strong>
                    <div>{{ \Carbon\Carbon::parse($shipment->shipment_date)->format('d-m-Y') }}</div>
                </div>
                <div class="col-md-2">
                    <strong>Delivery Note</strong>
                    <div>{{ $shipment->delivery_note_number ?: '-' }}</div>
                </div>

                <div class="col-md-4">
                    <strong>No Invoice</strong>
                    <div>{{ $shipment->invoice_number ?: '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Tanggal Invoice</strong>
                    <div>
                        {{ $shipment->invoice_date ? \Carbon\Carbon::parse($shipment->invoice_date)->format('d-m-Y') : '-' }}
                    </div>
                </div>
                <div class="col-md-4">
                    <strong>Currency</strong>
                    <div>{{ $shipment->invoice_currency ?: '-' }}</div>
                </div>

                <div class="col-12">
                    <strong>Catatan</strong>
                    <div>{{ $shipment->supplier_remark ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Item Dalam Dokumen Shipment</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
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
                            <td><strong>{{ $line->item_code }}</strong><br>{{ $line->item_name }}</td>
                            <td>{{ $line->po_unit_price !== null ? \App\Support\NumberFormatter::trim($line->po_unit_price) : '-' }}
                            </td>
                            <td>{{ $line->invoice_unit_price !== null ? \App\Support\NumberFormatter::trim($line->invoice_unit_price) : '-' }}
                            </td>
                            <td>{{ $line->invoice_line_total !== null ? \App\Support\NumberFormatter::trim($line->invoice_line_total) : '-' }}
                            </td>
                            <td>{{ \App\Support\NumberFormatter::trim($line->shipped_qty) }}</td>
                            <td>{{ \App\Support\NumberFormatter::trim($line->received_qty) }}</td>
                            <td>{{ \App\Support\NumberFormatter::trim(max(0, $line->shipped_qty - $line->received_qty)) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
