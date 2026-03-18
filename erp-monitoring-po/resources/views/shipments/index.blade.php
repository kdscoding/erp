@extends('layouts.erp')
@php($title='Shipment Tracking')
@php($header='Shipment Tracking')
@section('content')
<div class="card card-primary card-outline mb-3"><div class="card-body">
<form method="POST" action="{{ route('shipments.store') }}" class="row g-2">@csrf
<div class="col-md-4"><select name="purchase_order_id" class="form-select" required>@foreach($pos as $po)<option value="{{ $po->id }}">{{ $po->po_number }}</option>@endforeach</select></div>
<div class="col-md-3"><input type="date" name="shipment_date" class="form-control" required></div>
<div class="col-md-3"><input type="date" name="eta_date" class="form-control" placeholder="ETA"></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>No Shipment</th><th>PO</th><th>Tgl</th><th>ETA</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->shipment_number }}</td><td>{{ $r->po_number }}</td><td>{{ $r->shipment_date }}</td><td>{{ $r->eta_date }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
