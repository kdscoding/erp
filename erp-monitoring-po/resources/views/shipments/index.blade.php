@extends('layouts.erp')
@php($title='Shipment Tracking')
@php($header='Shipment Tracking')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card card-outline card-primary mb-3"><div class="card-body">
<form method="GET" class="row g-2 align-items-end">
<div class="col-md-3"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select"><option value="">Semua Supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->supplier_name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Cari PO / Item / Supplier</label><input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control" placeholder="PO, item code, item name, supplier"></div>
<div class="col-md-3"><label class="form-label">Cari Delivery Note</label><input type="text" name="delivery_note_number" value="{{ request('delivery_note_number') }}" class="form-control" placeholder="No surat jalan"></div>
<div class="col-md-2"><button class="btn btn-outline-primary w-100">Cari Kandidat</button></div>
</form></div></div>

<div class="card card-primary card-outline mb-3"><div class="card-body">
<form method="POST" action="{{ route('shipments.store') }}" class="row g-2">@csrf
<div class="col-md-4"><label class="form-label">Pilih PO Internal</label><select name="purchase_order_id" class="form-select" required><option value="">Pilih PO</option>@foreach($pos as $po)<option value="{{ $po->id }}">{{ $po->po_number }} - {{ $po->supplier_name }} ({{ $po->status }})</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">No Delivery Note</label><input type="text" name="delivery_note_number" class="form-control" placeholder="No surat jalan supplier" required></div>
<div class="col-md-2"><label class="form-label">Tgl Shipment</label><input type="date" name="shipment_date" class="form-control" required></div>
<div class="col-md-2"><label class="form-label">ETA</label><input type="date" name="eta_date" class="form-control" placeholder="ETA"></div>
<div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100">Simpan</button></div>
<div class="col-md-4">
    <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" value="1" name="po_reference_missing" id="po_reference_missing">
        <label class="form-check-label" for="po_reference_missing">Dokumen supplier tidak mencantumkan nomor PO</label>
    </div>
</div>
<div class="col-md-8"><input type="text" name="supplier_remark" class="form-control" placeholder="Catatan supplier / alasan pemilihan PO internal"></div>
</form></div></div>

<div class="card mb-3"><div class="card-header"><h3 class="card-title">Kandidat PO untuk Dokumen Tanpa Nomor PO</h3></div><div class="card-body table-responsive p-0"><table class="table table-hover mb-0"><thead><tr><th>PO</th><th>Supplier</th><th>Status</th><th>Jumlah Item</th></tr></thead><tbody>@forelse($pos as $po)<tr><td>{{ $po->po_number }}</td><td>{{ $po->supplier_name }}</td><td>{{ $po->status }}</td><td>{{ $po->item_count }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">Tidak ada kandidat PO.</td></tr>@endforelse</tbody></table></div></div>

<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>No Shipment</th><th>PO</th><th>Supplier</th><th>Delivery Note</th><th>Tgl</th><th>ETA</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->shipment_number }}</td><td>{{ $r->po_number }}</td><td>{{ $r->supplier_name }}</td><td>{{ $r->delivery_note_number ?: '-' }}</td><td>{{ \Carbon\Carbon::parse($r->shipment_date)->format('d-m-Y') }}</td><td>{{ $r->eta_date ? \Carbon\Carbon::parse($r->eta_date)->format('d-m-Y') : '-' }}</td></tr>@endforeach</tbody></table></div></div>
<div class="mt-2">{{ $rows->links() }}</div>
@endsection
