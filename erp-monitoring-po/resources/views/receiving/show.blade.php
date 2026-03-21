@extends('layouts.erp')
@php($title = 'Detail Goods Receipt')
@php($header = 'Detail Goods Receipt')

@section('content')
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Dokumen GR {{ $receipt->gr_number }}</h3>
        <a href="{{ route('receiving.history') }}" class="btn btn-sm btn-light">Kembali ke Riwayat GR</a>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-3"><strong>No GR</strong><div>{{ $receipt->gr_number }}</div></div>
            <div class="col-md-3"><strong>Tanggal Terima</strong><div>{{ \Carbon\Carbon::parse($receipt->receipt_date)->format('d-m-Y') }}</div></div>
            <div class="col-md-3"><strong>Supplier</strong><div>{{ $receipt->supplier_name }}</div></div>
            <div class="col-md-3"><strong>Status</strong><div><span class="badge bg-success">{{ $receipt->status }}</span></div></div>
            <div class="col-md-3"><strong>Shipment</strong><div>{{ $receipt->shipment_number ?: '-' }}</div></div>
            <div class="col-md-3"><strong>Delivery Note</strong><div>{{ $receipt->delivery_note_number ?: '-' }}</div></div>
            <div class="col-md-3"><strong>PO</strong><div>{{ $receipt->po_number }}</div></div>
            <div class="col-md-3"><strong>Warehouse</strong><div>{{ $receipt->warehouse_name ?: '-' }}</div></div>
            <div class="col-md-3"><strong>Penerima</strong><div>{{ $receipt->receiver_name ?: '-' }}</div></div>
            <div class="col-md-3"><strong>No Dokumen</strong><div>{{ $receipt->document_number ?: '-' }}</div></div>
            <div class="col-md-6"><strong>Catatan</strong><div>{{ $receipt->remark ?: '-' }}</div></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Detail Item Goods Receipt</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty Shipment</th>
                    <th>Qty Diterima</th>
                    <th>Accepted</th>
                    <th>Rejected</th>
                    <th>Variance</th>
                    <th>Total Received PO</th>
                    <th>Outstanding PO</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td><strong>{{ $item->item_code }}</strong><br>{{ $item->item_name }}</td>
                        <td>{{ number_format($item->shipped_qty ?? 0, 2, ',', '.') }} {{ $item->unit_name }}</td>
                        <td>{{ number_format($item->received_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                        <td>{{ number_format($item->accepted_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                        <td>{{ number_format($item->rejected_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                        <td>{{ number_format($item->qty_variance, 2, ',', '.') }}</td>
                        <td>{{ number_format($item->total_po_received_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                        <td>{{ number_format($item->outstanding_qty, 2, ',', '.') }} {{ $item->unit_name }}</td>
                        <td>{{ $item->note ?: $item->remark ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
