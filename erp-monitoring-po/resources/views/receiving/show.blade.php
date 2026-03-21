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
            <div class="col-md-3"><strong>Status</strong><div><span class="badge {{ $receipt->status === 'Cancelled' ? 'bg-danger' : 'bg-success' }}">{{ \App\Support\TermCatalog::label('goods_receipt_status', $receipt->status, $receipt->status) }}</span></div></div>
            <div class="col-md-3"><strong>Shipment</strong><div>{{ $receipt->shipment_number ?: '-' }}</div></div>
            <div class="col-md-3"><strong>Delivery Note</strong><div>{{ $receipt->delivery_note_number ?: '-' }}</div></div>
            <div class="col-md-3"><strong>PO</strong><div>{{ $receipt->po_number }}</div></div>
            <div class="col-md-3"><strong>Warehouse</strong><div>{{ $receipt->warehouse_name ?: '-' }}</div></div>
            <div class="col-md-3"><strong>Penerima</strong><div>{{ $receipt->receiver_name ?: '-' }}</div></div>
            <div class="col-md-3"><strong>No Dokumen</strong><div>{{ $receipt->document_number ?: '-' }}</div></div>
            <div class="col-md-6"><strong>Catatan</strong><div>{{ $receipt->remark ?: '-' }}</div></div>
            @if ($receipt->cancel_reason)
                <div class="col-md-12"><strong>Alasan Batal</strong><div class="text-danger">{{ $receipt->cancel_reason }}</div></div>
            @endif
        </div>
    </div>
</div>

@if ($receipt->status === 'Posted')
<div class="card card-outline card-danger mb-3">
    <div class="card-header"><h3 class="card-title">Batalkan Goods Receipt</h3></div>
    <div class="card-body">
        <div class="alert alert-warning">Gunakan hanya jika posting GR salah. Sistem akan mengembalikan qty received ke shipment dan PO terkait.</div>
        <form method="POST" action="{{ route('receiving.cancel', $receipt->id) }}">
            @csrf
            @method('PATCH')
            <div class="mb-2">
                <label class="form-label">Alasan Pembatalan</label>
                <textarea name="cancel_reason" class="form-control form-control-sm" rows="3" required></textarea>
            </div>
            <button class="btn btn-danger btn-sm" onclick="return confirm('Batalkan GR ini dan kembalikan qty receiving?')">Batalkan GR</button>
        </form>
    </div>
</div>
@endif

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
                        <td>{{ \App\Support\NumberFormatter::trim($item->shipped_qty ?? 0) }} {{ $item->unit_name }}</td>
                        <td>{{ \App\Support\NumberFormatter::trim($item->received_qty) }} {{ $item->unit_name }}</td>
                        <td>{{ \App\Support\NumberFormatter::trim($item->accepted_qty) }} {{ $item->unit_name }}</td>
                        <td>{{ \App\Support\NumberFormatter::trim($item->rejected_qty) }} {{ $item->unit_name }}</td>
                        <td>{{ \App\Support\NumberFormatter::trim($item->qty_variance) }}</td>
                        <td>{{ \App\Support\NumberFormatter::trim($item->total_po_received_qty) }} {{ $item->unit_name }}</td>
                        <td>{{ \App\Support\NumberFormatter::trim($item->outstanding_qty) }} {{ $item->unit_name }}</td>
                        <td>{{ $item->note ?: $item->remark ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
