@extends('layouts.erp')
@php($title='Traceability')
@php($header='Traceability PO -> Shipment -> Receiving')
@section('content')
<div class="card card-outline card-primary mb-3"><div class="card-body">
<form method="GET" class="row g-2">
<div class="col-md-4"><input name="po_number" class="form-control" placeholder="Cari PO Number" value="{{ request('po_number') }}"></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Cari</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>PO</th><th>Tgl PO</th><th>Supplier</th><th>Shipment</th><th>GR</th><th>Status</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->po_number }}</td><td>{{ $r->po_date }}</td><td>{{ $r->supplier_name }}</td><td>{{ $r->shipment_number }} / {{ $r->shipment_date }}</td><td>{{ $r->gr_number }} / {{ $r->receipt_date }}</td><td>{{ $r->status }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
