@extends('layouts.erp')
@php($title='Goods Receiving')
@php($header='Goods Receiving')
@section('content')
<div class="card card-success card-outline mb-3"><div class="card-body">
<form method="POST" action="{{ route('receiving.store') }}" class="row g-2">@csrf
<div class="col-md-5"><select name="purchase_order_item_id" class="form-select" required>@foreach($poItems as $i)<option value="{{ $i->id }}">{{ $i->po_number }} | Item #{{ $i->item_id }} | OS: {{ $i->outstanding_qty }}</option>@endforeach</select></div>
<div class="col-md-2"><input type="date" name="receipt_date" class="form-control" required></div>
<div class="col-md-2"><input type="number" step="0.01" name="received_qty" class="form-control" placeholder="Qty" required></div>
<div class="col-md-3"><button class="btn btn-success w-100">Post GR</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-hover text-nowrap mb-0"><thead><tr><th>GR</th><th>PO</th><th>Tgl</th></tr></thead><tbody>@foreach($rows as $r)<tr><td>{{ $r->gr_number }}</td><td>{{ $r->po_number }}</td><td>{{ $r->receipt_date }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
