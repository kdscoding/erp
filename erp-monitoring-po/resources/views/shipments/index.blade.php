@extends('layouts.erp')
@php($title='Shipment Tracking')
@php($header='Shipment Tracking')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card card-primary card-outline mb-3"><div class="card-body">
<form method="POST" action="{{ route('shipments.store') }}" class="row g-2">@csrf
<div class="col-md-4"><select name="purchase_order_id" class="form-select" required><option value="">Pilih PO</option>@foreach($pos as $po)<option value="{{ $po->id }}">{{ $po->po_number }} ({{ $po->status }})</option>@endforeach</select></div>
<div class="col-md-3"><input type="date" name="shipment_date" class="form-control" required></div>
<div class="col-md-3"><input type="date" name="eta_date" class="form-control" placeholder="ETA"></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>No Shipment</th><th>PO</th><th>Supplier</th><th>Tgl</th><th>ETA</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->shipment_number }}</td><td>{{ $r->po_number }}</td><td>{{ $r->supplier_name }}</td><td>{{ \Carbon\Carbon::parse($r->shipment_date)->format('d-m-Y') }}</td><td>{{ $r->eta_date ? \Carbon\Carbon::parse($r->eta_date)->format('d-m-Y') : '-' }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
